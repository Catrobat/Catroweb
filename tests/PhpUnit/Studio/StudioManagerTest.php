<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Studio;

use App\DB\Entity\Project\Project;
use App\DB\Entity\Studio\Studio;
use App\DB\Entity\Studio\StudioActivity;
use App\DB\Entity\Studio\StudioJoinRequest;
use App\DB\Entity\Studio\StudioProject;
use App\DB\Entity\Studio\StudioUser;
use App\DB\Entity\User\Comment\UserComment;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\Project\ProjectRepository;
use App\DB\EntityRepository\Studios\StudioActivityRepository;
use App\DB\EntityRepository\Studios\StudioJoinRequestRepository;
use App\DB\EntityRepository\Studios\StudioProjectRepository;
use App\DB\EntityRepository\Studios\StudioRepository;
use App\DB\EntityRepository\Studios\StudioUserRepository;
use App\DB\EntityRepository\User\Comment\UserCommentRepository;
use App\Studio\StudioManager;
use App\System\Testing\DataFixtures\ProjectDataFixtures;
use App\System\Testing\DataFixtures\UserDataFixtures;
use App\User\Notification\NotificationManager;
use App\User\UserManager;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * @internal
 */
#[CoversClass(StudioManager::class)]
class StudioManagerTest extends KernelTestCase
{
  protected StudioManager $object;

  protected ?UserDataFixtures $user_fixture;

  protected ?ProjectDataFixtures $project_fixture;

  protected ?UserManager $user_manager;

  protected ?Studio $studio;

  protected ?User $user;

  protected EntityManager $entity_manager;

  #[\Override]
  protected function setUp(): void
  {
    self::bootKernel();
    $container = static::getContainer();

    /** @var \Doctrine\Persistence\ManagerRegistry $doctrine */
    $doctrine = $container->get('doctrine');
    $manager = $doctrine->getManager();
    \assert($manager instanceof EntityManager);
    $this->entity_manager = $manager;
    /** @var StudioRepository $studio_repository */
    $studio_repository = $this->entity_manager->getRepository(Studio::class);
    /** @var StudioActivityRepository $studio_activity_repository */
    $studio_activity_repository = $this->entity_manager->getRepository(StudioActivity::class);
    /** @var StudioProjectRepository $studio_project_repository */
    $studio_project_repository = $this->entity_manager->getRepository(StudioProject::class);
    /** @var StudioUserRepository $studio_user_repository */
    $studio_user_repository = $this->entity_manager->getRepository(StudioUser::class);
    /** @var UserCommentRepository $user_comment_repository */
    $user_comment_repository = $this->entity_manager->getRepository(UserComment::class);
    /** @var StudioJoinRequestRepository $studio_join_request_repository */
    $studio_join_request_repository = $this->entity_manager->getRepository(StudioJoinRequest::class);
    /** @var ProjectRepository $studio_program_repository */
    $studio_program_repository = $this->entity_manager->getRepository(Project::class);
    $parameter_bag = $this->createStub(ParameterBagInterface::class);
    $notification_manager = $this->createStub(NotificationManager::class);
    $image_variant_generator = $this->createStub(\App\Storage\Images\ImageVariantGenerator::class);
    $image_variant_url_builder = $this->createStub(\App\Storage\Images\ImageVariantUrlBuilder::class);
    $this->object = new StudioManager(
      $this->entity_manager,
      $studio_repository,
      $studio_activity_repository,
      $studio_project_repository,
      $studio_user_repository,
      $user_comment_repository,
      $studio_join_request_repository,
      $studio_program_repository,
      $parameter_bag,
      $notification_manager,
      $image_variant_generator,
      $image_variant_url_builder,
    );
    $user_manager = $container->get(UserManager::class);
    \assert($user_manager instanceof UserManager);
    $this->user_manager = $user_manager;
    $user_fixture = $container->get(UserDataFixtures::class);
    \assert($user_fixture instanceof UserDataFixtures);
    $this->user_fixture = $user_fixture;
    $project_fixture = $container->get(ProjectDataFixtures::class);
    \assert($project_fixture instanceof ProjectDataFixtures);
    $this->project_fixture = $project_fixture;
    $this->user = $this->user_manager->findUserByUsername('catroweb') ?? $this->user_fixture->insertUser(['name' => 'catroweb', 'password' => '123456']);
    $studio_name = 'testname_'.uniqid();
    $this->studio = $this->object->createStudio($this->user, $studio_name, 'test description');
  }

  #[\Override]
  protected function tearDown(): void
  {
    if (!isset($this->object, $this->studio, $this->user)) {
      parent::tearDown();

      return;
    }

    $this->object->deleteStudio($this->studio, $this->user);
    $this->entity_manager->close();
    parent::tearDown();
  }

  #[Group('integration')]
  public function testCreateDeleteStudio(): void
  {
    $this->assertInstanceOf(Studio::class, $this->studio);
    $this->assertNotNull($this->object->findStudioUser($this->user, $this->studio));
    $this->assertCount(1, $this->object->findAllStudioActivities($this->studio));
    $this->assertSame($this->studio, $this->object->findStudioById($this->studio->getId()));
    $studio_cloned = clone $this->studio;
    $this->object->deleteStudio($this->studio, $this->user);
    $this->assertNull($this->object->findStudioById($studio_cloned->getId()));
    $this->assertNull($this->object->findStudioUser($this->user, $studio_cloned));
    $this->assertEmpty($this->object->findAllStudioActivities($studio_cloned));
  }

  #[Group('integration')]
  public function testEditStudio(): void
  {
    $newStudio = clone $this->studio;
    $newStudio->setName('new studio name '.uniqid());
    $newStudio->setDescription('new studio description');
    $this->assertSame($newStudio, $this->object->changeStudio($this->user, $newStudio));
    $this->assertNotSame($this->studio, $newStudio);
  }

  #[Group('integration')]
  public function testAddAndRemoveStudioUsers(): void
  {
    $newUser = $this->user_fixture->insertUser(['name' => 'amrdiab_'.uniqid(), 'password' => '123456']);
    $this->assertFalse($this->object->isUserInStudio($newUser, $this->studio));
    $newStudioUser = $this->object->addUserToStudio($this->user, $this->studio, $newUser);
    $this->assertInstanceOf(StudioUser::class, $newStudioUser);
    $this->assertSame($newStudioUser, $this->object->findStudioUser($newUser, $this->studio));
    $this->assertNotNull($this->object->findStudioUser($newUser, $this->studio));
    $newUser_cloned = clone $newUser;
    $this->object->deleteUserFromStudio($this->user, $this->studio, $newUser);
    $this->assertNull($this->object->findStudioUser($newUser_cloned, $this->studio));
  }

  #[Group('integration')]
  public function testChangeStudioUserRole(): void
  {
    $newUser = $this->user_fixture->insertUser(['name' => 'leomessi_'.uniqid(), 'password' => '123456']);
    $this->assertNull($this->object->getStudioUserRole($newUser, $this->studio));
    if (is_null($this->object->addUserToStudio($this->user, $this->studio, $newUser))) {
      $this->markTestSkipped('unable to add new user to the studio');
    }

    $this->assertEquals(StudioUser::ROLE_ADMIN, $this->object->getStudioUserRole($this->user, $this->studio));
    $this->assertEquals(StudioUser::ROLE_MEMBER, $this->object->getStudioUserRole($newUser, $this->studio));
    $this->assertNull($this->object->changeStudioUserRole($newUser, $this->studio, $newUser, StudioUser::ROLE_ADMIN));
    $this->assertInstanceOf(StudioUser::class, $this->object->changeStudioUserRole($this->user, $this->studio, $newUser, StudioUser::ROLE_ADMIN));
    $this->assertEquals(StudioUser::ROLE_ADMIN, $this->object->getStudioUserRole($newUser, $this->studio));
  }

  #[Group('integration')]
  public function testChangeStudioUserStatus(): void
  {
    $newUser = $this->user_fixture->insertUser(['name' => 'lutherking_'.uniqid(), 'password' => '123456']);
    $this->assertNull($this->object->getStudioUserStatus($newUser, $this->studio));
    if (is_null($this->object->addUserToStudio($this->user, $this->studio, $newUser))) {
      $this->markTestSkipped('unable to add new user to the studio');
    }

    $this->assertEquals(StudioUser::STATUS_ACTIVE, $this->object->getStudioUserStatus($newUser, $this->studio));
    $this->assertNull($this->object->changeStudioUserStatus($newUser, $this->studio, $newUser, StudioUser::STATUS_BANNED));
    $this->assertInstanceOf(StudioUser::class, $this->object->changeStudioUserStatus($this->user, $this->studio, $newUser, StudioUser::STATUS_BANNED));
    $this->assertEquals(StudioUser::STATUS_BANNED, $this->object->getStudioUserStatus($newUser, $this->studio));
  }

  #[Group('integration')]
  public function testAddEditRemoveStudioComment(): void
  {
    $adminComment = $this->object->addCommentToStudio($this->user, $this->studio, 'test comment');
    $this->assertNotNull($adminComment);

    $newUser = $this->user_fixture->insertUser(['name' => 'eminem_'.uniqid(), 'password' => '123456']);
    $this->assertNull($this->object->addCommentToStudio($newUser, $this->studio, 'Only members of a studio can add comments'));

    $this->object->addUserToStudio($this->user, $this->studio, $newUser);

    $userComment = $this->object->addCommentToStudio($newUser, $this->studio, 'normal member comment');
    $this->assertNotNull($userComment);

    $adminCommentId = $adminComment->getId();
    $this->assertNotNull($adminCommentId);
    $userComment_2 = $this->object->addCommentToStudio($newUser, $this->studio, 'normal user comment 2');

    $this->assertNull($this->object->editStudioComment($newUser, $adminCommentId, "can't edit comments that are not your own"));

    $this->object->deleteCommentFromStudio($newUser, $adminCommentId);
    $this->assertNotNull($adminComment->getId(), "Can't delete comments that are not your own");

    $userCommentId = $userComment->getId();
    \assert(null !== $userCommentId);
    $this->object->deleteCommentFromStudio($newUser, $userCommentId);
    $this->assertNull($userComment->getId());

    $userComment2Id = $userComment_2->getId();
    \assert(null !== $userComment2Id);
    $this->object->deleteCommentFromStudio($newUser, $userComment2Id);
    $this->assertNull($userComment_2->getId());

    $this->assertCount(1, $this->object->findAllStudioComments($this->studio));
    $this->assertEquals(1, $this->object->countStudioComments($this->studio));

    $this->object->deleteCommentFromStudio($this->user, $adminComment->getId());
    $this->assertNull($adminComment->getId());

    $this->assertCount(0, $this->object->findAllStudioComments($this->studio));
    $this->assertEquals(0, $this->object->countStudioComments($this->studio));
  }

  /**
   * @throws \Exception
   */
  #[Group('integration')]
  public function testAddRemoveStudioProject(): void
  {
    $owner_name = 'kitkat_'.uniqid();
    $credit_name = 'peanutbutter_'.uniqid();
    $newUser = $this->user_fixture->insertUser(['name' => $owner_name, 'password' => '123456']);
    $newUser_2 = $this->user_fixture->insertUser(['name' => $credit_name, 'password' => '123456']);
    $project = $this->project_fixture->insertProject(['owned by' => $owner_name, 'name' => 'test prog',
      'description' => 'test desc', 'credit' => $credit_name, ]);
    $studio_project = $this->object->addProjectToStudio($newUser, $this->studio, $project);
    $this->assertNull($studio_project);
    $this->object->addUserToStudio($this->user, $this->studio, $newUser);
    $this->object->addUserToStudio($this->user, $this->studio, $newUser_2);

    $studio_project = $this->object->addProjectToStudio($newUser, $this->studio, $project);
    $this->assertInstanceOf(StudioProject::class, $studio_project);
    $this->object->deleteProjectFromStudio($newUser_2, $this->studio, $project);
    $this->assertNotNull($this->object->findStudioProject($this->studio, $project));
    $this->object->deleteProjectFromStudio($newUser, $this->studio, $project);
    $this->assertNull($this->object->findStudioProject($this->studio, $project));
    $this->object->addProjectToStudio($newUser_2, $this->studio, $project);
    $this->object->deleteProjectFromStudio($newUser, $this->studio, $project);
    $this->assertNull($this->object->findStudioProject($this->studio, $project));
    $this->object->addProjectToStudio($newUser, $this->studio, $project);
    $this->object->deleteProjectFromStudio($this->user, $this->studio, $project);
    $this->assertNull($this->object->findStudioProject($this->studio, $project));
    $this->assertCount(0, $this->object->findAllStudioProjects($this->studio));
  }

  #[Group('integration')]
  public function testAddRemoveStudioCommentReplies(): void
  {
    $studioComment = $this->object->addCommentToStudio($this->user, $this->studio, 'test comment');
    $replies = ['test reply 1', 'test reply 2'];
    $this->object->addCommentToStudio($this->user, $this->studio, $replies[0], $studioComment->getId());
    $this->object->addCommentToStudio($this->user, $this->studio, $replies[1], $studioComment->getId());
    $this->assertEquals(2, $this->object->countCommentReplies($studioComment->getId()));
    $replyTexts = array_map(
      static fn (UserComment $reply) => $reply->getText(),
      $this->object->findCommentReplies($studioComment->getId()),
    );
    sort($replyTexts);
    sort($replies);
    $this->assertEquals($replies, $replyTexts);

    $this->object->deleteCommentFromStudio($this->user, $studioComment->getId());

    $this->assertEquals(0, $this->object->countStudioComments($this->studio));
  }
}
