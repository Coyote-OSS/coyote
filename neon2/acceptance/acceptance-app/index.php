<?php

require '../../app/vendor/autoload.php';

$assetName = $_SERVER['REQUEST_URI'];
if ($assetName === '/') {
    $assetName = '/index.html';
}

$http = new \Neon2\HttpIntegration(new \Neon2\JobBoard\JobBoardGate());

if (($_SERVER['CONTENT_TYPE'] ?? null) === 'application/json') {
    $body = \json_decode(\file_get_contents('php://input'), true);
} else {
    $body = [];
}
if ($assetName === '/neon2/job-offers' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $createdJobOffer = $http->addAndReturnJobOffer($body['jobOfferTitle'], $body['jobOfferPlan']);
    \http_response_code(201);
    \header('Content-Type: application/json');
    echo \json_encode($createdJobOffer);
    return;
}

if ($assetName === '/neon2/job-offers/payment' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $http->initiateJobOfferPayment($body['jobOfferId']);
    \http_response_code(201);
    \header('Content-Type: application/json');
    return;
}

if ($assetName === '/neon2/job-offers' && $_SERVER['REQUEST_METHOD'] === 'PATCH') {
    $http->editJobOffer($body['jobOfferId'], $body['jobOfferTitle']);
    \http_response_code(201);
    \header('Content-Type: application/json');
    return;
}

if ($assetName === '/index.html') {
    echo $http->jobBoardView();
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
