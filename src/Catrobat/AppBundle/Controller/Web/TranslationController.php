<?php

namespace Catrobat\AppBundle\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;


/**
 * Class TranslationController
 * @package Catrobat\AppBundle\Controller\Web
 */
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
   * @Route("/transChoice/{word}/{count}/{array}/{domain}", name = "translate_choice", defaults={"array" = "", "domain" =
   *                                              "catroweb"})
   *
   * @param TranslatorInterface $translator
   * @param                     $word
   * @param integer             $count
   * @param string              $array
   * @param string              $domain
   *
   * @return JsonResponse
   */
  public function transChoiceAction(TranslatorInterface $translator, $word, $count, $array, $domain)
  {

    $decodedArray = [];

    if ($array !== "")
    {
      $decodedArray = $this->parseJavascriptDictArrayToPhp($array);
    }

    return JsonResponse::create($translator->transChoice($word, $count, $decodedArray, $domain), 200);
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