<?php

namespace App\Api_deprecated\Controller;

use App\Entity\Tag;
use App\Repository\TagRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @deprecated
 */
class TaggingController extends AbstractController
{
  /**
   * @deprecated
   *
   * @Route("/api/tags/getTags.json", name="api_get_tags", defaults={"_format": "json"}, methods={"GET"})
   */
  public function taggingAction(Request $request, TagRepository $tags_repo): JsonResponse
  {
    $em = $this->getDoctrine()->getManager();
    $metadata = $em->getClassMetadata(Tag::class)->getFieldNames();

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
      $tags['constantTags'][] = $tag[$language];
    }

    return JsonResponse::create($tags);
  }
}
