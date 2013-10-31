<?php
namespace Catrobat\CatrowebBundle\Model;

use Catrobat\CatrowebBundle\Model\Requests\AddProjectRequest;
use Catrobat\CatrowebBundle\Entity\Project;
use Catrobat\CatrowebBundle\Services\ProjectDirectoryValidator;

class ProjectManager
{
  protected $file_extractor;
  protected $file_repository;
  protected $screenshot_repository;
  protected $doctrine;
  
  public function __construct($file_extractor, $file_repository, $screenshot_repository, $doctrine)
  {
    $this->file_extractor = $file_extractor;
    $this->file_repository = $file_repository;
    $this->screenshot_repository = $screenshot_repository;
    $this->doctrine = $doctrine;
  }
  
  public function addProject(AddProjectRequest $request)
  {
    $file = $request->getProjectfile();

    $validator = new ProjectDirectoryValidator();
    $extract_dir = $this->file_extractor->extract($file);
     
    $info = $validator->getProjectInfo($extract_dir);
    
    $project = new Project();
    $project->setName($info['name']);
    $project->setDescription($info['description']);
    $project->setFilename($file->getFilename());
    $project->setThumbnail("");
    $project->setScreenshot("");
    $project->setUser($request->getUser());
    
    $em = $this->doctrine->getManager();
    $em->persist($project);
    $em->flush();

    //$this->screenshot_repository->saveProjectAssets($info['screenshot'], $project->getId());
  }
}