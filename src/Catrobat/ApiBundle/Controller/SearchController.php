<?php

namespace Catrobat\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Catrobat\CoreBundle\Model\ProgramManager;
use Symfony\Component\HttpFoundation\Request;

class SearchController
{
    protected $templating;
    protected $program_manager;
    
    public function __construct(EngineInterface $templating, ProgramManager $program_manager)
    {
      $this->templating = $templating;
      $this->program_manager = $program_manager;
    }
     
    public function searchProgramsAction(Request $request) 
    {
      $retArray = array();
      $programName = $request->request->get('projectName');
      $limit = intval($request->request->get('limit'));
      $offset = intval($request->request->get('offset'));
       
      //$retArray['programName'] = $programName;
      $retArray['limit'] = $limit;
      $retArray['offset'] = $offset;
      
      $entities = $this->program_manager->findAll();
      $retArray['numOfPrograms'] = count($entities);
      
      $entities = $this->program_manager->findOneByName($programName);
      $retArray['id'] = $entities->getId();
      $retArray['programName'] = $entities->getName();
      $retArray['description'] = $entities->getDescription();
      $retArray['downloads'] = $entities->getDownloads();
      $retArray['views'] = $entities->getViews();
      $retArray['author'] = $entities->getUser()->getUsername();
      $retArray['uploaded_time'] = $entities->getUploadedAt()->getTimestamp();
      $retArray['catrobat_version_name'] = $entities->getCatrobatVersionName();

      return $this->templating->renderResponse('CatrobatApiBundle:Api:searchPrograms.json.twig', $retArray);
    }
    
    public function recentProgramsAction(Request $request)
    {
      $retArray = array();
      $limit = intval($request->request->get('limit',10));
      $offset = intval($request->request->get('offset',0));
       
      $programs = $this->program_manager->findByOrderedByDate($limit, $offset);
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
      
      $retArray['CatrobatInformation'] = array("BaseUrl" => "https://localhost/", "TotalProjects" => 3, "ProjectsExtension" => ".catrobat");
      
      return $this->templating->renderResponse('CatrobatApiBundle:Api:recentPrograms.json.twig', array('b' => $retArray));
    }
    
}
