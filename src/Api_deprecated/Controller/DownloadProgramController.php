<?php

namespace App\Api_deprecated\Controller;

use App\Catrobat\Services\ExtractedFileRepository;
use App\Catrobat\Services\ProgramFileRepository;
use App\Entity\Program;
use App\Entity\ProgramDownloads;
use App\Entity\ProgramManager;
use App\Entity\User;
use App\Event\ProjectDownloadEvent;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @deprecated
 */
class DownloadProgramController extends AbstractController
{
  protected EventDispatcherInterface $event_dispatcher;
  protected ProgramManager $program_manager;
  protected ProgramFileRepository $file_repository;
  protected ExtractedFileRepository $extracted_file_repository;
  protected LoggerInterface $logger;

  public function __construct(EventDispatcherInterface $event_dispatcher,
                              ProgramFileRepository $file_repository,
                              ProgramManager $program_manager,
                              ExtractedFileRepository $extracted_file_repository,
                              LoggerInterface $downloadLogger)
  {
    $this->event_dispatcher = $event_dispatcher;
    $this->file_repository = $file_repository;
    $this->extracted_file_repository = $extracted_file_repository;
    $this->program_manager = $program_manager;
    $this->logger = $downloadLogger; // Automatically injects the download logger here thx to this syntax. (camelCase)
  }

  /**
   * @Route("/download/{id}.catrobat", name="download", defaults={"_format": "json"}, methods={"GET"})
   */
  public function downloadProgramAction(Request $request, string $id): BinaryFileResponse
  {
    /** @var User|null $user */
    $user = $this->getUser();

    $this->validateCsrfToken($request->query->get('token'));
    $project = $this->findProject($id);
    $file = $this->getZipFile($id);
    $response = $this->createDownloadFileResponse($id, $file);

    $this->event_dispatcher->dispatch(
      new ProjectDownloadEvent($user, $project, ProgramDownloads::TYPE_PROJECT, $request)
    );

    return $response;
  }

  protected function validateCsrfToken(?string $token): void
  {
    // Will kill the IOS implementation (already using it as API)
//    if ('prod' === $_ENV['APP_ENV'] && !$this->isCsrfTokenValid('project', $token)) {
//        throw new InvalidCsrfTokenException();
//    }
  }

  protected function findProject(string $id): Program
  {
    /* @var $project Program|null */
    $project = $this->program_manager->find($id);
    if (null === $project) {
      $this->logger->error('Project with ID: '.$id.' not found');
      throw new NotFoundHttpException();
    }

    return $project;
  }

  protected function getZipFile(string $id): File
  {
    try {
      if (!$this->file_repository->checkIfProjectZipFileExists($id)) {
        $this->file_repository->zipProject($this->extracted_file_repository->getBaseDir($id), $id);
      }
      $zipFile = $this->file_repository->getProjectZipFile($id);
      if (!$zipFile->isFile()) {
        $this->logger->error("ZIP File is no file for project with id: \"{$id}\" not found");
        throw new NotFoundHttpException();
      }
    } catch (FileNotFoundException $fileNotFoundException) {
      $this->logger->error("ZIP File to download project with id: \"{$id}\" not found");
      throw new NotFoundHttpException();
    }

    return $zipFile;
  }

  protected function createDownloadFileResponse(string $id, File $file): BinaryFileResponse
  {
    $username = $this->getUser() ? $this->getUser()->getUsername() : '-';
    $this->logger->debug("User \"{$username}\" downloaded project with ID \"{$id}\" successfully");

    $response = new BinaryFileResponse($file);
    $response->headers->set(
          'Content-Disposition',
          'attachment; filename="'.$id.'.catrobat"'
      );

    return $response;
  }
}
