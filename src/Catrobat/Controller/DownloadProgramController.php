<?php

namespace App\Catrobat\Controller;

use App\Catrobat\RecommenderSystem\RecommendedPageId;
use App\Catrobat\Services\ProgramFileRepository;
use App\Catrobat\StatusCode;
use App\Entity\Program;
use App\Entity\ProgramManager;
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
 * Class DownloadProgramController.
 */
class DownloadProgramController extends AbstractController
{
  /**
   * @Route("/download/{id}.catrobat", name="download", options={"expose": true}, defaults={"_format": "json"},
   * methods={"GET"})
   *
   * @param $id
   *
   * @throws ORMException
   * @throws OptimisticLockException
   *
   * @return BinaryFileResponse|JsonResponse
   */
  public function downloadProgramAction(Request $request, $id, ProgramManager $program_manager,
                                        ProgramFileRepository $file_repository, LoggerInterface $logger)
  {
    /* @var $program Program */
    $referrer = $request->getSession()->get('referer');

    $program = $program_manager->find($id);
    if (!$program)
    {
      throw new NotFoundHttpException();
    }
    if (!$program->isVisible())
    {
      throw new NotFoundHttpException();
    }

    $rec_by_page_id = intval($request->query->get('rec_by_page_id', RecommendedPageId::INVALID_PAGE));
    $rec_by_program_id = intval($request->query->get('rec_by_program_id', 0));
    $rec_user_specific = 1 == intval($request->query->get('rec_user_specific', 0)) ? true : false;
    $rec_tag_by_program_id = intval($request->query->get('rec_from', 0));
    try
    {
      $file = $file_repository->getProgramFile($id);
    }
    catch (FileNotFoundException $e)
    {
      $logger->error('[FILE] failed to get program file with id: '.$id);

      return JsonResponse::create('Invalid file upload', StatusCode::INVALID_FILE_UPLOAD);
    }

    if ($file->isFile())
    {
      $downloaded = $request->getSession()->get('downloaded', []);
      if (!in_array($program->getId(), $downloaded, true))
      {
        $program_manager->increaseDownloads($program);
        $downloaded[] = $program->getId();
        $request->getSession()->set('downloaded', $downloaded);
        $request->attributes->set('download_statistics_program_id', $id);
        $request->attributes->set('referrer', $referrer);

        if (RecommendedPageId::isValidRecommendedPageId($rec_by_page_id))
        {
          // all recommendations (except tag-recommendations -> see below)
          $request->attributes->set('rec_by_page_id', $rec_by_page_id);
          $request->attributes->set('rec_by_program_id', $rec_by_program_id);
          $request->attributes->set('rec_user_specific', $rec_user_specific);
        }
        else
        {
          if ($rec_tag_by_program_id > 0)
          {
            // tag-recommendations
            $request->attributes->set('rec_from', $rec_tag_by_program_id);
          }
        }
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
