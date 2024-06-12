<?php

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

use WebPConvert\WebPConvert;

// Absolute file path to source file. Comes from the server config! (.htaccess no used due performance reasons)
$source = $_GET['source'];

// Store the converted images besides the original images (other options are available!)
$destination = substr($source, 0, strrpos($source, '.')).'.webp';

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
