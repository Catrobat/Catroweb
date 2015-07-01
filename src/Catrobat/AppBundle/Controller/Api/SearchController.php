<?php

namespace Catrobat\AppBundle\Controller\Api;

use Catrobat\AppBundle\Entity\ProgramManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SearchController extends Controller
{
    /**
   * @Route("/api/projects/search.json", name="api_search_programs", defaults={"_format": "json"})
   * @Method({"GET"})
   */
  public function searchProgramsAction(Request $request)
  {
      $program_manager = $this->get('programmanager');
      $screenshot_repository = $this->get('screenshotrepository');
      $elapsed_time = $this->get('elapsedtime');

      $retArray = array();
      $query = $request->query->get('q');
      $limit = intval($request->query->get('limit'));
      $offset = intval($request->query->get('offset'));
      $numbOfTotalProjects = $program_manager->searchCount($query);
      $programs = $program_manager->search($query, $limit, $offset);
      $retArray['CatrobatProjects'] = array();
      foreach ($programs as $program) {
          $new_program = array();
          $new_program['ProjectName'] = $program->getName();
          $new_program['ProjectNameShort'] = $program->getName();
          $new_program['ProjectId'] = $program->getId();
          $new_program['Author'] = $program->getUser()->getUserName();
          $new_program['Description'] = $program->getDescription();
          $new_program['Version'] = $program->getCatrobatVersionName();
          $new_program['Views'] = $program->getViews();
          $new_program['Downloads'] = $program->getDownloads();
          $new_program['Uploaded'] = $program->getUploadedAt()->getTimestamp();
          $new_program['UploadedString'] = $elapsed_time->getElapsedTime($program->getUploadedAt()->getTimestamp());
          $new_program['ScreenshotBig'] = $screenshot_repository->getScreenshotWebPath($program->getId());
          $new_program['ScreenshotSmall'] = $screenshot_repository->getThumbnailWebPath($program->getId());
          $new_program['ProjectUrl'] = ltrim($this->generateUrl('program', array('flavor' => $request->attributes->get('flavor'), 'id' => $program->getId())), '/');
          $new_program['DownloadUrl'] = ltrim($this->generateUrl('download', array('id' => $program->getId())), '/');
          $new_program['FileSize'] = $program->getFilesize() / 1048576;
          $retArray['CatrobatProjects'][] = $new_program;
      }
      $retArray['completeTerm'] = '';
      $retArray['preHeaderMessages'] = '';
      $retArray['CatrobatInformation'] = array(
        'BaseUrl' => ($request->isSecure() ? 'https://' : 'http://').$request->getHttpHost().'/',
        'TotalProjects' => $numbOfTotalProjects,
        'ProjectsExtension' => '.catrobat',
    );

      return JsonResponse::create($retArray);
  }
}
