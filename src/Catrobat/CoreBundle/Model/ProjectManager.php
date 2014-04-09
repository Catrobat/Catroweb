<?php

namespace Catrobat\CoreBundle\Model;

use Catrobat\CoreBundle\Events\InvalidProjectUploadedEvent;
use Catrobat\CoreBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\CoreBundle\Model\Requests\AddProjectRequest;
use Catrobat\CoreBundle\Entity\Project;
use Knp\Component\Pager\Paginator;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Catrobat\CoreBundle\Events\ProjectBeforeInsertEvent;

class ProjectManager implements \Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface
{
  protected $file_extractor;
  protected $file_repository;
  protected $screenshot_repository;
  protected $event_dispatcher;
  protected $entity_manager;
  protected $project_repository;
  protected $pagination;

  public function __construct($file_extractor, $file_repository, $screenshot_repository, $entity_manager, $project_repository, EventDispatcherInterface $event_dispatcher)
  {
    $this->file_extractor = $file_extractor;
    $this->event_dispatcher = $event_dispatcher;
    $this->file_repository = $file_repository;
    $this->screenshot_repository = $screenshot_repository;
    $this->entity_manager = $entity_manager;
    $this->project_repository = $project_repository;
  }

  public function setPaginator(Paginator $paginator)
  {
    $this->pagination = $paginator;
  }
  
  public function addProject(AddProjectRequest $request)
  {
    $file = $request->getProjectfile();

    $extracted_file = $this->file_extractor->extract($file);
    try {
      $event = $this->event_dispatcher->dispatch("catrobat.project.before", new ProjectBeforeInsertEvent($extracted_file));
    }
    catch (InvalidCatrobatFileException $e) {
      $event = $this->event_dispatcher->dispatch("catrobat.project.invalid.upload", new InvalidProjectUploadedEvent($file));
      throw $e;
    }

    if ($event->isPropagationStopped())
    {
      return null;
    }
    
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
    $project->setApproved(false);
    $project->setUploadLanguage("en");
    
    $this->entity_manager->persist($project);
    $this->entity_manager->flush();
    
    $this->screenshot_repository->saveProjectAssets($extracted_file->getScreenshotPath(), $project->getId());
    $this->file_repository->saveProjectfile($file, $project->getId());
    
    return $project;
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
  
  public function findByOrderedByDate($limit = 1, $offset = 1)
  {
    return $this->project_repository->createQueryBuilder('e')->select('e')->orderBy('e.uploaded_at', 'DESC')->setFirstResult($offset)->setMaxResults($limit)->getQuery()->getResult();
//    $offset = $offset / $limit;
//    $query = $this->project_repository->createQueryBuilder('e')->select('e')->orderBy('e.uploaded_at', 'DESC');
//    return $this->pagination->paginate($query, 1, $limit);
  }
  
  
}