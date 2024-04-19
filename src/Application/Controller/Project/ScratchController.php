<?php

declare(strict_types=1);

namespace App\Application\Controller\Project;

use App\Project\Scratch\AsyncHttpClient;
use App\Project\Scratch\ScratchManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ScratchController extends AbstractController
{
  protected AsyncHttpClient $async_http_client;

  public function __construct(protected ScratchManager $scratch_manager)
  {
    $this->async_http_client = new AsyncHttpClient(['timeout' => 12, 'max_number_of_concurrent_requests' => 1]);
  }

  #[Route(path: '/scratch/project/{id}', name: 'scratch_program', methods: ['GET', 'POST'])]
  public function scratchProject(Request $request, int $id): Response
  {
    $project = $this->scratch_manager->createScratchProjectFromId($id);
    if (null === $project) {
      throw $this->createNotFoundException('Error creating Scratch project');
    }
    $url = $this->generateUrl('program', ['id' => $project->getId()]);
    if ($request->isMethod('GET')) {
      return $this->redirect($url);
    }

    return new Response($project->getId(), Response::HTTP_CREATED, ['Location' => $url]);
  }
}
