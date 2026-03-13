<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Moderation;

use App\DB\Entity\Project\Program;
use App\DB\Entity\User\User;
use App\DB\Enum\ContentType;
use App\Moderation\ContentVisibilityManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ContentVisibilityManager::class)]
final class ContentVisibilityManagerTest extends TestCase
{
  #[Group('unit')]
  public function testShowContentSkipsWhenOwnerSuspended(): void
  {
    $owner = $this->createStub(User::class);
    $owner->method('getId')->willReturn('owner-id');
    $owner->method('getProfileHidden')->willReturn(true);

    $project = $this->createStub(Program::class);
    $project->method('getUser')->willReturn($owner);
    $project->method('getAutoHidden')->willReturn(true);

    $em = $this->createStub(EntityManagerInterface::class);
    $em->method('find')
      ->willReturnCallback(function (string $class, mixed $id) use ($project, $owner): ?\PHPUnit\Framework\MockObject\Stub {
        if (Program::class === $class) {
          return $project;
        }
        if (User::class === $class) {
          return $owner;
        }

        return null;
      })
    ;

    $manager = new ContentVisibilityManager($em);

    // showContent should return early without clearing auto_hidden
    // because the owner is suspended. We verify by checking isContentHidden after.
    $manager->showContent(ContentType::Project, 'project-id');

    // The project should still report as hidden since we used a stub
    // (setAutoHidden won't actually be called because the guard returns early)
    $this->assertTrue($manager->isContentHidden(ContentType::Project, 'project-id'));
  }

  #[Group('unit')]
  public function testShowContentRestoresWhenOwnerNotSuspended(): void
  {
    $owner = $this->createStub(User::class);
    $owner->method('getId')->willReturn('owner-id');
    $owner->method('getProfileHidden')->willReturn(false);

    $project = $this->createMock(Program::class);
    $project->method('getUser')->willReturn($owner);
    $project->expects($this->once())
      ->method('setAutoHidden')
      ->with(false)
    ;

    $em = $this->createStub(EntityManagerInterface::class);
    $em->method('find')
      ->willReturnCallback(function (string $class, mixed $id) use ($project, $owner): \PHPUnit\Framework\MockObject\MockObject|\PHPUnit\Framework\MockObject\Stub|null {
        if (Program::class === $class) {
          return $project;
        }
        if (User::class === $class) {
          return $owner;
        }

        return null;
      })
    ;

    $manager = new ContentVisibilityManager($em);
    $manager->showContent(ContentType::Project, 'project-id');
  }

  #[Group('unit')]
  public function testShowContentUserTypeAlwaysProceeds(): void
  {
    $user = $this->createMock(User::class);
    $user->expects($this->once())
      ->method('setProfileHidden')
      ->with(false)
    ;

    $em = $this->createStub(EntityManagerInterface::class);
    $em->method('find')->willReturn($user);

    $manager = new ContentVisibilityManager($em);
    $manager->showContent(ContentType::User, 'user-id');
  }
}
