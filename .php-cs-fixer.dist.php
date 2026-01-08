<?php

use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

$finder = PhpCsFixer\Finder::create()
  ->in(__DIR__)
    ->exclude(['var', 'node_modules', 'vendor'])
    ->notPath('config/reference.php');

$config = new PhpCsFixer\Config();
$config
  ->setRiskyAllowed(true)
  ->setParallelConfig(ParallelConfigFactory::detect())
  ->setRules([
    '@PSR1' => true,
    '@PSR2' => true,
    '@PSR12' => true,
    '@PhpCsFixer' => true,
    '@Symfony' => true,
    'strict_param' => true,
  ])
  ->setFinder($finder)
  ->setIndent('  ');

return $config;
