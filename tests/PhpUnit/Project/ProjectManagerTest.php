<?php

namespace Tests\PhpUnit\Project;

use App\DB\Entity\Project\Project;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\Project\ExtensionRepository;
use App\DB\EntityRepository\Project\ProjectLikeRepository;
use App\DB\EntityRepository\Project\ProjectRepository;
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
  private ProjectManager $project_manager;

  private MockObject|ProjectFileRepository $file_repository;

  private MockObject|ScreenshotRepository $screenshot_repository;

  private EntityManager|MockObject $entity_manager;

  private EventDispatcherInterface|MockObject $event_dispatcher;

  private AddProjectRequest|MockObject $request;

  private ExtractedCatrobatFile|MockObject $extracted_file;

  private MockObject|ProjectBeforeInsertEvent $projectBeforeInsertEvent;

  private MockObject|ProjectAfterInsertEvent $projectAfterInsertEvent;

  /**
   * @throws \Exception
   */
  protected function setUp(): void
  {
    $file_extractor = $this->createMock(CatrobatFileExtractor::class);
    $this->file_repository = $this->createMock(ProjectFileRepository::class);
    $this->screenshot_repository = $this->createMock(ScreenshotRepository::class);
    $this->entity_manager = $this->createMock(EntityManager::class);
    $project_repository = $this->createMock(ProjectRepository::class);
    $this->event_dispatcher = $this->createMock(EventDispatcherInterface::class);
    $this->request = $this->createMock(AddProjectRequest::class);
    $user = $this->createMock(User::class);
    $this->extracted_file = $this->createMock(ExtractedCatrobatFile::class);
    $inserted_project = $this->createMock(Project::class);
    $tag_repository = $this->createMock(TagRepository::class);
    $project_like_repository = $this->createMock(ProjectLikeRepository::class);
    $featured_repository = $this->createMock(FeaturedRepository::class);
    $example_repository = $this->createMock(ExampleRepository::class);
    $logger = $this->createMock(LoggerInterface::class);
    $app_request = $this->createMock(RequestHelper::class);
    $this->projectBeforeInsertEvent = $this->createMock(ProjectBeforeInsertEvent::class);
    $this->projectAfterInsertEvent = $this->createMock(ProjectAfterInsertEvent::class);
    $extension_repository = $this->createMock(ExtensionRepository::class);
    $catrobat_file_sanitizer = $this->createMock(CatrobatFileSanitizer::class);
    $notification_service = $this->createMock(NotificationManager::class);
    $project_finder = $this->createMock(TransformedFinder::class);
    $url_helper = new UrlHelper(new RequestStack());
    $security = $this->createMock(Security::class);

    $this->project_manager = new ProjectManager(
      $file_extractor, $this->file_repository, $this->screenshot_repository,
      $this->entity_manager, $project_repository, $tag_repository, $project_like_repository, $featured_repository,
      $example_repository, $this->event_dispatcher, $logger, $app_request, $extension_repository,
      $catrobat_file_sanitizer, $notification_service, $project_finder, $url_helper, $security
    );

    $this->extracted_file->expects($this->any())->method('getName')->willReturn('TestProject');
    $this->extracted_file->expects($this->any())->method('getApplicationVersion')->willReturn('0.999');
    $this->extracted_file->expects($this->any())->method('getProjectXmlProperties')
      ->willReturn(new \SimpleXMLElement('<empty></empty>')
      )
    ;
    $this->extracted_file->expects($this->any())->method('getDirHash')->willReturn('451f778e4bf3');

    fopen('/tmp/PhpUnitTest', 'w');
    $file = new File('/tmp/PhpUnitTest');
    $this->request->expects($this->any())->method('getProjectFile')->willReturn($file);
    $this->request->expects($this->any())->method('getUser')->willReturn($user);
    $this->request->expects($this->any())->method('getIp')->willReturn('127.0.0.1');
    $this->request->expects($this->any())->method('getLanguage')->willReturn('en');
    $this->request->expects($this->any())->method('getFlavor')->willReturn('pocketcode');
    $file_extractor->expects($this->any())->method('extract')->with($file)->willReturn($this->extracted_file);
    $inserted_project->expects($this->any())->method('getId')->willReturn('1');

    $this->projectBeforeInsertEvent->expects($this->any())->method('isPropagationStopped')->willReturn(false);
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(ProjectManager::class, $this->project_manager);
  }

  /**
   * @throws \Exception
   */
  public function testReturnsTheProjectAfterSuccessfullyAddingAProject(): void
  {
    $func = function (Project $project): Project {
      $project->setId('1');

      return $project;
    };

    $this->entity_manager->expects($this->atLeastOnce())->method('persist')->with($this->isInstanceOf(Project::class))
      ->will($this->returnCallback($func))
    ;
    $this->entity_manager->expects($this->atLeastOnce())->method('flush');
    $this->entity_manager->expects($this->atLeastOnce())->method('refresh')
      ->with($this->isInstanceOf(Project::class))
    ;
    $this->event_dispatcher->expects($this->atLeastOnce())->method('dispatch')
      ->willReturn($this->projectBeforeInsertEvent)
    ;

    $this->assertInstanceOf(Project::class, $this->project_manager->addProject($this->request));
  }

  /**
   * @throws \Exception
   */
  public function testSavesTheProjectToTheFileRepositoryIfTheUploadSucceeded(): void
  {
    $this->entity_manager->expects($this->atLeastOnce())->method('persist')
      ->will($this->returnCallback(function (Project $project): Project {
        $project->setId('1');

        return $project;
      }))
    ;
    fopen('/tmp/PhpUnitTest', 'w');
    $file = new File('/tmp/PhpUnitTest');
    $this->file_repository->expects($this->atLeastOnce())->method('saveProjectZipFile')->with($file, 1);
    $this->entity_manager->expects($this->atLeastOnce())->method('flush');
    $this->entity_manager->expects($this->atLeastOnce())->method('refresh')->with($this->isInstanceOf(Project::class));

    $this->event_dispatcher->expects($this->atLeastOnce())->method('dispatch')->willReturn($this->projectBeforeInsertEvent);

    $this->project_manager->addProject($this->request);
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
      ->will($this->returnCallback(function (Project $project): Project {
        $project->setId('1');

        return $project;
      }))
    ;
    $this->entity_manager->expects($this->atLeastOnce())->method('flush');
    $this->entity_manager->expects($this->atLeastOnce())
      ->method('refresh')->with($this->isInstanceOf(Project::class))
    ;

    $this->screenshot_repository->expects($this->atLeastOnce())
      ->method('saveProjectAssetsTemp')->with('./path/to/screenshot', 1)
    ;
    $this->screenshot_repository->expects($this->atLeastOnce())
      ->method('makeTempProjectAssetsPerm')->with(1)
    ;

    $this->event_dispatcher->expects($this->atLeastOnce())->method('dispatch')->willReturn($this->projectBeforeInsertEvent);

    $this->project_manager->addProject($this->request);
  }

  /**
   * @throws \Exception
   */
  public function testFiresAnEventBeforeInsertingAProject(): void
  {
    $func = function (Project $project): Project {
      $project->setId('1');

      return $project;
    };

    $this->entity_manager->expects($this->atLeastOnce())->method('persist')
      ->will($this->returnCallback($func))
    ;

    $this->entity_manager->expects($this->atLeastOnce())->method('flush');
    $this->entity_manager->expects($this->atLeastOnce())->method('refresh')
      ->with($this->isInstanceOf(Project::class))
    ;

    $this->event_dispatcher->expects($this->atLeastOnce())->method('dispatch')
      ->willReturn($this->projectBeforeInsertEvent)
    ;

    $this->assertInstanceOf(Project::class, $this->project_manager->addProject($this->request));
  }

  /**
   * @throws \Exception
   */
  public function testFiresAnEventWhenTheProjectIsInvalid(): void
  {
    $validation_exception = new InvalidCatrobatFileException('500', 500);

    $this->expectException(InvalidCatrobatFileException::class);

    $this->event_dispatcher
      ->expects($this->atLeastOnce())
      ->method('dispatch')
      ->will($this->throwException($validation_exception))
    ;

    $this->project_manager->addProject($this->request);
  }

  /**
   * @throws \Exception
   */
  public function testFiresAnEventWhenTheProjectIsStored(): void
  {
    $this->entity_manager->expects($this->atLeastOnce())->method('persist')
      ->will($this->returnCallback(function (Project $project): Project {
        $project->setId('1');

        return $project;
      }))
    ;

    $this->entity_manager->expects($this->atLeastOnce())->method('flush');
    $this->entity_manager->expects($this->atLeastOnce())->method('refresh')
      ->with($this->isInstanceOf(Project::class))
    ;

    $this->event_dispatcher->expects($this->atLeastOnce())->method('dispatch')->willReturn($this->projectBeforeInsertEvent);

    $this->event_dispatcher->expects($this->atLeastOnce())->method('dispatch')
      ->willReturn($this->onConsecutiveCalls($this->projectBeforeInsertEvent, $this->createMock(ProjectBeforePersistEvent::class), $this->projectAfterInsertEvent))
    ;

    $this->assertInstanceOf(Project::class, $this->project_manager->addProject($this->request));
  }
}
