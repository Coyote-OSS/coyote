<?php

function mime(string $asset): string
{
    if (\str_ends_with($asset, '.js')) {
        return 'application/javascript';
    }
    return \mime_content_type($asset);
}

$root = \realPath(__DIR__ . '/../../web/dist');
$assetName = $_SERVER['REQUEST_URI'];
if ($assetName === '/') {
    $assetName = '/index.html';
}
$asset = \realPath($root . $assetName);
if ($asset === false || !str_starts_with($asset, $root) || !file_exists($asset)) {
    http_response_code(404);
    exit;
}
\header('Content-Type: ' . mime($asset));
\readFile($asset);
