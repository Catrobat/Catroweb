<?php

namespace Catrobat\AppBundle\Controller\Api;

use Catrobat\AppBundle\Services\Formatter\ElapsedTimeStringFormatter;
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
   * @Route("/{flavor}/api/projects/recent.json", name="api_recent_programs", defaults={"_format": "json"}, requirements={"flavor": "pocketcode|pocketkodey"})
   * @Method({"GET"})
   */
  public function listProgramsAction(Request $request)
  {
    return $this->listSortedPrograms($request, "recent");
  }

  /**
   * @Route("/api/projects/recentIDs.json", name="catrobat_api_recent_program_ids", defaults={"_format": "json"})
   * @Route("/{flavor}/api/projects/recentIDs.json", name="api_recent_program_ids", defaults={"_format": "json"}, requirements={"flavor": "pocketcode|pocketkodey"})
   * @Method({"GET"})
   */
  public function listProgramIdsAction(Request $request)
  {
      return $this->listSortedPrograms($request, "recent", false);
  }
  
  /**
   * @Route("/api/projects/mostDownloaded.json", name="catrobat_api_most_downloaded_programs", defaults={"_format": "json"})
   * @Route("/{flavor}/api/projects/mostDownloaded.json", name="api_most_downloaded_programs", defaults={"_format": "json"}, requirements={"flavor": "pocketcode|pocketkodey"})
   * @Method({"GET"})
   */
  public function listMostDownloadedProgramsAction(Request $request)
  {
    return $this->listSortedPrograms($request, "downloads");
  }

  /**
   * @Route("/api/projects/mostDownloadedIDs.json", name="catrobat_api_most_downloaded_program_ids", defaults={"_format": "json"})
   * @Route("/{flavor}/api/projects/mostDownloadedIDs.json", name="api_most_downloaded_program_ids", defaults={"_format": "json"}, requirements={"flavor": "pocketcode|pocketkodey"})
   * @Method({"GET"})
   */
  public function listMostDownloadedProgramIdsAction(Request $request)
  {
      return $this->listSortedPrograms($request, "downloads", false);
  }
  
  /**
   * @Route("/api/projects/mostViewed.json", name="catrobat_api_most_viewed_programs", defaults={"_format": "json"})
   * @Route("/{flavor}/api/projects/mostViewed.json", name="api_most_viewed_programs", defaults={"_format": "json"}, requirements={"flavor": "pocketcode|pocketkodey"})
   * @Method({"GET"})
   */
  public function listMostViewedProgramsAction(Request $request)
  {
    return $this->listSortedPrograms($request, "views");
  }

  /**
   * @Route("/api/projects/mostViewedIDs.json", name="catrobat_api_most_viewed_programids", defaults={"_format": "json"})
   * @Route("/{flavor}/api/projects/mostViewedIDs.json", name="api_most_viewed_programids", defaults={"_format": "json"}, requirements={"flavor": "pocketcode|pocketkodey"})
   * @Method({"GET"})
   */
  public function listMostViewedProgramIdsAction(Request $request)
  {
      return $this->listSortedPrograms($request, "views", false);
  }

  /**
   * @Route("/api/projects/userPrograms.json", name="catrobat_api_user_programs", defaults={"_format": "json"})
   * @Route("/{flavor}/api/projects/userPrograms.json", name="api_user_programs", defaults={"_format": "json"}, requirements={"flavor": "pocketcode|pocketkodey"})
   * @Method({"GET"})
   */
  public function listUserProgramsAction(Request $request)
  {
    return $this->listSortedPrograms($request, "user");
  }
  
  private function listSortedPrograms(Request $request, $sortBy, $details = true)
  {
    $program_manager = $this->get("programmanager");
    $screenshot_repository = $this->get("screenshotrepository");
    $elapsed_time = $this->get("elapsedtime");
    
    
    $retArray = array ();
    $limit = intval($request->query->get('limit', 20));
    $offset = intval($request->query->get('offset', 0));
    $user_id = intval($request->query->get('user_id', 0));

    if ($sortBy == "downloads")
      $programs = $program_manager->getMostDownloadedPrograms($limit, $offset);
    else if ($sortBy == "views")
      $programs = $program_manager->getMostViewedPrograms($limit, $offset);
    else if ($sortBy == "user")
      $programs = $program_manager->getUserPrograms($user_id);
    else
      $programs = $program_manager->getRecentPrograms($limit, $offset);
    
    if ($sortBy == "user")
        $numbOfTotalProjects = count($programs);
    else
        $numbOfTotalProjects = $program_manager->getTotalPrograms();
    
    $retArray['CatrobatProjects'] = array ();
    foreach($programs as $program)
    {
      $new_program = array ();
      $new_program['ProjectId'] = $program->getId();
      $new_program['ProjectName'] = $program->getName();
      if ($details === true)
      {
          $new_program['ProjectNameShort'] = $program->getName();
          $new_program['Author'] = $program->getUser()->getUserName();
          $new_program['Description'] = $program->getDescription();
          $new_program['Version'] = $program->getCatrobatVersionName();
          $new_program['Views'] = $program->getViews();
          $new_program['Downloads'] = $program->getDownloads();
          $new_program['Uploaded'] = $program->getUploadedAt()->getTimestamp();
          $new_program['UploadedString'] = $elapsed_time->getElapsedTime($program->getUploadedAt()->getTimestamp());
          $new_program['ScreenshotBig'] = $screenshot_repository->getScreenshotWebPath($program->getId());
          $new_program['ScreenshotSmall'] = $screenshot_repository->getThumbnailWebPath($program->getId());
          $new_program['ProjectUrl'] = ltrim($this->generateUrl('program', array('flavor' => $request->attributes->get("flavor"), 'id' => $program->getId())),"/");
          $new_program['DownloadUrl'] = ltrim($this->generateUrl('download', array('id' => $program->getId())),"/");
          $new_program['FileSize'] = $program->getFilesize()/1048576;
      }
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
