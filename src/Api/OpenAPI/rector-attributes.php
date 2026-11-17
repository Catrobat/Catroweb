<?php

declare(strict_types=1);

// Run by code-generation.sh: turns the docblock annotations the generator writes (@Assert\..., JMS
// @Type/@SerializedName) into PHP attributes. Neither Symfony's validator nor JMS reads docblock
// annotations (doctrine/annotations is not installed), so without this no model constraint holds.

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;

return RectorConfig::configure()
  // Request models only. Response models keep their annotations on purpose: JMS types them from
  // their PHP types, and handlers fill some with plain nested arrays (e.g. CodeViewResponse) that a
  // read #[Type('array<Model>')] would refuse to serialize.
  ->withPaths([__DIR__.'/Server/Model/*Request.php'])
  ->withAttributesSets(symfony: true, jms: true)
  // Not part of Rector's Symfony set (it takes nested constraints)
  ->withConfiguredRule(AnnotationToAttributeRector::class, [
    new AnnotationToAttribute('Symfony\Component\Validator\Constraints\All'),
  ])
;
