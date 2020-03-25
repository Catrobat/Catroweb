<?php

namespace App\Catrobat\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class TranslationController extends AbstractController
{
  /**
   * @Route("/translate/{word}/{array}/{domain}", name="translate_word", defaults={"array": "", "domain": "catroweb"})
   *
   * @param mixed $word
   */
  public function translateAction(TranslatorInterface $translator, $word, string $array = '',
                                  string $domain = 'catroweb'): JsonResponse
  {
    $decodedArray = [];

    if ('' !== $array)
    {
      $decodedArray = $this->parseJavascriptDictArrayToPhp($array);
    }

    return JsonResponse::create($translator->trans($word, $decodedArray, $domain), 200);
  }

  /**
   * @Route("/transChoice/{word}/{count}/{array}/{domain}", name="translate_choice",
   * defaults={"array": "", "domain": "catroweb"})
   */
  public function transChoiceAction(TranslatorInterface $translator, string $word, int $count,
                                    string $array, string $domain): JsonResponse
  {
    $decodedArray = [];

    if ('' !== $array)
    {
      $decodedArray = $this->parseJavascriptDictArrayToPhp($array);
    }

    return JsonResponse::create($translator->transChoice($word, $count, $decodedArray, $domain), 200);
  }

  private function parseJavascriptDictArrayToPhp(string $array): array
  {
    $array = (array) json_decode($array, false, 512, JSON_THROW_ON_ERROR);
    $decodedArray = [];
    foreach ($array as $value)
    {
      $value = (array) $value;
      $decodedArray[$value['key']] = $value['value'];
    }

    return $decodedArray;
  }
}
