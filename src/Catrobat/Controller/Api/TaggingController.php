<?php

namespace App\Catrobat\Controller\Api;

use App\Repository\TagRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class TaggingController.
 */
class TaggingController extends AbstractController
{
  /**
   * @Route("/api/tags/getTags.json", name="api_get_tags", defaults={"_format": "json"}, methods={"GET"})
   *
   * @return JsonResponse
   */
  public function taggingAction(Request $request, TagRepository $tags_repo)
  {
    $em = $this->getDoctrine()->getManager();
    $metadata = $em->getClassMetadata('App\Entity\Tag')->getFieldNames();

    $tags = [];
    $tags['statusCode'] = 200;
    $tags['constantTags'] = [];

    $language = $request->query->get('language');
    if (!in_array($language, $metadata, true))
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
