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
  protected $extracted_file_validator;
  
  public function __construct($file_extractor, $file_repository, $screenshot_repository, $doctrine, $extracted_file_validator)
  {
    $this->file_extractor = $file_extractor;
    $this->file_repository = $file_repository;
    $this->screenshot_repository = $screenshot_repository;
    $this->doctrine = $doctrine;
    $this->extracted_file_validator = $extracted_file_validator;
  }
  
  public function addProject(AddProjectRequest $request)
  {
    $file = $request->getProjectfile();

    $extracted_file = $this->file_extractor->extract($file);

    $this->extracted_file_validator->validate($extracted_file);
    
    $project = new Project();
    $project->setName($extracted_file->getName());
    $project->setDescription($extracted_file->getDescription());
    $project->setFilename($file->getFilename());
    $project->setThumbnail("");
    $project->setScreenshot("");
    $project->setUser($request->getUser());
    
    $em = $this->doctrine->getManager();
    $em->persist($project);
    $em->flush();

    $this->screenshot_repository->saveProjectAssets($extracted_file->getScreenshotPath(), $project->getId());
    $this->file_repository->saveProjectfile($file,$project->getId());
    
    return $project->getId();
  }
  
}