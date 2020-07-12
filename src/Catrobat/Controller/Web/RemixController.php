<?php

namespace App\Catrobat\Controller\Web;

use App\Catrobat\Services\ScreenshotRepository;
use App\Entity\RemixManager;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

class RemixController extends AbstractController
{
  private RouterInterface $router;
  private ScreenshotRepository $screenshot_repository;
  private RemixManager $remix_manager;

  public function __construct(RouterInterface $router, ScreenshotRepository $screenshot_repository,
                              RemixManager $remix_manager)
  {
    $this->router = $router;
    $this->screenshot_repository = $screenshot_repository;
    $this->remix_manager = $remix_manager;
  }

  /**
   * @Route("/project/{id}/remix_graph", name="remix_graph", methods={"GET"})
   */
  public function view(string $id): Response
  {
    return $this->render('Program/remix_graph.html.twig', [
      'id' => $id,
      'program_details_url_template' => $this->router->generate('program', ['id' => 0]),
    ]);
  }

  /**
   * @Route("/project/{id}/remix_graph_count", name="remix_graph_count", methods={"GET"})
   */
  public function getRemixCount(string $id): Response
  {
    // very computation intensive!
    return new JsonResponse(['count' => $this->remix_manager->remixCount($id)], Response::HTTP_OK);
  }

  /**
   * @Route("/project/{id}/remix_graph_data", name="remix_graph_data", methods={"GET"})
   *
   * @throws Exception
   */
  public function getRemixGraphData(Request $request, string $id): JsonResponse
  {
    $remix_graph_data = $this->remix_manager->getFullRemixGraph($id);

    $catrobat_program_thumbnails = [];
    foreach ($remix_graph_data['catrobatNodes'] as $node_id)
    {
      if (!array_key_exists($node_id, $remix_graph_data['catrobatNodesData']))
      {
        $catrobat_program_thumbnails[$node_id] = '/images/default/not_available.png';
        continue;
      }
      $catrobat_program_thumbnails[$node_id] = '/'.$this->screenshot_repository
        ->getThumbnailWebPath($node_id)
      ;
    }

    return new JsonResponse([
      'id' => $id,
      'remixGraph' => $remix_graph_data,
      'catrobatProgramThumbnails' => $catrobat_program_thumbnails,
    ]);
  }
}
