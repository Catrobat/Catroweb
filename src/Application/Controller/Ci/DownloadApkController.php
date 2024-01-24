<?php

namespace App\Application\Controller\Ci;

use App\DB\Entity\Project\Project;
use App\DB\Entity\Project\ProjectDownloads;
use App\DB\Entity\User\User;
use App\Project\Apk\ApkRepository;
use App\Project\Event\ProjectDownloadEvent;
use App\Project\ProjectManager;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DownloadApkController.
 *
 * @deprecated - Move to Catroweb-API
 */
class DownloadApkController extends AbstractController
{
  public function __construct(protected EventDispatcherInterface $event_dispatcher,
    private readonly ProjectManager $project_manager,
    private readonly ApkRepository $apk_repository,
    protected LoggerInterface $logger)
  {
    // Automatically injects the download logger here thx to this syntax. (camelCase)
  }

  #[Route(path: '/ci/download/{id}', name: 'ci_download', methods: ['GET'])]
  public function downloadApkAction(string $id, Request $request): BinaryFileResponse
  {
    /** @var User|null $user */
    $user = $this->getUser();
    $project = $this->findProject($id);
    $file = $this->getApkFile($id);
    $response = $this->createDownloadApkFileResponse($id, $file);
    $this->event_dispatcher->dispatch(
      new ProjectDownloadEvent($user, $project, ProjectDownloads::TYPE_APK)
    );

    return $response;
  }

  protected function createDownloadApkFileResponse(string $id, File $file): BinaryFileResponse
  {
    $username = $this->getUser() ? $this->getUser()->getUserIdentifier() : '-';
    $this->logger->debug("User \"{$username}\" downloaded project apk with ID \"{$id}\" successfully");

    $response = new BinaryFileResponse($file);
    $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
      ResponseHeaderBag::DISPOSITION_ATTACHMENT,
      "{$id}.apk"
    ));
    $response->headers->set('Content-type', 'application/vnd.android.package-archive');

    return $response;
  }

  protected function getApkFile(string $id): File
  {
    try {
      $file = $this->apk_repository->getProjectFile($id);
      if (!$file->isFile()) {
        throw new NotFoundHttpException();
      }
    } catch (\Exception $exception) {
      $project = $this->project_manager->find($id);
      if (null !== $project) {
        $project->setApkStatus(Project::APK_NONE);
        $this->project_manager->save($project);
        $this->logger->error("Project apk for id: \"{$id}\" not found; Status reset");
      }
      throw new NotFoundHttpException($exception->getMessage());
    }

    return $file;
  }

  protected function findProject(string $id): Project
  {
    /* @var $project Project|null */
    $project = $this->project_manager->find($id);
    if (null === $project || !$project->isVisible() || Project::APK_READY != $project->getApkStatus()) {
      $this->logger->warning('Project with ID: '.$id.' not found');
      throw new NotFoundHttpException();
    }

    return $project;
  }
}
