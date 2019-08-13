<?php

namespace App\Catrobat\Controller;

use App\Entity\Program;
use App\Entity\User;
use App\Catrobat\RecommenderSystem\RecommendedPageId;
use App\Catrobat\StatusCode;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\ProgramManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Catrobat\Services\ProgramFileRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;


/**
 * Class DownloadProgramController
 * @package App\Catrobat\Controller
 */
class DownloadProgramController extends AbstractController
{
  /**
   * @Route("/download/{id}.catrobat", name="download", options={"expose"=true}, defaults={"_format": "json"},
   *         methods={"GET"})
   *
   * @param Request $request
   * @param $id
   * @param ProgramManager $program_manager
   * @param ProgramFileRepository $file_repository
   * @param LoggerInterface $logger
   *
   * @return BinaryFileResponse|JsonResponse
   * @throws ORMException
   * @throws OptimisticLockException
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
    $rec_user_specific = intval($request->query->get('rec_user_specific', 0)) == 1 ? true : false;
    $rec_tag_by_program_id = intval($request->query->get('rec_from', 0));
    try
    {
      $file = $file_repository->getProgramFile($id);
    } catch (FileNotFoundException $e)
    {
      $logger->error('[FILE] failed to get program file with id: ' . $id);

      return JsonResponse::create('Invalid file upload', StatusCode::INVALID_FILE_UPLOAD);
    }

    if ($file->isFile())
    {
      $downloaded = $request->getSession()->get('downloaded', []);
      if (!in_array($program->getId(), $downloaded))
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
      $d = $response->headers->makeDisposition(
        ResponseHeaderBag::DISPOSITION_ATTACHMENT,
        $program->getId() . '.catrobat'
      );
      $response->headers->set('Content-Disposition', $d);

      return $response;
    }
    throw new NotFoundHttpException();
  }
}
