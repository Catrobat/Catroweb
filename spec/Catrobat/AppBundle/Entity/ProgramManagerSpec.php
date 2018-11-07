<?php

namespace spec\Catrobat\AppBundle\Entity;

use Catrobat\AppBundle\Entity\Program;
use Catrobat\AppBundle\Entity\ProgramLikeRepository;
use Catrobat\AppBundle\Entity\ProgramRepository;
use Catrobat\AppBundle\Entity\TagRepository;
use Catrobat\AppBundle\Entity\User;
use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\AppBundle\Requests\AddProgramRequest;
use Catrobat\AppBundle\Services\CatrobatFileExtractor;
use Catrobat\AppBundle\Services\ExtractedCatrobatFile;
use Catrobat\AppBundle\Services\ProgramFileRepository;
use Catrobat\AppBundle\Services\ScreenshotRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Catrobat\AppBundle\Entity\GameJam;
use Sonata\CoreBundle\Model\Metadata;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;

class ProgramManagerSpec extends ObjectBehavior
{

  public function let(CatrobatFileExtractor $file_extractor, ProgramFileRepository $file_repository, ScreenshotRepository $screenshot_repository, EntityManager $entity_manager, ProgramRepository $program_repository, EventDispatcherInterface $event_dispatcher, AddProgramRequest $request, File $file, User $user, ExtractedCatrobatFile $extracted_file, Program $inserted_program, TagRepository $tag_repository, ProgramLikeRepository $program_like_repository)
  {
    $this->beConstructedWith($file_extractor, $file_repository, $screenshot_repository, $entity_manager, $program_repository, $tag_repository, $program_like_repository, $event_dispatcher);
    $request->getProgramfile()->willReturn($file);
    $request->getUser()->willReturn($user);
    $request->getIp()->willReturn('127.0.0.1');
    $request->getGamejam()->willReturn(null);
    $request->getLanguage()->willReturn('en');
    $request->getFlavor()->willReturn('pocketcode');
    $file_extractor->extract($file)->willReturn($extracted_file);
    $inserted_program->getId()->willReturn(1);
    $event_dispatcher->dispatch(Argument::any(), Argument::any())->willReturnArgument(1);
  }

  public function it_is_initializable()
  {
    $this->shouldHaveType('Catrobat\AppBundle\Entity\ProgramManager');
  }

  public function it_returns_the_program_after_successfully_adding_a_program(AddProgramRequest $request, EntityManager $entity_manager, ClassMetadata $metadata)
  {
    $metadata->getFieldNames()->willReturn(['id']);
    $entity_manager->getClassMetadata(Argument::any())->willReturn($metadata);
    $entity_manager->persist(Argument::type('\Catrobat\AppBundle\Entity\Program'))->shouldBecalled();
    $entity_manager->flush()->shouldBecalled();
    $entity_manager->refresh(Argument::type('\Catrobat\AppBundle\Entity\Program'))->shouldBecalled();
    $this->addProgram($request)->shouldHaveType('Catrobat\AppBundle\Entity\Program');
  }

  public function it_saves_the_program_to_the_file_repository_if_the_upload_succeeded(AddProgramRequest $request, EntityManager $entity_manager, ExtractedCatrobatFile $extracted_file, ProgramFileRepository $file_repository, ClassMetadata $metadata)
  {
    $metadata->getFieldNames()->willReturn(['id']);
    $entity_manager->getClassMetadata(Argument::any())->willReturn($metadata);

    $entity_manager->persist(Argument::type('\Catrobat\AppBundle\Entity\Program'))->will(function ($args) {
      $args[0]->setId(1);

      return $args[0];
    });
    $entity_manager->flush()->shouldBecalled();
    $entity_manager->refresh(Argument::type('\Catrobat\AppBundle\Entity\Program'))->shouldBecalled();

    $this->addProgram($request);
    $file_repository->saveProgramTemp($extracted_file, 1)->shouldHaveBeenCalled();
    $file_repository->makeTempProgramPerm(1)->shouldHaveBeenCalled();
  }

  public function it_saves_the_screenshots_to_the_screenshot_repository(AddProgramRequest $request, EntityManager $entity_manager, ExtractedCatrobatFile $extracted_file, ScreenshotRepository $screenshot_repository, ClassMetadata $metadata)
  {
    $metadata->getFieldNames()->willReturn(['id']);
    $entity_manager->getClassMetadata(Argument::any())->willReturn($metadata);

    $extracted_file->getScreenshotPath()->willReturn('./path/to/screenshot');
    $extracted_file->getName()->willReturn(null);
    $extracted_file->getDescription()->willReturn(null);
    $extracted_file->getApplicationVersion()->willReturn(null);
    $extracted_file->getLanguageVersion()->willReturn(null);
    $extracted_file->getTags()->willReturn(null);

    $entity_manager->persist(Argument::type('\Catrobat\AppBundle\Entity\Program'))->will(function ($args) {
      $args[0]->setId(1);

      return $args[0];
    });
    $entity_manager->flush()->shouldBecalled();
    $entity_manager->refresh(Argument::type('\Catrobat\AppBundle\Entity\Program'))->shouldBecalled();

    $this->addProgram($request);
    $screenshot_repository->saveProgramAssetsTemp('./path/to/screenshot', 1)->shouldHaveBeenCalled();
    $screenshot_repository->makeTempProgramAssetsPerm(1)->shouldHaveBeenCalled();
  }

  public function it_fires_an_event_before_inserting_a_program(AddProgramRequest $request, EventDispatcherInterface $event_dispatcher, ClassMetadata $metadata, EntityManager $entity_manager)
  {
    $metadata->getFieldNames()->willReturn(['id']);
    $entity_manager->getClassMetadata(Argument::any())->willReturn($metadata);

    $entity_manager->persist(Argument::type('\Catrobat\AppBundle\Entity\Program'))->will(function ($args) {
      $args[0]->setId(1);

      return $args[0];
    });
    $entity_manager->flush()->shouldBecalled();
    $entity_manager->refresh(Argument::type('\Catrobat\AppBundle\Entity\Program'))->shouldBecalled();

    $this->addProgram($request)->shouldHaveType('Catrobat\AppBundle\Entity\Program');
    $event_dispatcher->dispatch('catrobat.program.before', Argument::type('Catrobat\AppBundle\Events\ProgramBeforeInsertEvent'))->shouldHaveBeenCalled();
  }

  public function it_fires_an_event_when_the_program_is_invalid(AddProgramRequest $request, EventDispatcherInterface $event_dispatcher)
  {
    $validation_exception = new InvalidCatrobatFileException('500', 500);
    $event_dispatcher->dispatch('catrobat.program.before', Argument::type('Catrobat\AppBundle\Events\ProgramBeforeInsertEvent'))
      ->willThrow($validation_exception)
      ->shouldBeCalled();

    $this->shouldThrow('\Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException')->during('addProgram', [
      $request,
    ]);

    $event_dispatcher->dispatch('catrobat.program.invalid.upload', Argument::type('Catrobat\AppBundle\Events\InvalidProgramUploadedEvent'))->shouldHaveBeenCalled();
  }

  public function it_fires_an_event_when_the_program_is_stored(AddProgramRequest $request, EventDispatcherInterface $event_dispatcher, ClassMetadata $metadata, EntityManager $entity_manager)
  {
    $metadata->getFieldNames()->willReturn(['id']);
    $entity_manager->getClassMetadata(Argument::any())->willReturn($metadata);

    $entity_manager->persist(Argument::type('\Catrobat\AppBundle\Entity\Program'))->will(function ($args) {
      $args[0]->setId(1);

      return $args[0];
    });
    $entity_manager->flush()->shouldBecalled();
    $entity_manager->refresh(Argument::type('\Catrobat\AppBundle\Entity\Program'))->shouldBecalled();

    $this->addProgram($request)->shouldHaveType('Catrobat\AppBundle\Entity\Program');
    $event_dispatcher->dispatch('catrobat.program.successful.upload', Argument::type('Catrobat\AppBundle\Events\ProgramInsertEvent'))->shouldHaveBeenCalled();
  }

  public function it_marks_the_game_as_gamejam_submission_if_a_jam_is_provided(AddProgramRequest $request, ClassMetadata $metadata, EntityManager $entity_manager)
  {
    $metadata->getFieldNames()->willReturn(['id']);
    $entity_manager->getClassMetadata(Argument::any())->willReturn($metadata);

    $entity_manager->persist(Argument::type('\Catrobat\AppBundle\Entity\Program'))->will(function ($args) {
      $args[0]->setId(1);

      return $args[0];
    });
    $entity_manager->flush()->shouldBeCalled();
    $entity_manager->refresh(Argument::type('\Catrobat\AppBundle\Entity\Program'))->shouldBecalled();

    $request->getGamejam()->willReturn(new GameJam());
    $program = $this->addProgram($request)->getWrappedObject();
    expect($program->getGamejam())->notToBeNull();
  }
}