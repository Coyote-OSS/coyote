<?php

require '../../app/vendor/autoload.php';

function mime(string $asset): string
{
    if (\str_ends_with($asset, '.js')) {
        return 'application/javascript';
    }
    return \mime_content_type($asset);
}

function includeJsArguments(array $jobOffers): string
{
    $backendInput = \json_encode($jobOffers, \JSON_THROW_ON_ERROR);
    return "<script>var backendInput = $backendInput;</script>";
}

$assetName = $_SERVER['REQUEST_URI'];
if ($assetName === '/') {
    $assetName = '/index.html';
}

$jobBoard = new \Neon2\JobBoard\JobBoard();

if ($assetName === '/job-offers' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $createdJobOffer = $jobBoard->addJobOffer($_POST['jobOfferTitle'], $_POST['jobOfferPlan']);
    \http_response_code(201);
    \header('Content-Type: application/json');
    echo \json_encode($createdJobOffer);
    return;
}

if ($assetName === '/job-offers/payment' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $jobBoard->initiateJobOfferPayment($_POST['jobOfferId']);
    \http_response_code(201);
    \header('Content-Type: application/json');
    return;
}

if ($assetName === '/job-offers' && $_SERVER['REQUEST_METHOD'] === 'PATCH') {
    [$_PUT] = \request_parse_body();
    $jobBoard->editJobOffer($_PUT['jobOfferId'], $_PUT['jobOfferTitle']);
    \http_response_code(201);
    return;
}

$root = \realPath(__DIR__ . '/../../web/dist');
$asset = \realPath($root . $assetName);
if ($asset === false || !str_starts_with($asset, $root) || !file_exists($asset)) {
    \http_response_code(404);
    exit;
}
\header('Content-Type: ' . mime($asset));
\readFile($asset);

if ($assetName === '/index.html') {
    echo includeJsArguments([
        'jobOffers' => $jobBoard->listJobOffers(),
    ]);
}
