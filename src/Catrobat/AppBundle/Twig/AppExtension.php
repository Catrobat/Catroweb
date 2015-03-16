<?php

namespace Catrobat\AppBundle\Twig;

use Symfony\Component\Intl\Intl;
use Symfony\Component\HttpFoundation\Request;


class AppExtension extends \Twig_Extension
{
  public function getFunctions()
  {
    return array(
      'countriesList' => new \Twig_Function_Method($this, 'getCountriesList'),
    );
  }

  public function getCountriesList()
  {
    $request = Request::createFromGlobals();

    //todo: try to find a better solutions ... getLocale didn't work :-(
    $language = substr($request->cookies->get('hl'), 0, 2);

    return Intl::getRegionBundle()->getCountryNames($language);
  }

  public function getName()
  {
    return 'app_extension';
  }
}