<?php

namespace Catrobat\ApiBundle\Controller;

use Catrobat\CoreBundle\Model\ProgramManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Catrobat\CoreBundle\Services\ElapsedTimeString;

class SearchController
{
  protected $program_manager;
  protected $elapsed_time;

  public function __construct(ProgramManager $program_manager, ElapsedTimeString $elapsed_time)
  {
    $this->program_manager = $program_manager;
    $this->elapsed_time = $elapsed_time;
  }

  public function searchProgramsAction(Request $request)
  {
    $retArray = array();
    $query = $request->query->get('q');
    $limit = intval($request->query->get('limit'));
    $offset = intval($request->query->get('offset'));
    $numbOfTotalProjects = $this->program_manager->searchCount($query);
    $programs = $this->program_manager->search($query, $limit, $offset);
    $retArray['CatrobatProjects'] = array();
    foreach ($programs as $program)
    {
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
      $new_program['UploadedString'] = $this->elapsed_time->getElapsedTime($program->getUploadedAt()->getTimestamp());
      $new_program['ScreenshotBig'] = "resources/thumbnails/" . $program->getId() . "_large.png";
      $new_program['ScreenshotSmall'] = "resources/thumbnails/"  . $program->getId() . "_small.png";
      $new_program['ProjectUrl'] = "details/" . $program->getId();
      $new_program['DownloadUrl'] = "download/"  . $program->getId() . ".catrobat";
      $retArray['CatrobatProjects'][] = $new_program;
    }
    $retArray['completeTerm'] = "";
    $retArray['preHeaderMessages'] = "";
    $retArray['CatrobatInformation'] = array("BaseUrl" => 'https://' . $request->getHttpHost() . '/', "TotalProjects" => $numbOfTotalProjects, "ProjectsExtension" => ".catrobat");
    return JsonResponse::create($retArray);
  }
        
}
