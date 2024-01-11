<?php

namespace Tests\PhpUnit\Project;

use App\DB\Entity\Project\Program;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\Project\ExtensionRepository;
use App\DB\EntityRepository\Project\ProgramLikeRepository;
use App\DB\EntityRepository\Project\ProgramRepository;
use App\DB\EntityRepository\Project\Special\ExampleRepository;
use App\DB\EntityRepository\Project\Special\FeaturedRepository;
use App\DB\EntityRepository\Project\TagRepository;
use App\Project\AddProgramRequest;
use App\Project\CatrobatFile\CatrobatFileExtractor;
use App\Project\CatrobatFile\CatrobatFileSanitizer;
use App\Project\CatrobatFile\ExtractedCatrobatFile;
use App\Project\CatrobatFile\InvalidCatrobatFileException;
use App\Project\CatrobatFile\ProgramFileRepository;
use App\Project\Event\ProgramAfterInsertEvent;
use App\Project\Event\ProgramBeforeInsertEvent;
use App\Project\Event\ProgramBeforePersistEvent;
use App\Project\ProjectManager;
use App\Storage\ScreenshotRepository;
use App\User\Notification\NotificationManager;
use App\Utils\RequestHelper;
use Doctrine\ORM\EntityManager;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\UrlHelper;
use Symfony\Component\Security\Core\Security;

/**
 * @internal
 *
 * @covers \App\Project\ProjectManager
 */
class ProjectManagerTest extends TestCase
{
  private ProjectManager $program_manager;

  private MockObject|ProgramFileRepository $file_repository;

  private MockObject|ScreenshotRepository $screenshot_repository;

  private EntityManager|MockObject $entity_manager;

  private EventDispatcherInterface|MockObject $event_dispatcher;

  private AddProgramRequest|MockObject $request;

  private ExtractedCatrobatFile|MockObject $extracted_file;

  private MockObject|ProgramBeforeInsertEvent $programBeforeInsertEvent;

  private MockObject|ProgramAfterInsertEvent $programAfterInsertEvent;

  /**
   * @throws \Exception
   */
  protected function setUp(): void
  {
    $file_extractor = $this->createMock(CatrobatFileExtractor::class);
    $this->file_repository = $this->createMock(ProgramFileRepository::class);
    $this->screenshot_repository = $this->createMock(ScreenshotRepository::class);
    $this->entity_manager = $this->createMock(EntityManager::class);
    $program_repository = $this->createMock(ProgramRepository::class);
    $this->event_dispatcher = $this->createMock(EventDispatcherInterface::class);
    $this->request = $this->createMock(AddProgramRequest::class);
    $user = $this->createMock(User::class);
    $this->extracted_file = $this->createMock(ExtractedCatrobatFile::class);
    $inserted_program = $this->createMock(Program::class);
    $tag_repository = $this->createMock(TagRepository::class);
    $program_like_repository = $this->createMock(ProgramLikeRepository::class);
    $featured_repository = $this->createMock(FeaturedRepository::class);
    $example_repository = $this->createMock(ExampleRepository::class);
    $logger = $this->createMock(LoggerInterface::class);
    $app_request = $this->createMock(RequestHelper::class);
    $this->programBeforeInsertEvent = $this->createMock(ProgramBeforeInsertEvent::class);
    $this->programAfterInsertEvent = $this->createMock(ProgramAfterInsertEvent::class);
    $extension_repository = $this->createMock(ExtensionRepository::class);
    $catrobat_file_sanitizer = $this->createMock(CatrobatFileSanitizer::class);
    $notification_service = $this->createMock(NotificationManager::class);
    $program_finder = $this->createMock(TransformedFinder::class);
    $url_helper = new UrlHelper(new RequestStack());
    $security = $this->createMock(Security::class);

    $this->program_manager = new ProjectManager(
      $file_extractor, $this->file_repository, $this->screenshot_repository,
      $this->entity_manager, $program_repository, $tag_repository, $program_like_repository, $featured_repository,
      $example_repository, $this->event_dispatcher, $logger, $app_request, $extension_repository,
      $catrobat_file_sanitizer, $notification_service, $program_finder, $url_helper, $security
    );

    $this->extracted_file->expects($this->any())->method('getName')->willReturn('TestProject');
    $this->extracted_file->expects($this->any())->method('getApplicationVersion')->willReturn('0.999');
    $this->extracted_file->expects($this->any())->method('getProgramXmlProperties')
      ->willReturn(new \SimpleXMLElement('<empty></empty>')
      )
    ;
    $this->extracted_file->expects($this->any())->method('getDirHash')->willReturn('451f778e4bf3');

    fopen('/tmp/PhpUnitTest', 'w');
    $file = new File('/tmp/PhpUnitTest');
    $this->request->expects($this->any())->method('getProgramfile')->willReturn($file);
    $this->request->expects($this->any())->method('getUser')->willReturn($user);
    $this->request->expects($this->any())->method('getIp')->willReturn('127.0.0.1');
    $this->request->expects($this->any())->method('getLanguage')->willReturn('en');
    $this->request->expects($this->any())->method('getFlavor')->willReturn('pocketcode');
    $file_extractor->expects($this->any())->method('extract')->with($file)->willReturn($this->extracted_file);
    $inserted_program->expects($this->any())->method('getId')->willReturn('1');

    $this->programBeforeInsertEvent->expects($this->any())->method('isPropagationStopped')->willReturn(false);
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(ProjectManager::class, $this->program_manager);
  }

  /**
   * @throws \Exception
   */
  public function testReturnsTheProgramAfterSuccessfullyAddingAProgram(): void
  {
    $func = function (Program $project): Program {
      $project->setId('1');

      return $project;
    };

    $this->entity_manager->expects($this->atLeastOnce())->method('persist')->with($this->isInstanceOf(Program::class))
      ->will($this->returnCallback($func))
    ;
    $this->entity_manager->expects($this->atLeastOnce())->method('flush');
    $this->entity_manager->expects($this->atLeastOnce())->method('refresh')
      ->with($this->isInstanceOf(Program::class))
    ;
    $this->event_dispatcher->expects($this->atLeastOnce())->method('dispatch')
      ->willReturn($this->programBeforeInsertEvent)
    ;

    $this->assertInstanceOf(Program::class, $this->program_manager->addProgram($this->request));
  }

  /**
   * @throws \Exception
   */
  public function testSavesTheProgramToTheFileRepositoryIfTheUploadSucceeded(): void
  {
    $this->entity_manager->expects($this->atLeastOnce())->method('persist')
      ->will($this->returnCallback(function (Program $project): Program {
        $project->setId('1');

        return $project;
      }))
    ;
    fopen('/tmp/PhpUnitTest', 'w');
    $file = new File('/tmp/PhpUnitTest');
    $this->file_repository->expects($this->atLeastOnce())->method('saveProjectZipFile')->with($file, 1);
    $this->entity_manager->expects($this->atLeastOnce())->method('flush');
    $this->entity_manager->expects($this->atLeastOnce())->method('refresh')->with($this->isInstanceOf(Program::class));

    $this->event_dispatcher->expects($this->atLeastOnce())->method('dispatch')->willReturn($this->programBeforeInsertEvent);

    $this->program_manager->addProgram($this->request);
  }

  /**
   * @throws \Exception
   */
  public function testSavesTheScreenshotsToTheScreenshotRepository(): void
  {
    $this->extracted_file->expects($this->atLeastOnce())->method('getScreenshotPath')->willReturn('./path/to/screenshot');
    $this->extracted_file->expects($this->atLeastOnce())->method('getDescription')->willReturn('');
    $this->extracted_file->expects($this->atLeastOnce())->method('getLanguageVersion')->willReturn('');
    $this->extracted_file->expects($this->atLeastOnce())->method('getTags')->willReturn([]);
    $this->extracted_file->expects($this->atLeastOnce())->method('isDebugBuild')->willReturn(false);
    $this->entity_manager->expects($this->atLeastOnce())->method('persist')
      ->will($this->returnCallback(function (Program $project): Program {
        $project->setId('1');

        return $project;
      }))
    ;
    $this->entity_manager->expects($this->atLeastOnce())->method('flush');
    $this->entity_manager->expects($this->atLeastOnce())
      ->method('refresh')->with($this->isInstanceOf(Program::class))
    ;

    $this->screenshot_repository->expects($this->atLeastOnce())
      ->method('saveProgramAssetsTemp')->with('./path/to/screenshot', 1)
    ;
    $this->screenshot_repository->expects($this->atLeastOnce())
      ->method('makeTempProgramAssetsPerm')->with(1)
    ;

    $this->event_dispatcher->expects($this->atLeastOnce())->method('dispatch')->willReturn($this->programBeforeInsertEvent);

    $this->program_manager->addProgram($this->request);
  }

  /**
   * @throws \Exception
   */
  public function testFiresAnEventBeforeInsertingAProgram(): void
  {
    $func = function (Program $project): Program {
      $project->setId('1');

      return $project;
    };

    $this->entity_manager->expects($this->atLeastOnce())->method('persist')
      ->will($this->returnCallback($func))
    ;

    $this->entity_manager->expects($this->atLeastOnce())->method('flush');
    $this->entity_manager->expects($this->atLeastOnce())->method('refresh')
      ->with($this->isInstanceOf(Program::class))
    ;

    $this->event_dispatcher->expects($this->atLeastOnce())->method('dispatch')
      ->willReturn($this->programBeforeInsertEvent)
    ;

    $this->assertInstanceOf(Program::class, $this->program_manager->addProgram($this->request));
  }

  /**
   * @throws \Exception
   */
  public function testFiresAnEventWhenTheProgramIsInvalid(): void
  {
    $validation_exception = new InvalidCatrobatFileException('500', 500);

    $this->expectException(InvalidCatrobatFileException::class);

    $this->event_dispatcher
      ->expects($this->atLeastOnce())
      ->method('dispatch')
      ->will($this->throwException($validation_exception))
    ;

    $this->program_manager->addProgram($this->request);
  }

  /**
   * @throws \Exception
   */
  public function testFiresAnEventWhenTheProgramIsStored(): void
  {
    $this->entity_manager->expects($this->atLeastOnce())->method('persist')
      ->will($this->returnCallback(function (Program $project): Program {
        $project->setId('1');

        return $project;
      }))
    ;

    $this->entity_manager->expects($this->atLeastOnce())->method('flush');
    $this->entity_manager->expects($this->atLeastOnce())->method('refresh')
      ->with($this->isInstanceOf(Program::class))
    ;

    $this->event_dispatcher->expects($this->atLeastOnce())->method('dispatch')->willReturn($this->programBeforeInsertEvent);

    $this->event_dispatcher->expects($this->atLeastOnce())->method('dispatch')
      ->willReturn($this->onConsecutiveCalls($this->programBeforeInsertEvent, $this->createMock(ProgramBeforePersistEvent::class), $this->programAfterInsertEvent))
    ;

    $this->assertInstanceOf(Program::class, $this->program_manager->addProgram($this->request));
  }
}
