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
      'isWebview' => new \Twig_Function_Method($this, 'isWebview'),
    );
  }

  public function getName()
  {
    return 'app_extension';
  }

  public function getCountriesList()
  {
    $request = Request::createFromGlobals();

    //todo: try to find a better solutions ... getLocale didn't work :-(
    $language = substr($request->cookies->get('hl'), 0, 2);

    return Intl::getRegionBundle()->getCountryNames($language);
  }

  public function isWebview()
  {
    $request = Request::createFromGlobals();
    $user_agent = $request->headers->get('User-Agent');

    // Example Webview: $user_agent = "Catrobat/0.93 PocketCode/0.9.14 Platform/Android";
    return preg_match("/Catrobat/", $user_agent);
  }
}