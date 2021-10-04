<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * DoctrineFixturesBundle
 * https://symfony.com/bundles/DoctrineFixturesBundle/current/index.html.
 *
 * Fixtures are used to load a "fake" set of data into a database that can then be used for testing or to help give
 * you some interesting data while you're developing your application.
 */
class AppFixtures extends Fixture
{
  public function load(ObjectManager $manager): void
  {
    // $program = new Program();
    // $manager->persist($program);

    $manager->flush();
  }
}
