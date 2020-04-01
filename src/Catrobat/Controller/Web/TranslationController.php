<?php

namespace App\Catrobat\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class TranslationController extends AbstractController
{
  /**
   * @Route("/translate/{word}/{array}/{domain}", name="translate", defaults={"array": "", "domain": "catroweb"})
   */
  public function translateAction(TranslatorInterface $translator, string $word, string $array = '',
                                  string $domain = 'catroweb'): JsonResponse
  {
    $decodedArray = [];

    if ('' !== $array)
    {
      $decodedArray = $this->parseJavascriptDictArrayToPhp($array);
    }

    return JsonResponse::create($translator->trans($word, $decodedArray, $domain), 200);
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
