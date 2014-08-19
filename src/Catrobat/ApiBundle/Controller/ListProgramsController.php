<?php

namespace Catrobat\ApiBundle\Controller;

use Catrobat\CoreBundle\Services\ElapsedTimeString;
use Catrobat\CoreBundle\Model\ProgramManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Catrobat\CoreBundle\Services\ScreenshotRepository;

class ListProgramsController
{
  protected $program_manager;
  protected $screenshot_repository;
  protected $elapsed_time;

  public function __construct(ProgramManager $program_manager, ScreenshotRepository $screenshot_repository, ElapsedTimeString $elapsed_time)
  {
    $this->program_manager = $program_manager;
    $this->screenshot_repository = $screenshot_repository;
    $this->elapsed_time = $elapsed_time;
  }

  public function listProgramsAction(Request $request)
  {
    return $this->listSortedPrograms($request, "recent");
  }

  public function listMostDownloadedProgramsAction(Request $request)
  {
    return $this->listSortedPrograms($request, "downloads");
  }

  public function listMostViewedProgramsAction(Request $request)
  {
    return $this->listSortedPrograms($request, "views");
  }

  private function listSortedPrograms(Request $request, $sortBy)
  {
    $retArray = array ();
    $limit = intval($request->query->get('limit', 10));
    $offset = intval($request->query->get('offset', 0));
    $numbOfTotalProjects = $this->program_manager->getTotalPrograms();
    
    if ($sortBy == "downloads")
      $programs = $this->program_manager->getMostDownloadedPrograms($limit, $offset);
    else if ($sortBy == "views")
      $programs = $this->program_manager->getMostViewedPrograms($limit, $offset);
    else
      $programs = $this->program_manager->getRecentPrograms($limit, $offset);
    
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
      $new_program['UploadedString'] = $this->elapsed_time->getElapsedTime($program->getUploadedAt()->getTimestamp());
      $new_program['ScreenshotBig'] = $this->screenshot_repository->getScreenshotWebPath($program->getId());
      $new_program['ScreenshotSmall'] = $this->screenshot_repository->getThumbnailWebPath($program->getId());
      $new_program['ProjectUrl'] = "details/" . $program->getId();
      $new_program['DownloadUrl'] = "download/" . $program->getId() . ".catrobat";
      $retArray['CatrobatProjects'][] = $new_program;
    }
    $retArray['completeTerm'] = "";
    $retArray['preHeaderMessages'] = "";
    
    $retArray['CatrobatInformation'] = array (
        "BaseUrl" => ($request->isSecure() ? 'https://' : 'http://'). $request->getHttpHost() . '/',
        "TotalProjects" => $numbOfTotalProjects,
        "ProjectsExtension" => ".catrobat" 
    );
    
    return JsonResponse::create($retArray);
  }

}
