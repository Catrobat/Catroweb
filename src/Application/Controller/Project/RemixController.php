<?php

declare(strict_types=1);

namespace App\Application\Controller\Project;

use App\Project\Remix\RemixManager;
use App\Storage\ScreenshotRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RemixController extends AbstractController
{
  private const string SCRATCH_THUMBNAIL_URL_TEMPLATE = 'https://cdn2.scratch.mit.edu/get_image/project/%s_140x140.png';
  private const string IMAGE_NOT_AVAILABLE_URL = '/images/default/not_available.png';

  public function __construct(
    private readonly ScreenshotRepository $screenshot_repository,
    private readonly RemixManager $remix_manager,
  ) {
  }

  #[Route(path: '/api/projects/{id}/remix-graph', name: 'project_remix_graph_api', methods: ['GET'])]
  public function getRemixGraph(string $id): JsonResponse
  {
    $remix_graph = $this->remix_manager->getRenderableRemixGraph($id);

    $nodes = array_map(function (array $node): array {
      if (!$node['available']) {
        $node['thumbnailUrl'] = self::IMAGE_NOT_AVAILABLE_URL;

        return $node;
      }

      if ('scratch' === $node['source']) {
        $node['thumbnailUrl'] = sprintf(self::SCRATCH_THUMBNAIL_URL_TEMPLATE, $node['projectId']);

        return $node;
      }

      $node['thumbnailUrl'] = '/'.$this->screenshot_repository->getThumbnailWebPath($node['projectId']);

      return $node;
    }, $remix_graph['nodes']);

    $response = new JsonResponse([
      ...$remix_graph,
      'nodes' => $nodes,
    ], Response::HTTP_OK);
    $response->setPrivate();
    $response->setMaxAge(300);

    return $response;
  }
}
