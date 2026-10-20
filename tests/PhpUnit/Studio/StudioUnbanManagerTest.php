<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Studio;

use App\DB\Entity\Studio\Studio;
use App\DB\Entity\Studio\StudioUser;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\Project\ProjectRepository;
use App\DB\EntityRepository\Studios\StudioActivityRepository;
use App\DB\EntityRepository\Studios\StudioInviteRepository;
use App\DB\EntityRepository\Studios\StudioJoinRequestRepository;
use App\DB\EntityRepository\Studios\StudioProjectRepository;
use App\DB\EntityRepository\Studios\StudioRepository;
use App\DB\EntityRepository\Studios\StudioUserRepository;
use App\DB\EntityRepository\User\Comment\UserCommentRepository;
use App\Storage\Images\ImageVariantGenerator;
use App\Storage\Images\ImageVariantUrlBuilder;
use App\Studio\StudioManager;
use App\User\Notification\NotificationManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/** @internal */
#[CoversClass(StudioManager::class)]
#[Group('unit')]
final class StudioUnbanManagerTest extends TestCase
{
  #[DataProvider('adminMemberships')]
  public function testOnlyAnActiveAdminMayRemoveABan(?string $status, string $role, bool $allowed): void
  {
    $admin = new User();
    $target = new User();
    $studio = new Studio();
    $admin_membership = null === $status ? null : new StudioUser()->setRole($role)->setStatus($status);
    $banned = new StudioUser()->setRole(StudioUser::ROLE_ADMIN)->setStatus(StudioUser::STATUS_BANNED);
    $memberships = $this->createMock(StudioUserRepository::class);
    $memberships->expects(self::exactly($allowed ? 2 : 1))->method('findStudioUser')->willReturnCallback(
      static fn (?\Symfony\Component\Security\Core\User\UserInterface $user, Studio $requested_studio): ?StudioUser => $requested_studio === $studio ? ($user === $admin ? $admin_membership : ($user === $target ? $banned : null)) : null,
    );
    $entity_manager = $this->createMock(EntityManagerInterface::class);
    $entity_manager->expects($allowed ? self::once() : self::never())->method('remove')->with($banned);
    $entity_manager->expects($allowed ? self::once() : self::never())->method('flush');
    $entity_manager->expects(self::never())->method('persist');
    $manager = new StudioManager(
      $entity_manager,
      $this->createStub(StudioRepository::class),
      $this->createStub(StudioActivityRepository::class),
      $this->createStub(StudioProjectRepository::class),
      $memberships,
      $this->createStub(UserCommentRepository::class),
      $this->createStub(StudioJoinRequestRepository::class),
      $this->createStub(ProjectRepository::class),
      $this->createStub(ParameterBagInterface::class),
      $this->createStub(NotificationManager::class),
      $this->createStub(ImageVariantGenerator::class),
      $this->createStub(ImageVariantUrlBuilder::class),
      $this->createStub(StudioInviteRepository::class),
    );
    self::assertSame($allowed, $manager->unbanStudioUser($admin, $studio, $target));
    self::assertSame(StudioUser::STATUS_BANNED, $banned->getStatus(), 'Removing the row must not activate or restore the old admin membership');
  }

  public static function adminMemberships(): iterable
  {
    yield 'active admin' => [StudioUser::STATUS_ACTIVE, StudioUser::ROLE_ADMIN, true];
    yield 'banned admin' => [StudioUser::STATUS_BANNED, StudioUser::ROLE_ADMIN, false];
    yield 'pending admin' => [StudioUser::STATUS_PENDING_REQUEST, StudioUser::ROLE_ADMIN, false];
    yield 'active member' => [StudioUser::STATUS_ACTIVE, StudioUser::ROLE_MEMBER, false];
    yield 'absent admin' => [null, StudioUser::ROLE_ADMIN, false];
  }
}
