<?php
namespace Coyote\Http\Controllers\Job;

use Coyote\Events\PaymentPaid;
use Coyote\Firm;
use Coyote\Http\Controllers\Controller;
use Coyote\Http\Requests\Job\PaymentRequest;
use Coyote\Payment;
use Coyote\Repositories\Eloquent\CountryRepository;
use Coyote\Repositories\Eloquent\CouponRepository;
use Coyote\Repositories\Eloquent\PaymentRepository;
use Coyote\Services\Invoice;
use Coyote\Services\Invoice\CalculatorFactory;
use Coyote\Services\Invoice\VatRateCalculator;
use Coyote\Services\UrlBuilder;
use Illuminate\Database;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\Webhook;

class PaymentController extends Controller
{
    private array $vatRates;

    public function __construct(
        private Invoice\Generator   $invoice,
        private Database\Connection $database,
        private CountryRepository   $country,
        private CouponRepository    $coupon)
    {
        parent::__construct();

        $this->middleware(
            function (Request $request, $next) {
                /** @var Payment $payment */
                $payment = $request->route('payment');
                if ($payment instanceof Payment) {
                    abort_if($payment->status_id === Payment::PAID, 404);
                }
                return $next($request);
            },
            ['except' => 'success'],
        );

        $this->breadcrumb->push('Praca', route('neon.jobOffer.list'));
        $this->vatRates = $this->country->vatRatesList();
    }

    public function index(Payment $payment): View
    {
        $this->breadcrumb->push($payment->job->title, UrlBuilder::job($payment->job));
        $this->breadcrumb->push('Płatność', route('job.payment', ['payment' => $payment]));
        $firm = $payment->job->firm ?? new Firm();
        $calculator = CalculatorFactory::payment($payment);
        return $this->view('job.payment', [
            'payment'    => $payment,
            'vat_rates'  => $this->vatRates,
            'vat_rate'   => $calculator->vatRate,
            'net_price'  => $calculator->netPrice(),
            'firm'       => $firm,
            'countries'  => $this->country->pluck('code', 'id'),
            'stripe_key' => config('services.stripe.key'),
        ]);
    }

    public function makePayment(PaymentRequest $request, Payment $payment): Response|array
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $coupon = $this->coupon->findBy('code', $request->input('coupon'));
        $payment->coupon_id = $coupon?->id;

        $calculator = CalculatorFactory::payment($payment);
        $calculator->setCoupon($coupon);
        $calculator->vatRate = new VatRateCalculator()->vatRate(
            $this->country->find($request->input('invoice.country_id')),
            $request->input('invoice.vat_id'));

        if ($payment->job->firm_id) {
            // update firm's VAT ID
            $payment->job->firm->fill($request->only(['invoice.vat_id', 'invoice.country_id'])['invoice']);
            $payment->job->firm->save();
        }

        $this->database->beginTransaction();

        try {
            // save invoice data. keep in mind that we do not setup invoice number until payment is done.
            $invoice = $this->invoice->create([
                ...$request->input('invoice', []),
                'user_id' => $this->auth->id,
            ],
                $payment,
                $calculator);
            $payment->invoice()->associate($invoice);
            $payment->save();
            $this->database->commit();
        } catch (\Exception $exception) {
            $this->database->rollBack();
            logger()->error($exception);
            if (app()->environment('production')) {
                app('sentry')->captureException($exception);
            }
            throw $exception;
        }

        if (!$calculator->grossPrice()) {
            event(new PaymentPaid($payment));
            session()->flash('success', trans('payment.success'));
            return response(UrlBuilder::job($payment->job, true), 201);
        }

        $intent = PaymentIntent::create([
            'amount'               => $payment->invoice->grossPrice() * 100,
            'currency'             => \strToLower($payment->invoice->currency->name),
            'metadata'             => ['id' => $payment->id],
            'payment_method_types' => [$request->input('payment_method')],
        ]);

        return [
            'token'       => $intent->client_secret,
            'success_url' => route('job.payment.success', [$payment]),
            'status_url'  => route('job.payment.status', [$payment]),
        ];
    }

    public function success(Payment $payment): RedirectResponse
    {
        return redirect()
            ->to(UrlBuilder::job($payment->job))
            ->with('success', trans('payment.pending'));
    }

    public function paymentStatus(PaymentRepository $repository): void
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        $payload = @file_get_contents('php://input');
        $event = Webhook::constructEvent($payload, $_SERVER['HTTP_STRIPE_SIGNATURE'], config('services.stripe.endpoint_secret'));
        if ($event->type === 'payment_intent.succeeded') {
            $paymentIntent = $event->data->object;
            $payment = $repository->findOrFail($paymentIntent->metadata->id);
            event(new PaymentPaid($payment));
        }
    }
}
