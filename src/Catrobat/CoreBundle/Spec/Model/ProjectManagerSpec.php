<?php

namespace Catrobat\CoreBundle\Spec\Model;

use Catrobat\CoreBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\CoreBundle\Services\CatrobatFileExtractor;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Catrobat\CoreBundle\Model\Requests\AddProjectRequest;
use Symfony\Component\HttpFoundation\File\File;

class ProjectManagerSpec extends ObjectBehavior
{

  /**
   * @param \Catrobat\CoreBundle\Services\CatrobatFileExtractor $file_extractor          
   * @param \Catrobat\CoreBundle\Services\ProjectFileRepository $file_repository          
   * @param \Catrobat\CoreBundle\Services\ScreenshotRepository $screenshot_repository
   * @param \Catrobat\CoreBundle\Entity\ProjectRepository $project_repository          
   * @param \Doctrine\ORM\EntityManager $entity_manager          
   * @param \Symfony\Component\HttpFoundation\File\File $file          
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   * @param \Catrobat\CoreBundle\Entity\User $user          
   * @param \Catrobat\CoreBundle\Model\Requests\AddProjectRequest $request          
   * @param \Catrobat\CoreBundle\Model\ExtractedCatrobatFile $extracted_file          
   * @param \Catrobat\CoreBundle\Entity\Project $inserted_project
   * @param \Symfony\Component\EventDispatcher\Event $event
   * @param \Catrobat\CoreBundle\Exceptions\InvalidCatrobatFileException $validation_exception
   */
  
  function let($file_extractor, $file_repository, $screenshot_repository, $entity_manager, $project_repository, $event_dispatcher, $request, $file, $user, $extracted_file, $inserted_project, $event)
  {
    $this->beConstructedWith($file_extractor, $file_repository, $screenshot_repository, $entity_manager, $project_repository, $event_dispatcher);
    $request->getProjectfile()->willReturn($file);
    $request->getUser()->willReturn($user);
    $file_extractor->extract($file)->willReturn($extracted_file);
    $inserted_project->getId()->willReturn(1);
    $event_dispatcher->dispatch(Argument::any(),Argument::any())->willReturn($event);
  }

  function it_is_initializable()
  {
    $this->shouldHaveType('Catrobat\CoreBundle\Model\ProjectManager');
  }

  function it_returns_the_project_after_successfully_adding_a_project($request, $entity_manager)
  {
    $entity_manager->persist(Argument::any())->shouldBecalled();
    $entity_manager->flush()->shouldBecalled();
    $this->addProject($request)->shouldHaveType('Catrobat\CoreBundle\Entity\Project');
  }

  function it_fires_an_event_before_inserting_a_project($request, $event_dispatcher)
  {
    $this->addProject($request)->shouldHaveType('Catrobat\CoreBundle\Entity\Project');
    $event_dispatcher->dispatch("catrobat.project.before",Argument::type('Catrobat\CoreBundle\Events\ProjectBeforeInsertEvent'))->shouldHaveBeenCalled();
  }

  function it_fires_an_event_when_the_project_is_invalid($request, $event_dispatcher)
  {
    $validation_exception = new InvalidCatrobatFileException("500");
    $event_dispatcher->dispatch("catrobat.project.before",Argument::type('Catrobat\CoreBundle\Events\ProjectBeforeInsertEvent'))->willThrow($validation_exception)->shouldBeCalled();
    
    $this->shouldThrow('\Catrobat\CoreBundle\Exceptions\InvalidCatrobatFileException')->during('addProject',array($request));
    
    $event_dispatcher->dispatch("catrobat.project.invalid.upload",Argument::type('Catrobat\CoreBundle\Events\InvalidProjectUploadedEvent'))->shouldHaveBeenCalled();
  }
  
   function it_fires_an_event_when_the_project_is_stored($request, $event_dispatcher)
   {
     $this->addProject($request)->shouldHaveType('Catrobat\CoreBundle\Entity\Project');
     $event_dispatcher->dispatch("catrobat.project.successful.upload",Argument::type('Catrobat\CoreBundle\Events\ProjectInsertEvent'))->shouldHaveBeenCalled();
   }
  
}
