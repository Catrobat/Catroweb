<?php

$finder = PhpCsFixer\Finder::create()
  ->exclude(['Migrations', 'Resources'])
  ->in('src');

return PhpCsFixer\Config::create()
  ->setRules([
    '@PSR2'               => true,
    '@PhpCsFixer'         => true,
    '@Symfony'            => true,
    '@DoctrineAnnotation' => true,
    'strict_param'        => true,
    'braces'              => ['position_after_control_structures'   => 'next',
                              'position_after_anonymous_constructs' => 'next'],
//    'phpdoc_to_return_type' => true, EXPERIMENTAL
//    'phpdoc_to_param_type'  => true, EXPERIMENTAL
  ])
  ->setFinder($finder)
  ->setUsingCache(true)
  ->setIndent('  ');