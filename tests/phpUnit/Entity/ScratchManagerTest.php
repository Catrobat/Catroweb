<?php

namespace Tests\phpUnit\Entity;

use App\Catrobat\Requests\AppRequest;
use App\Catrobat\Services\CatrobatFileExtractor;
use App\Catrobat\Services\CatrobatFileSanitizer;
use App\Catrobat\Services\CatroNotificationService;
use App\Catrobat\Services\ProgramFileRepository;
use App\Catrobat\Services\ScreenshotRepository;
use App\Entity\Program;
use App\Entity\ProgramManager;
use App\Entity\ScratchManager;
use App\Entity\User;
use App\Entity\UserManager;
use App\Repository\ExampleRepository;
use App\Repository\ExtensionRepository;
use App\Repository\FeaturedRepository;
use App\Repository\ProgramLikeRepository;
use App\Repository\ProgramRepository;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use FOS\UserBundle\Util\CanonicalFieldsUpdater;
use FOS\UserBundle\Util\PasswordUpdaterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\UrlHelper;

/**
 * @internal
 * @coversNothing
 */
class ScratchManagerTest extends TestCase
{
  protected ScratchManager $scratch_manager;
  protected ProgramManager $program_manager;
  protected UserManager $user_manager;

  /** @var EntityManager|MockObject */
  protected $program_entity_manager;

  /** @var EntityManager|MockObject */
  protected $user_entity_manager;

  /** @var ProgramRepository|MockObject */
  protected $program_repository;

  /** @var EntityRepository|MockObject */
  protected $user_repository;

  public function setUp(): void
  {
    $file_extractor = $this->createMock(CatrobatFileExtractor::class);
    $file_repository = $this->createMock(ProgramFileRepository::class);
    $screenshot_repository = $this->createMock(ScreenshotRepository::class);
    $this->program_repository = $this->createMock(ProgramRepository::class);
    $this->program_entity_manager = $this->createMock(EntityManager::class);
    $event_dispatcher = $this->createMock(EventDispatcherInterface::class);
    $tag_repository = $this->createMock(TagRepository::class);
    $program_like_repository = $this->createMock(ProgramLikeRepository::class);
    $featured_repository = $this->createMock(FeaturedRepository::class);
    $example_repository = $this->createMock(ExampleRepository::class);
    $logger = $this->createMock(LoggerInterface::class);
    $app_request = $this->createMock(AppRequest::class);
    $extension_repository = $this->createMock(ExtensionRepository::class);
    $catrobat_file_sanitizer = $this->createMock(CatrobatFileSanitizer::class);
    $notification_service = $this->createMock(CatroNotificationService::class);
    $urlHelper = new UrlHelper(new RequestStack());
    $this->program_manager = new ProgramManager(
      $file_extractor, $file_repository, $screenshot_repository,
      $this->program_entity_manager, $this->program_repository, $tag_repository, $program_like_repository, $featured_repository,
      $example_repository, $event_dispatcher, $logger, $app_request, $extension_repository,
      $catrobat_file_sanitizer, $notification_service, $urlHelper
    );

    $passwordUpdater = $this->createMock(PasswordUpdaterInterface::class);
    $canonicalFieldsUpdater = $this->createMock(CanonicalFieldsUpdater::class);
    $this->user_entity_manager = $this->createMock(EntityManager::class);
    $this->user_manager = new UserManager($passwordUpdater, $canonicalFieldsUpdater, $this->user_entity_manager, $this->program_manager);

    $this->program_entity_manager->expects($this->any())->method('getRepository')->willReturn($this->program_repository);

    $this->user_repository = $this->createMock(EntityRepository::class);
    $this->user_entity_manager->expects($this->any())->method('getRepository')->willReturn($this->user_repository);

    $this->scratch_manager = new ScratchManager($this->program_manager, $this->user_manager);
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(ScratchManager::class, $this->scratch_manager);
  }

  public function testGetInvalidUser(): void
  {
    $name = 'a';
    $user = $this->scratch_manager->createScratchUserFromName($name);
    self::assertNull($user);
  }

  public function testGetUser(): void
  {
    $name = 'nposss';
    $this->user_entity_manager->expects($this->once())->method('persist')->with($this->isInstanceOf(User::class))
      ->will($this->returnCallback(function (User $user): User
      {
        $user->setId('1');

        return $user;
      }))
    ;
    $user = $this->scratch_manager->createScratchUserFromName($name);
    self::assertValidScratchUser($user);
  }

  public function testGetInvalidScratchProject(): void
  {
    $id = 0;
    $program = $this->scratch_manager->createScratchProgramFromId($id);
    self::assertNull($program);
  }

  public function testGetScratchProject(): void
  {
    $id = 117697631;
    $this->program_entity_manager->expects($this->once())->method('persist')->with($this->isInstanceOf(Program::class))
      ->will($this->returnCallback(function (Program $project): Program
      {
        $project->setId('1');

        return $project;
      }))
    ;
    $this->user_entity_manager->expects($this->once())->method('persist')->with($this->isInstanceOf(User::class))
      ->will($this->returnCallback(function (User $user): User
      {
        $user->setId('1');

        return $user;
      }))
    ;
    $program = $this->scratch_manager->createScratchProgramFromId($id);
    self::assertNotNull($program);
    self::assertEquals($id, $program->getScratchId());
    self::assertNotNull($program->getId());
    self::assertEquals(Program::INITIAL_VERSION, $program->getVersion());
    self::assertValidScratchUser($program->getUser());
  }

  public function testUpdateScratchProject(): void
  {
    $id = 117697631;
    $this->program_entity_manager->expects($this->exactly(2))->method('persist')->with($this->isInstanceOf(Program::class))
      ->will($this->returnCallback(function (Program $project): Program
      {
        if (null === $project->getId())
        {
          $project->setId('1');
        }

        return $project;
      }))
    ;

    $program_old = $this->scratch_manager->createScratchProgramFromId($id);
    $program_old->setLastModifiedAt($program_old->getLastModifiedAt()->sub(new \DateInterval('P1M')));
    $program_old->setDescription('test_description');

    $this->program_repository->expects($this->once())->method('findOneBy')->withAnyParameters()
      ->will($this->returnCallback(function (array $criteria) use ($program_old): Program
      {
        return $program_old;
      }))
    ;

    $program_new = $this->scratch_manager->createScratchProgramFromId($id);
    self::assertNotEquals('test_description', $program_new->getDescription());
    self::assertEquals(2, $program_new->getVersion());
  }

  public function testGetScratchProjectTwice(): void
  {
    $id = 117697631;
    $this->program_entity_manager->expects($this->once())->method('persist')->with($this->isInstanceOf(Program::class))
      ->will($this->returnCallback(function (Program $project): Program
      {
        if (null === $project->getId())
        {
          $project->setId('1');
        }

        return $project;
      }))
    ;
    $program = $this->scratch_manager->createScratchProgramFromId($id);
    $this->program_repository->expects($this->once())->method('findOneBy')->withAnyParameters()
      ->will($this->returnCallback(function (array $criteria) use ($program): Program
      {
        return $program;
      }))
    ;
    $program = $this->scratch_manager->createScratchProgramFromId($id);
    self::assertNotNull($program->getId());
    self::assertEquals(Program::INITIAL_VERSION, $program->getVersion());
  }

  private static function assertValidScratchUser(?User $user): void
  {
    self::assertNotNull($user);
    self::assertNotNull($user->getId());
    self::assertNotNull($user->getScratchUserId());
    self::assertNotNull($user->getScratchUsername());
    self::assertStringStartsWith(User::$SCRATCH_PREFIX, $user->getUsername());
  }
}
