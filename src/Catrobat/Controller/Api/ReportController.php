<?php

namespace App\Catrobat\Controller\Api;

use App\Entity\Program;
use App\Entity\User;
use App\Catrobat\Events\ReportInsertEvent;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\ProgramManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Catrobat\StatusCode;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Entity\ProgramInappropriateReport;


/**
 * Class ReportController
 * @package App\Catrobat\Controller\Api
 */
class ReportController extends Controller
{

  /**
   * @Route("/api/reportProject/reportProject.json", name="catrobat_api_report_program",
   *   defaults={"_format": "json"}, methods={"POST", "GET"})
   *
   * @param Request $request
   *
   * @return JsonResponse
   */
  public function reportProgramAction(Request $request)
  {
    /* @var $program_manager ProgramManager */
    /* @var $program Program */
    /* @var $user User */

    $program_manager = $this->get('programmanager');
    $entity_manager = $this->getDoctrine()->getManager();
    $event_dispatcher = $this->get('event_dispatcher');

    $response = [];
    if (!$request->get('program') || !$request->get('category') || !$request->get('note'))
    {
      $response['statusCode'] = StatusCode::MISSING_POST_DATA;
      $response['answer'] = $this->trans('errors.post-data');
      $response['preHeaderMessages'] = '';

      return JsonResponse::create($response);
    }

    $program = $program_manager->find($request->get('program'));
    if ($program == null)
    {
      $response['statusCode'] = StatusCode::INVALID_PROGRAM;
      $response['answer'] = $this->trans('errors.program.invalid');
      $response['preHeaderMessages'] = '';

      return JsonResponse::create($response);
    }

    $report = new ProgramInappropriateReport();

    if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED'))
    {
      $user = $this->get('security.token_storage')->getToken()->getUser();
      $report->setReportingUser($user);
    }
    else
    {
      $report->setReportingUser(null); // could be anon
    }

    $program->setVisible(false);
    $report->setCategory($request->get('category'));
    $report->setNote($request->get('note'));
    $report->setProgram($program);

    $entity_manager->persist($report);
    $entity_manager->flush();

    $event_dispatcher->dispatch('catrobat.report.insert',
      new ReportInsertEvent($request->get('category'), $request->get('note'), $report));

    $response = [];
    $response['answer'] = $this->trans('success.report');
    $response['statusCode'] = StatusCode::OK;

    return JsonResponse::create($response);
  }

  /**
   * @param       $message
   * @param array $parameters
   *
   * @return string
   */
  private function trans($message, $parameters = [])
  {
    return $this->get('translator')->trans($message, $parameters, 'catroweb');
  }
}
