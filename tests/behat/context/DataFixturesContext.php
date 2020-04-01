<?php

namespace Tests\behat\context;

use App\Catrobat\Services\TestEnv\SymfonySupport;
use App\Entity\AchievementNotification;
use App\Entity\AnniversaryNotification;
use App\Entity\BroadcastNotification;
use App\Entity\CatroNotification;
use App\Entity\ClickStatistic;
use App\Entity\CommentNotification;
use App\Entity\Extension;
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
use App\Entity\RudeWord;
use App\Entity\StarterCategory;
use App\Entity\Tag;
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

class DataFixturesContext implements KernelAwareContext
{
  use SymfonySupport;

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
    foreach ($table->getHash() as $config)
    {
      $this->insertUser($config, false);
    }
    $this->getManager()->flush();
  }

  /**
   * @Given /^there are admins:$/
   */
  public function thereAreAdmins(TableNode $table): void
  {
    foreach ($table->getHash() as $user_config)
    {
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
    for ($i = 0; $i < $user_count; ++$i)
    {
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
    foreach ($table->getHash() as $config)
    {
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
    for ($i = 1; $i < $number_of_users; ++$i)
    {
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
    foreach ($ids as $id)
    {
      /** @var User|null $followUser */
      $followUser = $this->getUserManager()->find($id);
      $user->addFollowing($followUser);
      $this->getUserManager()->updateUser($user);
    }
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
    foreach ($table->getHash() as $config)
    {
      $this->insertProject($config, false);
    }
    $this->getManager()->flush();
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
    foreach ($table->getHash() as $config)
    {
      $program = $this->insertProject($config, false);
      $file_repo->saveProgramFile(new File($this->FIXTURES_DIR.'test.catrobat'), $program->getId());
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
    foreach ($table->getHash() as $config)
    {
      $this->insertFeaturedProject($config, false);
    }
    $this->getManager()->flush();
  }

  /**
   * @Given /^there are starter programs:$/
   * @Given /^there are starter projects:$/
   *
   * @throws Exception
   */
  public function thereAreStarterPrograms(TableNode $table): void
  {
    $em = $this->getManager();

    $starter = new StarterCategory();
    $starter->setName('Games');
    $starter->setAlias('games');
    $starter->setOrder(1);

    $programs = $table->getHash();
    foreach ($programs as $config)
    {
      $program = $this->insertProject($config);
      $starter->addProgram($program);
    }
    $em->persist($starter);
    $em->flush();
  }

  /**
   * @Given /^there are programs with a large description:$/
   * @Given /^there are projects with a large description:$/
   *
   * @throws Exception
   */
  public function thereAreProgramsWithALargeDescription(TableNode $table): void
  {
    foreach ($table->getHash() as $config)
    {
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
    $this->getFileRepository()->saveProgramFile(
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

    foreach ($program_tags as $program_tag)
    {
      /* @var Tag $program_tag */
      Assert::assertTrue(
        in_array($program_tag->getDe(), $tags, true) || in_array($program_tag->getEn(), $tags, true),
        'The tag is not found!'
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
   * @Then the embroidery program should have the :extension extension
   *
   * @param mixed $extension
   */
  public function theEmbroideryProgramShouldHaveTheExtension($extension): void
  {
    $program_extensions = $this->getProgramManager()->findOneByName('ZigZag Stich')->getExtensions();

    Assert::assertNotNull($program_extensions);

    foreach ($program_extensions as $program_extension)
    {
      /* @var $program_extension Extension */
      Assert::assertContains($program_extension->getName(), $extension, 'The Extension was not found!');
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
    foreach ($program_extensions as $program_extension)
    {
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

    Assert::assertCount(0, $program_extensions, 'Too much or too less tags found!');

    $ext = ['Arduino', 'Lego', 'Phiro'];
    foreach ($program_extensions as $program_extension)
    {
      /* @var Extension $program_extension */
      Assert::assertContains($program_extension->getName(), $ext, 'The Extension is not found!');
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
    Assert::assertEquals('phirocode', $program->getFlavor(), 'Program is NOT flagged aa a phiro');
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

    $this->getFileRepository()->saveProgramFile(
      new File($this->FIXTURES_DIR.'GeneratedFixtures/phiro.catrobat'), $program->getId()
    );
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
    foreach ($table->getHash() as $config)
    {
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
    foreach ($table->getHash() as $config)
    {
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
    foreach ($table->getHash() as $config)
    {
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
    foreach ($packages as $package)
    {
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
    foreach ($categories as $category)
    {
      $new_category = new MediaPackageCategory();
      $new_category->setName($category['name']);

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
    $files = $table->getHash();
    foreach ($files as $file)
    {
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
      if (!empty($file['flavor']))
      {
        $new_file->setFlavor($file['flavor']);
      }
      $new_file->setAuthor($file['author']);

      $file_repo->saveMediaPackageFile(new File($this->MEDIA_PACKAGE_DIR.$file['id'].'.'.
        $file['extension']), $file['id'], $file['extension']);

      $em->persist($new_file);
    }
    $em->flush();
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
    foreach ($table->getHash() as $config)
    {
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
   * @Then the program download statistic should have a download timestamp, \
   *       an anonymous user and the following statistics:
   *
   * @throws Exception
   */
  public function theProgramShouldHaveADownloadTimestampAndTheFollowingStatistics(TableNode $table): void
  {
    $statistics = $table->getHash();
    foreach ($statistics as $statistic)
    {
      $ip = $statistic['ip'];
      $country_code = $statistic['country_code'];
      if ('NULL' === $country_code)
      {
        $country_code = null;
      }
      $country_name = $statistic['country_name'];
      if ('NULL' === $country_name)
      {
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
    foreach ($table->getHash() as $config)
    {
      $this->insertUserLikeSimilarity($config, false);
    }
    $this->getManager()->flush();
  }

  /**
   * @Given /^there are remix similar users:$/
   */
  public function thereAreRemixSimilarUsers(TableNode $table): void
  {
    foreach ($table->getHash() as $config)
    {
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
    foreach ($table->getHash() as $config)
    {
      $this->insertProgramLike($config, false);
    }
    $this->getManager()->flush();
  }

  /**
   * @Given /^there are tags:$/
   */
  public function thereAreTags(TableNode $table): void
  {
    foreach ($table->getHash() as $config)
    {
      $this->insertTag($config, false);
    }
    $this->getManager()->flush();
  }

  /**
   * @Given /^there are extensions:$/
   */
  public function thereAreExtensions(TableNode $table): void
  {
    foreach ($table->getHash() as $config)
    {
      $this->insertExtension($config, false);
    }
    $this->getManager()->flush();
  }

  /**
   * @Given /^there are forward remix relations:$/
   */
  public function thereAreForwardRemixRelations(TableNode $table): void
  {
    foreach ($table->getHash() as $config)
    {
      $this->insertForwardRemixRelation($config, false);
    }
    $this->getManager()->flush();
  }

  /**
   * @Given /^there are backward remix relations:$/
   */
  public function thereAreBackwardRemixRelations(TableNode $table): void
  {
    foreach ($table->getHash() as $config)
    {
      $this->insertBackwardRemixRelation($config, false);
    }
    $this->getManager()->flush();
  }

  /**
   * @Given /^there are Scratch remix relations:$/
   */
  public function thereAreScratchRemixRelations(TableNode $table): void
  {
    foreach ($table->getHash() as $config)
    {
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

    foreach ($table->getHash() as $data)
    {
      $project = $this->getProgramManager()->find($data['project']);
      if (null === $project)
      {
        throw new Exception('Project with id '.$data['project'].' does not exist.');
      }

      /** @var User|null $user */
      $user = $this->getUserManager()->findUserByUsername($data['user']);
      if (null === $user)
      {
        throw new Exception('User with username '.$data['user'].' does not exist.');
      }

      $type = $data['type'];
      if (ctype_digit($type))
      {
        $type = (int) $type;
      }
      else
      {
        $type = array_search($type, ProgramLike::$TYPE_NAMES, true);
        if (false === $type)
        {
          throw new Exception('Unknown type "'.$data['type'].'" given.');
        }
      }
      if (!ProgramLike::isValidType($type))
      {
        throw new Exception('Unknown type "'.$data['type'].'" given.');
      }

      $like = new ProgramLike($project, $user, $type);

      if (array_key_exists('created at', $data) && !empty(trim($data['created at'])))
      {
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

    foreach ($notifications as $notification)
    {
      /** @var User|null $user */
      $user = $this->getUserManager()->findUserByUsername($notification['user']);

      Assert::assertNotNull($user, 'user is null');

      switch ($notification['type'])
      {
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
        case 'anniversary':
          $to_create = new AnniversaryNotification($user, $notification['title'], $notification['message'], $notification['prize']);
          break;
        case 'achievement':
          $to_create = new AchievementNotification($user, $notification['title'], $notification['message'], $notification['image_path']);
          break;
        case 'broadcast':
          $to_create = new BroadcastNotification($user, $notification['title'], $notification['message']);
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
      if (isset($notification['id']))
      {
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

    for ($i = 0; $i < $arg1; ++$i)
    {
      /** @var User|null $user */
      $user = $this->getUserManager()->findUserByUsername($username);

      Assert::assertNotNull($user, 'user is null');

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

    for ($i = 0; $i < $amount; ++$i)
    {
      switch ($type)
      {
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
          $to_create = new RemixNotification($program->getUser(), $program->getUser(), $program, $program);
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
   * @Given /^I define the following rude words:$/
   */
  public function iDefineTheFollowingRudeWords(TableNode $table): void
  {
    $words = $table->getHash();
    $em = $this->getManager();

    foreach ($words as $word)
    {
      $rude_word = new RudeWord();
      $rude_word->setWord($word['word']);
      $em->persist($rude_word);
    }
    $em->flush();
  }
}
