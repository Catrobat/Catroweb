<?php

namespace App\Entity;

use Doctrine\Common\Persistence\ObjectManager;
use FOS\UserBundle\Util\CanonicalFieldsUpdater;
use FOS\UserBundle\Util\PasswordUpdaterInterface;

/**
 * Class UserManager
 * @package App\Entity
 */
class UserManager extends \Sonata\UserBundle\Entity\UserManager
{
  /**
   * UserManager constructor.
   *
   * @param PasswordUpdaterInterface $passwordUpdater
   * @param CanonicalFieldsUpdater   $canonicalFieldsUpdater
   * @param ObjectManager            $om
   */
  public function __construct(PasswordUpdaterInterface $passwordUpdater,
                                 CanonicalFieldsUpdater $canonicalFieldsUpdater,
                                 ObjectManager $om)
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