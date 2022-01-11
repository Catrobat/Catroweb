<?php

namespace App\Catrobat\Controller\Ci;

use App\Catrobat\Services\ApkRepository;
use App\Entity\Program;
use App\Entity\ProgramDownloads;
use App\Entity\ProgramManager;
use App\Entity\User;
use App\Event\ProjectDownloadEvent;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;

/**
 * Class DownloadApkController.
 *
 * @deprecated - Move to Catroweb-API
 */
class DownloadApkController extends AbstractController
{
  protected EventDispatcherInterface $event_dispatcher;
  private ProgramManager $program_manager;
  protected LoggerInterface $logger;
  private ApkRepository $apk_repository;

  public function __construct(EventDispatcherInterface $event_dispatcher,
                              ProgramManager $program_manager,
                              ApkRepository $apk_repository,
                              LoggerInterface $downloadLogger)
  {
    $this->event_dispatcher = $event_dispatcher;
    $this->program_manager = $program_manager;
    $this->apk_repository = $apk_repository;
    $this->logger = $downloadLogger; // Automatically injects the download logger here thx to this syntax. (camelCase)
  }

  /**
   * @Route("/ci/download/{id}", name="ci_download", methods={"GET"})
   */
  public function downloadApkAction(string $id, Request $request): BinaryFileResponse
  {
    /** @var User|null $user */
    $user = $this->getUser();

    $this->validateCsrfToken($request->query->get('token'));
    $project = $this->findProject($id);
    $file = $this->getApkFile($id);
    $response = $this->createDownloadApkFileResponse($id, $file);

    $this->event_dispatcher->dispatch(
      new ProjectDownloadEvent($user, $project, ProgramDownloads::TYPE_APK, $request)
    );

    return $response;
  }

  protected function createDownloadApkFileResponse(string $id, File $file): BinaryFileResponse
  {
    $username = $this->getUser() ? $this->getUser()->getUsername() : '-';
    $this->logger->debug("User \"{$username}\" downloaded project apk with ID \"{$id}\" successfully");

    $response = new BinaryFileResponse($file);
    $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
      ResponseHeaderBag::DISPOSITION_ATTACHMENT,
      "$id.apk"
    ));
    $response->headers->set('Content-type', 'application/vnd.android.package-archive');

    return $response;
  }

  protected function getApkFile(string $id): File
  {
    try {
      $file = $this->apk_repository->getProgramFile($id);
      if (!$file->isFile()) {
        $this->logger->error("Project apk for id: \"$id\" not found (1)");
        throw new NotFoundHttpException();
      }
    } catch (Exception $exception) {
      $this->logger->error("Project apk for id: \"$id\" not found (2)");
      throw new NotFoundHttpException($exception->__toString());
    }

    return $file;
  }

  protected function validateCsrfToken(?string $token): void
  {
    if (!$this->isCsrfTokenValid('project', $token)) {
        throw new InvalidCsrfTokenException();
    }
  }

  protected function findProject(string $id): Program
  {
    /* @var $project Program|null */
    $project = $this->program_manager->find($id);
    if (null === $project || !$project->isVisible() || Program::APK_READY != $project->getApkStatus()) {
      $this->logger->error('Project with ID: '.$id.' not found');
      throw new NotFoundHttpException();
    }

    return $project;
  }
}
