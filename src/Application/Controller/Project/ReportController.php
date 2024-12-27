<?php

declare(strict_types=1);

namespace App\Application\Controller\Project;

use App\DB\Entity\Project\Program;
use App\DB\Entity\Project\ProgramInappropriateReport;
use App\DB\Entity\User\User;
use App\Project\Event\ReportInsertEvent;
use App\Project\ProjectManager;
use App\User\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ReportController extends AbstractController
{
    public function __construct(
        private readonly UserManager $user_manager,
        private readonly ProjectManager $project_manager,
        private readonly TranslatorInterface $translator,
        private readonly EventDispatcherInterface $event_dispatcher,
        private readonly AuthorizationCheckerInterface $authorization_checker,
        private readonly TokenStorageInterface $usage_tracking_token_storage,
        private readonly EntityManagerInterface $entity_manager,
    ) {
    }

    #[Route(path: '/api/reportProject/reportProject.json', name: 'api_report_program', defaults: ['_format' => 'json'], methods: ['POST', 'GET'])]
    public function reportProject(Request $request): JsonResponse
    {
        $response = [];
        if (!$request->request->get('program') || !$request->request->get('category') || !$request->request->get('note')) {
            $response['statusCode'] = 400; // Bad request
            $response['answer'] = $this->translator->trans('errors.post-data', [], 'catroweb');
            $response['preHeaderMessages'] = '';
            $response ["test"] = $request->request->get('category');

            return new JsonResponse($response);
        }

        $category = strval($request->request->get('category'));
        $note = strval($request->request->get('note'));
        $projectId = strval($request->request->get('program'));
        $project = $this->project_manager->find($projectId);
        if (null == $project) {
            $response['statusCode'] = 404; // Not found
            $response['answer'] = $this->translator->trans('errors.project.invalid', [], 'catroweb');
            $response['preHeaderMessages'] = '';

            return new JsonResponse($response);
        }

        $report = new ProgramInappropriateReport();
        $approved_project = $project->getApproved();
        $featured_project = $this->project_manager->getFeaturedRepository()->isFeatured($project);
        if ($approved_project || $featured_project) {
            $response['answer'] = $this->translator->trans('success.report', [], 'catroweb');
            $response['statusCode'] = Response::HTTP_OK;

            return new JsonResponse($response);
        }

        $token = $request->headers->get('authorization');
        if ($this->authorization_checker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $user = $this->usage_tracking_token_storage->getToken()->getUser();
            $report->setReportingUser($user);
        } elseif (null !== $token) {
            $user = $this->entity_manager->getRepository(User::class)
                ->findOneBy(['upload_token' => $token]);

            if (null !== $user) {
                $report->setReportingUser($user);
            } else {
                $token = preg_split('#\s+#', $token)[1]; // strip "bearer"
                $jwt_payload = $this->user_manager->decodeToken($token);
                if (!array_key_exists('username', $jwt_payload)) {
                    return new JsonResponse([], Response::HTTP_UNAUTHORIZED);
                }

                $report->setReportingUser($jwt_payload['username']);
            }
        } else {
            return new JsonResponse([], Response::HTTP_UNAUTHORIZED);
        }

        $project->setVisible(false);
        $report->setCategory($category);
        $report->setNote($note);
        $report->setProgram($project);
        $report->setReportedUser($project->getUser());

        $this->entity_manager->persist($report);
        $this->entity_manager->flush();

        $this->event_dispatcher->dispatch(new ReportInsertEvent($category, $note, $report));
        $response['answer'] = $this->translator->trans('success.report', [], 'catroweb');
        $response['statusCode'] = Response::HTTP_OK;

        return new JsonResponse($response);
    }
}