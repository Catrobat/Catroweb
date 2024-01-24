<?php

namespace App\Application\Controller\Ci;

use App\DB\Entity\Project\Project;
use App\Project\ProjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class ApkStatusController.
 *
 * @deprecated - Move to Catroweb-API
 */
class ApkStatusController extends AbstractController
{
  public function __construct(private readonly ProjectManager $project_manager, private readonly TranslatorInterface $translator)
  {
  }

  #[Route(path: '/ci/status/{id}', name: 'ci_status', defaults: ['_format' => 'json'], methods: ['GET'])]
  public function getApkStatusAction(string $id): JsonResponse
  {
    /** @var Project|null $project */
    $project = $this->project_manager->findProjectIfVisibleToCurrentUser($id);
    if (null === $project) {
      return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }
    $result = [];
    switch ($project->getApkStatus()) {
      case Project::APK_READY:
        $result['status'] = 'ready';
        $result['url'] = $this->generateUrl('ci_download',
          ['id' => $project->getId(), 'fname' => $project->getName()], UrlGeneratorInterface::ABSOLUTE_URL);
        $result['label'] = $this->translator->trans('ci.download', [], 'catroweb');
        break;
      case Project::APK_PENDING:
        $result['status'] = 'pending';
        $result['label'] = $this->translator->trans('ci.pending', [], 'catroweb');
        break;
      default:
        $result['label'] = $this->translator->trans('ci.generate', [], 'catroweb');
        $result['status'] = 'none';
    }

    return new JsonResponse($result);
  }
}
