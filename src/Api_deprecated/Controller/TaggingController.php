<?php

declare(strict_types=1);

namespace App\Api_deprecated\Controller;

use App\Api\Services\Base\TranslatorAwareInterface;
use App\Api\Services\Base\TranslatorAwareTrait;
use App\DB\Entity\Project\Tag;
use App\DB\EntityRepository\Project\TagRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
   */
  #[Route(path: '/api/tags/getTags.json', name: 'api_get_tags', defaults: ['_format' => 'json'], methods: ['GET'])]
  public function tagging(TagRepository $tags_repo): JsonResponse
  {
    $tags = [];
    $tags['statusCode'] = 200;
    $tags['constantTags'] = [];
    // $language = $request->query->get('language');
    $results = $tags_repo->getActiveTags();
    /** @var Tag $tag */
    foreach ($results as $tag) {
      $tags['constantTags'][] = $tag->getInternalTitle();
      // $tags['translated'][] = $this->trans($tag->getTitleLtmCode(), [], $language); only 4 the new API!
    }

    return new JsonResponse($tags);
  }
}
