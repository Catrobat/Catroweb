<?php

namespace Catrobat\CoreBundle\Model;

use Catrobat\CoreBundle\Model\Requests\AddProjectRequest;
use Catrobat\CoreBundle\Entity\Project;

class ProjectManager
{
  protected $file_extractor;
  protected $file_repository;
  protected $screenshot_repository;
  protected $doctrine;
  protected $extracted_file_validator;
  protected $entity_manager;
  protected $project_repository;

  public function __construct($file_extractor, $file_repository, $screenshot_repository, $doctrine, $extracted_file_validator)
  {
    $this->file_extractor = $file_extractor;
    $this->extracted_file_validator = $extracted_file_validator;
    $this->file_repository = $file_repository;
    $this->screenshot_repository = $screenshot_repository;
    $this->doctrine = $doctrine;
    $this->entity_manager = $this->doctrine->getManager();
    $this->project_repository = $this->doctrine->getRepository('CatrobatCoreBundle:Project');
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
    $project->setCatrobatVersion(1);
    $project->setCatrobatVersionName($extracted_file->getApplicationVersion());
    $project->setLanguageVersion($extracted_file->getLanguageVersion());
    $project->setUploadIp("127.0.0.1");
    $project->setRemixCount(0);
    $project->setFilesize(0);
    $project->setVisible(true);
    $project->setUploadLanguage("en");
    
    $this->entity_manager->persist($project);
    $this->entity_manager->flush();
    
    $this->screenshot_repository->saveProjectAssets($extracted_file->getScreenshotPath(), $project->getId());
    $this->file_repository->saveProjectfile($file, $project->getId());
    
    return $project->getId();
  }

  public function findOneByName($projectName)
  {
    return $this->project_repository->findOneByName($projectName);
  }

  public function findAll()
  {
    return $this->project_repository->findAll();
  }

  public function find($id)
  {
    return $this->project_repository->find($id);
  }
  
  public function findByOrderedByDownloads($limit = null, $offset = null)
  {
    return $this->project_repository->createQueryBuilder('e')->select('e')->orderBy('e.downloads', 'DESC')->setFirstResult($offset)->setMaxResults($limit)->getQuery()->getResult();
  }

  public function findByOrderedByViews($limit = null, $offset = null)
  {
    return $this->project_repository->findBy(array(),array('views' => 'desc'), $limit, $offset);
  }
  
  public function findByOrderedByDate($limit = null, $offset = null)
  {
    return $this->project_repository->findBy(array(),array('uploaded_at' => 'desc'), $limit, $offset);
  }
  
}