<?php

namespace Catrobat\CoreBundle\Spec\Model;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UserManagerSpec extends ObjectBehavior
{
    /**
     * @param \Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface $encoder_factory 
     * @param \FOS\UserBundle\Util\CanonicalizerInterface $username_canonicalizer 
     * @param \FOS\UserBundle\Util\CanonicalizerInterface $email_canonicalizer
     * @param \Doctrine\Common\Persistence\ObjectManager $object_manager 
     * @param \Catrobat\CoreBundle\Entity\User $user
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadata $meta
     * @param \Doctrine\ORM\EntityRepository $repository
     * @param \Symfony\Component\Security\Core\Encoder\BasePasswordEncoder $password_encoder
     */
    function let($encoder_factory, $username_canonicalizer, $email_canonicalizer, $object_manager, $user, $meta, $repository, $password_encoder)
    {
      $password_encoder->isPasswordValid(Argument::any(),"123", Argument::any())->willReturn(true);
      $password_encoder->isPasswordValid(Argument::any(),"abc", Argument::any())->willReturn(false);
      $encoder_factory->getEncoder(Argument::any())->willReturn($password_encoder);
      $object_manager->getClassMetadata(Argument::any())->willReturn($meta);
      $object_manager->getRepository(Argument::any())->willReturn($repository);
      $this->beConstructedWith($encoder_factory, $username_canonicalizer, $email_canonicalizer, $object_manager, $user);
    }
  
    function it_is_initializable()
    {
      $this->shouldHaveType('Catrobat\CoreBundle\Model\UserManager');
    }
    
    function it_checks_if_password_is_valid($user, $object_manager, $repository)
    {
      $this->isPasswordValid($user, "123")->shouldBe(true);
      $this->isPasswordValid($user, "abc")->shouldBe(false);
    }
}
