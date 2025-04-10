<?php

use Neon2\JobBoard;
use Neon2\Payment\PaymentGate;
use Neon2\Payment\PaymentService;
use Neon2\Payment\PaymentStatus;
use Neon2\Payment\Provider\Stripe;
use Neon2\Payment\Provider\StripeWebhook;
use Neon2\Payment\Provider\TestPaymentProvider;
use Neon2\Payment\Provider\TestPaymentWebhook;

require '../../app/vendor/autoload.php';

$assetName = \parse_url($_SERVER['REQUEST_URI'], \PHP_URL_PATH);
if ($assetName === '/') {
    $assetName = '/index.html';
}

$gate = new PaymentGate();
$jobBoardGate = new \Neon2\JobBoard\JobBoardGate();
$jobBoardView = new \Neon2\JobBoardView($jobBoardGate);
$board = new JobBoard($gate, $jobBoardGate, testMode:true);
if ($board->testMode()) {
    $paymentProvider = new TestPaymentProvider();
    $paymentWebhook = new TestPaymentWebhook($paymentProvider);
} else {
    $paymentProvider = new Stripe('sk_test_51RBWn0Rf5n1iRahJzOJ6tJvWNO6fwKBaN7O2uVdhSGxVFVAsCeBTDgL13UWJ3VEGGLJc1ntyC5oDq5QQbVByEY8j00aluGGN0L');
    $paymentWebhook = new StripeWebhook('whsec_W5t2VrjF8hVHk3Fp6bM4scZ5HyX9y4xB');
}
$paymentService = new PaymentService($gate, $paymentProvider);

if (($_SERVER['CONTENT_TYPE'] ?? null) === 'application/json') {
    $body = \json_decode(\file_get_contents('php://input'), true);
} else {
    $body = [];
}
if ($assetName === '/neon2/job-offers' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $createdJobOffer = $board->addJobOffer($body['jobOfferTitle'], $body['jobOfferPlan']);
    \http_response_code(201);
    \header('Content-Type: application/json');
    echo \json_encode($createdJobOffer);
    return;
}

if ($assetName === '/neon2/job-offers/payment' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment = $paymentService->preparePayment(2000, $body['paymentId']);
    \http_response_code(201);
    \header('Content-Type: application/json');
    echo \json_encode([
        'providerReady' => $payment->providerReady,
        'paymentId'     => $payment->paymentId,
        'paymentToken'  => $payment->paymentToken,
    ]);
    return;
}

if ($assetName === '/neon2/job-offers' && $_SERVER['REQUEST_METHOD'] === 'PATCH') {
    $jobBoardGate->editJobOffer($body['jobOfferId'], $body['jobOfferTitle']);
    \http_response_code(201);
    \header('Content-Type: application/json');
    return;
}

if ($assetName === '/neon2/webhook' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $paymentUpdate = $paymentWebhook->acceptPaymentUpdate(
        \file_get_contents('php://input'),
        $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '');
    if ($paymentUpdate) {
        $board->paymentUpdate($paymentUpdate);
    }
    return;
}
if ($assetName === '/neon2/status' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    parse_str($_SERVER['QUERY_STRING'], $query);
    $status = $gate->readPaymentStatus($query['paymentId']);
    header('Content-Type: application/json');
    echo \json_encode(match ($status) {
        PaymentStatus::Completed => 'paymentComplete',
        PaymentStatus::Failed    => 'paymentFailed',
        PaymentStatus::Awaiting  => 'awaitingPayment',
    });
    return;
}

if ($assetName === '/index.html') {
    echo $jobBoardView->jobBoardView($board->testMode());
    return;
}

$root = \realPath(__DIR__ . '/../../web/dist');
if (\str_starts_with($assetName, '/neon2/static/')) {
    $assetName = \substr($assetName, \strLen('/neon2/static'));
}
$asset = \realPath($root . $assetName);
if ($asset === false || !str_starts_with($asset, $root) || !file_exists($asset)) {
    \http_response_code(404);
    exit;
}
\header('Content-Type: ' . mime($asset));
\readFile($asset);

function mime(string $asset): string {
    if (\str_ends_with($asset, '.js')) {
        return 'application/javascript';
    }
    return \mime_content_type($asset);
}
