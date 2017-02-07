<?php
namespace spec\Catrobat\AppBundle\Entity;

use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Doctrine\ORM\Mapping\ClassMetadata;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Catrobat\AppBundle\Entity\GameJam;
use Sonata\CoreBundle\Model\Metadata;

class ProgramManagerSpec extends ObjectBehavior
{

    /**
     *
     * @param \Catrobat\AppBundle\Services\CatrobatFileExtractor $file_extractor
     * @param \Catrobat\AppBundle\Services\ProgramFileRepository $file_repository
     * @param \Catrobat\AppBundle\Services\ScreenshotRepository $screenshot_repository
     * @param \Catrobat\AppBundle\Entity\ProgramRepository $program_repository
     * @param \Catrobat\AppBundle\Entity\TagRepository $tag_repository
     * @param \Catrobat\AppBundle\Entity\ProgramLikeRepository $program_like_repository
     * @param \Doctrine\ORM\EntityManager $entity_manager
     * @param \Symfony\Component\HttpFoundation\File\File $file
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
     * @param \Catrobat\AppBundle\Entity\User $user
     * @param \Catrobat\AppBundle\Requests\AddProgramRequest $request
     * @param \Catrobat\AppBundle\Services\ExtractedCatrobatFile $extracted_file
     * @param \Catrobat\AppBundle\Entity\Program $inserted_program
     * @param \Symfony\Component\EventDispatcher\Event $event
     * @param \Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException $validation_exception
     * @param \Doctrine\ORM\Mapping\ClassMetadata $metadata
     */
    public function let($file_extractor, $file_repository, $screenshot_repository, $entity_manager, $program_repository, $event_dispatcher, $request, $file, $user, $extracted_file, $inserted_program, $tag_repository, $program_like_repository)
    {
        $this->beConstructedWith($file_extractor, $file_repository, $screenshot_repository, $entity_manager, $program_repository, $tag_repository, $program_like_repository, $event_dispatcher);
        $request->getProgramfile()->willReturn($file);
        $request->getUser()->willReturn($user);
        $request->getIp()->willReturn('127.0.0.1');
        $request->getGamejam()->willReturn(null);
        $request->getLanguage()->willReturn('en');
        $file_extractor->extract($file)->willReturn($extracted_file);
        $inserted_program->getId()->willReturn(1);
        $event_dispatcher->dispatch(Argument::any(), Argument::any())->willReturnArgument(1);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Catrobat\AppBundle\Entity\ProgramManager');
    }

    public function it_returns_the_program_after_successfully_adding_a_program($request, $entity_manager, $metadata)
    {
        $metadata->getFieldNames()->willReturn(array('id'));
        $entity_manager->getClassMetadata(Argument::any())->willReturn($metadata);
        $entity_manager->persist(Argument::type('\Catrobat\AppBundle\Entity\Program'))->shouldBecalled();
        $entity_manager->flush()->shouldBecalled();
        $entity_manager->refresh(Argument::type('\Catrobat\AppBundle\Entity\Program'))->shouldBecalled();
        $this->addProgram($request)->shouldHaveType('Catrobat\AppBundle\Entity\Program');
    }

    public function it_saves_the_program_to_the_file_repository($request, $entity_manager, $extracted_file, $file_repository, $metadata)
    {
        $metadata->getFieldNames()->willReturn(array('id'));
        $entity_manager->getClassMetadata(Argument::any())->willReturn($metadata);

        $entity_manager->persist(Argument::type('\Catrobat\AppBundle\Entity\Program'))->will(function ($args) {
            $args[0]->setId(1);
            return $args[0];
        });
        $entity_manager->flush()->shouldBecalled();
        $entity_manager->refresh(Argument::type('\Catrobat\AppBundle\Entity\Program'))->shouldBecalled();

        $this->addProgram($request);
        $file_repository->saveProgram($extracted_file, 1)->shouldHaveBeenCalled();
    }

    public function it_saves_the_screenshots_to_the_screenshot_repository($request, $entity_manager, $extracted_file, $screenshot_repository, $metadata)
    {
        $metadata->getFieldNames()->willReturn(array('id'));
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
        $screenshot_repository->saveProgramAssets('./path/to/screenshot', 1)->shouldHaveBeenCalled();
    }

    public function it_fires_an_event_before_inserting_a_program($request, $event_dispatcher, $metadata, $entity_manager)
    {
        $metadata->getFieldNames()->willReturn(array('id'));
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

    public function it_fires_an_event_when_the_program_is_invalid($request, $event_dispatcher)
    {
        $validation_exception = new InvalidCatrobatFileException('500', 500);
        $event_dispatcher->dispatch('catrobat.program.before', Argument::type('Catrobat\AppBundle\Events\ProgramBeforeInsertEvent'))
            ->willThrow($validation_exception)
            ->shouldBeCalled();

        $this->shouldThrow('\Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException')->during('addProgram', array(
            $request
        ));

        $event_dispatcher->dispatch('catrobat.program.invalid.upload', Argument::type('Catrobat\AppBundle\Events\InvalidProgramUploadedEvent'))->shouldHaveBeenCalled();
    }

    public function it_fires_an_event_when_the_program_is_stored($request, $event_dispatcher, $metadata, $entity_manager)
    {
        $metadata->getFieldNames()->willReturn(array('id'));
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

    public function it_marks_the_game_as_gamejam_submission_if_a_jam_is_provided($request, $metadata, $entity_manager)
    {
        $metadata->getFieldNames()->willReturn(array('id'));
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