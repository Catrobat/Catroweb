<?php

declare(strict_types=1);

namespace App\Application\Controller\Project;

use App\Project\Remix\RemixManager;
use App\Storage\ScreenshotRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;

class RemixController extends AbstractController
{
  public function __construct(private readonly RouterInterface $router, private readonly ScreenshotRepository $screenshot_repository, private readonly RemixManager $remix_manager)
  {
  }

  #[Route(path: '/project/{id}/remix_graph', name: 'remix_graph', methods: ['GET'])]
  public function view(string $id): Response
  {
    return $this->render('Project/remix_graph.html.twig', [
      'id' => $id,
      'project_details_url_template' => $this->router->generate('program', ['id' => 0]),
    ]);
  }

  #[Route(path: '/project/{id}/remix_graph_count', name: 'remix_graph_count', methods: ['GET'])]
  public function getRemixCount(string $id): Response
  {
    // very computation intensive!
    return new JsonResponse(['count' => $this->remix_manager->remixCount($id)], Response::HTTP_OK);
  }

  /**
   * @throws \Exception
   */
  #[Route(path: '/project/{id}/remix_graph_data', name: 'remix_graph_data', methods: ['GET'])]
  public function getRemixGraphData(string $id): JsonResponse
  {
    $remix_graph_data = $this->remix_manager->getFullRemixGraph($id);
    $catrobat_project_thumbnails = [];
    foreach ($remix_graph_data['catrobatNodes'] as $node_id) {
      if (!array_key_exists($node_id, $remix_graph_data['catrobatNodesData'])) {
        $catrobat_project_thumbnails[$node_id] = '/images/default/not_available.png';
        continue;
      }

      $catrobat_project_thumbnails[$node_id] = '/'.$this->screenshot_repository
        ->getThumbnailWebPath($node_id)
      ;
    }

    return new JsonResponse([
      'id' => $id,
      'remixGraph' => $remix_graph_data,
      'catrobatProgramThumbnails' => $catrobat_project_thumbnails,
    ]);
  }
}
