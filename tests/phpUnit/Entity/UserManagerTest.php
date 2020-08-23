<?php

namespace Tests\phpUnit\Entity;

use App\Entity\ProgramManager;
use App\Entity\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use FOS\UserBundle\Util\CanonicalFieldsUpdater;
use FOS\UserBundle\Util\PasswordUpdaterInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\UrlHelper;

/**
 * @internal
 * @covers \App\Entity\UserManager
 */
class UserManagerTest extends TestCase
{
  private UserManager $user_manager;

  protected function setUp(): void
  {
    $passwordUpdater = $this->createMock(PasswordUpdaterInterface::class);
    $canonicalFieldsUpdater = $this->createMock(CanonicalFieldsUpdater::class);
    $object_manager = $this->createMock(EntityManagerInterface::class);
    $meta = $this->createMock(ClassMetadata::class);
    $repository = $this->createMock(EntityRepository::class);
    $object_manager->expects($this->any())->method('getClassMetadata')->willReturn($meta);
    $object_manager->expects($this->any())->method('getRepository')->willReturn($repository);
    $program_manager = $this->createMock(ProgramManager::class);
    $user_finder = $this->createMock(TransformedFinder::class);
    $url_helper = new UrlHelper(new RequestStack());
    $this->user_manager = new UserManager($passwordUpdater, $canonicalFieldsUpdater, $object_manager, $user_finder, $program_manager, $url_helper);
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(UserManager::class, $this->user_manager);
  }
}
