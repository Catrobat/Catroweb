<?php

namespace Catrobat\AppBundle\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;


/**
 * Class TaggingController
 * @package Catrobat\AppBundle\Controller\Api
 */
class TaggingController extends Controller
{

  /**
   * @Route("/api/tags/getTags.json", name="api_get_tags", defaults={"_format": "json"}, methods={"GET"})
   *
   * @param Request $request
   *
   * @return JsonResponse
   */
  public function taggingAction(Request $request)
  {
    $tags_repo = $this->get('tagrepository');

    $em = $this->getDoctrine()->getManager();
    $metadata = $em->getClassMetadata('Catrobat\AppBundle\Entity\Tag')->getFieldNames();

    $tags = [];
    $tags['statusCode'] = 200;
    $tags['constantTags'] = [];

    $language = $request->query->get('language');
    if (!in_array($language, $metadata))
    {
      $language = 'en';
      $tags['statusCode'] = 404;
    }
    $results = $tags_repo->getConstantTags($language);

    foreach ($results as $tag)
    {
      array_push($tags['constantTags'], $tag[$language]);
    }

    return JsonResponse::create($tags);
  }
}
