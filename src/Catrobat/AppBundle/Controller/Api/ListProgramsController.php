<?php

namespace Catrobat\AppBundle\Controller\Api;

use Catrobat\AppBundle\Services\ElapsedTimeString;
use Catrobat\AppBundle\Model\ProgramManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Catrobat\AppBundle\Services\ScreenshotRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class ListProgramsController extends Controller
{

  /**
   * @Route("/api/projects/recent.json", name="catrobat_api_recent_programs", defaults={"_format": "json"})
   * @Method({"GET"})
   */
  public function listProgramsAction(Request $request)
  {
    return $this->listSortedPrograms($request, "recent");
  }

  /**
   * @Route("/api/projects/mostDownloaded.json", name="catrobat_api_most_downloaded_programs", defaults={"_format": "json"})
   * @Method({"GET"})
   */
  public function listMostDownloadedProgramsAction(Request $request)
  {
    return $this->listSortedPrograms($request, "downloads");
  }

  /**
   * @Route("/api/projects/mostViewed.json", name="catrobat_api_most_viewed_programs", defaults={"_format": "json"})
   * @Method({"GET"})
   */
  public function listMostViewedProgramsAction(Request $request)
  {
    return $this->listSortedPrograms($request, "views");
  }

  private function listSortedPrograms(Request $request, $sortBy)
  {
    $program_manager = $this->get("programmanager");
    $screenshot_repository = $this->get("screenshotrepository");
    $elapsed_time = $this->get("elapsedtime");
    
    
    $retArray = array ();
    $limit = intval($request->query->get('limit', 10));
    $offset = intval($request->query->get('offset', 0));
    $numbOfTotalProjects = $program_manager->getTotalPrograms();
    
    if ($sortBy == "downloads")
      $programs = $program_manager->getMostDownloadedPrograms($limit, $offset);
    else if ($sortBy == "views")
      $programs = $program_manager->getMostViewedPrograms($limit, $offset);
    else
      $programs = $program_manager->getRecentPrograms($limit, $offset);
    
    $retArray['CatrobatProjects'] = array ();
    foreach($programs as $program)
    {
      $new_program = array ();
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
      $new_program['ProjectUrl'] = "details/" . $program->getId();
      $new_program['DownloadUrl'] = "download/" . $program->getId() . ".catrobat";
      $retArray['CatrobatProjects'][] = $new_program;
    }
    $retArray['completeTerm'] = "";
    $retArray['preHeaderMessages'] = "";
    
    $retArray['CatrobatInformation'] = array (
        "BaseUrl" => ($request->isSecure() ? 'https://' : 'http://'). $request->getHttpHost() . $request->getBaseUrl() . '/',
        "TotalProjects" => $numbOfTotalProjects,
        "ProjectsExtension" => ".catrobat" 
    );
    
    return JsonResponse::create($retArray);
  }

}
