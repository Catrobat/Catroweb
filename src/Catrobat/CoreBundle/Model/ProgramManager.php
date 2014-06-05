<?php

namespace Catrobat\CoreBundle\Model;

use Catrobat\CoreBundle\Events\InvalidProgramUploadedEvent;
use Catrobat\CoreBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\CoreBundle\Model\Requests\AddProgramRequest;
use Catrobat\CoreBundle\Entity\Program;
use Knp\Component\Pager\Paginator;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Catrobat\CoreBundle\Events\ProgramBeforeInsertEvent;
use Catrobat\CoreBundle\Events\ProgramInsertEvent;

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
      $event = $this->event_dispatcher->dispatch("catrobat.program.invalid.upload", new InvalidProgramUploadedEvent($file));
      throw $e;
    }

    if ($event->isPropagationStopped())
    {
      return null;
    }
    
    $program = new Program();
    $program->setName($extracted_file->getName());
    $program->setDescription($extracted_file->getDescription());
    $program->setFilename($file->getFilename());
    $program->setThumbnail("");
    $program->setScreenshot("");
    $program->setUser($request->getUser());
    $program->setCatrobatVersion(1);
    $program->setCatrobatVersionName($extracted_file->getApplicationVersion());
    $program->setLanguageVersion($extracted_file->getLanguageVersion());
    $program->setUploadIp("127.0.0.1");
    $program->setRemixCount(0);
    $program->setFilesize(0);
    $program->setVisible(true);
    $program->setApproved(false);
    $program->setUploadLanguage("en");
    
    $this->entity_manager->persist($program);
    $this->entity_manager->flush();
    
    $this->screenshot_repository->saveProgramAssets($extracted_file->getScreenshotPath(), $program->getId());
    $this->file_repository->saveProgramfile($file, $program->getId());

    $event = $this->event_dispatcher->dispatch("catrobat.program.successful.upload", new ProgramInsertEvent());
    
    return $program;
  }

  public function findOneByName($programName)
  {
    return $this->program_repository->findOneByName($programName);
  }

  public function findAll()
  {
    return $this->program_repository->findAll();
  }

  public function find($id)
  {
    return $this->program_repository->find($id);
  }
  
  public function getRecentPrograms($limit = null, $offset = null)
  {
    return $this->program_repository->getRecentPrograms($limit, $offset);
  }

  public function findByOrderedByViews($limit = null, $offset = null)
  {
    return $this->program_repository->findByOrderedByViews($limit, $offset);
  }
  
  public function getMostDownloadedPrograms($limit = null, $offset = null)
  {
    return $this->program_repository->getMostDownloadedPrograms($limit, $offset);
//    $offset = $offset / $limit;
//    $query = $this->program_repository->createQueryBuilder('e')->select('e')->orderBy('e.uploaded_at', 'DESC');
//    return $this->pagination->paginate($query, 1, $limit);
  }

  public function search($query, $limit=10, $offset=0)
  {
    return $this->program_repository->search($query, $limit, $offset);
  }

  public function searchCount($query)
  {
    return $this->program_repository->searchCount($query);
  }

  public function getTotalPrograms()
  {
    return $this->program_repository->getTotalPrograms();
  }

}