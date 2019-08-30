<?php

namespace App\Catrobat\Controller\Api;

use App\Catrobat\Responses\ProgramListResponse;
use App\Entity\ProgramManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


/**
 * Class ProgramController
 * @package App\Catrobat\Controller\Api
 */
class ProgramController extends AbstractController
{
  /**
   * @Route("/api/projects/getInfoById.json", name="api_info_by_id", defaults={"_format": "json"}, methods={"GET"})
   *
   * @param Request $request
   * @param ProgramManager $program_manager
   *
   * @return ProgramListResponse|JsonResponse
   */
  public function showProgramAction(Request $request, ProgramManager $program_manager)
  {
    /** @var ProgramManager $program_manager */
    $id = $request->get('id', 0);

    $programs = [];
    $program = $program_manager->find($id);
    if ($program === null)
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
