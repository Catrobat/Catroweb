<?php

namespace App\Catrobat\Controller\Web;

use App\Catrobat\Responses\ProgramListResponse;
use App\Catrobat\Services\ScratchHttpClient;
use App\Entity\Program;
use App\Entity\ScratchManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ScratchController extends AbstractController
{
  protected ScratchManager $scratch_manager;
  protected ScratchHttpClient $scratch_http_client;

  public function __construct(ScratchManager $scratch_manager)
  {
    $this->scratch_manager = $scratch_manager;
    $this->scratch_http_client = new ScratchHttpClient(['timeout' => 12]);
  }

  /**
   * @Route("/scratch/project/{id}", name="scratch_program", methods={"GET", "POST"})
   */
  public function scratchProjectAction(Request $request, int $id): Response
  {
    $program = $this->scratch_manager->createScratchProgramFromId($id);
    if (null === $program)
    {
      throw $this->createNotFoundException('Error creating Scratch program');
    }
    $url = $this->generateUrl('program', ['id' => $program->getId()]);

    if ($request->isMethod('GET'))
    {
      return $this->redirect($url);
    }

    return new Response($program->getId(), Response::HTTP_CREATED, ['Location' => $url]);
  }

  /**
   * @Route("/scratch/search/projects", name="search_scratch_program", methods={"GET"})
   *
   * Returns a list of the found programs. Scratch doesn't return the number of total found projects.
   * Total_programs is set to null if the number of found programs isn't known yet. When offset is higher than the
   * number of found projects it's set to the offset.
   */
  public function searchScratchProjectAction(Request $request): ProgramListResponse
  {
    $q = $request->query->get('q', null);
    $offset = $request->query->getInt('offset', 0);
    $limit = $request->query->getInt('limit', 40);
    if ($limit > 40)
    {
      $limit = 40;
    }

    $projects_data = $this->scratch_http_client->searchProjects($q, $offset, $limit);
    $total_programs = null;
    if (count($projects_data) !== $limit)
    {
      $total_programs = $offset + count($projects_data);
    }

    $programs = [];
    foreach ($projects_data as $project_data)
    {
      $programs[] = $this->scratch_manager->getPseudoProgramFromData($project_data);
    }

    return new ProgramListResponse($programs, $total_programs);
  }

  /**
   * TODO ONLY FOR DEMONSTRATION PURPOSES, REMOVE.
   *
   * @deprecated
   * @Route("/scratch/projects/search/{q}", name="demo_scratch_search", methods={"GET"})
   */
  public function searchScratch(Request $request, string $q): Response
  {
    return $this->render('Search/search_scratch.html.twig', ['q' => $q]);
  }
}
