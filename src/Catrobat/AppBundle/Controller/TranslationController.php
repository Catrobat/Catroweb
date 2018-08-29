<?php

namespace Catrobat\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class TranslationController extends Controller
{

  /**
   * @Route("/translate/{word}/{domain}", name="translate_word")
   */
  public function translateAction(TranslatorInterface $translator, $word, $domain)
  {
    return JsonResponse::create($translator->trans($word, [], $domain), 200);
  }

}