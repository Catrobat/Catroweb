<?php

namespace App\Catrobat\Controller;

use App\Entity\Program;
use App\Entity\User;
use App\Catrobat\RecommenderSystem\RecommendedPageId;
use App\Catrobat\StatusCode;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\ProgramManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Catrobat\Services\ProgramFileRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;


/**
 * Class DownloadProgramController
 * @package App\Catrobat\Controller
 */
class DownloadProgramController extends Controller
{
  /**
   * @Route("/download/{id}.catrobat", name="download", options={"expose"=true}, defaults={"_format": "json"},
   *         methods={"GET"})
   *
   * @param Request $request
   * @param         $id
   *
   * @return JsonResponse | BinaryFileResponse
   */
  public function downloadProgramAction(Request $request, $id)
  {
    /* @var $program_manager ProgramManager */
    /* @var $file_repository ProgramFileRepository */
    /* @var $logger Logger */
    $referrer = $request->getSession()->get('referer');
    $program_manager = $this->get('programmanager');
    $file_repository = $this->get('filerepository');

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
      $logger = $this->get('logger');
      $logger->error('[FILE] failed to get program file with id: ' . $id);

      return JsonResponse::create('Invalid file upload', StatusCode::INVALID_FILE_UPLOAD);
    }

    if ($file->isFile())
    {
      $downloaded = $request->getSession()->get('downloaded', []);
      if (!in_array($program->getId(), $downloaded))
      {
        $this->get('programmanager')->increaseDownloads($program);
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
