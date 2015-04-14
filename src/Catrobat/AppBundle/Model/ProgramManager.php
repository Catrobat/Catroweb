<?php

namespace Catrobat\AppBundle\Model;

use Catrobat\AppBundle\Events\InvalidProgramUploadedEvent;
use Catrobat\AppBundle\Events\ProgramAfterInsertEvent;
use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\AppBundle\Requests\AddProgramRequest;
use Catrobat\AppBundle\Entity\Program;
use Knp\Component\Pager\Paginator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Catrobat\AppBundle\Events\ProgramBeforeInsertEvent;
use Catrobat\AppBundle\Events\ProgramInsertEvent;
use Catrobat\AppBundle\Events\ProgramBeforePersistEvent;

class ProgramManager implements \Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface
{
  protected $file_extractor;
  protected $file_repository;
  protected $screenshot_repository;
  protected $event_dispatcher;
  protected $entity_manager;
  protected $program_repository;
  protected $pagination;

  public function __construct($file_extractor, $file_repository, $screenshot_repository, $entity_manager, $program_repository, EventDispatcherInterface $event_dispatcher)
  {
    $this->file_extractor = $file_extractor;
    $this->event_dispatcher = $event_dispatcher;
    $this->file_repository = $file_repository;
    $this->screenshot_repository = $screenshot_repository;
    $this->entity_manager = $entity_manager;
    $this->program_repository = $program_repository;
  }

  public function setPaginator(Paginator $paginator)
  {
    $this->pagination = $paginator;
  }
  
  public function addProgram(AddProgramRequest $request)
  {
    $file = $request->getProgramfile();

    $extracted_file = $this->file_extractor->extract($file);
    try {
      $event = $this->event_dispatcher->dispatch("catrobat.program.before", new ProgramBeforeInsertEvent($extracted_file));
    }
    catch (InvalidCatrobatFileException $e) {
      $event = $this->event_dispatcher->dispatch("catrobat.program.invalid.upload", new InvalidProgramUploadedEvent($file, $e));
      throw $e;
    }

    if ($event->isPropagationStopped())
    {
      return null;
    }

    /* @var $program Program*/
    
    $old_program = $this->findOneByNameAndUser($extracted_file->getName(), $request->getUser());
    if ($old_program != null)
    {
      $program = $old_program;
      //it's an update
      $program->incrementVersion();
    }
    else
    {
      $program = new Program();
    }
    $program->setName($extracted_file->getName());
    $program->setDescription($extracted_file->getDescription());
    $program->setUser($request->getUser());
    $program->setCatrobatVersion(1);
    $program->setCatrobatVersionName($extracted_file->getApplicationVersion());
    $program->setLanguageVersion($extracted_file->getLanguageVersion());
    $program->setUploadIp($request->getIp());
    $program->setRemixCount(0);
    $program->setFilesize($file->getSize());
    $program->setVisible(true);
    $program->setApproved(false);
    $program->setUploadLanguage("en");
    
    $this->event_dispatcher->dispatch("catrobat.program.before.persist", new ProgramBeforePersistEvent($extracted_file, $program));
    
    $this->entity_manager->persist($program);

    $this->event_dispatcher->dispatch("catrobat.program.after.insert", new ProgramAfterInsertEvent($extracted_file, $program));

    $this->entity_manager->persist($program);
    $this->entity_manager->flush();

    if ($extracted_file->getScreenshotPath() == null)
    {
      // Todo: maybe for later implementations
    }
    else
    {
      $this->screenshot_repository->saveProgramAssets($extracted_file->getScreenshotPath(), $program->getId());
    }
    $this->file_repository->saveProgram($extracted_file, $program->getId());

    $event = $this->event_dispatcher->dispatch("catrobat.program.successful.upload", new ProgramInsertEvent());
    
    return $program;
  }

  public function findOneByNameAndUser($program_name, $user)
  {
    return $this->program_repository->findOneBy(array('name' => $program_name, 'user' => $user));
  }
  
  public function findOneByName($programName)
  {
    return $this->program_repository->findOneByName($programName);
  }

  public function getUserPrograms($user_id)
  {
    return $this->program_repository->getUserPrograms($user_id);
  }

  public function findAll()
  {
    return $this->program_repository->findAll();
  }

  public function find($id)
  {
    return $this->program_repository->find($id);
  }
  
  public function getRecentPrograms($flavor, $limit = null, $offset = null)
  {
    return $this->program_repository->getRecentPrograms($flavor, $limit, $offset);
  }

  public function getMostViewedPrograms($flavor, $limit = null, $offset = null)
  {
    return $this->program_repository->getMostViewedPrograms($flavor, $limit, $offset);
  }
  
  public function getMostDownloadedPrograms($flavor, $limit = null, $offset = null)
  {
    return $this->program_repository->getMostDownloadedPrograms($flavor, $limit, $offset);
  }

  public function search($query, $limit=10, $offset=0)
  {
    return $this->program_repository->search($query, $limit, $offset);
  }

  public function searchCount($query)
  {
    return $this->program_repository->searchCount($query);
  }


  public function searchCountUserPrograms($user_id)
  {
    return $this->program_repository->searchCountUserPrograms($user_id);
  }


  public function getTotalPrograms($flavor)
  {
    return $this->program_repository->getTotalPrograms($flavor);
  }

  public function increaseViews(Program $program)
  {
    $program->setViews($program->getViews() + 1);
    $this->save($program);
  }

  public function increaseDownloads(Program $program)
  {
    $program->setDownloads($program->getDownloads() + 1);
    $this->save($program);
  }

  public function save(Program $program)
  {
    $this->entity_manager->persist($program);
    $this->entity_manager->flush();
  }

}