<?php
namespace Catrobat\CatrowebBundle\Model;

use FOS\UserBundle\Util\CanonicalizerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use FOS\UserBundle\Model\UserInterface;

class UserManager extends \FOS\UserBundle\Doctrine\UserManager
{
  public function __construct(EncoderFactoryInterface $encoderFactory, CanonicalizerInterface $usernameCanonicalizer, CanonicalizerInterface $emailCanonicalizer, ObjectManager $om, $class)
  {
    parent::__construct($encoderFactory, $usernameCanonicalizer, $emailCanonicalizer, $om, $class);
  }
  
  public function isPasswordValid(UserInterface $user, $raw_password)
  {
    return $this->getEncoder($user)->isPasswordValid($user->getPassword(), $raw_password, $user->getSalt());
  }
}
