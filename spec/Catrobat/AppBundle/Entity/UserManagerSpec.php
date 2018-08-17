<?php

namespace spec\Catrobat\AppBundle\Entity;

use Catrobat\AppBundle\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use FOS\UserBundle\Util\CanonicalFieldsUpdater;
use FOS\UserBundle\Util\PasswordUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;


class UserManagerSpec extends ObjectBehavior
{

    public function let(PasswordUpdaterInterface $passwordUpdater, CanonicalFieldsUpdater $canonicalFieldsUpdater,
                        ObjectManager $object_manager, User $user, ClassMetadata $meta, EntityRepository $repository)
    {
        $object_manager->getClassMetadata(Argument::any())->willReturn($meta);
        $object_manager->getRepository(Argument::any())->willReturn($repository);
        $this->beConstructedWith($passwordUpdater, $canonicalFieldsUpdater, $object_manager, $user);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Catrobat\AppBundle\Entity\UserManager');
    }

//    public function it_checks_if_password_is_valid(User $user, ObjectManager $object_manager, EntityRepository $repository)
//    {
//        $this->isPasswordValid($user, '123')->shouldBe(true);
//        $this->isPasswordValid($user, 'abc')->shouldBe(false);
//    }
}
