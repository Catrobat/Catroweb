<?php

namespace Tests\behat\context;

use App\Catrobat\Services\TestEnv\SymfonySupport;
use App\Commands\DBUpdater\UpdateAchievementsCommand;
use App\Commands\Helpers\CommandHelper;
use App\Entity\Achievements\Achievement;
use App\Entity\Achievements\UserAchievement;
use App\Entity\BroadcastNotification;
use App\Entity\CatroNotification;
use App\Entity\ClickStatistic;
use App\Entity\CommentNotification;
use App\Entity\Extension;
use App\Entity\FeaturedProgram;
use App\Entity\FollowNotification;
use App\Entity\HomepageClickStatistic;
use App\Entity\LikeNotification;
use App\Entity\MediaPackage;
use App\Entity\MediaPackageCategory;
use App\Entity\MediaPackageFile;
use App\Entity\NewProgramNotification;
use App\Entity\Program;
use App\Entity\ProgramDownloads;
use App\Entity\ProgramLike;
use App\Entity\RemixNotification;
use App\Entity\Survey;
use App\Entity\Tag;
use App\Entity\Translation\CommentMachineTranslation;
use App\Entity\Translation\ProjectMachineTranslation;
use App\Entity\User;
use App\Entity\UserComment;
use App\Utils\MyUuidGenerator;
use App\Utils\TimeUtils;
use Behat\Gherkin\Node\TableNode;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use DateTime;
use DateTimeZone;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use ImagickException;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Process\Process;

class DataFixturesContext implements KernelAwareContext
{
  use SymfonySupport;

  private array $programs = [];
  private array $featured_programs = [];
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
   *
   * @param mixed $id
   */
  public function theNextUuidValueWillBe($id): void
  {
    MyUuidGenerator::setNextValue($id);
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

      $followings = explode(', ', $config['following']);
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
   *
   * @param mixed $user_count
   */
  public function thereAreManyUsers($user_count): void
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
   *
   * @param mixed $arg1
   */
  public function theUserShouldNotExist($arg1): void
  {
    $user = $this->getUserManager()->findUserByUsername($arg1);
    Assert::assertNull($user);
  }

  /**
   * @Then /^the user "([^"]*)" with email "([^"]*)" should exist and be enabled$/
   *
   * @param mixed $arg2
   * @param mixed $arg1
   */
  public function theUserWithUsernameAndEmailShouldExistAndBeEnabled($arg1, $arg2): void
  {
    $em = $this->getManager();
    $user = $em->getRepository(User::class)->findOneBy([
      'username' => $arg1,
    ]);

    Assert::assertInstanceOf(User::class, $user);
    Assert::assertEquals($arg2, $user->getEmail());
    Assert::assertTrue($user->IsEnabled());
  }

  /**
   * @Given :number_of_users users follow:
   *
   * @param mixed $number_of_users
   */
  public function thereAreNUsersThatFollow($number_of_users, TableNode $table): void
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
   *
   * @param mixed $user_id
   * @param mixed $follow_ids
   */
  public function userIsFollowed($user_id, $follow_ids): void
  {
    /** @var User|null $user */
    $user = $this->getUserManager()->find($user_id);

    $ids = explode(',', $follow_ids);
    foreach ($ids as $id) {
      /** @var User|null $followUser */
      $followUser = $this->getUserManager()->find($id);
      $user->addFollowing($followUser);
      $this->getUserManager()->updateUser($user);
    }
  }

  /**
   * @When /^User "([^"]*)" is followed by user "([^"]*)"$/
   *
   * @param mixed $user_id
   * @param mixed $follow_id
   */
  public function userIsFollowedByUser($user_id, $follow_id): void
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

  /**
   * @Then /^user "([^"]*)" with country code "([^"]*)" should exist$/
   */
  public function userWithUsernameWithCountryShouldExist(string $username, string $country_code): void
  {
    $em = $this->getManager();

    /** @var User|null $user */
    $user = $em->getRepository(User::class)->findOneBy([
      'username' => $username,
    ]);

    Assert::assertInstanceOf(User::class, $user);
    Assert::assertEquals($country_code, $user->getCountry());
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
      $em->persist($survey);
    }
    $em->flush();
    $this->getManager()->flush();
  }

  // -------------------------------------------------------------------------------------------------------------------
  //  Projects
  // -------------------------------------------------------------------------------------------------------------------

  /**
   * @Given /^there are programs:$/
   * @Given /^there are projects:$/
   *
   * @throws Exception
   */
  public function thereArePrograms(TableNode $table): void
  {
    foreach ($table->getHash() as $config) {
      /** @var Program $program */
      $program = $this->insertProject($config, false);
      $this->programs[] = $program;
    }
    $this->getManager()->flush();
  }

  /**
   * @Given /^there are click statistics:$/
   *
   * @throws Exception
   */
  public function thereAreClickStatistics(TableNode $table): void
  {
    foreach ($table->getHash() as $config) {
      $this->insertClickStatistic($config, false);
    }
    $this->getManager()->flush();
  }

  /**
   * @Given /^there are "([^"]*)" similar projects$/
   *
   * @param mixed $num_of_projects
   *
   * @throws Exception
   */
  public function thereAreNumberOfSimilarProjects($num_of_projects): void
  {
    for ($project = 1; $project <= $num_of_projects; ++$project) {
      $program_info = ['name' => 'basic '.$project];
      $program = $this->insertProject($program_info, false);
      $this->programs[] = $program;
    }
    $this->getManager()->flush();
  }

  public function getPrograms(): array
  {
    return $this->programs;
  }

  public function getFeaturedPrograms(): array
  {
    return $this->featured_programs;
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
   * @Given /^there are downloadable programs:$/
   * @Given /^there are downloadable projects:$/
   *
   * @throws Exception
   */
  public function thereAreDownloadablePrograms(TableNode $table): void
  {
    $file_repo = $this->getFileRepository();
    foreach ($table->getHash() as $config) {
      $program = $this->insertProject($config, false);
      $file_repo->saveProjectZipFile(new File($this->FIXTURES_DIR.'test.catrobat'), $program->getId());
    }
    $this->getManager()->flush();
  }

  /**
   * @Given /^there are featured programs:$/
   * @Given /^there are featured projects:$/
   * @Given /^following programs are featured:$/
   * @Given /^following projects are featured:$/
   */
  public function thereAreFeaturedPrograms(TableNode $table): void
  {
    foreach ($table->getHash() as $config) {
      /** @var FeaturedProgram $program */
      $program = $this->insertFeaturedProject($config, false);
      $this->featured_programs[] = $program;
    }
    $this->getManager()->flush();
  }

  /**
   * @Given /^there are example programs:$/
   * @Given /^there are example projects:$/
   * @Given /^following programs are examples:$/
   * @Given /^following projects are examples:$/
   */
  public function thereAreExamplePrograms(TableNode $table): void
  {
    foreach ($table->getHash() as $config) {
      $this->insertExampleProject($config, false);
    }
    $this->getManager()->flush();
  }

  /**
   * @Given /^there are programs with a large description:$/
   * @Given /^there are projects with a large description:$/
   *
   * @throws Exception
   */
  public function thereAreProgramsWithALargeDescription(TableNode $table): void
  {
    foreach ($table->getHash() as $config) {
      $config['description'] = str_repeat('10 chars !', 950).'the end of the description';
      $this->insertProject($config, false);
    }
    $this->getManager()->flush();
  }

  /**
   * @Given /^I have a program "([^"]*)" with id "([^"]*)"$/
   *
   * @param mixed $name
   * @param mixed $id
   *
   * @throws Exception
   */
  public function iHaveAProgramWithId($name, $id): void
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
   * @Given /^program "([^"]*)" is not visible$/
   *
   * @param mixed $program_name
   */
  public function programIsNotVisible($program_name): void
  {
    $program = $this->getProgramManager()->findOneByName($program_name);
    Assert::assertNotNull($program, 'There is no program named '.$program_name);
    $program->setVisible(false);
    $this->getManager()->persist($program);
    $this->getManager()->flush();
  }

  /**
   * @Then /^there should be "([^"]*)" programs in the database$/
   *
   * @param mixed $number_of_projects
   */
  public function thereShouldBeProgramsInTheDatabase($number_of_projects): void
  {
    $programs = $this->getProgramManager()->findAll();
    Assert::assertCount($number_of_projects, $programs);
  }

  /**
   * @Then /^the program should not be tagged$/
   */
  public function theProgramShouldNotBeTagged(): void
  {
    $program_tags = $this->getProgramManager()->findAll()[0]->getTags();
    Assert::assertEmpty($program_tags, 'The program is tagged but should not be tagged');
  }

  /**
   * @Then /^the program should be tagged with "([^"]*)" in the database$/
   *
   * @param mixed $arg1
   */
  public function theProgramShouldBeTaggedWithInTheDatabase($arg1): void
  {
    $program_tags = $this->getProgramManager()->findAll()[0]->getTags();
    $tags = explode(',', $arg1);
    Assert::assertEquals(is_countable($program_tags) ? count($program_tags) : 0, count($tags), 'Too much or too less tags found!');

    foreach ($program_tags as $program_tag) {
      /* @var Tag $program_tag */
      Assert::assertTrue(
        in_array($program_tag->getInternalTitle(), $tags, true), 'The tag is not found!'
      );
    }
  }

  /**
   * @Then the project should have no extension
   */
  public function theProjectShouldHaveNoExtension(): void
  {
    /** @var Program $program */
    $program = $this->getProgramManager()->findAll()[0];
    Assert::assertNotNull($program->getExtensions());
  }

  /**
   * @Then the project with name :name should have :number_of_tags tags
   */
  public function theProjectWithNameShouldHaveTags(string $name, int $number_of_tags): void
  {
    $program = $this->getProgramManager()->findOneByName($name);
    Assert::assertCount($number_of_tags, $program->getTags());
  }

  /**
   * @Then the embroidery program should have the :extension extension
   *
   * @param mixed $extension
   */
  public function theEmbroideryProgramShouldHaveTheExtension($extension): void
  {
    $program_extensions = $this->getProgramManager()->findOneByName('ZigZag Stich')->getExtensions();

    Assert::assertNotNull($program_extensions);

    foreach ($program_extensions as $program_extension) {
      /* @var $program_extension Extension */
      Assert::assertStringContainsString($program_extension->getName(), $extension, 'The Extension was not found!');
    }
  }

  /**
   * @Then /^the program should be marked with extensions in the database$/
   */
  public function theProgramShouldBeMarkedWithExtensionsInTheDatabase(): void
  {
    $program_extensions = $this->getProgramManager()->findOneByName('extensions')->getExtensions();

    Assert::assertNotNull($program_extensions);

    Assert::assertCount(3, $program_extensions, 'Too much or too less tags found!');

    $ext = ['Arduino', 'Lego', 'Phiro'];
    foreach ($program_extensions as $program_extension) {
      /* @var Extension $program_extension */
      Assert::assertContains($program_extension->getName(), $ext, 'The Extension is not found!');
    }
  }

  /**
   * @Then the program with id :arg1 should be marked with no extensions in the database
   *
   * @param mixed $id
   */
  public function theProgramWithIdShouldBeMarkedWithNoExtensionsInTheDatabase($id): void
  {
    $program_extensions = $this->getProgramManager()->find($id)->getExtensions();

    Assert::assertNotNull($program_extensions);

    Assert::assertCount(0, $program_extensions, 'Too much or too less extensions found!');

    $ext = ['Arduino', 'Lego', 'Phiro'];
    foreach ($program_extensions as $program_extension) {
      /* @var Extension $program_extension */
      Assert::assertContains($program_extension->getName(), $ext, 'The extension is not found!');
    }
  }

  /**
   * @Then /^the program should be flagged as phiro$/
   */
  public function theProgramShouldBeFlaggedAsPhiroPro(): void
  {
    $program_manager = $this->getProgramManager();
    $program = $program_manager->find('1');
    Assert::assertNotNull($program, 'No program added');
    Assert::assertEquals('phirocode', $program->getFlavor(), 'Program is NOT flagged as phiro');
  }

  /**
   * @Then /^the program should not be flagged as phiro$/
   */
  public function theProgramShouldNotBeFlaggedAsPhiroPro(): void
  {
    $program_manager = $this->getProgramManager();
    $program = $program_manager->find('1');
    Assert::assertNotNull($program, 'No program added');
    Assert::assertNotEquals('phirocode', $program->getFlavor(), 'Program is flagged as a phiro');
  }

  /**
   * @Given /^I have a program "([^"]*)" with id "([^"]*)" and a vibrator brick$/
   *
   * @param mixed $name
   * @param mixed $id
   *
   * @throws Exception
   */
  public function iHaveAProgramWithIdAndAVibratorBrick($name, $id): void
  {
    MyUuidGenerator::setNextValue($id);
    $config = [
      'name' => $name,
    ];
    $program = $this->insertProject($config);

    $this->getFileRepository()->saveProjectZipFile(
      new File($this->FIXTURES_DIR.'GeneratedFixtures/phiro.catrobat'), $program->getId()
    );
  }

  /**
   * @Then I enable snapshots for the project with id :id
   */
  public function iEnableSnapshotsForTheProjectWithId(string $id): void
  {
    $project = $this->getProgramManager()->find($id);
    $project->setSnapshotsEnabled(true);
    $this->getManager()->persist($project);
    $this->getManager()->flush();
  }

  /**
   * @Then I disable snapshots for the project with id :id
   */
  public function iDisableSnapshotsForTheProjectWithId(string $id): void
  {
    $project = $this->getProgramManager()->find($id);
    $project->setSnapshotsEnabled(false);
    $this->getManager()->persist($project);
    $this->getManager()->flush();
  }

  // -------------------------------------------------------------------------------------------------------------------
  //  Comments
  // -------------------------------------------------------------------------------------------------------------------

  /**
   * @Given /^there are comments:$/
   *
   * @throws Exception
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
   * @throws Exception
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
   * @Given /^there are notifications:$/
   */
  public function thereAreNotifications(TableNode $table): void
  {
    foreach ($table->getHash() as $config) {
      $this->insertNotification($config, false);
    }
    $this->getManager()->flush();
  }

  /**
   * @Given /^there is a notification that "([^"]*)" follows "([^"]*)"$/
   *
   * @param mixed $user
   * @param mixed $user_to_follow
   */
  public function thereAreFollowNotifications($user, $user_to_follow): void
  {
    /** @var User $user_to_follow */
    $user_to_follow = $this->getUserManager()->findUserByUsername($user_to_follow);

    /** @var User|null $user */
    $user = $this->getUserManager()->findUserByUsername($user);

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
        $new_category->setPriority($category['priority']);
      }

      /** @var MediaPackage|null $package */
      $package = $em->getRepository(MediaPackage::class)->findOneBy(['name' => $category['package']]);
      Assert::assertNotNull($package, 'Fatal error package not found');

      $new_category->setPackage(new ArrayCollection([$package]));
      $current_categories = $package->getCategories();
      $current_categories = null == $current_categories ? [] : $current_categories;
      $current_categories->add($new_category);
      $package->setCategories($current_categories);
      $em->persist($new_category);
    }
    $em->flush();
  }

  /**
   * @Given /^there are media package files:$/
   *
   * @throws ImagickException
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
      $new_file->setActive($file['active']);

      /** @var MediaPackageCategory|null $category */
      $category = $em->getRepository(MediaPackageCategory::class)->findOneBy(['name' => $file['category']]);
      Assert::assertNotNull($category, 'Fatal error category not found');
      $new_file->setCategory($category);
      $old_files = $category->getFiles();
      $old_files = null == $old_files ? [] : $old_files;
      $old_files->add($new_file);
      $category->setFiles($old_files);
      if (!empty($file['flavors'])) {
        foreach (explode(',', $file['flavors']) as $flavor) {
          $new_file->addFlavor($flavor_repo->getFlavorByName(trim($flavor)));
        }
      }
      $new_file->setAuthor($file['author']);

      $file_repo->saveFile(new File($this->MEDIA_PACKAGE_DIR.$file['id'].'.'.
        $file['extension']), $file['id'], $file['extension']);

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

  // -------------------------------------------------------------------------------------------------------------------
  //  Statistics
  // -------------------------------------------------------------------------------------------------------------------

  /**
   * @Given /^there are program download statistics:$/
   *
   * @throws Exception
   */
  public function thereAreProgramDownloadStatistics(TableNode $table): void
  {
    foreach ($table->getHash() as $config) {
      $this->insertProgramDownloadStatistics($config, false);
    }
    $this->getManager()->flush();
  }

  /**
   * @Then /^There should be no recommended click statistic database entry$/
   */
  public function thereShouldBeNoRecommendedClickStatisticDatabaseEntry(): void
  {
    $clicks = $this->getManager()->getRepository(ClickStatistic::class)->findAll();
    Assert::assertEquals(0, count($clicks), 'Unexpected database entry found!');
  }

  /**
   * @Then /^There should be no homepage click statistic database entry$/
   */
  public function thereShouldBeNoHomepageClickStatisticDatabaseEntry(): void
  {
    $clicks = $this->getManager()->getRepository(HomepageClickStatistic::class)->findAll();
    Assert::assertEquals(0, count($clicks), 'Unexpected database entry found!');
  }

  /**
   * @Then /^There should be one homepage click database entry with type is "([^"]*)" and program id is "([^"]*)"$/
   *
   * @param mixed $type_name
   * @param mixed $id
   */
  public function thereShouldBeOneHomepageClickDatabaseEntryWithTypeIsAndIs($type_name, $id): void
  {
    $clicks = $this->getManager()->getRepository(HomepageClickStatistic::class)->findAll();
    Assert::assertEquals(1, count($clicks), 'No database entry found!');
    $click = $clicks[0];
    Assert::assertEquals($type_name, $click->getType());
    Assert::assertEquals($id, $click->getProgram()->getId());
  }

  /**
   * @Then the program download statistic should have a download timestamp, an anonymous user and the following statistics:
   *
   * @throws Exception
   */
  public function theProgramShouldHaveADownloadTimestampAndTheFollowingStatistics(TableNode $table): void
  {
    $statistics = $table->getHash();
    foreach ($statistics as $statistic) {
      $ip = $statistic['ip'];
      $country_code = $statistic['country_code'];
      if ('NULL' === $country_code) {
        $country_code = null;
      }
      $country_name = $statistic['country_name'];
      if ('NULL' === $country_name) {
        $country_name = null;
      }
      $program_id = $statistic['program_id'];
      $repository = $this->getManager()->getRepository(ProgramDownloads::class);
      $program_download_statistics = $repository->find(1);
      Assert::assertEquals($ip, $program_download_statistics->getIp(), 'Wrong IP in download statistics');
      Assert::assertEquals(
          $country_code, $program_download_statistics->getCountryCode(),
          'Wrong country code in download statistics'
        );
      Assert::assertEquals(
          $country_name, strtoupper($program_download_statistics->getCountryName()),
          'Wrong country name in download statistics'
        );
      /** @var Program $project */
      $project = $program_download_statistics->getProgram();
      Assert::assertEquals(
          $program_id, $project->getId(),
          'Wrong program ID in download statistics'
        );
      Assert::assertNull($program_download_statistics->getUser(), 'Wrong username in download statistics');
      Assert::assertNotEmpty(
          $program_download_statistics->getUserAgent(),
          'No user agent was written to download statistics'
        );
      $limit = 5.0;
      /** @var DateTime $download_time */
      $download_time = $program_download_statistics->getDownloadedAt();
      $current_time = TimeUtils::getDateTime();
      $time_delta = $current_time->getTimestamp() - $download_time->getTimestamp();
      Assert::assertTrue(
          $time_delta < $limit,
          'Download time difference in download statistics too high'
        );
    }
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
   * @throws Exception
   */
  public function thereAreLikes(TableNode $table): void
  {
    foreach ($table->getHash() as $config) {
      $this->insertProgramLike($config, false);
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
   * @throws Exception
   */
  public function thereAreProjectReactions(TableNode $table): void
  {
    $em = $this->getManager();

    foreach ($table->getHash() as $data) {
      $project = $this->getProgramManager()->find($data['project']);
      if (null === $project) {
        throw new Exception('Project with id '.$data['project'].' does not exist.');
      }

      /** @var User|null $user */
      $user = $this->getUserManager()->findUserByUsername($data['user']);
      if (null === $user) {
        throw new Exception('User with username '.$data['user'].' does not exist.');
      }

      $type = $data['type'];
      if (ctype_digit($type)) {
        $type = (int) $type;
      } else {
        $type = array_search($type, ProgramLike::$TYPE_NAMES, true);
        if (false === $type) {
          throw new Exception('Unknown type "'.$data['type'].'" given.');
        }
      }
      if (!ProgramLike::isValidType($type)) {
        throw new Exception('Unknown type "'.$data['type'].'" given.');
      }

      $like = new ProgramLike($project, $user, $type);

      if (array_key_exists('created at', $data) && !empty(trim($data['created at']))) {
        $like->setCreatedAt(new DateTime($data['created at'], new DateTimeZone('UTC')));
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

          $program = $this->getProgramManager()->find($notification['program_id']);
          $to_create = new LikeNotification($user, $liker, $program);
          break;
        case 'follow_program':
          $program = $this->getProgramManager()->find($notification['program_id']);
          $to_create = new NewProgramNotification($user, $program);
          break;
        case 'broadcast':
          $to_create = new BroadcastNotification($user, 'title_deprecated', $notification['message']);
          break;
        case 'remix':
          /** @var Program $parent_program */
          $parent_program = $this->getProgramManager()->find($notification['parent_program']);
          /** @var Program $child_program */
          $child_program = $this->getProgramManager()->find($notification['child_program']);
          $to_create = new RemixNotification($user, $parent_program->getUser(), $parent_program, $child_program);
          break;
        default:
          $to_create = new CatroNotification($user, $notification['title'], $notification['message']);
          break;
      }

      // Some specific id desired?
      if (isset($notification['id'])) {
        $to_create->setId($notification['id']);
      }

      $em->persist($to_create);
      $em->flush();
    }
  }

  /**
   * @Given /^there are "([^"]*)"\+ notifications for "([^"]*)"$/
   *
   * @param mixed $arg1
   * @param mixed $username
   */
  public function thereAreNotificationsFor($arg1, $username): void
  {
    $em = $this->getManager();

    /** @var User|null $user */
    $user = $this->getUserManager()->findUserByUsername($username);
    Assert::assertNotNull($user, 'user is null');

    for ($i = 0; $i < $arg1; ++$i) {
      $to_create = new CatroNotification($user, 'Random Title', 'Random Text');
      $em->persist($to_create);
    }
    $em->flush();
  }

  /**
   * @Given /^there are "([^"]*)" "([^"]*)" notifications for program "([^"]*)" from "([^"]*)"$/
   *
   * @param mixed $amount
   * @param mixed $type
   * @param mixed $program_name
   * @param mixed $user
   */
  public function thereAreSpecificNotificationsFor($amount, $type, $program_name, $user): void
  {
    $em = $this->getManager();

    /** @var User|null $user */
    $user = $this->getUserManager()->findUserByUsername($user);

    $program = $this->getProgramManager()->findOneByName($program_name);

    Assert::assertNotNull($user, 'user is null');

    for ($i = 0; $i < $amount; ++$i) {
      switch ($type) {
        case 'comment':
          $temp_comment = new UserComment();
          $temp_comment->setUsername($user->getUsername());
          $temp_comment->setUser($user);
          $temp_comment->setText('This is a comment');
          $temp_comment->setProgram($program);
          $temp_comment->setUploadDate(date_create());
          $temp_comment->setIsReported(false);
          $em->persist($temp_comment);
          $to_create = new CommentNotification($program->getUser(), $temp_comment);
          $em->persist($to_create);
          break;

        case 'like':
          $to_create = new LikeNotification($program->getUser(), $user, $program);
          $em->persist($to_create);
          break;
        case 'remix':
          $to_create = new RemixNotification($program->getUser(), $user, $program, $program);
          $em->persist($to_create);
          break;
        case 'catro notifications':
          $to_create = new CatroNotification($user, 'Random Title', 'Random Text');
          $em->persist($to_create);
          break;
        case 'default':
          Assert::assertTrue(false);
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
   *
   * @param mixed $number_of_users
   */
  public function thereShouldBeUsersInTheDatabase($number_of_users): void
  {
    $users = $this->getUserManager()->findAll();
    Assert::assertCount($number_of_users, $users);
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
        ->setEnabled($config['enabled'] ?? true)
        ->setPriority($config['priority'] ?? 0)
            ;
      $this->getManager()->persist($achievement);
    }
    $this->getManager()->flush();
  }

  /**
   * @Given /^there are user achievements:$/
   *
   * @throws Exception
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
        ->setSeenAt(!empty($config['seen_at']) ? new DateTime($config['seen_at']) : null)
        ->setUnlockedAt(!empty($config['unlocked_at']) ? new DateTime($config['unlocked_at']) : new DateTime('now'))
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
   * @Given there are project machine translations:
   */
  public function thereAreProjectMachineTranslations(TableNode $table): void
  {
    foreach ($table->getHash() as $config) {
      $project = $this->getProgramManager()->find($config['project_id']);
      $source_language = $config['source_language'];
      $target_language = $config['target_language'];
      $provider = $config['provider'];
      $usage_count = $config['usage_count'];

      $project_machine_translation = new ProjectMachineTranslation($project, $source_language, $target_language, $provider, $usage_count);
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

    Assert::assertEquals(count($project_machine_translations), count($table_rows), 'table has different number of rows');

    foreach ($project_machine_translations as $translation) {
      /** @var Program $project */
      $project = $translation->getProject();
      $project_id = $project->getId();
      $source_language = $translation->getSourceLanguage();
      $target_language = $translation->getTargetLanguage();
      $provider = $translation->getProvider();
      $usage_count = $translation->getUsageCount();

      $matching_row = array_filter($table_rows,
        function ($row) use ($project_id, $source_language, $target_language, $provider, $usage_count) {
          return $project_id == $row['project_id']
            && $source_language == $row['source_language']
            && $target_language == $row['target_language']
            && $provider == $row['provider']
            && $usage_count == $row['usage_count'];
        });

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
      $usage_count = $config['usage_count'];

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
        function ($row) use ($comment_id, $source_language, $target_language, $provider, $usage_count) {
          return $comment_id == $row['comment_id']
            && $source_language == $row['source_language']
            && $target_language == $row['target_language']
            && $provider == $row['provider']
            && $usage_count == $row['usage_count'];
        });

      Assert::assertEquals(1, count($matching_row), "row not found: {$comment_id}");
    }
  }
}
