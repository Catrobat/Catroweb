<?php

namespace Tests\PhpUnit\User;

use App\DB\EntityRepository\User\UserRepository;
use App\Project\ProjectManager;
use App\User\UserManager;
use App\Utils\CanonicalFieldsUpdater;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\UrlHelper;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @internal
 *
 * @covers \App\User\UserManager
 */
class UserManagerTest extends TestCase
{
  private UserManager $user_manager;

  protected function setUp(): void
  {
    $canonicalFieldsUpdater = $this->createMock(CanonicalFieldsUpdater::class);
    $userPasswordHasher = $this->createMock(UserPasswordHasherInterface::class);
    $object_manager = $this->createMock(EntityManagerInterface::class);
    $meta = $this->createMock(ClassMetadata::class);
    $repository = $this->createMock(EntityRepository::class);
    $object_manager->expects($this->any())->method('getClassMetadata')->willReturn($meta);
    $object_manager->expects($this->any())->method('getRepository')->willReturn($repository);
    $program_manager = $this->createMock(ProjectManager::class);
    $user_finder = $this->createMock(TransformedFinder::class);
    $user_repository = $this->createMock(UserRepository::class);
    $url_helper = new UrlHelper(new RequestStack());
    $this->user_manager = new UserManager(
      $canonicalFieldsUpdater,
      $userPasswordHasher,
      $object_manager,
      $user_finder,
      $program_manager,
      $url_helper,
      $user_repository
    );
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(UserManager::class, $this->user_manager);
  }
}
