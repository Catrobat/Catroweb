<?php

$finder = PhpCsFixer\Finder::create()
  ->exclude(['Migrations', 'Resources'])
  ->in(['src', 'tests'])
  ->append([
      __DIR__.'/php-cs-fixer',
  ]);

$config = new PhpCsFixer\Config();
$config
  ->setRiskyAllowed(true)
  ->setRules([
    '@PSR2'               => true,
    '@PhpCsFixer'         => true,
    '@Symfony'            => true,
    '@DoctrineAnnotation' => true,
    'strict_param'        => true,
  ])
  ->setFinder($finder)
  ->setUsingCache(true)
  ->setIndent('  ');

return $config;