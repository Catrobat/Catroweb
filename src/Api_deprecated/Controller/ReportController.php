<?php

namespace App\Api_deprecated\Controller;

use App\DB\Entity\Project\Program;
use App\DB\Entity\Project\ProgramInappropriateReport;
use App\DB\Entity\User\User;
use App\Project\Event\ReportInsertEvent;
use App\Project\ProgramManager;
use App\User\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @deprecated
 */
class ReportController extends AbstractController
{
  private UserManager $user_manager;
  private ProgramManager $program_manager;
  private TranslatorInterface $translator;
  private EventDispatcherInterface $event_dispatcher;

  public function __construct(UserManager $user_manager, ProgramManager $program_manager,
                              TranslatorInterface $translator, EventDispatcherInterface $event_dispatcher)
  {
    $this->user_manager = $user_manager;
    $this->program_manager = $program_manager;
    $this->translator = $translator;
    $this->event_dispatcher = $event_dispatcher;
  }

  /**
   * @deprecated
   *
   * @Route("/api/reportProject/reportProject.json", name="catrobat_api_report_program",
   * defaults={"_format": "json"}, methods={"POST", "GET"})
   */
  public function reportProgramAction(Request $request): JsonResponse
  {
    /* @var $program Program */
    /* @var $user User */

    $entity_manager = $this->getDoctrine()->getManager();

    $response = [];
    if (!$request->get('program') || !$request->get('category') || !$request->get('note')) {
      $response['statusCode'] = 501; // should be a bad request!
      $response['answer'] = $this->translator->trans('errors.post-data', [], 'catroweb');
      $response['preHeaderMessages'] = '';

      return JsonResponse::create($response);
    }

    $program = $this->program_manager->find($request->get('program'));
    if (null == $program) {
      $response['statusCode'] = 506; // should be 404!
      $response['answer'] = $this->translator->trans('errors.program.invalid', [], 'catroweb');
      $response['preHeaderMessages'] = '';

      return JsonResponse::create($response);
    }

    $report = new ProgramInappropriateReport();
    $approved_project = $program->getApproved();
    $featured_project = $this->program_manager->getFeaturedRepository()->isFeatured($program);
    if ($approved_project || $featured_project) {
      $response = [];
      $response['answer'] = $this->translator->trans('success.report', [], 'catroweb');
      $response['statusCode'] = Response::HTTP_OK;

      return JsonResponse::create($response);
    }

    $token = $request->headers->get('authorization');

    if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
      $user = $this->get('security.token_storage')->getToken()->getUser();
      $report->setReportingUser($user);
    } elseif (null !== $token) {
      $user = $entity_manager->getRepository(User::class)
        ->findOneBy(['upload_token' => $token])
      ;

      if (null !== $user) {
        // old deprecated upload_token Auth
        $report->setReportingUser($user);
      } else {
        // JWT Auth. (new)
        $token = preg_split('#\s+#', $token)[1]; // strip "bearer"
        $jwt_payload = $this->user_manager->decodeToken($token);
        if (!array_key_exists('username', $jwt_payload)) {
          return JsonResponse::create([], Response::HTTP_UNAUTHORIZED);
        }
        $report->setReportingUser($jwt_payload['username']);
      }
    } else {
      return JsonResponse::create([], Response::HTTP_UNAUTHORIZED);
    }

    $program->setVisible(false);
    $report->setCategory($request->get('category'));
    $report->setNote($request->get('note'));
    $report->setProgram($program);
    $report->setReportedUser($program->getUser());

    $entity_manager->persist($report);
    $entity_manager->flush();

    $this->event_dispatcher->dispatch(
      new ReportInsertEvent($request->get('category'), $request->get('note'), $report)
    );

    $response = [];
    $response['answer'] = $this->translator->trans('success.report', [], 'catroweb');
    $response['statusCode'] = Response::HTTP_OK;

    return JsonResponse::create($response);
  }
}
