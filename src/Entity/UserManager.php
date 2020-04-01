<?php

namespace App\Entity;

use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Util\CanonicalFieldsUpdater;
use FOS\UserBundle\Util\PasswordUpdaterInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class UserManager extends \Sonata\UserBundle\Entity\UserManager
{
  public function __construct(PasswordUpdaterInterface $passwordUpdater,
                              CanonicalFieldsUpdater $canonicalFieldsUpdater,
                              EntityManagerInterface $om)
  {
    parent::__construct($passwordUpdater, $canonicalFieldsUpdater, $om, User::class);
  }

  public function isPasswordValid(UserInterface $user, string $password, PasswordEncoderInterface $encoder): bool
  {
    return $encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt());
  }
}
