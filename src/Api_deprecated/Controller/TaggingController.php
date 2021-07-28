<?php

namespace App\Api_deprecated\Controller;

use App\Api\Services\Base\TranslatorAwareInterface;
use App\Api\Services\Base\TranslatorAwareTrait;
use App\Entity\Tag;
use App\Repository\TagRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @deprecated
 */
class TaggingController extends AbstractController implements TranslatorAwareInterface
{
  use TranslatorAwareTrait;

  public function __construct(TranslatorInterface $translator)
  {
    $this->initTranslator($translator);
  }

  /**
   * @deprecated
   *
   * @Route("/api/tags/getTags.json", name="api_get_tags", defaults={"_format": "json"}, methods={"GET"})
   */
  public function taggingAction(Request $request, TagRepository $tags_repo): JsonResponse
  {
    $em = $this->getDoctrine()->getManager();

    $tags = [];
    $tags['statusCode'] = 200;
    $tags['constantTags'] = [];

    $language = $request->query->get('language');
    $results = $tags_repo->getAllEnabledTags();

    /** @var Tag $tag */
    foreach ($results as $tag) {
      $tags['constantTags'][] = $this->trans($tag->getTitleLtmCode(), [], $language);
    }

    return JsonResponse::create($tags);
  }
}
