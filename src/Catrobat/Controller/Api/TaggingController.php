<?php

namespace App\Catrobat\Controller\Api;

use App\Repository\TagRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;


/**
 * Class TaggingController
 * @package App\Catrobat\Controller\Api
 */
class TaggingController extends AbstractController
{

  /**
   * @Route("/api/tags/getTags.json", name="api_get_tags", defaults={"_format": "json"}, methods={"GET"})
   *
   * @param Request $request
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
