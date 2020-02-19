<?php

namespace App\Entity;

use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Util\CanonicalFieldsUpdater;
use FOS\UserBundle\Util\PasswordUpdaterInterface;

/**
 * Class UserManager.
 */
class UserManager extends \Sonata\UserBundle\Entity\UserManager
{
  /**
   * UserManager constructor.
   */
  public function __construct(PasswordUpdaterInterface $passwordUpdater,
                              CanonicalFieldsUpdater $canonicalFieldsUpdater,
                              EntityManagerInterface $om)
  {
    parent::__construct($passwordUpdater, $canonicalFieldsUpdater, $om, User::class);
  }

  /**
   * @param $user
   * @param $password
   * @param $encoder
   *
   * @return mixed
   */
  public function isPasswordValid($user, $password, $encoder)
  {
    return $encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt());
  }
}
