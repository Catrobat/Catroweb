<?php

namespace App\Catrobat\Controller\Api;

use App\Catrobat\Events\ReportInsertEvent;
use App\Catrobat\StatusCode;
use App\Entity\Program;
use App\Entity\ProgramInappropriateReport;
use App\Entity\ProgramManager;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ReportController extends AbstractController
{
  /**
   * @deprecated
   *
   * @Route("/api/reportProject/reportProject.json", name="catrobat_api_report_program",
   * defaults={"_format": "json"}, methods={"POST", "GET"})
   */
  public function reportProgramAction(Request $request, ProgramManager $program_manager,
                                      TranslatorInterface $translator, EventDispatcherInterface $event_dispatcher): JsonResponse
  {
    /* @var $program_manager ProgramManager */
    /* @var $program Program */
    /* @var $user User */

    $entity_manager = $this->getDoctrine()->getManager();

    $response = [];
    if (!$request->get('program') || !$request->get('category') || !$request->get('note'))
    {
      $response['statusCode'] = StatusCode::MISSING_POST_DATA;
      $response['answer'] = $translator->trans('errors.post-data', [], 'catroweb');
      $response['preHeaderMessages'] = '';

      return JsonResponse::create($response);
    }

    $program = $program_manager->find($request->get('program'));
    if (null == $program)
    {
      $response['statusCode'] = StatusCode::INVALID_PROGRAM;
      $response['answer'] = $translator->trans('errors.program.invalid', [], 'catroweb');
      $response['preHeaderMessages'] = '';

      return JsonResponse::create($response);
    }

    $report = new ProgramInappropriateReport();
    $approved_project = $program->getApproved();
    $featured_project = $program_manager->getFeaturedRepository()->isFeatured($program);
    if ($approved_project || $featured_project)
    {
      $response = [];
      $response['answer'] = $translator->trans('success.report', [], 'catroweb');
      $response['statusCode'] = StatusCode::OK;

      return JsonResponse::create($response);
    }

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

    $event_dispatcher->dispatch(
      new ReportInsertEvent($request->get('category'), $request->get('note'), $report)
    );

    $response = [];
    $response['answer'] = $translator->trans('success.report', [], 'catroweb');
    $response['statusCode'] = StatusCode::OK;

    return JsonResponse::create($response);
  }
}
