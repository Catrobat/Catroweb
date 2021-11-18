<?php

namespace App\Api_deprecated\Controller;

use App\Catrobat\Services\ExtractedFileRepository;
use App\Catrobat\Services\ProgramFileRepository;
use App\Catrobat\StatusCode;
use App\Entity\Program;
use App\Entity\ProgramManager;
use App\Entity\User;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @deprecated
 */
class DownloadProgramController extends AbstractController
{
  /**
   * @Route("/download/{id}.catrobat", name="download", options={"expose": true}, defaults={"_format": "json"},
   * methods={"GET"})
   *
   * @throws ORMException
   * @throws OptimisticLockException
   *
   * @return BinaryFileResponse|JsonResponse
   */
  public function downloadProgramAction(Request $request, string $id, ProgramManager $program_manager,
                                        ProgramFileRepository $file_repository, LoggerInterface $logger,
                                        ExtractedFileRepository $extracted_file_repository)
  {
    /* @var $program Program */
    $referrer = $request->getSession()->get('referer');

    $program = $program_manager->find($id);
    if (null === $program) {
      throw new NotFoundHttpException();
    }
    try {
      if (!$file_repository->checkIfProjectZipFileExists($program->getId())) {
        $file_repository->zipProject($extracted_file_repository->getBaseDir($program), $program->getId());
      }
      $file = $file_repository->getProjectZipFile($id);
    } catch (FileNotFoundException $fileNotFoundException) {
      $logger->error('[FILE] failed to get program file with id: '.$id);

      return JsonResponse::create('Invalid file upload', StatusCode::INVALID_FILE_UPLOAD);
    }

    if ($file->isFile()) {
      $downloaded = $request->getSession()->get('downloaded', []);
      if (!in_array($program->getId(), $downloaded, true)) {
        /** @var User|null $user */
        $user = $this->getUser();
        $program_manager->increaseDownloads($program, $user);
        $downloaded[] = $program->getId();
        $request->getSession()->set('downloaded', $downloaded);
        $request->attributes->set('referrer', $referrer);
      }

      $response = new BinaryFileResponse($file);
      // can be changed back to $response->setContentDisposition
      // after https://github.com/symfony/symfony/issues/34099 has been fixed
      $response->headers->set(
        'Content-Disposition',
        'attachment; filename="'.$program->getId().'.catrobat"'
      );

      return $response;
    }
    throw new NotFoundHttpException();
  }
}
