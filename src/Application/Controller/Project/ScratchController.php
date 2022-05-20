<?php

namespace App\Application\Controller\Project;

use App\Project\Scratch\AsyncHttpClient;
use App\Project\Scratch\ScratchManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ScratchController extends AbstractController
{
  protected AsyncHttpClient $async_http_client;

  public function __construct(protected ScratchManager $scratch_manager)
  {
    $this->async_http_client = new AsyncHttpClient(['timeout' => 12, 'max_number_of_concurrent_requests' => 1]);
  }

  /**
   * @Route("/scratch/project/{id}", name="scratch_program", methods={"GET", "POST"})
   */
  public function scratchProjectAction(Request $request, int $id): Response
  {
    $program = $this->scratch_manager->createScratchProgramFromId($id);
    if (null === $program) {
      throw $this->createNotFoundException('Error creating Scratch program');
    }
    $url = $this->generateUrl('program', ['id' => $program->getId()]);

    if ($request->isMethod('GET')) {
      return $this->redirect($url);
    }

    return new Response($program->getId(), Response::HTTP_CREATED, ['Location' => $url]);
  }
}
