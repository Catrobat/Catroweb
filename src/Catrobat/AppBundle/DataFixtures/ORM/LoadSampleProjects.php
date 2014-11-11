<?php

namespace Catrobat\AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Catrobat\AppBundle\Entity\Program;
use Catrobat\AppBundle\Entity\User;

class LoadSamplePrograms implements FixtureInterface
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
    
    
    $program = new Program();
    $program->setDescription("My First Program");
    $program->setName("First Program");
    $program->setFilename("file.catrobat");
    $program->setThumbnail("thumb.png");
    $program->setScreenshot("screenshot.png");
    $program->setFilename("file.catrobat");
    $program->setDownloads(4);
    $program->setViews(4);
    $program->setFilename("file.catrobat");
    $program->setCatrobatVersionName("0.8.5");
    $program->setCatrobatVersion(1);
    $program->setUser($user);
    $program->setUploadIp("127.0.0.1");
    $program->setVisible(true);
    $program->setUploadLanguage("en");
    $program->setFilesize("100");

      $program->setApproved(false);
    $program->setRemixCount(0);
    $manager->persist($program);

    $program = new Program();
    $program->setDescription("Most Downloaded Program");
    $program->setName("Sample Program 2");
    $program->setFilename("file.catrobat");
    $program->setThumbnail("thumb.png");
    $program->setScreenshot("screenshot.png");
    $program->setFilename("file.catrobat");
    $program->setDownloads(100);
    $program->setViews(100);
    $program->setFilename("file.catrobat");
    $program->setCatrobatVersionName("0.8.5");
    $program->setCatrobatVersion(1);
    $program->setUser($user);
    $program->setUploadIp("127.0.0.1");
    $program->setVisible(true);
    $program->setUploadLanguage("en");
    $program->setFilesize("100");
    $program->setRemixCount(0);
      $program->setApproved(false);
    $manager->persist($program);
    
    
    for ($i = 0; $i < 30; $i++)
    {
      $program = new Program();
      $program->setDescription("Number: " + $i);
      $program->setName("Program " . $i);
      $program->setFilename("file.catrobat");
      $program->setThumbnail("thumb.png");
      $program->setScreenshot("screenshot.png");
      $program->setFilename("file.catrobat");
      $program->setDownloads(40);
      $program->setViews(200);
      $program->setFilename("file.catrobat");
      $program->setCatrobatVersionName("0.8.5");
      $program->setCatrobatVersion(1);
      $program->setUser($user);
      $program->setUploadedAt(new \DateTime('2013-01-01 15:00:00'));
      $program->setUploadIp("127.0.0.1");
      $program->setVisible(true);
      $program->setUploadLanguage("en");
      $program->setFilesize("100");
      $program->setRemixCount(0);
        $program->setApproved(false);
      $manager->persist($program);
    } 
    
    
    $manager->flush();
  }
}
