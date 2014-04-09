<?php

namespace Catrobat\WebBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Catrobat\CoreBundle\Entity\Project;
use Catrobat\CoreBundle\Entity\User;

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
    $project->setCatrobatVersionName("0.8.5");
    $project->setCatrobatVersion(1);
    $project->setUser($user);
    $project->setUploadIp("127.0.0.1");
    $project->setVisible(true);
    $project->setUploadLanguage("en");
    $project->setFilesize("100");

      $project->setApproved(false);
    $project->setRemixCount(0);
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
    $project->setCatrobatVersionName("0.8.5");
    $project->setCatrobatVersion(1);
    $project->setUser($user);
    $project->setUploadIp("127.0.0.1");
    $project->setVisible(true);
    $project->setUploadLanguage("en");
    $project->setFilesize("100");
    $project->setRemixCount(0);
      $project->setApproved(false);
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
      $project->setCatrobatVersionName("0.8.5");
      $project->setCatrobatVersion(1);
      $project->setUser($user);
      $project->setUploadedAt(new \DateTime('2013-01-01 15:00:00'));
      $project->setUploadIp("127.0.0.1");
      $project->setVisible(true);
      $project->setUploadLanguage("en");
      $project->setFilesize("100");
      $project->setRemixCount(0);
        $project->setApproved(false);
      $manager->persist($project);
    } 
    
    
    $manager->flush();
  }
}
