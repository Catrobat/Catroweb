<?php

namespace App\Catrobat\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use App\Catrobat\Responses\ProgramListResponse;


/**
 * Class ProgramController
 * @package App\Catrobat\Controller\Api
 */
class ProgramController extends Controller
{
  /**
   * @Route("/api/projects/getInfoById.json", name="api_info_by_id", defaults={"_format": "json"}, methods={"GET"})
   *
   * @param Request $request
   *
   * @return ProgramListResponse|JsonResponse
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
