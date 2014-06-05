<?php

namespace Catrobat\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Catrobat\CoreBundle\Model\ProgramManager;
use Symfony\Component\HttpFoundation\Request;

class ListProgramsController
{
    protected $templating;
    protected $program_manager;
    
    public function __construct(EngineInterface $templating, ProgramManager $program_manager)
    {
      $this->templating = $templating;
      $this->program_manager = $program_manager;
    }

    public function listProgramsAction(Request $request)
    {
      $retArray = array();
      $limit = intval($request->query->get('limit',10));
      $offset = intval($request->query->get('offset',0));
      $numbOfTotalProjects = $this->program_manager->getTotalPrograms();
       
      $programs = $this->program_manager->getRecentPrograms($limit, $offset);
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
        $new_program['UploadedString'] = "";
        $new_program['ScreenshotBig'] = "resources/thumbnails/" . $program->getId() . "_large.png";
        $new_program['ScreenshotSmall'] = "resources/thumbnails/"  . $program->getId() . "_small.png";
        $new_program['ProjectUrl'] = "details/" . $program->getId();
        $new_program['DownloadUrl'] = "download/"  . $program->getId() . ".catrobat";
        $retArray['CatrobatProjects'][] = $new_program;
      }
      $retArray['completeTerm'] = "";
      $retArray['preHeaderMessages'] = "";
      
      $retArray['CatrobatInformation'] = array("BaseUrl" => 'https://' . $request->getHttpHost() . '/', "TotalProjects" => $numbOfTotalProjects, "ProjectsExtension" => ".catrobat");
      
      return $this->templating->renderResponse('CatrobatApiBundle:Api:recentPrograms.json.twig', array('b' => $retArray));
    }

  public function listMostDownloadedProgramsAction(Request $request)
  {
    $retArray = array();
    $limit = intval($request->query->get('limit',10));
    $offset = intval($request->query->get('offset',0));
    $numbOfTotalProjects = $this->program_manager->getTotalPrograms();

    $programs = $this->program_manager->getMostDownloadedPrograms($limit, $offset);
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
      $new_program['UploadedString'] = "";
      $new_program['ScreenshotBig'] = "resources/thumbnails/" . $program->getId() . "_large.png";
      $new_program['ScreenshotSmall'] = "resources/thumbnails/"  . $program->getId() . "_small.png";
      $new_program['ProjectUrl'] = "details/" . $program->getId();
      $new_program['DownloadUrl'] = "download/"  . $program->getId() . ".catrobat";
      $retArray['CatrobatProjects'][] = $new_program;
    }
    $retArray['completeTerm'] = "";
    $retArray['preHeaderMessages'] = "";

    $retArray['CatrobatInformation'] = array("BaseUrl" => 'https://' . $request->getHttpHost() . '/', "TotalProjects" => $numbOfTotalProjects, "ProjectsExtension" => ".catrobat");

    return $this->templating->renderResponse('CatrobatApiBundle:Api:recentPrograms.json.twig', array('b' => $retArray));
  }

  public function listMostViewedProgramsAction(Request $request)
  {
    $retArray = array();
    $limit = intval($request->query->get('limit',10));
    $offset = intval($request->query->get('offset',0));
    $numbOfTotalProjects = $this->program_manager->getTotalPrograms();

    $programs = $this->program_manager->getMostViewedPrograms($limit, $offset);
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
      $new_program['UploadedString'] = "";
      $new_program['ScreenshotBig'] = "resources/thumbnails/" . $program->getId() . "_large.png";
      $new_program['ScreenshotSmall'] = "resources/thumbnails/"  . $program->getId() . "_small.png";
      $new_program['ProjectUrl'] = "details/" . $program->getId();
      $new_program['DownloadUrl'] = "download/"  . $program->getId() . ".catrobat";
      $retArray['CatrobatProjects'][] = $new_program;
    }
    $retArray['completeTerm'] = "";
    $retArray['preHeaderMessages'] = "";

    $retArray['CatrobatInformation'] = array("BaseUrl" => 'https://' . $request->getHttpHost() . '/', "TotalProjects" => $numbOfTotalProjects, "ProjectsExtension" => ".catrobat");

    return $this->templating->renderResponse('CatrobatApiBundle:Api:recentPrograms.json.twig', array('b' => $retArray));
  }
    
}
