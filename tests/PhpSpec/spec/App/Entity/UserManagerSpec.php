<?php

namespace tests\PhpSpec\spec\App\Entity;

use App\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use FOS\UserBundle\Util\CanonicalFieldsUpdater;
use FOS\UserBundle\Util\PasswordUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;


/**
 * Class UserManagerSpec
 * @package tests\PhpSpec\spec\App\Entity
 */
class UserManagerSpec extends ObjectBehavior
{

  /**
   * @param PasswordUpdaterInterface|\PhpSpec\Wrapper\Collaborator $passwordUpdater
   * @param CanonicalFieldsUpdater|\PhpSpec\Wrapper\Collaborator   $canonicalFieldsUpdater
   * @param ObjectManager|\PhpSpec\Wrapper\Collaborator            $object_manager
   * @param User|\PhpSpec\Wrapper\Collaborator                     $user
   * @param ClassMetadata|\PhpSpec\Wrapper\Collaborator            $meta
   * @param EntityRepository|\PhpSpec\Wrapper\Collaborator         $repository
   */
  public function let(PasswordUpdaterInterface $passwordUpdater, CanonicalFieldsUpdater $canonicalFieldsUpdater,
                      ObjectManager $object_manager, User $user, ClassMetadata $meta, EntityRepository $repository)
  {
    $object_manager->getClassMetadata(Argument::any())->willReturn($meta);
    $object_manager->getRepository(Argument::any())->willReturn($repository);
    $this->beConstructedWith($passwordUpdater, $canonicalFieldsUpdater, $object_manager, $user);
  }

  /**
   *
   */
  public function it_is_initializable()
  {
    $this->shouldHaveType('App\Entity\UserManager');
  }

}
