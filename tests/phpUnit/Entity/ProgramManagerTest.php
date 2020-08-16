<?php

namespace Tests\phpUnit\Entity;

use App\Catrobat\Events\ProgramAfterInsertEvent;
use App\Catrobat\Events\ProgramBeforeInsertEvent;
use App\Catrobat\Exceptions\InvalidCatrobatFileException;
use App\Catrobat\Requests\AddProgramRequest;
use App\Catrobat\Requests\AppRequest;
use App\Catrobat\Services\CatrobatFileExtractor;
use App\Catrobat\Services\CatrobatFileSanitizer;
use App\Catrobat\Services\CatroNotificationService;
use App\Catrobat\Services\ExtractedCatrobatFile;
use App\Catrobat\Services\ProgramFileRepository;
use App\Catrobat\Services\ScreenshotRepository;
use App\Entity\Program;
use App\Entity\ProgramManager;
use App\Entity\User;
use App\Repository\ExampleRepository;
use App\Repository\ExtensionRepository;
use App\Repository\FeaturedRepository;
use App\Repository\ProgramLikeRepository;
use App\Repository\ProgramRepository;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Exception;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use SimpleXMLElement;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\UrlHelper;

/**
 * @internal
 * @covers \App\Entity\ProgramManager
 */
class ProgramManagerTest extends TestCase
{
  private ProgramManager $program_manager;

  /**
   * @var MockObject|ProgramFileRepository
   */
  private $file_repository;

  /**
   * @var MockObject|ScreenshotRepository
   */
  private $screenshot_repository;

  /**
   * @var EntityManager|MockObject
   */
  private $entity_manager;

  /**
   * @var EventDispatcherInterface|MockObject
   */
  private $event_dispatcher;

  /**
   * @var AddProgramRequest|MockObject
   */
  private $request;

  /**
   * @var ExtractedCatrobatFile|MockObject
   */
  private $extracted_file;

  /**
   * @var ProgramBeforeInsertEvent|MockObject
   */
  private $programBeforeInsertEvent;

  /**
   * @var ProgramAfterInsertEvent|MockObject
   */
  private $programAfterInsertEvent;

  /**
   * @throws Exception
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
    $app_request = $this->createMock(AppRequest::class);
    $this->programBeforeInsertEvent = $this->createMock(ProgramBeforeInsertEvent::class);
    $this->programAfterInsertEvent = $this->createMock(ProgramAfterInsertEvent::class);
    $extension_repository = $this->createMock(ExtensionRepository::class);
    $catrobat_file_sanitizer = $this->createMock(CatrobatFileSanitizer::class);
    $notification_service = $this->createMock(CatroNotificationService::class);
    $program_finder = $this->createMock(TransformedFinder::class);
    $url_helper = new UrlHelper(new RequestStack());

    $this->program_manager = new ProgramManager(
      $file_extractor, $this->file_repository, $this->screenshot_repository,
      $this->entity_manager, $program_repository, $tag_repository, $program_like_repository, $featured_repository,
      $example_repository, $this->event_dispatcher, $logger, $app_request, $extension_repository,
      $catrobat_file_sanitizer, $notification_service, $program_finder, $url_helper
    );

    $this->extracted_file->expects($this->any())->method('getName')->willReturn('TestProject');
    $this->extracted_file->expects($this->any())->method('getApplicationVersion')->willReturn('0.999');
    $this->extracted_file->expects($this->any())->method('getProgramXmlProperties')
      ->willReturn(new SimpleXMLElement('<empty></empty>')
      )
    ;
    $this->extracted_file->expects($this->any())->method('getDirHash')->willReturn('451f778e4bf3');

    fopen('/tmp/phpUnitTest', 'w');
    $file = new File('/tmp/phpUnitTest');
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
    $this->assertInstanceOf(ProgramManager::class, $this->program_manager);
  }

  /**
   * @throws Exception
   */
  public function testReturnsTheProgramAfterSuccessfullyAddingAProgram(): void
  {
    $metadata = $this->createMock(ClassMetadata::class);
    $metadata->expects($this->atLeastOnce())->method('getFieldNames')->willReturn(['id']);
    $this->entity_manager->expects($this->atLeastOnce())->method('getClassMetadata')->willReturn($metadata);
    $this->entity_manager->expects($this->atLeastOnce())->method('persist')->with($this->isInstanceOf(Program::class))
      ->will($this->returnCallback(function (Program $project): Program
      {
        $project->setId('1');

        return $project;
      }))
    ;
    $this->entity_manager->expects($this->atLeastOnce())->method('flush');
    $this->entity_manager->expects($this->atLeastOnce())->method('refresh')->with($this->isInstanceOf(Program::class));
    $this->event_dispatcher->expects($this->atLeastOnce())->method('dispatch')->willReturn($this->programBeforeInsertEvent);
    $this->assertInstanceOf(Program::class, $this->program_manager->addProgram($this->request));
  }

  /**
   * @throws Exception
   */
  public function testSavesTheProgramToTheFileRepositoryIfTheUploadSucceeded(): void
  {
    $metadata = $this->createMock(ClassMetadata::class);
    $metadata->expects($this->atLeastOnce())->method('getFieldNames')->willReturn(['id']);
    $this->entity_manager->expects($this->atLeastOnce())->method('getClassMetadata')->willReturn($metadata);
    $this->entity_manager->expects($this->atLeastOnce())->method('persist')
      ->will($this->returnCallback(function (Program $project): Program
      {
        $project->setId('1');

        return $project;
      }))
    ;
    fopen('/tmp/phpUnitTest', 'w');
    $file = new File('/tmp/phpUnitTest');
    $this->file_repository->expects($this->atLeastOnce())->method('saveProgramFile')->with($file, 1);
    $this->entity_manager->expects($this->atLeastOnce())->method('flush');
    $this->entity_manager->expects($this->atLeastOnce())->method('refresh')->with($this->isInstanceOf(Program::class));

    $this->event_dispatcher->expects($this->atLeastOnce())->method('dispatch')->willReturn($this->programBeforeInsertEvent);

    $this->program_manager->addProgram($this->request);
  }

  /**
   * @throws Exception
   */
  public function testSavesTheScreenshotsToTheScreenshotRepository(): void
  {
    $metadata = $this->createMock(ClassMetadata::class);
    $metadata->expects($this->atLeastOnce())->method('getFieldNames')->willReturn(['id']);
    $this->entity_manager->expects($this->atLeastOnce())->method('getClassMetadata')->willReturn($metadata);
    $this->extracted_file->expects($this->atLeastOnce())->method('getScreenshotPath')->willReturn('./path/to/screenshot');
    $this->extracted_file->expects($this->atLeastOnce())->method('getDescription')->willReturn('');
    $this->extracted_file->expects($this->atLeastOnce())->method('getLanguageVersion')->willReturn('');
    $this->extracted_file->expects($this->atLeastOnce())->method('getTags')->willReturn([]);
    $this->extracted_file->expects($this->atLeastOnce())->method('isDebugBuild')->willReturn(false);
    $this->entity_manager->expects($this->atLeastOnce())->method('persist')
      ->will($this->returnCallback(function (Program $project): Program
      {
        $project->setId('1');

        return $project;
      }))
    ;
    $this->entity_manager->expects($this->atLeastOnce())->method('flush');
    $this->entity_manager->expects($this->atLeastOnce())
      ->method('refresh')->with($this->isInstanceOf(Program::class));

    $this->screenshot_repository->expects($this->atLeastOnce())
      ->method('saveProgramAssetsTemp')->with('./path/to/screenshot', 1);
    $this->screenshot_repository->expects($this->atLeastOnce())
      ->method('makeTempProgramAssetsPerm')->with(1);

    $this->event_dispatcher->expects($this->atLeastOnce())->method('dispatch')->willReturn($this->programBeforeInsertEvent);

    $this->program_manager->addProgram($this->request);
  }

  /**
   * @throws Exception
   */
  public function testFiresAnEventBeforeInsertingAProgram(): void
  {
    $metadata = $this->createMock(ClassMetadata::class);
    $metadata->expects($this->atLeastOnce())->method('getFieldNames')->willReturn(['id']);
    $this->entity_manager->expects($this->atLeastOnce())->method('getClassMetadata')->willReturn($metadata);
    $this->entity_manager->expects($this->atLeastOnce())->method('persist')
      ->will($this->returnCallback(function (Program $project): Program
      {
        $project->setId('1');

        return $project;
      }))
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
   * @throws Exception
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
   * @throws Exception
   */
  public function testFiresAnEventWhenTheProgramIsStored(): void
  {
    $metadata = $this->createMock(ClassMetadata::class);
    $metadata->expects($this->atLeastOnce())->method('getFieldNames')->willReturn(['id']);
    $this->entity_manager->expects($this->atLeastOnce())->method('getClassMetadata')->willReturn($metadata);
    $this->entity_manager->expects($this->atLeastOnce())->method('persist')
      ->will($this->returnCallback(function (Program $project): Program
      {
        $project->setId('1');

        return $project;
      }))
    ;

    $this->entity_manager->expects($this->atLeastOnce())->method('flush');
    $this->entity_manager->expects($this->atLeastOnce())->method('refresh')
      ->with($this->isInstanceOf(Program::class))
    ;

    $this->event_dispatcher->expects($this->atLeastOnce())->method('dispatch')
      ->will($this->onConsecutiveCalls($this->programBeforeInsertEvent, $this->programAfterInsertEvent))
    ;

    $this->assertInstanceOf(Program::class, $this->program_manager->addProgram($this->request));
  }
}
