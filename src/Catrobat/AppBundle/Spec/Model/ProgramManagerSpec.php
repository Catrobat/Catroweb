<?php

namespace Catrobat\AppBundle\Spec\Model;

use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\AppBundle\Services\CatrobatFileExtractor;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Catrobat\AppBundle\Model\Requests\AddProgramRequest;
use Symfony\Component\HttpFoundation\File\File;

class ProgramManagerSpec extends ObjectBehavior
{

  /**
   * @param \Catrobat\AppBundle\Services\CatrobatFileExtractor $file_extractor          
   * @param \Catrobat\AppBundle\Services\ProgramFileRepository $file_repository          
   * @param \Catrobat\AppBundle\Services\ScreenshotRepository $screenshot_repository
   * @param \Catrobat\AppBundle\Entity\ProgramRepository $program_repository          
   * @param \Doctrine\ORM\EntityManager $entity_manager          
   * @param \Symfony\Component\HttpFoundation\File\File $file          
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   * @param \Catrobat\AppBundle\Entity\User $user          
   * @param \Catrobat\AppBundle\Model\Requests\AddProgramRequest $request          
   * @param \Catrobat\AppBundle\Model\ExtractedCatrobatFile $extracted_file          
   * @param \Catrobat\AppBundle\Entity\Program $inserted_program
   * @param \Symfony\Component\EventDispatcher\Event $event
   * @param \Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException $validation_exception
   */
  
  function let($file_extractor, $file_repository, $screenshot_repository, $entity_manager, $program_repository, $event_dispatcher, $request, $file, $user, $extracted_file, $inserted_program, $event)
  {
    $this->beConstructedWith($file_extractor, $file_repository, $screenshot_repository, $entity_manager, $program_repository, $event_dispatcher);
    $request->getProgramfile()->willReturn($file);
    $request->getUser()->willReturn($user);
    $request->getIp()->willReturn("127.0.0.1");
    $file_extractor->extract($file)->willReturn($extracted_file);
    $inserted_program->getId()->willReturn(1);
    $event_dispatcher->dispatch(Argument::any(),Argument::any())->willReturn($event);
  }

  function it_is_initializable()
  {
    $this->shouldHaveType('Catrobat\AppBundle\Model\ProgramManager');
  }

  function it_returns_the_program_after_successfully_adding_a_program($request, $entity_manager)
  {
    $entity_manager->persist(Argument::any())->shouldBecalled();
    $entity_manager->flush()->shouldBecalled();
    $this->addProgram($request)->shouldHaveType('Catrobat\AppBundle\Entity\Program');
  }

  function it_fires_an_event_before_inserting_a_program($request, $event_dispatcher)
  {
    $this->addProgram($request)->shouldHaveType('Catrobat\AppBundle\Entity\Program');
    $event_dispatcher->dispatch("catrobat.program.before",Argument::type('Catrobat\AppBundle\Events\ProgramBeforeInsertEvent'))->shouldHaveBeenCalled();
  }

  function it_fires_an_event_when_the_program_is_invalid($request, $event_dispatcher)
  {
    $validation_exception = new InvalidCatrobatFileException("500");
    $event_dispatcher->dispatch("catrobat.program.before",Argument::type('Catrobat\AppBundle\Events\ProgramBeforeInsertEvent'))->willThrow($validation_exception)->shouldBeCalled();
    
    $this->shouldThrow('\Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException')->during('addProgram',array($request));
    
    $event_dispatcher->dispatch("catrobat.program.invalid.upload",Argument::type('Catrobat\AppBundle\Events\InvalidProgramUploadedEvent'))->shouldHaveBeenCalled();
  }
  
   function it_fires_an_event_when_the_program_is_stored($request, $event_dispatcher)
   {
     $this->addProgram($request)->shouldHaveType('Catrobat\AppBundle\Entity\Program');
     $event_dispatcher->dispatch("catrobat.program.successful.upload",Argument::type('Catrobat\AppBundle\Events\ProgramInsertEvent'))->shouldHaveBeenCalled();
   }
  
}
