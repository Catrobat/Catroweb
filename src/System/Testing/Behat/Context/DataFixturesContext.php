<?php

declare(strict_types=1);

namespace App\System\Testing\Behat\Context;

use App\DB\Entity\Flavor;
use App\DB\Entity\MaintenanceInformation;
use App\DB\Entity\MediaLibrary\MediaPackage;
use App\DB\Entity\MediaLibrary\MediaPackageCategory;
use App\DB\Entity\MediaLibrary\MediaPackageFile;
use App\DB\Entity\Project\Extension;
use App\DB\Entity\Project\Program;
use App\DB\Entity\Project\ProgramLike;
use App\DB\Entity\Project\Tag;
use App\DB\Entity\Studio\Studio;
use App\DB\Entity\Studio\StudioActivity;
use App\DB\Entity\Studio\StudioJoinRequest;
use App\DB\Entity\Studio\StudioUser;
use App\DB\Entity\Survey;
use App\DB\Entity\Translation\CommentMachineTranslation;
use App\DB\Entity\Translation\ProjectCustomTranslation;
use App\DB\Entity\Translation\ProjectMachineTranslation;
use App\DB\Entity\User\Achievements\Achievement;
use App\DB\Entity\User\Achievements\UserAchievement;
use App\DB\Entity\User\Comment\UserComment;
use App\DB\Entity\User\Notifications\BroadcastNotification;
use App\DB\Entity\User\Notifications\CatroNotification;
use App\DB\Entity\User\Notifications\CommentNotification;
use App\DB\Entity\User\Notifications\FollowNotification;
use App\DB\Entity\User\Notifications\LikeNotification;
use App\DB\Entity\User\Notifications\NewProgramNotification;
use App\DB\Entity\User\Notifications\RemixNotification;
use App\DB\Entity\User\User;
use App\DB\Generator\MyUuidGenerator;
use App\System\Commands\DBUpdater\UpdateAchievementsCommand;
use App\System\Commands\Helpers\CommandHelper;
use App\System\Testing\Behat\ContextTrait;
use App\Utils\TimeUtils;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Process\Process;

class DataFixturesContext implements Context
{
  use ContextTrait;

  private array $projects = [];
  private array $featured_projects = [];
  private array $media_files = [];
  private array $users = [];

  /**
   * @BeforeFeature
   */
  public static function resetElastic(): void
  {
    $process = new Process(['bin/console', 'fos:elastica:reset', '-q']);
    $process->run();
  }

  /**
   * @Given the next Uuid Value will be :id
   */
  public function theNextUuidValueWillBe(string $id): void
  {
    MyUuidGenerator::setNextValue($id);
  }

  /**
   * @Given /^the current time is "([^"]*)"$/
   *
   * @throws \Exception
   */
  public function theCurrentTimeIs(string $time): void
  {
    $date = new \DateTime($time, new \DateTimeZone('UTC'));
    TimeUtils::freezeTime($date);
  }

  // -------------------------------------------------------------------------------------------------------------------
  //  Users
  // -------------------------------------------------------------------------------------------------------------------

  /**
   * @Given /^there are users:$/
   */
  public function thereAreUsers(TableNode $table): void
  {
    foreach ($table->getHash() as $config) {
      $user = $this->insertUser($config, false);
      $this->users[] = $user;
    }
    $this->getManager()->flush();
  }

  /**
   * @Given /^there are followers:$/
   */
  public function thereAreFollowers(TableNode $table): void
  {
    foreach ($table->getHash() as $config) {
      /** @var User|null $user */
      $user = $this->getUserManager()->findOneBy(['username' => $config['name']]);

      $followings = explode(', ', (string) $config['following']);
      foreach ($followings as $follow) {
        /** @var User|null $follow_user */
        $follow_user = $this->getUserManager()->findOneBy(['username' => $follow]);
        $user->addFollowing($follow_user);
        $this->getUserManager()->updateUser($user);
      }
    }

    $users = $this->users;
    unset($this->users);

    /** @var User|null $user */
    foreach ($users as $user) {
      $this->users[] = $this->getUserManager()->find($user->getId());
    }
  }

  /**
   * @Given /^there are admins:$/
   */
  public function thereAreAdmins(TableNode $table): void
  {
    foreach ($table->getHash() as $user_config) {
      $user_config['admin'] = 'true';
      $this->insertUser($user_config, false);
    }
    $this->getManager()->flush();
  }

  /**
   * @Given /^there are (\d+) additional users$/
   * @Given /^there are (\d+) users$/
   */
  public function thereAreManyUsers(string $user_count): void
  {
    $list = ['name'];
    $base = 10 ** strlen(strval((int) $user_count - 1));
    for ($i = 0; $i < $user_count; ++$i) {
      $list[] = 'User'.($base + $i);
    }
    $table = TableNode::fromList($list);
    $this->thereAreUsers($table);
  }

  /**
   * @Then /^the following users exist in the database:$/
   */
  public function theFollowingUsersExistInTheDatabase(TableNode $table): void
  {
    foreach ($table->getHash() as $config) {
      $this->assertUser($config);
    }
  }

  /**
   * @Then /^the user "([^"]*)" should not exist$/
   */
  public function theUserShouldNotExist(string $username): void
  {
    $user = $this->getUserManager()->findUserByUsername($username);
    Assert::assertNull($user);
  }

  /**
   * @Then /^the user "([^"]*)" with email "([^"]*)" should exist and be enabled$/
   */
  public function theUserWithUsernameAndEmailShouldExistAndBeEnabled(string $username, string $email): void
  {
    $em = $this->getManager();
    $user = $em->getRepository(User::class)->findOneBy([
      'username' => $username,
    ]);

    Assert::assertInstanceOf(User::class, $user);
    Assert::assertEquals($email, $user->getEmail());
    Assert::assertTrue($user->IsEnabled());
  }

  /**
   * @Given :number_of_users users follow:
   */
  public function thereAreNUsersThatFollow(string $number_of_users, TableNode $table): void
  {
    $user = $table->getHash()[0];
    $followedUser = $this->insertUser($user, false);
    for ($i = 1; $i < $number_of_users; ++$i) {
      $user = $this->insertUser([], false);
      $user->addFollowing($followedUser);
      $this->getUserManager()->updateUser($user, false);
      $notification = new FollowNotification($followedUser, $user);
      $this->getManager()->persist($notification);
    }
    $this->getManager()->flush();
  }

  /**
   * @When /^User "([^"]*)" is followed by "([^"]*)"$/
   */
  public function userIsFollowed(string $user_id, string $follow_ids_as_string): void
  {
    /** @var User|null $user */
    $user = $this->getUserManager()->find($user_id);

    $ids = explode(',', $follow_ids_as_string);
    foreach ($ids as $id) {
      /** @var User|null $followUser */
      $followUser = $this->getUserManager()->find($id);
      $user->addFollowing($followUser);
      $this->getUserManager()->updateUser($user);
    }
  }

  /**
   * @When /^User "([^"]*)" is followed by user "([^"]*)"$/
   */
  public function userIsFollowedByUser(string $user_id, string $follow_id): void
  {
    /** @var User|null $user */
    $user = $this->getUserManager()->find($user_id);

    /** @var User|null $followUser */
    $followUser = $this->getUserManager()->find($follow_id);
    $user->addFollowing($followUser);
    $this->getUserManager()->updateUser($user);
  }

  /**
   * @Then /^user "([^"]*)" with email "([^"]*)" should exist$/
   */
  public function userWithUsernameWithEmailShouldExist(string $username, string $email): void
  {
    $em = $this->getManager();

    /** @var User|null $user */
    $user = $em->getRepository(User::class)->findOneBy([
      'username' => $username,
    ]);

    Assert::assertInstanceOf(User::class, $user);
    Assert::assertEquals($email, $user->getEmail());
  }

  // -------------------------------------------------------------------------------------------------------------------
  //  Surveys
  // -------------------------------------------------------------------------------------------------------------------

  /**
   * @Given /^there are surveys:$/
   */
  public function thereAreSurveys(TableNode $table): void
  {
    $em = $this->getManager();
    foreach ($table->getHash() as $survey_config) {
      $survey = new Survey();
      $survey->setLanguageCode($survey_config['language code']);
      $survey->setUrl($survey_config['url']);
      $survey->setFlavor($this->getFlavorRepository()->getFlavorByName($survey_config['flavor'] ?? ''));
      $survey->setPlatform($survey_config['platform'] ?? '');
      $em->persist($survey);
    }
    $em->flush();
    $this->getManager()->flush();
  }
  // -------------------------------------------------------------------------------------------------------------------
  //
  // -------------------------------------------------------------------------------------------------------------------

  /**
   * @Given /^there are studio join requests:$/
   */
  public function thereAreStudioJoinRequests(TableNode $table): void
  {
    $em = $this->getManager();

    foreach ($table->getHash() as $joinRequestConfig) {
      /** @var User|null $user */
      $user = $this->getUserManager()->findUserByUsername($joinRequestConfig['User']);
      if (!$user) {
        throw new \RuntimeException('User not found: '.$joinRequestConfig['User']);
      }
      $studio = $this->getStudioManager()->findStudioByName($joinRequestConfig['Studio']);
      if (!$studio) {
        throw new \RuntimeException('Studio not found: '.$joinRequestConfig['Studio']);
      }
      $joinRequest = new StudioJoinRequest();
      $joinRequest->setUser($user);
      $joinRequest->setStudio($studio);
      $joinRequest->setStatus($joinRequestConfig['Status']);
      $em->persist($joinRequest);
    }

    $em->flush();
  }

  // -------------------------------------------------------------------------------------------------------------------
  //  MaintenanceInformation
  // -------------------------------------------------------------------------------------------------------------------

  /**
   * @Given /^there are maintenance information:$/
   */
  public function thereAreMaintenanceInformation(TableNode $table): void
  {
    $em = $this->getManager();
    foreach ($table->getHash() as $maintenanceinformation_config) {
      $maintenanceInformation = new MaintenanceInformation();
      $maintenance_start = $maintenanceinformation_config['Maintenance Start'];
      $format = 'Y-m-d';
      $date = \DateTime::createFromFormat($format, $maintenance_start);
      $maintenanceInformation->setLtmMaintenanceStart($date);
      $maintenance_end = $maintenanceinformation_config['Maintenance End'];
      $date = \DateTime::createFromFormat($format, $maintenance_end);
      $maintenanceInformation->setLtmMaintenanceEnd($date);
      $maintenanceInformation->setLtmAdditionalInformation($maintenanceinformation_config['Additional Information']);
      $maintenanceInformation->setLtmCode('maintenanceinformations.maintenance_information.feature_'.$maintenanceinformation_config['Id']);
      $maintenanceInformation->setIcon($maintenanceinformation_config['Icon']);
      $maintenanceInformation->setActive((bool) $maintenanceinformation_config['Active']);
      $maintenanceInformation->setInternalTitle($maintenanceinformation_config['Title']);
      $em->persist($maintenanceInformation);
    }
    $em->flush();
  }
  // -------------------------------------------------------------------------------------------------------------------
  //  Projects
  // -------------------------------------------------------------------------------------------------------------------

  /**
   * @Given /^there are projects:$/
   *
   * @throws \Exception
   */
  public function thereAreProjects(TableNode $table): void
  {
    foreach ($table->getHash() as $config) {
      $inserted_project = $this->insertProject($config, false);
      $this->projects[] = $inserted_project;
    }
    $this->getManager()->flush();
  }

  /**
   * @Then /^the following projects exist in the database:$/
   */
  public function theFollowingProjectsExistInTheDatabase(TableNode $table): void
  {
    foreach ($table->getHash() as $config) {
      $this->assertProject($config);
    }
  }

  /**
   * @Given /^there are "([^"]*)" similar projects$/
   *
   * @throws \Exception
   */
  public function thereAreNumberOfSimilarProjects(string $num_of_projects): void
  {
    for ($project = 1; $project <= $num_of_projects; ++$project) {
      $project_info = ['name' => 'basic '.$project];
      $inserted_project = $this->insertProject($project_info, false);
      $this->projects[] = $inserted_project;
    }
    $this->getManager()->flush();
  }

  public function getProjects(): array
  {
    return $this->projects;
  }

  public function getFeaturedProjects(): array
  {
    return $this->featured_projects;
  }

  public function getMediaFiles(): array
  {
    return $this->media_files;
  }

  public function getUsers(): array
  {
    $users = $this->users;
    unset($this->users);

    /** @var User|null $user */
    foreach ($users as $user) {
      $this->users[] = $this->getUserManager()->find($user->getId());
    }

    return $this->users;
  }

  /**
   * @Given /^there are downloadable projects:$/
   *
   * @throws \Exception
   */
  public function thereAreDownloadableProjects(TableNode $table): void
  {
    $file_repo = $this->getFileRepository();
    foreach ($table->getHash() as $config) {
      $project = $this->insertProject($config, false);
      $file_repo->saveProjectZipFile(new File($this->FIXTURES_DIR.'test.catrobat'), $project->getId());
    }
    $this->getManager()->flush();
  }

  /**
   * @Given /^there are featured projects:$/
   * @Given /^following projects are featured:$/
   */
  public function thereAreFeaturedProjects(TableNode $table): void
  {
    foreach ($table->getHash() as $config) {
      $project = $this->insertFeaturedProject($config, false);
      $this->featured_projects[] = $project;
    }
    $this->getManager()->flush();
  }

  /**
   * @Given /^there are feature flags:$/
   */
  public function thereAreFeatureFlags(TableNode $table): void
  {
    $manager = $this->getFeatureFlagManager();
    foreach ($table->getHash() as $config) {
      $manager->setFlagValue($config['name'], (bool) $config['value']);
    }
  }

  /**
   * @Given /^there are example projects:$/
   * @Given /^following projects are examples:$/
   */
  public function thereAreExampleProjects(TableNode $table): void
  {
    foreach ($table->getHash() as $config) {
      $this->insertExampleProject($config, false);
    }
    $this->getManager()->flush();
  }

  /**
   * @Given /^there are projects with a large description:$/
   *
   * @throws \Exception
   */
  public function thereAreProjectsWithALargeDescription(TableNode $table): void
  {
    foreach ($table->getHash() as $config) {
      $config['description'] = str_repeat('10 chars !', 950).'the end of the description';
      $this->insertProject($config, false);
    }
    $this->getManager()->flush();
  }

  /**
   * @Given /^I have a project "([^"]*)" with id "([^"]*)"$/
   *
   * @throws \Exception
   */
  public function iHaveAProjectWithId(string $name, string $id): void
  {
    $config = [
      'id' => $id,
      'name' => $name,
    ];
    $this->insertProject($config);
    $this->getFileRepository()->saveProjectZipFile(
      new File($this->FIXTURES_DIR.'test.catrobat'), $id
    );
  }

  /**
   * @Given /^project "([^"]*)" is not visible$/
   */
  public function projectIsNotVisible(string $project_name): void
  {
    $project = $this->getProjectManager()->findOneByName($project_name);
    Assert::assertNotNull($project, 'There is no project named '.$project_name);
    $project->setVisible(false);
    $this->getManager()->persist($project);
    $this->getManager()->flush();
  }

  /**
   * @Then /^there should be "([^"]*)" projects in the database$/
   */
  public function thereShouldBeProjectsInTheDatabase(string $number_of_projects): void
  {
    $projects = $this->getProjectManager()->findAll();
    Assert::assertCount((int) $number_of_projects, $projects);
  }

  /**
   * @Then /^the project should not be tagged$/
   */
  public function theProjectShouldNotBeTagged(): void
  {
    $project_tags = $this->getProjectManager()->findAll()[0]->getTags();
    Assert::assertEmpty($project_tags, 'The project is tagged but should not be tagged');
  }

  /**
   * @Then /^the project should be tagged with "([^"]*)" in the database$/
   */
  public function theProjectShouldBeTaggedWithInTheDatabase(string $tags_as_string): void
  {
    $project_tags = $this->getProjectManager()->findAll()[0]->getTags() ?? [];
    $tags = explode(',', $tags_as_string);
    Assert::assertEquals(count($tags), is_countable($project_tags) ? count($project_tags) : 0, 'Too much or too less tags found!');

    foreach ($project_tags as $project_tag) {
      /* @var Tag $project_tag */
      Assert::assertTrue(
        in_array($project_tag->getInternalTitle(), $tags, true), 'The tag is not found!'
      );
    }
  }

  /**
   * @Then the project should have no extension
   */
  public function theProjectShouldHaveNoExtension(): void
  {
    /** @var Program $project */
    $project = $this->getProjectManager()->findAll()[0];
    Assert::assertTrue($project->getExtensions()->isEmpty());
  }

  /**
   * @Then the project :id should have :downloads downloads
   */
  public function theProjectShouldHaveDownloads(string $id, string $downloads): void
  {
    /** @var Program $project */
    $project = $this->getProjectManager()->find($id);
    $this->getManager()->refresh($project);
    Assert::assertEquals($downloads, $project->getDownloads());
  }

  /**
   * @Then the project with name :name should have :number_of_tags tags
   */
  public function theProjectWithNameShouldHaveTags(string $name, int $number_of_tags): void
  {
    $project = $this->getProjectManager()->findOneByName($name);
    Assert::assertCount($number_of_tags, $project->getTags());
  }

  /**
   * @Then the project with name :name should have :number_of_extensions extensions
   */
  public function theProjectWithNameShouldHaveExtensions(string $name, int $number_of_extensions): void
  {
    $project = $this->getProjectManager()->findOneByName($name);
    Assert::assertCount($number_of_extensions, $project->getExtensions());
  }

  /**
   * @Then the embroidery project should have the :extension file extension
   */
  public function theEmbroideryProjectShouldHaveTheExtension(string $extension): void
  {
    $project_extensions = $this->getProjectManager()->findOneByName('ZigZag Stich')->getExtensions();

    Assert::assertNotNull($project_extensions);

    foreach ($project_extensions as $project_extension) {
      /* @var $project_extension Extension */
      Assert::assertStringContainsString($project_extension->getInternalTitle(), $extension, 'The Extension was not found!');
    }
  }

  /**
   * @Then the project with id :id should be marked with :arg2 extensions in the database
   */
  public function theProjectShouldBeMarkedWithExtensionsInTheDatabase(string $id, string $count): void
  {
    $project = $this->getProjectManager()->find($id);
    $this->getManager()->refresh($project);
    $project_extensions = $project->getExtensions();
    Assert::assertNotNull($project_extensions);
    Assert::assertCount((int) $count, $project_extensions, 'Too much or too less extensions found!');
  }

  /**
   * @Then /^the project should be flagged as phiro$/
   */
  public function theProjectShouldBeFlaggedAsPhiroPro(): void
  {
    $project_manager = $this->getProjectManager();
    $project = $project_manager->find('1');
    Assert::assertNotNull($project, 'No project added');
    Assert::assertEquals(Flavor::PHIROCODE, $project->getFlavor(), 'Project is NOT flagged as phiro');
  }

  /**
   * @Then /^the project should not be flagged as phiro$/
   */
  public function theProjectShouldNotBeFlaggedAsPhiroPro(): void
  {
    $project_manager = $this->getProjectManager();
    $project = $project_manager->find('1');
    Assert::assertNotNull($project, 'No project added');
    Assert::assertNotEquals(Flavor::PHIROCODE, $project->getFlavor(), 'Project is flagged as a phiro');
  }

  /**
   * @Given /^I have a project "([^"]*)" with id "([^"]*)" and a vibrator brick$/
   *
   * @throws \Exception
   */
  public function iHaveAProjectWithIdAndAVibratorBrick(string $name, string $id): void
  {
    MyUuidGenerator::setNextValue($id);
    $config = [
      'name' => $name,
    ];
    $project = $this->insertProject($config);

    $this->getFileRepository()->saveProjectZipFile(
      new File($this->FIXTURES_DIR.'GeneratedFixtures/phiro.catrobat'), $project->getId()
    );
  }

  // -------------------------------------------------------------------------------------------------------------------
  //  Comments
  // -------------------------------------------------------------------------------------------------------------------

  /**
   * @Given /^there are comments:$/
   *
   * @throws \Exception
   */
  public function thereAreComments(TableNode $table): void
  {
    foreach ($table->getHash() as $config) {
      $this->insertUserComment($config, false);
    }
    $this->getManager()->flush();
  }

  // -------------------------------------------------------------------------------------------------------------------
  //  Reports
  // -------------------------------------------------------------------------------------------------------------------

  /**
   * @Given /^there are inappropriate reports:$/
   *
   * @throws \Exception
   */
  public function thereAreInappropriateReports(TableNode $table): void
  {
    foreach ($table->getHash() as $config) {
      $this->insertProjectReport($config, false);
    }
    $this->getManager()->flush();
  }

  // -------------------------------------------------------------------------------------------------------------------
  //  Notifications
  // -------------------------------------------------------------------------------------------------------------------
  /**
   * @Given /^there is a notification that "([^"]*)" follows "([^"]*)"$/
   */
  public function thereAreFollowNotifications(string $username, string $username_to_follow): void
  {
    /** @var User $user_to_follow */
    $user_to_follow = $this->getUserManager()->findUserByUsername($username_to_follow);

    /** @var User|null $user */
    $user = $this->getUserManager()->findUserByUsername($username);

    Assert::assertNotNull($user, 'user is null');

    $notification = new FollowNotification($user_to_follow, $user);

    $this->getManager()->persist($notification);
    $this->getManager()->flush();
  }

  // -------------------------------------------------------------------------------------------------------------------
  //  Statistics
  // -------------------------------------------------------------------------------------------------------------------

  /**
   * @Given /^there are media packages:$/
   */
  public function thereAreMediaPackages(TableNode $table): void
  {
    $em = $this->getManager();
    $packages = $table->getHash();
    foreach ($packages as $package) {
      $new_package = new MediaPackage();
      $new_package->setName($package['name']);
      $new_package->setNameUrl($package['name_url']);
      $em->persist($new_package);
    }
    $em->flush();
  }

  /**
   * @Given /^there are media package categories:$/
   */
  public function thereAreMediaPackageCategories(TableNode $table): void
  {
    $em = $this->getManager();
    $categories = $table->getHash();
    foreach ($categories as $category) {
      $new_category = new MediaPackageCategory();
      $new_category->setName($category['name']);
      if (!empty($category['priority'])) {
        $new_category->setPriority((int) $category['priority']);
      }

      /** @var MediaPackage|null $package */
      $package = $em->getRepository(MediaPackage::class)->findOneBy(['name' => $category['package']]);
      Assert::assertNotNull($package, 'Fatal error package not found');

      $new_category->setPackage(new ArrayCollection([$package]));
      $current_categories = $package->getCategories();
      $current_categories->add($new_category);
      $package->setCategories($current_categories);
      $em->persist($new_category);
    }
    $em->flush();
  }

  /**
   * @Given /^there are media package files:$/
   *
   * @throws \ImagickException
   */
  public function thereAreMediaPackageFiles(TableNode $table): void
  {
    $em = $this->getManager();
    $file_repo = $this->getMediaPackageFileRepository();
    $flavor_repo = $this->getFlavorRepository();
    $files = $table->getHash();
    foreach ($files as $file) {
      $new_file = new MediaPackageFile();
      $new_file->setName($file['name']);
      $new_file->setDownloads(0);
      $new_file->setExtension($file['extension']);
      $new_file->setActive((bool) $file['active']);

      /** @var MediaPackageCategory|null $category */
      $category = $em->getRepository(MediaPackageCategory::class)->findOneBy(['name' => $file['category']]);
      Assert::assertNotNull($category, 'Fatal error category not found');
      $new_file->setCategory($category);
      $old_files = $category->getFiles();
      $old_files->add($new_file);
      $category->setFiles(new ArrayCollection($old_files->toArray()));
      if (!empty($file['flavors'])) {
        foreach (explode(',', (string) $file['flavors']) as $flavor) {
          $new_file->addFlavor($flavor_repo->getFlavorByName(trim($flavor)));
        }
      }
      $new_file->setAuthor($file['author']);

      $file_repo->saveFile(
        new File($this->MEDIA_PACKAGE_DIR.$file['id'].'.'.$file['extension']),
        (int) $file['id'],
        $file['extension']
      );

      $em->persist($new_file);

      /** @var MediaPackage $mediaPackage */
      $mediaPackage = $category->getPackage()->getValues()[0];
      $this->media_files[] = [
        'id' => $file['id'],
        'name' => $file['name'],
        'flavor' => $new_file->getFlavor(),
        'flavors' => $new_file->getFlavorNames(),
        'package' => $mediaPackage->getName(),
        'category' => $file['category'],
        'author' => $file['author'],
        'extension' => $file['extension'],
        'download_url' => 'http://localhost/app/download-media/'.$file['id'],
      ];
    }
    $em->flush();
  }

  /**
   * @Given /^there are flavors:$/
   */
  public function thereAreFlavors(TableNode $table): void
  {
    foreach ($table->getHash() as $config) {
      $this->insertFlavor($config, false);
    }
    $this->getManager()->flush();
  }

  /**
   * @Given /^there are like similar users:$/
   */
  public function thereAreLikeSimilarUsers(TableNode $table): void
  {
    foreach ($table->getHash() as $config) {
      $this->insertUserLikeSimilarity($config, false);
    }
    $this->getManager()->flush();
  }

  /**
   * @Given /^there are remix similar users:$/
   */
  public function thereAreRemixSimilarUsers(TableNode $table): void
  {
    foreach ($table->getHash() as $config) {
      $this->insertUserRemixSimilarity($config, false);
    }
    $this->getManager()->flush();
  }

  /**
   * @Given /^there are likes:$/
   *
   * @throws \Exception
   */
  public function thereAreLikes(TableNode $table): void
  {
    foreach ($table->getHash() as $config) {
      $this->insertProjectLike($config, false);
    }
    $this->getManager()->flush();
  }

  /**
   * @Given /^there are tags:$/
   */
  public function thereAreTags(TableNode $table): void
  {
    foreach ($table->getHash() as $config) {
      $this->insertTag($config, false);
    }
    $this->getManager()->flush();
  }

  /**
   * @Given /^there are extensions:$/
   */
  public function thereAreExtensions(TableNode $table): void
  {
    foreach ($table->getHash() as $config) {
      $this->insertExtension($config, false);
    }
    $this->getManager()->flush();
  }

  /**
   * @Given /^there are forward remix relations:$/
   */
  public function thereAreForwardRemixRelations(TableNode $table): void
  {
    foreach ($table->getHash() as $config) {
      $this->insertForwardRemixRelation($config, false);
    }
    $this->getManager()->flush();
  }

  /**
   * @Given /^there are backward remix relations:$/
   */
  public function thereAreBackwardRemixRelations(TableNode $table): void
  {
    foreach ($table->getHash() as $config) {
      $this->insertBackwardRemixRelation($config, false);
    }
    $this->getManager()->flush();
  }

  /**
   * @Given /^there are Scratch remix relations:$/
   */
  public function thereAreScratchRemixRelations(TableNode $table): void
  {
    foreach ($table->getHash() as $config) {
      $this->insertScratchRemixRelation($config, false);
    }
    $this->getManager()->flush();
  }

  /**
   * @Given /^there are project reactions:$/
   *
   * @throws \Exception
   */
  public function thereAreProjectReactions(TableNode $table): void
  {
    $em = $this->getManager();

    foreach ($table->getHash() as $data) {
      $project = $this->getProjectManager()->find($data['project']);
      if (null === $project) {
        throw new \Exception('Project with id '.$data['project'].' does not exist.');
      }

      /** @var User|null $user */
      $user = $this->getUserManager()->findUserByUsername($data['user']);
      if (null === $user) {
        throw new \Exception('User with username '.$data['user'].' does not exist.');
      }

      $type = $data['type'];
      if (ctype_digit((string) $type)) {
        $type = (int) $type;
      } else {
        $type = array_search($type, ProgramLike::$TYPE_NAMES, true);
        if (false === $type) {
          throw new \Exception('Unknown type "'.$data['type'].'" given.');
        }
      }
      if (!ProgramLike::isValidType($type)) {
        throw new \Exception('Unknown type "'.$data['type'].'" given.');
      }

      $like = new ProgramLike($project, $user, $type);

      if (array_key_exists('created at', $data) && !empty(trim((string) $data['created at']))) {
        $like->setCreatedAt(new \DateTime($data['created at'], new \DateTimeZone('UTC')));
      }

      $em->persist($like);
    }
    $em->flush();
  }

  /**
   * @Given /^there are catro notifications:$/
   */
  public function thereAreCatroNotifications(TableNode $table): void
  {
    $em = $this->getManager();
    $notifications = $table->getHash();

    foreach ($notifications as $notification) {
      /** @var User|null $user */
      $user = $this->getUserManager()->findUserByUsername($notification['user']);

      Assert::assertNotNull($user, 'user is null');

      switch ($notification['type']) {
        case 'comment':
          /** @var UserComment $comment */
          $comment = $em->getRepository(UserComment::class)->find($notification['commentID']);
          $to_create = new CommentNotification($user, $comment);
          break;
        case 'follower':
          /** @var User $follower */
          $follower = $this->getUserManager()->find($notification['follower_id']);
          $to_create = new FollowNotification($user, $follower);
          break;
        case 'like':
          /** @var User $liker */
          $liker = $this->getUserManager()->find($notification['like_from']);

          $project = $this->getProjectManager()->find($notification['project_id']);
          $to_create = new LikeNotification($user, $liker, $project);
          break;
        case 'follow_project':
          $project = $this->getProjectManager()->find($notification['project_id']);
          $to_create = new NewProgramNotification($user, $project);
          break;
        case 'broadcast':
          $to_create = new BroadcastNotification($user, 'title_deprecated', $notification['message']);
          break;
        case 'remix':
          /** @var Program $parent_project */
          $parent_project = $this->getProjectManager()->find($notification['parent_project']);
          /** @var Program $child_project */
          $child_project = $this->getProjectManager()->find($notification['child_project']);
          $to_create = new RemixNotification($user, $parent_project->getUser(), $parent_project, $child_project);
          break;
        default:
          $to_create = new CatroNotification($user, $notification['title'], $notification['message']);
          break;
      }

      // Some specific id desired?
      if (isset($notification['id'])) {
        $to_create->setId((int) $notification['id']);
      }
      if (isset($notification['seen'])) {
        $to_create->setSeen((bool) $notification['seen']);
      }

      $em->persist($to_create);
      $em->flush();
    }
  }

  /**
   * @Given /^the following catro notifications exist in the database:$/
   */
  public function followingCatroNotificationsExist(TableNode $table): void
  {
    $em = $this->getManager();
    $notifications = $table->getHash();

    foreach ($notifications as $notification) {
      $notification_found = $em->getRepository(CatroNotification::class)->find($notification['id']);
      Assert::assertNotNull($notification_found);
      if (isset($notification['seen'])) {
        Assert::assertEquals($notification['seen'], $notification_found->getSeen(), 'seen wrong'.$notification['seen'].'expected, but '.$notification_found->getSeen().'found');
      }
    }
  }

  /**
   * @Given /^there are "([^"]*)"\+ notifications for "([^"]*)"$/
   */
  public function thereAreNotificationsFor(string $count, string $username): void
  {
    $em = $this->getManager();

    /** @var User|null $user */
    $user = $this->getUserManager()->findUserByUsername($username);
    Assert::assertNotNull($user, 'user is null');

    for ($i = 0; $i < (int) $count; ++$i) {
      $to_create = new CatroNotification($user, 'Random Title', 'Random Text');
      $em->persist($to_create);
    }
    $em->flush();
  }

  /**
   * @Given /^there are "([^"]*)" "([^"]*)" notifications for project "([^"]*)" from "([^"]*)"$/
   */
  public function thereAreSpecificNotificationsFor(string $amount, string $type, string $project_name, string $username): void
  {
    $em = $this->getManager();

    /** @var User|null $user */
    $user = $this->getUserManager()->findUserByUsername($username);

    $project = $this->getProjectManager()->findOneByName($project_name);

    Assert::assertNotNull($user, 'user is null');

    for ($i = 0; $i < $amount; ++$i) {
      switch ($type) {
        case 'comment':
          $temp_comment = new UserComment();
          $temp_comment->setUsername($user->getUserIdentifier());
          $temp_comment->setUser($user);
          $temp_comment->setText('This is a comment');
          $temp_comment->setProgram($project);
          $temp_comment->setUploadDate(date_create());
          $temp_comment->setIsReported(false);
          $em->persist($temp_comment);
          $to_create = new CommentNotification($project->getUser(), $temp_comment);
          $em->persist($to_create);
          break;

        case 'like':
          $to_create = new LikeNotification($project->getUser(), $user, $project);
          $em->persist($to_create);
          break;
        case 'remix':
          $to_create = new RemixNotification($project->getUser(), $user, $project, $project);
          $em->persist($to_create);
          break;
        case 'catro notifications':
          $to_create = new CatroNotification($user, 'Random Title', 'Random Text');
          $em->persist($to_create);
          break;
        default:
          throw new \InvalidArgumentException('Unknown type "'.$type.'" given.');
      }
    }
    $em->flush();
  }

  /**
   * @Given /^the users are created at:$/
   */
  public function theUsersAreCreatedAt(TableNode $table): void
  {
    foreach ($table->getHash() as $config) {
      $this->getUserDataFixtures()->overwriteCreatedAt($config);
    }
    $this->getManager()->flush();
  }

  /**
   * @Then /^there should be "([^"]*)" users in the database$/
   */
  public function thereShouldBeUsersInTheDatabase(string $number_of_users): void
  {
    $users = $this->getUserManager()->findAll();
    Assert::assertCount((int) $number_of_users, $users);
  }

  /**
   * @Given /^there are studios:$/
   */
  public function thereAreStudios(TableNode $table): void
  {
    foreach ($table->getHash() as $config) {
      if (array_key_exists('id', $config)) {
        MyUuidGenerator::setNextValue($config['id']);
      }

      $isPublic = filter_var($config['is_public'] ?? true, FILTER_VALIDATE_BOOLEAN);
      $allowComments = filter_var($config['allow_comments'] ?? true, FILTER_VALIDATE_BOOLEAN);
      $isEnabled = filter_var($config['is_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN);

      $studio = (new Studio())
        ->setName($config['name'])
        ->setDescription($config['description'] ?? '')
        ->setAllowComments($allowComments)
        ->setIsPublic($isPublic)
        ->setIsEnabled($isEnabled)
        ->setCreatedOn(isset($config['created_on']) ?
            new \DateTime($config['created_on'], new \DateTimeZone('UTC')) :
            new \DateTime('01.01.2013 12:00', new \DateTimeZone('UTC'))
        )
      ;
      $this->getManager()->persist($studio);
    }

    $this->getManager()->flush();
  }

  /**
   * @Given /^there are studio users:$/
   */
  public function thereAreStudioUsers(TableNode $table): void
  {
    foreach ($table->getHash() as $config) {
      if (array_key_exists('id', $config)) {
        MyUuidGenerator::setNextValue($config['id']);
      }

      $studio = $this->getStudioManager()->findStudioById($config['studio_id']);
      /** @var User|null $user */
      $user = $this->getUserManager()->findUserByUsername($config['user']);

      $activity = (new StudioActivity())
        ->setUser($user)
        ->setStudio($studio)
        ->setType('user')
        ->setCreatedOn(isset($config['created_on']) ?
          new \DateTime($config['created_on'], new \DateTimeZone('UTC')) :
          new \DateTime('01.01.2013 12:00', new \DateTimeZone('UTC'))
        )
      ;

      $this->getManager()->persist($activity);

      $studio_user = (new StudioUser())
        ->setUser($user)
        ->setStudio($studio)
        ->setRole($config['role'] ?? 'member')
        ->setActivity($activity)
        ->setStatus($config['status'] ?? 'active')
        ->setCreatedOn(isset($config['created_on']) ?
          new \DateTime($config['created_on'], new \DateTimeZone('UTC')) :
          new \DateTime('01.01.2013 12:00', new \DateTimeZone('UTC'))
        )
      ;

      $this->getManager()->persist($studio_user);
    }
    $this->getManager()->flush();
  }

  /**
   * @Given /^there are studio projects:$/
   */
  public function thereAreStudioProjects(TableNode $table): void
  {
    foreach ($table->getHash() as $config) {
      if (array_key_exists('id', $config)) {
        MyUuidGenerator::setNextValue($config['id']);
      }

      $studio = $this->getStudioManager()->findStudioById($config['studio_id']);
      $project = $this->getProjectManager()->findOneByName($config['project']);
      /** @var User|null $user */
      $user = $this->getUserManager()->findUserByUsername($config['user']);

      $studio_project = $this->getStudioManager()->addProjectToStudio($user, $studio, $project);
      $this->getManager()->persist($studio_project);
    }
    $this->getManager()->flush();
  }

  /**
   * @Given /^there are studio comments:$/
   */
  public function thereAreStudioComments(TableNode $table): void
  {
    foreach ($table->getHash() as $config) {
      if (array_key_exists('id', $config)) {
        MyUuidGenerator::setNextValue($config['id']);
      }

      $studio = $this->getStudioManager()->findStudioById($config['studio_id']);
      /** @var User|null $user */
      $user = $this->getUserManager()->findUserByUsername($config['user']);

      $studio_comment = $this->getStudioManager()->addCommentToStudio($user, $studio, $config['comment']);
      $this->getManager()->persist($studio_comment);
    }
    $this->getManager()->flush();
  }

  /**
   * @Given /^there are achievements:$/
   */
  public function thereAreAchievements(TableNode $table): void
  {
    foreach ($table->getHash() as $config) {
      $achievement = (new Achievement())
        ->setInternalTitle($config['internal_title'])
        ->setInternalDescription($config['internal_description'] ?? '')
        ->setTitleLtmCode($config['title_ltm_code'] ?? '')
        ->setDescriptionLtmCode($config['description_ltm_code'] ?? '')
        ->setBadgeSvgPath($config['badge_svg_path'] ?? UpdateAchievementsCommand::ACHIEVEMENT_IMAGE_ASSETS_PATH.'achievement_badge_1.svg')
        ->setBadgeLockedSvgPath($config['badge_locked_svg_path'] ?? UpdateAchievementsCommand::ACHIEVEMENT_IMAGE_ASSETS_PATH.'achievement_badge_locked_1.svg')
        ->setBannerSvgPath($config['banner_svg_path'] ?? UpdateAchievementsCommand::ACHIEVEMENT_IMAGE_ASSETS_PATH.'achievement_banner.svg')
        ->setBannerColor($config['banner_color'] ?? '#00ff00')
        ->setEnabled((bool) ($config['enabled'] ?? true))
        ->setPriority((int) ($config['priority'] ?? 0))
      ;
      $this->getManager()->persist($achievement);
    }
    $this->getManager()->flush();
  }

  /**
   * @Given /^there are user achievements:$/
   *
   * @throws \Exception
   */
  public function thereAreUserAchievements(TableNode $table): void
  {
    foreach ($table->getHash() as $config) {
      /** @var User|null $user */
      $user = $this->getUserManager()->findUserByUsername($config['user']);
      $achievement = $this->getAchievementManager()->findAchievementByInternalTitle($config['achievement']);
      $user_achievement = (new UserAchievement())
        ->setUser($user)
        ->setAchievement($achievement)
        ->setSeenAt(!empty($config['seen_at']) ? new \DateTime($config['seen_at']) : null)
        ->setUnlockedAt(!empty($config['unlocked_at']) ? new \DateTime($config['unlocked_at']) : new \DateTime('now'))
      ;
      $this->getManager()->persist($user_achievement);
    }
    $this->getManager()->flush();
  }

  /**
   * @Then there should be :number_of_achievements achievements in the database
   */
  public function thereShouldBeAchievementsInTheDatabase(int $number_of_achievements): void
  {
    $achievements = $this->getAchievementManager()->findAllAchievements();
    Assert::assertCount($number_of_achievements, $achievements);
  }

  /**
   * @Then there should be :number_of_tags tags in the database
   */
  public function thereShouldBeTagsInTheDatabase(int $number_of_tags): void
  {
    $tags = $this->getTagRepository()->findAll();
    Assert::assertCount($number_of_tags, $tags);
  }

  /**
   * @Then there should be :number_of_extensions extensions in the database
   */
  public function thereShouldBeExtensionsInTheDatabase(int $number_of_extensions): void
  {
    $tags = $this->getExtensionRepository()->findAll();
    Assert::assertCount($number_of_extensions, $tags);
  }

  /**
   * @Then there should be :number_of_flavors flavors in the database
   */
  public function thereShouldBeFlavorsInTheDatabase(int $number_of_flavors): void
  {
    $tags = $this->getFlavorRepository()->findAll();
    Assert::assertCount($number_of_flavors, $tags);
  }

  /**
   * @Then there should be :number_of_user_achievements user achievements in the database
   */
  public function thereShouldBeUserAchievementsInTheDatabase(int $number_of_user_achievements): void
  {
    $user_achievements = $this->getAchievementManager()->findAllUserAchievements();
    Assert::assertCount($number_of_user_achievements, $user_achievements);
  }

  /**
   * @Then there should be :number_of_cron_jobs cron jobs in the database
   */
  public function thereShouldBeCronJobsInTheDatabase(int $number_of_cron_jobs): void
  {
    $cron_jobs = $this->getCronJobRepository()->findAll();
    Assert::assertCount($number_of_cron_jobs, $cron_jobs);
  }

  /**
   * @Given I run the update achievements command
   */
  public function iRunTheUpdateAchievementsCommand(): void
  {
    CommandHelper::executeShellCommand(
      ['bin/console', 'catrobat:update:achievements'], [], 'Creating Achievements'
    );
  }

  /**
   * @Given I run the update tags command
   */
  public function iRunTheUpdateTagsCommand(): void
  {
    CommandHelper::executeShellCommand(
      ['bin/console', 'catrobat:update:tags'], [], 'Creating Tags'
    );
  }

  /**
   * @Given I run the update extensions command
   */
  public function iRunTheUpdateExtensionsCommand(): void
  {
    CommandHelper::executeShellCommand(
      ['bin/console', 'catrobat:update:extensions'], [], 'Creating Extensions'
    );
  }

  /**
   * @Given I run the update flavors command
   */
  public function iRunTheUpdateFlavorsCommand(): void
  {
    CommandHelper::executeShellCommand(
      ['bin/console', 'catrobat:update:flavors'], [], 'Creating Flavors'
    );
  }

  /**
   * @Given I run the special update command
   */
  public function iRunTheSpecialUpdateCommand(): void
  {
    CommandHelper::executeShellCommand(
      ['bin/console', 'catrobat:update:special'], [], 'Updating database'
    );
  }

  /**
   * @Given I run the cron job command
   */
  public function iRunTheCronJobsCommand(): void
  {
    CommandHelper::executeShellCommand(
      ['bin/console', 'catrobat:cronjob'], [], 'Updating user achievements'
    );
  }

  /**
   * @Given I run the add bronze_user user achievements command
   */
  public function iRunTheAddBronzeUserAchievementsCommand(): void
  {
    CommandHelper::executeShellCommand(
      ['bin/console', 'catrobat:workflow:achievement:bronze_user'], [], 'Updating user achievements'
    );
  }

  /**
   * @Given I run the add silver_user user achievements command
   */
  public function iRunTheAddSilverUserAchievementsCommand(): void
  {
    CommandHelper::executeShellCommand(
      ['bin/console', 'catrobat:workflow:achievement:silver_user'], [], 'Updating user achievements'
    );
  }

  /**
   * @Given I run the add gold_user user achievements command
   */
  public function iRunTheAddGoldUserAchievementsCommand(): void
  {
    CommandHelper::executeShellCommand(
      ['bin/console', 'catrobat:workflow:achievement:gold_user'], [], 'Updating user achievements'
    );
  }

  /**
   * @Given I run the add diamond_user user achievements command
   */
  public function iRunTheAddDiamondUserAchievementsCommand(): void
  {
    CommandHelper::executeShellCommand(
      ['bin/console', 'catrobat:workflow:achievement:diamond_user'], [], 'Updating user achievements'
    );
  }

  /**
   * @Given I run the add verified_developer user achievements command
   */
  public function iRunTheAddVerifiedDeveloperAchievementsCommand(): void
  {
    CommandHelper::executeShellCommand(
      ['bin/console', 'catrobat:workflow:achievement:verified_developer'], [], 'Updating user achievements'
    );
  }

  /**
   * @Given I run the add perfect_profile user achievements command
   */
  public function iRunTheAddPerfectProfileAchievementsCommand(): void
  {
    CommandHelper::executeShellCommand(
      ['bin/console', 'catrobat:workflow:achievement:perfect_profile'], [], 'Updating user achievements'
    );
  }

  /**
   * @Given I run the add translation user achievements command
   */
  public function iRunTheAddTranslationAchievementsCommand(): void
  {
    CommandHelper::executeShellCommand(
      ['bin/console', 'catrobat:workflow:achievement:translation'], []
    );
  }

  /**
   * @Given I run the refresh project extensions command
   */
  public function iRunTheRefreshProjectExtensionsCommand(): void
  {
    CommandHelper::executeShellCommand(
      ['bin/console', 'catrobat:workflow:project:refresh_extensions'], [], 'Refreshing project extensions'
    );
  }

  /**
   * @Given there are project machine translations:
   */
  public function thereAreProjectMachineTranslations(TableNode $table): void
  {
    foreach ($table->getHash() as $config) {
      $project = $this->getProjectManager()->find($config['project_id']);
      $source_language = $config['source_language'];
      $target_language = $config['target_language'];
      $provider = $config['provider'];
      $usage_count = (int) $config['usage_count'];
      $cached_name = $config['cached_name'] ?? null;
      $cached_description = $config['cached_description'] ?? null;
      $cached_credits = $config['cached_credits'] ?? null;

      $project_machine_translation = new ProjectMachineTranslation($project, $source_language, $target_language, $provider, $usage_count);
      $project_machine_translation->setCachedTranslation($cached_name, $cached_description, $cached_credits);
      $this->getManager()->persist($project_machine_translation);
    }
    $this->getManager()->flush();
  }

  /**
   * @Then there should be project machine translations:
   */
  public function thereShouldBeProjectMachineTranslations(TableNode $table): void
  {
    $project_machine_translations = $this->getManager()->getRepository(ProjectMachineTranslation::class)->findAll();
    // The returned entities may not be up to date
    foreach ($project_machine_translations as $translation) {
      $this->getManager()->refresh($translation);
    }
    $table_rows = $table->getHash();

    Assert::assertEquals(count($table_rows), count($project_machine_translations), 'table has different number of rows');

    foreach ($project_machine_translations as $translation) {
      /** @var Program $project */
      $project = $translation->getProject();
      $project_id = $project->getId();
      $source_language = $translation->getSourceLanguage();
      $target_language = $translation->getTargetLanguage();
      $provider = $translation->getProvider();
      $usage_count = $translation->getUsageCount();
      $cached_name = $translation->getCachedName();
      $cached_description = $translation->getCachedDescription();
      $cached_credits = $translation->getCachedCredits();

      $matching_row = array_filter($table_rows,
        fn ($row): bool => $project_id == $row['project_id']
          && $source_language == $row['source_language']
          && $target_language == $row['target_language']
          && $provider == $row['provider']
          && $usage_count == $row['usage_count']
          && $cached_name == ($row['cached_name'] ?? null)
          && $cached_description == ($row['cached_description'] ?? null)
          && $cached_credits == ($row['cached_credits'] ?? null));

      Assert::assertEquals(1, count($matching_row), "row not found: {$project_id}");
    }
  }

  /**
   * @Given there are comment machine translations:
   */
  public function thereAreCommentMachineTranslations(TableNode $table): void
  {
    $em = $this->getManager();
    $comment_repository = $em->getRepository(UserComment::class);

    foreach ($table->getHash() as $config) {
      $comment = $comment_repository->find($config['comment_id']);
      $source_language = $config['source_language'];
      $target_language = $config['target_language'];
      $provider = $config['provider'];
      $usage_count = (int) $config['usage_count'];

      $comment_machine_translation = new CommentMachineTranslation($comment, $source_language, $target_language, $provider, $usage_count);
      $this->getManager()->persist($comment_machine_translation);
    }
    $this->getManager()->flush();
  }

  /**
   * @Then there should be comment machine translations:
   */
  public function thereShouldBeCommentMachineTranslations(TableNode $table): void
  {
    $comment_machine_translations = $this->getManager()->getRepository(CommentMachineTranslation::class)->findAll();
    // The returned entities may not be up to date
    foreach ($comment_machine_translations as $translation) {
      $this->getManager()->refresh($translation);
    }
    $table_rows = $table->getHash();

    Assert::assertEquals(count($comment_machine_translations), count($table_rows), 'table has different number of rows');

    foreach ($comment_machine_translations as $translation) {
      $comment_id = $translation->getComment()->getId();
      $source_language = $translation->getSourceLanguage();
      $target_language = $translation->getTargetLanguage();
      $provider = $translation->getProvider();
      $usage_count = $translation->getUsageCount();

      $matching_row = array_filter($table_rows,
        fn ($row): bool => $comment_id == $row['comment_id']
          && $source_language == $row['source_language']
          && $target_language == $row['target_language']
          && $provider == $row['provider']
          && $usage_count == $row['usage_count']);

      Assert::assertEquals(1, count($matching_row), "row not found: {$comment_id}");
    }
  }

  /**
   * @Given there are project custom translations:
   */
  public function thereAreProjectCustomTranslations(TableNode $table): void
  {
    foreach ($table->getHash() as $config) {
      $project = $this->getProjectManager()->find($config['project_id']);
      $language = $config['language'];
      $name = '' === $config['name'] ? null : $config['name'];
      $description = '' === $config['description'] ? null : $config['description'];
      $credit = '' === $config['credit'] ? null : $config['credit'];

      $project_custom_translation = new ProjectCustomTranslation($project, $language);
      $project_custom_translation->setName($name);
      $project_custom_translation->setDescription($description);
      $project_custom_translation->setCredits($credit);
      $this->getManager()->persist($project_custom_translation);
    }
    $this->getManager()->flush();
  }

  /**
   * @Then there should be project custom translations:
   */
  public function thereShouldBeProjectCustomTranslations(TableNode $table): void
  {
    /** @var ProjectCustomTranslation[] $project_custom_translations */
    $project_custom_translations = $this->getManager()->getRepository(ProjectCustomTranslation::class)->findAll();
    // The returned entities may not be up to date
    foreach ($project_custom_translations as $translation) {
      $this->getManager()->refresh($translation);
    }
    $table_rows = $table->getHash();

    Assert::assertEquals(count($project_custom_translations), count($table_rows), 'table has different number of rows');

    foreach ($project_custom_translations as $translation) {
      $project = $translation->getProject();
      $project_id = $project->getId();
      $language = $translation->getLanguage();
      $name = $translation->getName();
      $description = $translation->getDescription();
      $credit = $translation->getCredits();

      $matching_row = array_filter($table_rows,
        fn ($row): bool => $project_id == $row['project_id']
          && $language == $row['language']
          && $name == $row['name']
          && $description == $row['description']
          && $credit == $row['credit']);

      Assert::assertEquals(1, count($matching_row), "row not found: {$project_id}");
    }
  }
}
