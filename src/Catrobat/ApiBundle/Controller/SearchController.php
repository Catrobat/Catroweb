<?php

namespace Catrobat\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Catrobat\CoreBundle\Model\ProjectManager;
use Symfony\Component\HttpFoundation\Request;

class SearchController
{
    protected $templating;
    protected $project_manager;
    
    public function __construct(EngineInterface $templating, ProjectManager $project_manager)
    {
      $this->templating = $templating;
      $this->project_manager = $project_manager;
    }
     
    public function searchProjectsAction(Request $request) 
    {
      $retArray = array();
      $projectName = $request->request->get('projectName');
      $limit = intval($request->request->get('limit'));
      $offset = intval($request->request->get('offset'));
       
      //$retArray['projectName'] = $projectName;
      $retArray['limit'] = $limit;
      $retArray['offset'] = $offset;
      
      $entities = $this->project_manager->findAll();
      $retArray['numOfProjects'] = count($entities);
      
      $entities = $this->project_manager->findOneByName($projectName);
      $retArray['id'] = $entities->getId();
      $retArray['projectName'] = $entities->getName();
      $retArray['description'] = $entities->getDescription();
      $retArray['downloads'] = $entities->getDownloads();
      $retArray['views'] = $entities->getViews();
      $retArray['author'] = $entities->getUser()->getUsername();
      $retArray['uploaded_time'] = $entities->getUploadedAt()->getTimestamp();
      $retArray['catrobat_version_name'] = $entities->getCatrobatVersionName();

      return $this->templating->renderResponse('CatrobatApiBundle:Api:searchProjects.json.twig', $retArray);
    }
    
    public function recentProjectsAction(Request $request)
    {
      $retArray = array();
      $limit = intval($request->request->get('limit',10));
      $offset = intval($request->request->get('offset',0));
       
      $projects = $this->project_manager->findByOrderedByDate($limit, $offset);
      foreach ($projects as $project)
      {
        $new_project = array();
        $new_project['ProjectName'] = $project->getName();
        $new_project['ProjectNameShort'] = $project->getName();
        $new_project['ProjectId'] = $project->getId();
        $new_project['Author'] = $project->getUser()->getUserName();
        $new_project['Description'] = $project->getDescription();
        $new_project['Version'] = $project->getCatrobatVersionName();
        $new_project['Views'] = $project->getViews();
        $new_project['Downloads'] = $project->getDownloads();
        $new_project['Uploaded'] = $project->getUploadedAt()->getTimestamp();
        $new_project['UploadedString'] = "";
        $new_project['ScreenshotBig'] = "resources/thumbnails/" . $project->getId() . "_large.png";
        $new_project['ScreenshotSmall'] = "resources/thumbnails/"  . $project->getId() . "_small.png";
        $new_project['ProjectUrl'] = "details/" . $project->getId();
        $new_project['DownloadUrl'] = "download/"  . $project->getId() . ".catrobat";
        $retArray['CatrobatProjects'][] = $new_project;
      }
      $retArray['completeTerm'] = "";
      $retArray['preHeaderMessages'] = "";
      
      $retArray['CatrobatInformation'] = array("BaseUrl" => "https://localhost/", "TotalProjects" => 3, "ProjectsExtension" => ".catrobat");
      
      return $this->templating->renderResponse('CatrobatApiBundle:Api:recentProjects.json.twig', array('b' => $retArray));
    }
    
}
