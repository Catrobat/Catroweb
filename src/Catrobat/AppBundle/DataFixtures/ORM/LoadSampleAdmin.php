<?php

namespace Catrobat\AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Catrobat\AppBundle\Entity\User;

class LoadSampleAdmin implements FixtureInterface
{
    /**
   * {@inheritDoc}
   */
  public function load(ObjectManager $manager)
  {
      $user = new User();
      $user->setUsername('admin');
      $user->setEmail('admin@pocketcode.org');
      $user->setPlainPassword('q1w2e3r4');
      $user->setEnabled(true);
      $user->setSuperAdmin(true);
      $manager->persist($user);

      $manager->flush();
  }
}
