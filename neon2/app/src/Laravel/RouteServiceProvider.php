<?php
namespace Neon2\Laravel;

use Coyote\Rules\VatIdRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades;
use Illuminate\Support\ServiceProvider;
use Neon2\Coyote\Integration;
use Neon2\JobBoard\Currency;
use Neon2\JobBoard\InvoiceInformation;
use Neon2\JobBoard\LegalForm;
use Neon2\JobBoard\Rate;
use Neon2\JobBoard\WorkExperience;
use Neon2\Payment\PaymentStatus;
use Neon2\Request\ApplicationMode;
use Neon2\Request\HiringType;
use Neon2\Request\JobOfferLocation;
use Neon2\Request\JobOfferSubmit;

class RouteServiceProvider extends ServiceProvider {
    public function boot(): void {
        Facades\Route::middleware(['web'])->group(function () {
            Facades\Route::post('/neon2/job-offers', function (Integration $integration) {
                $createdJobOffer = $integration->createJobOffer(
                    request()->get('jobOfferPlan'),
                    $this->requestJobOfferSubmit(request()));
                return response()->json($createdJobOffer, status:201);
            });
            Facades\Route::patch('/neon2/job-offers', function (Integration $integration) {
                $integration->updateJobOffer(
                    request()->get('jobOfferId'),
                    $this->requestJobOfferSubmit(request()));
                return response()->json([], status:201);
            });
            Facades\Route::post('/neon2/job-offers/payment', function (Integration $integration) {
                $invoiceInfo = $this->requestInvoiceInfo(request());
                $rule = new VatIdRule($invoiceInfo->countryCode);
                if ($invoiceInfo->vatId) {
                    if (!$rule->passes('', $invoiceInfo->vatId)) {
                        return response()->json(['status' => 'failedInvalidVatId'], status:422);
                    }
                }
                $preparedPayment = $integration->preparePayment(
                    auth()->id(),
                    request()->get('paymentId'),
                    $invoiceInfo);
                return response()->json([
                    'status'          => 'success',
                    'preparedPayment' => $preparedPayment,
                ], status:201);
            });
            Facades\Route::post('/neon2/job-offers/redeem-bundle', function (Integration $integration) {
                $integration->redeemBundle(
                    auth()->id(),
                    request()->get('jobOfferId'));
                return response()->json([], status:201);
            });
            Facades\Route::post('/neon2/webhook', function (Integration $integration) {
                $integration->paymentWebhook(
                    \file_get_contents('php://input'),
                    $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '');
            });
            Facades\Route::get('/neon2/status', function (Integration $integration) {
                $paymentId = request()->query->get('paymentId');
                return response()->json(match ($integration->paymentStatus($paymentId)) {
                    PaymentStatus::Completed => 'paymentComplete',
                    PaymentStatus::Failed    => 'paymentFailed',
                    PaymentStatus::Awaiting  => 'awaitingPayment',
                });
            });
        });
    }

    private function requestJobOfferSubmit(Request $request): JobOfferSubmit {
        return new JobOfferSubmit(
            $request->get('jobOfferTitle'),
            $request->get('jobOfferDescription'),
            $request->get('jobOfferSalaryRangeFrom'),
            $request->get('jobOfferSalaryRangeTo'),
            $request->get('jobOfferSalaryIsNet'),
            Currency::from($request->get('jobOfferSalaryCurrency')),
            Rate::from($request->get('jobOfferSalaryRate')),
            \array_map(
                $this->requestLocation(...),
                $request->get('jobOfferLocations')),
            $request->get('jobOfferTagNames'),
            $request->get('jobOfferTagPriorities'),
            $request->get('jobOfferWorkModeRemoteRange'),
            LegalForm::from($request->get('jobOfferLegalForm')),
            WorkExperience::from($request->get('jobOfferExperience')),
            ApplicationMode::from($request->get('jobOfferApplicationMode')),
            $request->get('jobOfferApplicationEmail'),
            $request->get('jobOfferApplicationExternalAts'),
            $request->get('jobOfferCompanyName'),
            $request->get('jobOfferCompanyLogoUrl'),
            $request->get('jobOfferCompanyWebsiteUrl'),
            $request->get('jobOfferCompanyDescription'),
            $request->get('jobOfferCompanyPhotoUrls'),
            $request->get('jobOfferCompanyVideoUrl'),
            $request->get('jobOfferCompanySizeLevel'),
            $request->get('jobOfferCompanyFundingYear'),
            $this->requestLocation($request->get('jobOfferCompanyAddress')),
            HiringType::from($request->get('jobOfferCompanyHiringType')));
    }

    private function requestInvoiceInfo(Request $request): InvoiceInformation {
        return new InvoiceInformation(
            $request->get('invoiceVatId'),
            $request->get('invoiceCountryCode'),
            $request->get('invoiceCompanyName'),
            $request->get('invoiceCompanyAddress'),
            $request->get('invoiceCompanyPostalCode'),
            $request->get('invoiceCompanyCity'));
    }

    private function requestLocation(?array $locationFields): ?JobOfferLocation {
        if ($locationFields === null) {
            return null;
        }
        return new JobOfferLocation(
            $locationFields['latitude'],
            $locationFields['longitude'],
            $locationFields['city'],
            $locationFields['streetName'],
            $locationFields['streetNumber'],
            $locationFields['countryCode'],
            $locationFields['postalCode']);
    }
}
