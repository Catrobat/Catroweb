<?php

namespace Catrobat\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class TranslationController extends Controller
{
  /**
   * @Route("/translate/{word}/{array}/{domain}", name = "translate_word", defaults={"array" = "", "domain" =
   *                                              "catroweb"})
   *
   * @param TranslatorInterface $translator
   * @param                     $word
   * @param string              $array
   * @param string              $domain
   *
   * @return JsonResponse
   */
  public function translateAction(TranslatorInterface $translator, $word, $array = "", $domain = "catroweb")
  {
    $decodedArray = [];

    if ($array !== "")
    {
      $decodedArray = $this->parseJavascriptDictArrayToPhp($array);
    }

    return JsonResponse::create($translator->trans($word, $decodedArray, $domain), 200);
  }

  /**
   * @param $array
   *
   * @return array
   */
  private function parseJavascriptDictArrayToPhp($array)
  {
    $array = (array)json_decode($array);
    $decodedArray = [];
    foreach ($array as $value)
    {
      $value = (array)$value;
      $decodedArray[$value['key']] = $value['value'];
    }

    return $decodedArray;
  }

}