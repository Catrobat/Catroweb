<?php

namespace Catrobat\CatrowebBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Catrobat\CatrowebBundle\Entity\Project;
use Catrobat\CatrowebBundle\Entity\User;

class LoadSampleProjects implements FixtureInterface
{
  /**
   * {@inheritDoc}
   */
  public function load(ObjectManager $manager)
  {
    $user = new User();
    $user->setUsername("catroweb");
    $user->setEmail("dev@pocketcode.org");
    $user->setPlainPassword("catroweb");
    $user->setEnabled(true);
    $manager->persist($user);
    
    
    $project = new Project();
    $project->setDescription("My First Project");
    $project->setName("First Project");
    $project->setFilename("file.catrobat");
    $project->setThumbnail("thumb.png");
    $project->setScreenshot("screenshot.png");
    $project->setFilename("file.catrobat");
    $project->setDownloads(4);
    $project->setViews(4);
    $project->setFilename("file.catrobat");
    $project->setUser($user);
    $manager->persist($project);

    $project = new Project();
    $project->setDescription("Most Downloaded Project");
    $project->setName("Sample Project 2");
    $project->setFilename("file.catrobat");
    $project->setThumbnail("thumb.png");
    $project->setScreenshot("screenshot.png");
    $project->setFilename("file.catrobat");
    $project->setDownloads(100);
    $project->setViews(100);
    $project->setFilename("file.catrobat");
    $manager->persist($project);
    
    
    for ($i = 0; $i < 30; $i++)
    {
      $project = new Project();
      $project->setDescription("Number: " + $i);
      $project->setName("Project " . $i);
      $project->setFilename("file.catrobat");
      $project->setThumbnail("thumb.png");
      $project->setScreenshot("screenshot.png");
      $project->setFilename("file.catrobat");
      $project->setDownloads(40);
      $project->setViews(200);
      $project->setFilename("file.catrobat");
      $project->setUploadedAt(new \DateTime('2013-01-01 15:00:00'));
      $manager->persist($project);
    } 
    
    
    $manager->flush();
  }
}
