<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project;

use App\DB\Entity\Flavor;
use App\DB\Entity\Project\Program;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\Project\ExtensionRepository;
use App\DB\EntityRepository\Project\ProgramLikeRepository;
use App\DB\EntityRepository\Project\ProgramRepository;
use App\DB\EntityRepository\Project\Special\ExampleRepository;
use App\DB\EntityRepository\Project\Special\FeaturedRepository;
use App\DB\EntityRepository\Project\TagRepository;
use App\Project\AddProjectRequest;
use App\Project\CatrobatFile\CatrobatFileExtractor;
use App\Project\CatrobatFile\CatrobatFileSanitizer;
use App\Project\CatrobatFile\ExtractedCatrobatFile;
use App\Project\CatrobatFile\InvalidCatrobatFileException;
use App\Project\CatrobatFile\ProjectFileRepository;
use App\Project\Event\ProjectAfterInsertEvent;
use App\Project\Event\ProjectBeforeInsertEvent;
use App\Project\Event\ProjectBeforePersistEvent;
use App\Project\ProjectManager;
use App\Storage\ScreenshotRepository;
use App\User\Notification\NotificationManager;
use App\Utils\RequestHelper;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\UrlHelper;

/**
 * @internal
 */
#[CoversClass(ProjectManager::class)]
class ProjectManagerTest extends TestCase
{
  private ProjectManager $program_manager;

  // Stubs used across multiple tests - converted to stubs to avoid needing AllowMockObjectsWithoutExpectations
  // Tests that need to verify behavior will create local mocks
  private ProjectFileRepository $file_repository;

  private ScreenshotRepository $screenshot_repository;

  private EntityManager $entity_manager;

  private EventDispatcherInterface $event_dispatcher;

  private AddProjectRequest $request;

  private ExtractedCatrobatFile $extracted_file;

  private ProjectBeforeInsertEvent $programBeforeInsertEvent;

  private ProjectAfterInsertEvent $programAfterInsertEvent;

  /**
   * @throws \Exception|Exception
   */
  #[\Override]
  protected function setUp(): void
  {
    // Use stubs for dependencies that only return values (no behavior verification needed)
    $file_extractor = $this->createStub(CatrobatFileExtractor::class);
    $program_repository = $this->createStub(ProgramRepository::class);
    $tag_repository = $this->createStub(TagRepository::class);
    $program_like_repository = $this->createStub(ProgramLikeRepository::class);
    $featured_repository = $this->createStub(FeaturedRepository::class);
    $example_repository = $this->createStub(ExampleRepository::class);
    $logger = $this->createStub(LoggerInterface::class);
    $app_request = $this->createStub(RequestHelper::class);
    $extension_repository = $this->createStub(ExtensionRepository::class);
    $catrobat_file_sanitizer = $this->createStub(CatrobatFileSanitizer::class);
    $notification_service = $this->createStub(NotificationManager::class);
    $program_finder = $this->createStub(TransformedFinder::class);
    $security = $this->createStub(Security::class);
    $user = $this->createStub(User::class);
    $inserted_program = $this->createStub(Program::class);

    // All dependencies are stubs to avoid AllowMockObjectsWithoutExpectations
    // Tests that need to verify behavior will create local mocks and new ProjectManager instances
    $this->file_repository = $this->createStub(ProjectFileRepository::class);
    $this->screenshot_repository = $this->createStub(ScreenshotRepository::class);
    $this->extracted_file = $this->createStub(ExtractedCatrobatFile::class);
    $this->entity_manager = $this->createStub(EntityManager::class);
    $this->event_dispatcher = $this->createStub(EventDispatcherInterface::class);
    $this->request = $this->createStub(AddProjectRequest::class);
    $this->programBeforeInsertEvent = $this->createStub(ProjectBeforeInsertEvent::class);
    $this->programAfterInsertEvent = $this->createStub(ProjectAfterInsertEvent::class);

    $url_helper = new UrlHelper(new RequestStack());

    $this->program_manager = new ProjectManager(
      $file_extractor, $this->file_repository, $this->screenshot_repository,
      $this->entity_manager, $program_repository, $tag_repository, $program_like_repository, $featured_repository,
      $example_repository, $this->event_dispatcher, $logger, $app_request, $extension_repository,
      $catrobat_file_sanitizer, $notification_service, $program_finder, $url_helper, $security
    );

    // Configure stub/mock return values (using method() instead of expects()->method())
    $this->extracted_file->method('getName')->willReturn('TestProject');
    $this->extracted_file->method('getApplicationVersion')->willReturn('0.999');
    $this->extracted_file->method('getProjectXmlProperties')->willReturn(new \SimpleXMLElement('<empty></empty>'));
    $this->extracted_file->method('getDirHash')->willReturn('451f778e4bf3');

    fopen('/tmp/PhpUnitTest', 'w');
    $file = new File('/tmp/PhpUnitTest');
    $this->request->method('getProjectFile')->willReturn($file);
    $this->request->method('getUser')->willReturn($user);
    $this->request->method('getIp')->willReturn('127.0.0.1');
    $this->request->method('getLanguage')->willReturn('en');
    $this->request->method('getFlavor')->willReturn(Flavor::POCKETCODE);
    $file_extractor->method('extract')->with($file)->willReturn($this->extracted_file);
    $inserted_program->method('getId')->willReturn('1');

    $this->programBeforeInsertEvent->method('isPropagationStopped')->willReturn(false);
  }

  /**
   * Helper to create a ProjectManager with custom mocks for testing behavior verification.
   *
   * @throws Exception
   */
  private function createProjectManagerWithMocks(
    ?ProjectFileRepository $file_repository = null,
    ?ScreenshotRepository $screenshot_repository = null,
    ?EntityManager $entity_manager = null,
    ?EventDispatcherInterface $event_dispatcher = null,
    ?ExtractedCatrobatFile $extracted_file = null,
  ): ProjectManager {
    $file_extractor = $this->createStub(CatrobatFileExtractor::class);
    $program_repository = $this->createStub(ProgramRepository::class);
    $tag_repository = $this->createStub(TagRepository::class);
    $program_like_repository = $this->createStub(ProgramLikeRepository::class);
    $featured_repository = $this->createStub(FeaturedRepository::class);
    $example_repository = $this->createStub(ExampleRepository::class);
    $logger = $this->createStub(LoggerInterface::class);
    $app_request = $this->createStub(RequestHelper::class);
    $extension_repository = $this->createStub(ExtensionRepository::class);
    $catrobat_file_sanitizer = $this->createStub(CatrobatFileSanitizer::class);
    $notification_service = $this->createStub(NotificationManager::class);
    $program_finder = $this->createStub(TransformedFinder::class);
    $security = $this->createStub(Security::class);
    $url_helper = new UrlHelper(new RequestStack());

    $user = $this->createStub(User::class);
    $extracted = $extracted_file ?? $this->extracted_file;

    fopen('/tmp/PhpUnitTest', 'w');
    $file = new File('/tmp/PhpUnitTest');
    $file_extractor->method('extract')->with($file)->willReturn($extracted);

    return new ProjectManager(
      $file_extractor,
      $file_repository ?? $this->file_repository,
      $screenshot_repository ?? $this->screenshot_repository,
      $entity_manager ?? $this->entity_manager,
      $program_repository,
      $tag_repository,
      $program_like_repository,
      $featured_repository,
      $example_repository,
      $event_dispatcher ?? $this->event_dispatcher,
      $logger,
      $app_request,
      $extension_repository,
      $catrobat_file_sanitizer,
      $notification_service,
      $program_finder,
      $url_helper,
      $security
    );
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(ProjectManager::class, $this->program_manager);
  }

  /**
   * @throws ORMException
   * @throws Exception
   */
  public function testReturnsTheProgramAfterSuccessfullyAddingAProgram(): void
  {
    $func = static function (Program $project): Program {
      $project->setId('1');

      return $project;
    };

    $entity_manager = $this->createMock(EntityManager::class);
    $entity_manager->expects($this->atLeastOnce())->method('persist')->with($this->isInstanceOf(Program::class))
      ->willReturnCallback($func)
    ;
    $entity_manager->expects($this->atLeastOnce())->method('flush');
    $entity_manager->expects($this->atLeastOnce())->method('refresh')
      ->with($this->isInstanceOf(Program::class))
    ;

    $event_dispatcher = $this->createMock(EventDispatcherInterface::class);
    $event_dispatcher->expects($this->atLeastOnce())->method('dispatch')
      ->willReturn($this->programBeforeInsertEvent)
    ;

    $program_manager = $this->createProjectManagerWithMocks(
      entity_manager: $entity_manager,
      event_dispatcher: $event_dispatcher
    );

    $this->assertInstanceOf(Program::class, $program_manager->addProject($this->request));
  }

  /**
   * @throws ORMException
   * @throws Exception
   */
  public function testSavesTheProgramToTheFileRepositoryIfTheUploadSucceeded(): void
  {
    $entity_manager = $this->createMock(EntityManager::class);
    $entity_manager->expects($this->atLeastOnce())->method('persist')
      ->willReturnCallback(static function (Program $project): Program {
        $project->setId('1');

        return $project;
      })
    ;
    $entity_manager->expects($this->atLeastOnce())->method('flush');
    $entity_manager->expects($this->atLeastOnce())->method('refresh')->with($this->isInstanceOf(Program::class));

    fopen('/tmp/PhpUnitTest', 'w');
    $file = new File('/tmp/PhpUnitTest');

    $file_repository = $this->createMock(ProjectFileRepository::class);
    $file_repository->expects($this->atLeastOnce())->method('saveProjectZipFile')->with($file, 1);

    $event_dispatcher = $this->createMock(EventDispatcherInterface::class);
    $event_dispatcher->expects($this->atLeastOnce())->method('dispatch')->willReturn($this->programBeforeInsertEvent);

    $program_manager = $this->createProjectManagerWithMocks(
      file_repository: $file_repository,
      entity_manager: $entity_manager,
      event_dispatcher: $event_dispatcher
    );

    $program_manager->addProject($this->request);
  }

  /**
   * @throws ORMException
   * @throws Exception
   */
  public function testSavesTheScreenshotsToTheScreenshotRepository(): void
  {
    $extracted_file = $this->createMock(ExtractedCatrobatFile::class);
    $extracted_file->method('getName')->willReturn('TestProject');
    $extracted_file->method('getApplicationVersion')->willReturn('0.999');
    $extracted_file->method('getProjectXmlProperties')->willReturn(new \SimpleXMLElement('<empty></empty>'));
    $extracted_file->method('getDirHash')->willReturn('451f778e4bf3');
    $extracted_file->expects($this->atLeastOnce())->method('getScreenshotPath')->willReturn('./path/to/screenshot');
    $extracted_file->expects($this->atLeastOnce())->method('getDescription')->willReturn('');
    $extracted_file->expects($this->atLeastOnce())->method('getLanguageVersion')->willReturn('');
    $extracted_file->expects($this->atLeastOnce())->method('getTags')->willReturn([]);
    $extracted_file->expects($this->atLeastOnce())->method('isDebugBuild')->willReturn(false);

    $entity_manager = $this->createMock(EntityManager::class);
    $entity_manager->expects($this->atLeastOnce())->method('persist')
      ->willReturnCallback(static function (Program $project): Program {
        $project->setId('1');

        return $project;
      })
    ;
    $entity_manager->expects($this->atLeastOnce())->method('flush');
    $entity_manager->expects($this->atLeastOnce())
      ->method('refresh')->with($this->isInstanceOf(Program::class))
    ;

    $screenshot_repository = $this->createMock(ScreenshotRepository::class);
    $screenshot_repository->expects($this->atLeastOnce())
      ->method('saveProjectAssetsTemp')->with('./path/to/screenshot', 1)
    ;
    $screenshot_repository->expects($this->atLeastOnce())
      ->method('makeTempProjectAssetsPerm')->with(1)
    ;

    $event_dispatcher = $this->createMock(EventDispatcherInterface::class);
    $event_dispatcher->expects($this->atLeastOnce())->method('dispatch')->willReturn($this->programBeforeInsertEvent);

    $program_manager = $this->createProjectManagerWithMocks(
      screenshot_repository: $screenshot_repository,
      entity_manager: $entity_manager,
      event_dispatcher: $event_dispatcher,
      extracted_file: $extracted_file
    );

    $program_manager->addProject($this->request);
  }

  /**
   * @throws ORMException
   * @throws Exception
   */
  public function testFiresAnEventBeforeInsertingAProgram(): void
  {
    $func = static function (Program $project): Program {
      $project->setId('1');

      return $project;
    };

    $entity_manager = $this->createMock(EntityManager::class);
    $entity_manager->expects($this->atLeastOnce())->method('persist')
      ->willReturnCallback($func)
    ;
    $entity_manager->expects($this->atLeastOnce())->method('flush');
    $entity_manager->expects($this->atLeastOnce())->method('refresh')
      ->with($this->isInstanceOf(Program::class))
    ;

    $event_dispatcher = $this->createMock(EventDispatcherInterface::class);
    $event_dispatcher->expects($this->atLeastOnce())->method('dispatch')
      ->willReturn($this->programBeforeInsertEvent)
    ;

    $program_manager = $this->createProjectManagerWithMocks(
      entity_manager: $entity_manager,
      event_dispatcher: $event_dispatcher
    );

    $this->assertInstanceOf(Program::class, $program_manager->addProject($this->request));
  }

  /**
   * @throws ORMException
   * @throws Exception
   */
  public function testFiresAnEventWhenTheProgramIsInvalid(): void
  {
    $validation_exception = new InvalidCatrobatFileException('500', 500);

    $this->expectException(InvalidCatrobatFileException::class);

    $event_dispatcher = $this->createMock(EventDispatcherInterface::class);
    $event_dispatcher
      ->expects($this->atLeastOnce())
      ->method('dispatch')
      ->will($this->throwException($validation_exception))
    ;

    $program_manager = $this->createProjectManagerWithMocks(
      event_dispatcher: $event_dispatcher
    );

    $program_manager->addProject($this->request);
  }

  /**
   * @throws Exception
   * @throws ORMException
   */
  public function testFiresAnEventWhenTheProgramIsStored(): void
  {
    $entity_manager = $this->createMock(EntityManager::class);
    $entity_manager->expects($this->atLeastOnce())->method('persist')
      ->willReturnCallback(static function (Program $project): Program {
        $project->setId('1');

        return $project;
      })
    ;
    $entity_manager->expects($this->atLeastOnce())->method('flush');
    $entity_manager->expects($this->atLeastOnce())->method('refresh')
      ->with($this->isInstanceOf(Program::class))
    ;

    $event_dispatcher = $this->createMock(EventDispatcherInterface::class);
    $event_dispatcher->expects($this->atLeastOnce())->method('dispatch')->willReturn($this->programBeforeInsertEvent);
    $event_dispatcher->expects($this->atLeastOnce())->method('dispatch')
      ->willReturn($this->programBeforeInsertEvent, $this->createStub(ProjectBeforePersistEvent::class), $this->programAfterInsertEvent)
    ;

    $program_manager = $this->createProjectManagerWithMocks(
      entity_manager: $entity_manager,
      event_dispatcher: $event_dispatcher
    );

    $this->assertInstanceOf(Program::class, $program_manager->addProject($this->request));
  }
}
