<?php

$finder = PhpCsFixer\Finder::create()
  ->in(__DIR__)
  ->exclude(['var', 'node_modules', 'vendor']);

$config = new PhpCsFixer\Config();
$config
  ->setRiskyAllowed(true)
  ->setRules([
    '@PSR1' => true,
    '@PSR2' => true,
    '@PSR12' => true,
    '@PhpCsFixer' => true,
    '@Symfony' => true,
    '@DoctrineAnnotation' => true,
    'strict_param' => true,
  ])
  ->setFinder($finder)
  ->setUsingCache(true)
  ->setIndent('  ');

return $config;