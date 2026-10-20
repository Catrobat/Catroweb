<?php

require_once dirname(__DIR__).'/vendor/autoload.php';

use App\Storage\PublicImageSourceResolver;
use WebPConvert\WebPConvert;

// Server rewrite input is untrusted: this script is also directly reachable.
$source = $_GET['source'] ?? '';
if (!is_string($source) || '' === $source) {
  http_response_code(400);
  exit;
}

$paths = (new PublicImageSourceResolver())->resolve(__DIR__, $source);
if (null === $paths) {
  header('Cache-Control: private, no-store');
  http_response_code(404);
  exit;
}
$source = $paths['source'];
$destination = $paths['destination'];
header('X-Content-Type-Options: nosniff');

$options = [
  // failure handling
  'fail' => 'original',   // ('original' | 404' | 'throw' | 'report')
  'fail-when-fail-fails' => 'throw',      // ('original' | 404' | 'throw' | 'report')

  // options influencing the decision process of what to be served
  'reconvert' => false,         // if true, existing (cached) image will be discarded
  'serve-original' => false,    // if true, the original image will be served rather than the converted
  'show-report' => false,       // if true, a report will be output rather than the raw image

  // warning handling
  'suppress-warnings' => true,            // if you set to false, make sure that warnings are not echoed out!

  // options when serving an image (be it the webp or the original, if the original is smaller than the webp)
  'serve-image' => [
    'headers' => [
      'cache-control' => true,
      'content-length' => true,
      'content-type' => true,
      'expires' => false,
      'last-modified' => true,
      'vary-accept' => false,
    ],
    'cache-control-header' => 'public, max-age=31536000',
  ],

  // redirect tweak
  'redirect-to-self-instead-of-serving' => false,  // if true, a redirect will be issues rather than serving

  'convert' => [
    // options for converting goes here
    'quality' => 'auto',
  ],
];

WebPConvert::serveConverted($source, $destination, $options);
