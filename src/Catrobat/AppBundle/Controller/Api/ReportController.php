<?php

namespace Catrobat\AppBundle\Controller\Api;

use Catrobat\AppBundle\Events\ReportInsertEvent;
use Symfony\Component\HttpFoundation\Request;
use Catrobat\AppBundle\Entity\ProgramManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Catrobat\AppBundle\StatusCode;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Catrobat\AppBundle\Entity\ProgramInappropriateReport;

class ReportController extends Controller
{
  /**
   * @Route("/api/reportProgram/reportProgram.json", name="catrobat_api_report_program", defaults={"_format": "json"},
   *                                                 methods={"POST", "GET"})
   */
  public function reportProgramAction(Request $request)
  {
    /* @var $program_manager \Catrobat\AppBundle\Entity\ProgramManager */
    /* @var $program \Catrobat\AppBundle\Entity\Program */

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
      $report->setReportingUser($this->get('security.token_storage')->getToken()->getUser()); //could be anon
    }
    else
    {
      $report->setReportingUser(null); //could be anon
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

  private function trans($message, $parameters = [])
  {
    return $this->get('translator')->trans($message, $parameters, 'catroweb');
  }
}
