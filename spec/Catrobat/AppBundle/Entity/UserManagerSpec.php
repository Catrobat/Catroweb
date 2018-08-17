<?php

namespace spec\Catrobat\AppBundle\Entity;

use Catrobat\AppBundle\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use FOS\UserBundle\Util\CanonicalizerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Encoder\BasePasswordEncoder;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class UserManagerSpec extends ObjectBehavior
{

    public function let(EncoderFactoryInterface $encoder_factory, CanonicalizerInterface $username_canonicalizer, CanonicalizerInterface $email_canonicalizer, ObjectManager $object_manager, User $user, ClassMetadata $meta, EntityRepository $repository, BasePasswordEncoder $password_encoder)
    {
        $password_encoder->isPasswordValid(Argument::any(), '123', Argument::any())->willReturn(true);
        $password_encoder->isPasswordValid(Argument::any(), 'abc', Argument::any())->willReturn(false);
        $encoder_factory->getEncoder(Argument::any())->willReturn($password_encoder);
        $object_manager->getClassMetadata(Argument::any())->willReturn($meta);
        $object_manager->getRepository(Argument::any())->willReturn($repository);
        $this->beConstructedWith($encoder_factory, $username_canonicalizer, $email_canonicalizer, $object_manager, $user);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Catrobat\AppBundle\Entity\UserManager');
    }

    public function it_checks_if_password_is_valid(User $user, ObjectManager $object_manager, EntityRepository $repository)
    {
        $this->isPasswordValid($user, '123')->shouldBe(true);
        $this->isPasswordValid($user, 'abc')->shouldBe(false);
    }
}
