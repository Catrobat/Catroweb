<?php

namespace Catrobat\WebBundle\Translation;


use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Translation\MessageSelector;


class CatrowebTranslator extends Translator
{

  public function __construct(ContainerInterface $container, MessageSelector $selector, $loaderIds = array(), array $options = array())
  {
    parent::__construct($container, $selector, $loaderIds, $options);
  }

  public function addResource($format, $resource, $locale, $domain = "catroweb")
  {
    if (file_exists("./../Resources/translations/catroweb.en.yml")) {
      $domain = "catroweb";
    } else {
      $domain = "messages";
    }
    parent::addResource($format, $resource, $locale, $domain);
  }

  public function trans($id, array $parameters = array(), $domain = "catroweb", $locale = null)
  {
    if (file_exists("./../Resources/translations/catroweb.en.yml")) {
      $domain = "catroweb";
    } else {
      $domain = "messages";
    }
    return parent::trans($id, $parameters, $domain, $locale);
  }

  public function transChoice($id, $number, array $parameters = array(), $domain = "catroweb", $locale = null)
  {
    if (file_exists("./../Resources/translations/catroweb.en.yml")) {
      $domain = "catroweb";
    } else {
      $domain = "messages";
    }
    return parent::transChoice($id, $number, $parameters, $domain, $locale);
  }
} 