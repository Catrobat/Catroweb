<?php

namespace Catrobat\AppBundle\Controller\Api;

use Catrobat\AppBundle\Entity\ProgramManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Catrobat\AppBundle\Services\ScreenshotRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Catrobat\AppBundle\Responses\ProgramListResponse;

class ProgramController extends Controller
{
  /**
   * @Route("/api/projects/getInfoById.json", name="api_info_by_id", defaults={"_format": "json"}, methods={"GET"})
   */
  public function showProgramAction(Request $request)
  {
    $id = intval($request->query->get('id'));
    $program_manager = $this->get('programmanager');

    $programs = [];
    $program = $program_manager->find($id);
    if ($program == null)
    {
      return JsonResponse::create(['Error' => 'Project not found (uploaded)', 'preHeaderMessages' => '']);
    }
    else
    {
      $numbOfTotalProjects = 1;
      $programs[] = $program;
    }

    return new ProgramListResponse($programs, $numbOfTotalProjects);
  }
}
