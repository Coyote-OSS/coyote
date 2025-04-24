<?php
namespace Neon2\Laravel;

use Illuminate\Http\Request;
use Illuminate\Support\Facades;
use Illuminate\Support\ServiceProvider;
use Neon\Currency;
use Neon\LegalForm;
use Neon\Rate;
use Neon\WorkExperience;
use Neon\WorkMode;
use Neon2\JobBoard\InvoiceInformation;
use Neon2\JobBoardInteractor;
use Neon2\Request\ApplicationMode;
use Neon2\Request\HiringType;
use Neon2\Request\JobOfferSubmit;

class RouteServiceProvider extends ServiceProvider {
    public function boot(): void {
        Facades\Route::post('/neon2/job-offers', function (JobBoardInteractor $listener) {
            $createdJobOffer = $listener->createJobOffer(
                request()->get('jobOfferPlan'),
                $this->requestJobOfferSubmit(request()));
            return response()->json($createdJobOffer, status:201);
        });
        Facades\Route::patch('/neon2/job-offers', function (JobBoardInteractor $listener) {
            $listener->updateJobOffer(
                request()->get('jobOfferId'),
                $this->requestJobOfferSubmit(request()));
            return response()->json([], status:201);
        });
        Facades\Route::post('/neon2/job-offers/payment', function (JobBoardInteractor $listener) {
            $preparedPayment = $listener->preparePayment(
                request()->get('userId'),
                request()->get('paymentId'),
                2000,
                $this->requestInvoiceInfo(request()));
            return response()->json($preparedPayment, status:201);
        });
        Facades\Route::post('/neon2/job-offers/redeem-bundle', function (JobBoardInteractor $listener) {
            $listener->redeemBundle(
                request()->get('jobOfferId'),
                request()->get('userId'));
            return response()->json([], status:201);
        });
        Facades\Route::post('/neon2/webhook', function (JobBoardInteractor $listener) {
            $listener->paymentWebhook(
                \file_get_contents('php://input'),
                $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '');
        });
        Facades\Route::get('/neon2/status', function (JobBoardInteractor $listener) {
            $status = $listener->paymentStatus(request()->query->get('paymentId'));
            return response()->json($status);
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
            $request->get('jobOfferLocations'),
            $request->get('jobOfferTagNames'),
            WorkMode::from($request->get('jobOfferWorkMode')),
            LegalForm::from($request->get('jobOfferLegalForm')),
            WorkExperience::from($request->get('jobOfferExperience')),
            ApplicationMode::from($request->get('jobOfferApplicationMode')),
            $request->get('jobOfferApplicationEmail'),
            $request->get('jobOfferApplicationExternalAts'),
            $request->get('jobOfferCompanyName'),
            $request->get('jobOfferCompanyLogoUrl'),
            $request->get('jobOfferCompanyWebsiteUrl'),
            $request->get('jobOfferCompanyDescription'),
            $request->get('jobOfferCompanyPhotoUrl'),
            $request->get('jobOfferCompanyVideoUrl'),
            $request->get('jobOfferCompanySizeLevel'),
            $request->get('jobOfferCompanyFundingYear'),
            $request->get('jobOfferCompanyAddress'),
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
}
