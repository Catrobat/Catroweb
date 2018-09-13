<?php

namespace Catrobat\AppBundle\Features\Web\Context;

use Behat\Behat\Context\CustomSnippetAcceptingContext;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\DriverException;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Catrobat\AppBundle\Entity\AchievementNotification;
use Catrobat\AppBundle\Entity\CatroNotification;
use Catrobat\AppBundle\Entity\CommentNotification;
use Catrobat\AppBundle\Entity\Extension;
use Catrobat\AppBundle\Entity\FeaturedProgram;
use Catrobat\AppBundle\Entity\MediaPackage;
use Catrobat\AppBundle\Entity\MediaPackageCategory;
use Catrobat\AppBundle\Entity\MediaPackageFile;
use Catrobat\AppBundle\Entity\NolbExampleProgram;
use Catrobat\AppBundle\Entity\Program;
use Catrobat\AppBundle\Entity\ProgramDownloads;
use Catrobat\AppBundle\Entity\ProgramLike;
use Catrobat\AppBundle\Entity\ProgramManager;
use Catrobat\AppBundle\Entity\ProgramRemixRelation;
use Catrobat\AppBundle\Entity\StarterCategory;
use Catrobat\AppBundle\Entity\Tag;
use Catrobat\AppBundle\Entity\TagRepository;
use Catrobat\AppBundle\Entity\User;
use Catrobat\AppBundle\Entity\UserComment;
use Catrobat\AppBundle\Entity\UserCommentRepository;
use Catrobat\AppBundle\Entity\UserLDAPManager;
use Catrobat\AppBundle\Entity\UserManager;
use Catrobat\AppBundle\Features\Helpers\SymfonySupport;
use Catrobat\AppBundle\Services\MediaPackageFileRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpKernel\KernelInterface;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Catrobat\AppBundle\Features\Helpers\BaseContext;
use PHPUnit\Framework\Assert;


//
// Require 3rd-party libraries here:
//

/**
 * Feature context.
 */
class FeatureContext extends MinkContext implements KernelAwareContext, CustomSnippetAcceptingContext
{
  private $kernel;
  private $screenshot_directory;
  private $client;
  private $use_real_oauth_javascript_code;

  const AVATAR_DIR = './testdata/DataFixtures/AvatarImages/';
  const MEDIAPACKAGE_DIR = './testdata/DataFixtures/MediaPackage/';
  const FIXTUREDIR = './testdata/DataFixtures/';
  const ALREADY_IN_DB_USER = 'AlreadyinDB';

  /**
   * Initializes context with parameters from behat.yml.
   *
   * @param array $screenshot_directory
   *
   * @throws \Exception
   */
  public function __construct($screenshot_directory)
  {
    $this->screenshot_directory = preg_replace('/([^\/]+)$/', '$1/', $screenshot_directory);
    if (!is_dir($this->screenshot_directory))
    {
      throw new \Exception('No screenshot directory specified!');
    }
    $this->use_real_oauth_javascript_code = false;
    $this->setOauthServiceParameter('0');
  }

  /**
   * Sets HttpKernel instance.
   * This method will be automatically called by Symfony2Extension ContextInitializer.
   *
   * @param KernelInterface $kernel
   */
  public function setKernel(KernelInterface $kernel)
  {
    $this->kernel = $kernel;
  }

  /**
   * @BeforeScenario @RealOAuth
   */
  public function activateRealOAuthService()
  {
    $this->setOauthServiceParameter('1');
    $this->use_real_oauth_javascript_code = true;
  }

  /**1
   * @AfterScenario @RealOAuth
   */
  public function deactivateRealOAuthService()
  {
    $this->setOauthServiceParameter('0');
    $this->use_real_oauth_javascript_code = false;
  }

  public static function getAcceptedSnippetType()
  {
    return 'regex';
  }

  private function deleteScreens()
  {
    $files = glob($this->screenshot_directory . '*');
    foreach ($files as $file)
    {
      if (is_file($file))
      {
        unlink($file);
      }
    }
  }

  /**
   * @When /^I go to the website root$/
   */
  public function iGoToTheWebsiteRoot()
  {
    $this->getSession()->visit(self::BASE_URL);
  }

  /**
   * @When /^I click browser's back button$/
   */
  public function iClickBrowsersBackButton()
  {
    $this->getSession()->back();
  }

  /**
   * @BeforeScenario
   */
  public function setup()
  {
    $this->getSession()->resizeWindow(1240, 1024);
  }

  /**
   * @AfterScenario
   */
  public function resetSession()
  {
    $this->getSession()->getDriver()->reset();
    $this->getSession()->getDriver()->reset();
  }

  /**
   * @AfterStep
   */
  public function makeScreenshot(AfterStepScope $scope)
  {
    if (!$scope->getTestResult()->isPassed())
    {
      $this->saveScreenshot(null, $this->screenshot_directory);
    }
  }

  /**
   * @BeforeScenario @Mobile
   */
  public function resizeWindowMobile()
  {
    $this->getSession()->resizeWindow(320, 1000);
  }

  /**
   * @BeforeScenario @Tablet
   */
  public function resizeWindowTablet()
  {
    $this->getSession()->resizeWindow(768, 1000);
  }

  /**
   * @When /^I wait (\d+) milliseconds$/
   */
  public function iWaitMilliseconds($milliseconds)
  {
    $this->getSession()->wait($milliseconds);
  }

  /**
   * @Then /^I should see (\d+) "([^"]*)"$/
   */
  public function iShouldSeeNumberOfElements($arg1, $arg2)
  {
    $programs = $this->getSession()->getPage()->findAll('css', $arg2);
    Assert::assertEquals($arg1, count($programs));
  }

  /**
   * @Then /^I should see a node with id "([^"]*)" having name "([^"]*)" and username "([^"]*)"$/
   */
  public function iShouldSeeANodeWithNameAndUsername($node_id, $expected_node_name, $expected_username)
  {
    $result = $this->getSession()->evaluateScript("
            return { nodeName: RemixGraph.getInstance().getNodes().get('" . $node_id . "').name,
                     username: RemixGraph.getInstance().getNodes().get('" . $node_id . "').username };
      ");
    $actual_node_name = is_array($result['nodeName']) ? implode('', $result['nodeName']) : $result['nodeName'];
    $actual_username = $result['username'];
    Assert::assertEquals($expected_node_name, $actual_node_name);
    Assert::assertEquals($expected_username, $actual_username);
  }

  /**
   * @Then /^I should see an edge from "([^"]*)" to "([^"]*)"$/
   */
  public function iShouldSeeAnEdgeFromTo($from_id, $to_id)
  {
    $result = $this->getSession()->evaluateScript("
            return RemixGraph.getInstance().getEdges().get().filter(function (edge) { return edge.from === '" . $from_id . "' && edge.to === '" . $to_id . "'; });
      ");
    Assert::assertCount(1, $result);
    Assert::assertEquals($from_id, $result[0]['from']);
    Assert::assertEquals($to_id, $result[0]['to']);
  }

  /**
   * @Then /^I should see the featured slider$/
   */
  public function iShouldSeeTheFeaturedSlider()
  {
    $this->assertSession()->responseContains('featured');
    Assert::assertTrue($this->getSession()->getPage()->findById('feature-slider')->isVisible());
  }

  /**
   * @Then /^I should see the welcome section$/
   */
  public function iShouldSeeTheWelcomeSection()
  {
    Assert::assertTrue($this->getSession()->getPage()->findById('welcomeSection')->isVisible());
  }


  /**
   * @Then /^I should not see the welcome section$/
   */
  public function iShouldNotSeeTheWelcomeSection()
  {
    Assert::assertNull($this->getSession()->getPage()->findById('welcomeSection'));
  }

  /**
   * @Then /^I should see ([^"]*) programs$/
   */
  public function iShouldSeePrograms($arg1)
  {
    $arg1 = trim($arg1);

    switch ($arg1)
    {
      case 'some':
        $this->assertSession()->elementExists('css', '.program');
        break;

      case 'no':
        $this->assertSession()->elementNotExists('css', '.program');
        break;

      case 'newest':
        $this->assertSession()->elementExists('css', '#newest');
        $this->assertSession()->elementExists('css', '.program');
        break;

      case 'most downloaded':
        $this->assertSession()->elementExists('css', '#mostDownloaded');
        $this->assertSession()->elementExists('css', '.program');
        break;

      case 'most viewed':
        $this->assertSession()->elementExists('css', '#mostViewed');
        $this->assertSession()->elementExists('css', '.program');
        break;

      default:
        Assert::assertTrue(false);
    }
  }

  /**
   * @Then /^the selected language should be "([^"]*)"$/
   * @Given /^the selected language is "([^"]*)"$/
   */
  public function theSelectedLanguageShouldBe($arg1)
  {
    switch ($arg1)
    {
      case 'English':
        $cookie = $this->getSession()->getCookie('hl');
        if (!empty($cookie))
        {
          $this->assertSession()->cookieEquals('hl', 'en');
        }
        break;

      case 'Deutsch':
        $this->assertSession()->cookieEquals('hl', 'de');
        break;

      default:
        Assert::assertTrue(false);
    }
  }

  /**
   * @Then /^I switch the language to "([^"]*)"$/
   */
  public function iSwitchTheLanguageTo($arg1)
  {
    switch ($arg1)
    {
      case 'English':
        $this->getSession()->setCookie('hl', 'en');
        break;
      case 'Deutsch':
        $this->getSession()->setCookie('hl', 'de');
        break;
      case 'Russisch':
        $this->getSession()->setCookie('hl', 'ru');
        break;
      case 'French':
        $this->getSession()->setCookie('hl', 'fr');
        break;
      default:
        Assert::assertTrue(false);
    }
    $this->reload();
  }

  /**
   * @Then /^I should see a( [^"]*)? help image "([^"]*)"$/
   */
  public function iShouldSeeAHelpImage($arg1, $arg2)
  {
    $arg1 = trim($arg1);

    $this->assertSession()->responseContains('help-desktop');
    $this->assertSession()->responseContains('help-mobile');

    if ($arg1 == 'big')
    {
      Assert::assertTrue($this->getSession()->getPage()->find('css', '.help-desktop')->isVisible());
      Assert::assertFalse($this->getSession()->getPage()->find('css', '.help-mobile')->isVisible());
    }
    elseif ($arg1 == 'small')
    {
      Assert::assertFalse($this->getSession()->getPage()->find('css', '.help-desktop')->isVisible());
      Assert::assertTrue($this->getSession()->getPage()->find('css', '.help-mobile')->isVisible());
    }
    elseif ($arg1 == '')
    {
      Assert::assertTrue($this->getSession()->getPage()->find('css', '.help-split-desktop')->isVisible());
    }
    else
    {
      Assert::assertTrue(false);
    }

    $img = null;
    $path = null;

    switch ($arg2)
    {
      case 'Hour of Code':
        if ($arg1 == 'big')
        {
          $img = $this->getSession()->getPage()->findById('hour-of-code-desktop');
          $path = '/images/help/hour_of_code.png';
        }
        elseif ($arg1 == 'small')
        {
          $img = $this->getSession()->getPage()->findById('hour-of-code-mobile');
          $path = '/images/help/hour_of_code_mobile.png';
        }
        else
        {
          Assert::assertTrue(false);
        }
        break;
      case 'Game Design':
        if ($arg1 == 'big')
        {
          $img = $this->getSession()->getPage()->findById('alice-tut-desktop');
          $path = '/images/help/alice_tut.png';
        }
        elseif ($arg1 == 'small')
        {
          $img = $this->getSession()->getPage()->findById('alice-tut-mobile');
          $path = '/images/help/alice_tut_mobile.png';
        }
        else
        {
          Assert::assertTrue(false);
        }
        break;
      case 'Step By Step':
        if ($arg1 == 'big')
        {
          $img = $this->getSession()->getPage()->findById('step-by-step-desktop');
          $path = '/images/help/step_by_step.png';
        }
        elseif ($arg1 == 'small')
        {
          $img = $this->getSession()->getPage()->findById('step-by-step-mobile');
          $path = '/images/help/step_by_step_mobile.png';
        }
        else
        {
          Assert::assertTrue(false);
        }
        break;
      case 'Tutorials':
        $img = $this->getSession()->getPage()->findById('tutorials');
        $path = '/images/help/tutorials.png';
        break;
      case 'Starters':
        $img = $this->getSession()->getPage()->findById('starters');
        $path = '/images/help/starters.png';
        break;
      case 'Education Platform':
        if ($arg1 == 'big')
        {
          $img = $this->getSession()->getPage()->findById('edu-desktop');
          $path = '/images/help/edu_site.png';
        }
        elseif ($arg1 == 'small')
        {
          $img = $this->getSession()->getPage()->findById('edu-mobile');
          $path = '/images/help/edu_site_mobile.png';
        }
        else
        {
          Assert::assertTrue(false);
        }
        break;
      case 'Discussion':
        if ($arg1 == 'big')
        {
          $img = $this->getSession()->getPage()->findById('discuss-desktop');
          $path = '/images/help/discuss.png';
        }
        elseif ($arg1 == 'small')
        {
          $img = $this->getSession()->getPage()->findById('discuss-mobile');
          $path = '/images/help/discuss_mobile.png';
        }
        else
        {
          Assert::assertTrue(false);
        }
        break;
      default:
        Assert::assertTrue(false);
        break;
    }

    if ($img != null)
    {
      Assert::assertEquals($img->getTagName(), 'img');
      Assert::assertEquals($img->getAttribute('src'), $path);
      Assert::assertTrue($img->isVisible());
    }
    else
    {
      Assert::assertTrue(false);
    }
  }

  /**
   * @Given /^there are users:$/
   */
  public function thereAreUsers(TableNode $table)
  {
    /**
     * @var $user_manager UserManager
     * @var $user         User
     */
    $user_manager = $this->kernel->getContainer()->get('usermanager');
    $users = $table->getHash();
    $user = null;
    $count = count($users);
    for ($i = 0; $i < $count; ++$i)
    {
      $user = $user_manager->createUser();
      $user->setUsername($users[$i]['name']);
      $user->setEmail($users[$i]['email']);
      $user->setAdditionalEmail('');
      $user->setPlainPassword($users[$i]['password']);
      $user->setEnabled(true);
      $user->setUploadToken($users[$i]['token']);
      $user->setCountry('at');
      $user->setNolbUser(isset($users[$i]['nolb_status']) ? $users[$i]['nolb_status'] == 'true' : false);
      $user_manager->updateUser($user, false);
    }
    $em = $this->kernel->getContainer()->get('doctrine')->getManager();
    $em->flush();
  }

  /**
   * @Given /^there are admins:$/
   */
  public function thereAreAdmins(TableNode $table)
  {
    /**
     * @var $user_manager UserManager
     * @var $user         User
     */
    $user_manager = $this->kernel->getContainer()->get('usermanager');
    $users = $table->getHash();
    $user = null;
    $count = count($users);
    for ($i = 0; $i < $count; ++$i)
    {
      $user = $user_manager->createUser();
      $user->setUsername($users[$i]['name']);
      $user->setEmail($users[$i]['email']);
      $user->setAdditionalEmail('');
      $user->setPlainPassword($users[$i]['password']);
      $user->setEnabled(true);
      $user->setUploadToken($users[$i]['token']);
      $user->setCountry('at');
      $user->setSuperAdmin(true);
      $user_manager->updateUser($user, false);
    }
    $user_manager->updateUser($user, true);
  }

  /**
   * @Given /^there are catro notifications:$/
   *
   * @param TableNode $table
   *
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function thereAreCatroNotifications(TableNode $table)
  {
    /**
     * @var $em EntityManager
     * @var $user User
     */
    $em = $this->kernel->getContainer()->get('doctrine')->getManager();
    $notifications = $table->getHash();

    foreach ($notifications as $notification)
    {
      $user = $em->getRepository('AppBundle:User')->findOneBy([
        'username' => $notification['user'],
      ]);
      if ($user == null)
      {
        Assert::assertTrue(false, "user is null");
      }
      switch ($notification['type'])
      {
        case "achievement":
          $to_create = new AchievementNotification($user, $notification['title'], $notification['message'], "");
          $em->persist($to_create);
          break;
        case "comment":
          $comment = $em->getRepository(UserComment::class)->find($notification['commentID']);
          $to_create = new CommentNotification($user, $notification['title'], $notification['message'], $comment);
          $em->persist($to_create);
          break;
        default:
          $to_create = new CatroNotification($user, $notification['title'], $notification['message']);
          $em->persist($to_create);
          break;
      }
    }
    $em->flush();
  }

  /**
   * @Then /^I click on the first "([^"]*)" button$/
   */
  public function iClickOnTheFirstButton($arg1)
  {
    $this->assertSession()->elementExists('css', $arg1);

    $this
      ->getSession()
      ->getPage()
      ->find('css', $arg1)
      ->click();
    $this->getSession()->wait(500);
  }

  /**
   * @Given /^there are programs:$/
   */
  public function thereArePrograms(TableNode $table)
  {
    /**
    * @var $program \Catrobat\AppBundle\Entity\Program
    * @var $em EntityManager
    */
    $em = $this->kernel->getContainer()->get('doctrine')->getManager();
    $programs = $table->getHash();
    $count = count($programs);
    for ($i = 0; $i < $count; ++$i)
    {
      $user = $em->getRepository('AppBundle:User')->findOneBy([
        'username' => $programs[$i]['owned by'],
      ]);
      $program = new Program();
      $program->setUser($user);
      $program->setName($programs[$i]['name']);
      $program->setDescription($programs[$i]['description']);
      $program->setViews($programs[$i]['views']);
      $program->setDownloads($programs[$i]['downloads']);
      $program->setApkDownloads($programs[$i]['apk_downloads']);
      $program->setApkStatus(isset($programs[$i]['apk_ready']) ? ($programs[$i]['apk_ready'] === 'true' ? Program::APK_READY : Program::APK_NONE) : Program::APK_NONE);
      $program->setUploadedAt(new \DateTime($programs[$i]['upload time'], new \DateTimeZone('UTC')));
      $program->setRemixMigratedAt(null);
      $program->setCatrobatVersion(1);
      $program->setCatrobatVersionName($programs[$i]['version']);
      $program->setLanguageVersion(isset($programs[$i]['language version']) ? $programs[$i]['language version'] : 1);
      $program->setUploadIp('127.0.0.1');
      $program->setFilesize(0);
      $program->setVisible(isset($programs[$i]['visible']) ? $programs[$i]['visible'] == 'true' : true);
      $program->setUploadLanguage('en');
      $program->setApproved(false);
      $program->setFbPostUrl(isset($programs[$i]['fb_post_url']) ? $programs[$i]['fb_post_url'] : '');
      $program->setRemixRoot(isset($programs[$i]['remix_root']) ? $programs[$i]['remix_root'] == 'true' : true);
      $program->setPrivate(isset($programs[$i]['private']) ? $programs[$i]['private'] : 0);

      if (isset($programs[$i]['tags_id']) && $programs[$i]['tags_id'] != null)
      {
        $tag_repo = $em->getRepository('AppBundle:Tag');
        $tags = explode(',', $programs[$i]['tags_id']);
        foreach ($tags as $tag_id)
        {
          $tag = $tag_repo->find($tag_id);
          $program->addTag($tag);
        }
      }

      if (isset($programs[$i]['extensions']) && $programs[$i]['extensions'] != null)
      {
        $extension_repo = $em->getRepository('AppBundle:Extension');
        $extensions = explode(',', $programs[$i]['extensions']);
        foreach ($extensions as $extension_name)
        {
          $extension = $extension_repo->findOneByName($extension_name);
          $program->addExtension($extension);
        }
      }

      if ($program->getApkStatus() == Program::APK_READY)
      {
        /* @var $apkrepository \Catrobat\AppBundle\Services\ApkRepository */
        $apkrepository = $this->kernel->getContainer()->get('apkrepository');
        $temppath = tempnam(sys_get_temp_dir(), 'apktest');
        copy(self::FIXTUREDIR . 'test.catrobat', $temppath);
        $apkrepository->save(new File($temppath), $i);

        $file_repo = $this->kernel->getContainer()->get('filerepository');
        $file_repo->saveProgramfile(new File(self::FIXTUREDIR . 'test.catrobat'), $i);
      }

      $em->persist($program);
    }
    $em->flush();
  }

  /**
   * @Given /^there are nolb example programs:$/
   */
  public function thereAreNolbExamplePrograms(TableNode $table)
  {
    $em = $this->kernel->getContainer()->get('doctrine')->getManager();
    $examples = $table->getHash();

    foreach ($examples as $example)
    {
      $new_example = new NolbExampleProgram();

      $program_id = intval($example['program']);
      $program = $em->getRepository('AppBundle:Program')->find($program_id);

      $new_example->setProgram($program);
      $new_example->setActive(isset($example['active']) ? $example['active'] == 'true' : true);
      $new_example->setIsForFemale(isset($example['for_female']) ? $example['for_female'] == 'true' : false);
      $new_example->setDownloadsFromFemale(intval($example['female_counter']));
      $new_example->setDownloadsFromMale(intval($example['male_counter']));
      $em->persist($new_example);
    }
    $em->flush();
  }


  /**
   * @Given /^there are comments:$/
   */
  public function thereAreComments(TableNode $table)
  {
    /*
    * @var $new_comment \Catrobat\AppBundle\Entity\UserComment
    */
    $em = $this->kernel->getContainer()->get('doctrine')->getManager();
    $pm = $this->kernel->getContainer()->get('programmanager');
    $comments = $table->getHash();

    foreach ($comments as $comment)
    {
      $new_comment = new UserComment();

      $new_comment->setUploadDate(new \DateTime($comment['upload_date'], new \DateTimeZone('UTC')));
      $new_comment->setProgram($pm->find($comment['program_id']));
      $new_comment->setProgramId($comment['program_id']);
      $new_comment->setUserId($comment['user_id']);
      $new_comment->setUsername($comment['user_name']);
      $new_comment->setIsReported(false);
      $new_comment->setText($comment['text']);
      $em->persist($new_comment);
    }
    $em->flush();
  }

  /**
   * @Given /^there are likes:$/
   */
  public function thereAreLikes(TableNode $table)
  {
    $em = $this->kernel->getContainer()->get('doctrine')->getManager();
    $likes = $table->getHash();

    foreach ($likes as $like)
    {
      $user = $this->kernel->getContainer()->get('usermanager')->findOneBy(['username' => $like['username']]);
      $program = $this->kernel->getContainer()->get('programrepository')->find($like['program_id']);

      $program_like = new ProgramLike($program, $user, $like['type']);
      $program_like->setCreatedAt(new \DateTime($like['created at'], new \DateTimeZone('UTC')));

      $em->persist($program_like);
    }
    $em->flush();
  }

  /**
   * @Given /^there are forward remix relations:$/
   */
  public function thereAreForwardRemixRelations(TableNode $table)
  {
    /*
    * @var $em \Doctrine\ORM\EntityManager
    */
    $em = $this->kernel->getContainer()->get('doctrine')->getManager();
    $relations = $table->getHash();

    foreach ($relations as $relation)
    {
      $ancestor_program = $em->getRepository('AppBundle:Program')->find($relation['ancestor_id']);
      $descendant_program = $em->getRepository('AppBundle:Program')->find($relation['descendant_id']);

      $forward_relation = new ProgramRemixRelation($ancestor_program, $descendant_program, intval($relation['depth']));
      $em->persist($forward_relation);
    }
    $em->flush();
  }

  /**
   * @Given /^there are featured programs:$/
   */
  public function thereAreFeaturedPrograms(TableNode $table)
  {
    /*
    * @var $em \Doctrine\ORM\EntityManager
    */
    $em = $this->kernel->getContainer()->get('doctrine')->getManager();
    $relations = $table->getHash();

    foreach ($relations as $relation)
    {
      $program = $em->getRepository('AppBundle:Program')->find($relation['program_id']);

      $featured_program = new FeaturedProgram();
      $featured_program->setProgram($program);
      $featured_program->setImageType($relation['imagetype']);
      $featured_program->setActive(intval($relation['active']));
      $featured_program->setFlavor($relation['flavor']);
      $featured_program->setPriority(intval($relation['priority']));
      $em->persist($featured_program);
    }
    $em->flush();
  }

  /**
   * @Given /^I write "([^"]*)" in textbox$/
   */
  public function iWriteInTextbox($arg1)
  {
    $textarea = $this->getSession()->getPage()->find('css', '.msg');
    Assert::assertNotNull($textarea, "Textarea not found");
    $textarea->setValue($arg1);
  }


  /**
   * @Given /^there are tags:$/
   */
  public function thereAreTags(TableNode $table)
  {
    $tags = $table->getHash();
    $em = $this->kernel->getContainer()->get('doctrine')->getManager();

    foreach ($tags as $tag)
    {
      $insert_tag = new Tag();

      $insert_tag->setEn($tag['en']);
      $insert_tag->setDe($tag['de']);

      $em->persist($insert_tag);
    }
    $em->flush();
  }

  /**
   * @Given /^the nolb example program (\d+) has "([^"]*)" set to (\d+)$/
   */
  public function theNolbExampleProgramHasSetTo($arg1, $arg2, $arg3)
  {
    $em = $this->kernel->getContainer()->get('doctrine')->getManager();
    $example_program = $em->getRepository('AppBundle:NolbExampleProgram')->find($arg1);

    switch ($arg2)
    {
      case "female_counter":
        $counter = $example_program->getDownloadsFromFemale();
        break;
      default:
        $counter = $example_program->getDownloadsFromMale();
    }

    Assert::assertEquals($arg3, $counter, "$arg2 not same");
  }


  /**
   * @Given /^there are extensions:$/
   */
  public function thereAreExtensions(TableNode $table)
  {
    $extensions = $table->getHash();
    $em = $this->kernel->getContainer()->get('doctrine')->getManager();

    foreach ($extensions as $extension)
    {
      $insert_extension = new Extension();

      $insert_extension->setName($extension['name']);
      $insert_extension->setPrefix($extension['prefix']);

      $em->persist($insert_extension);
    }
    $em->flush();
  }

  /**
   * @When /^I click "([^"]*)"$/
   */
  public function iClick($arg1)
  {
    $arg1 = trim($arg1);

    $this->assertSession()->elementExists('css', $arg1);

    $this
      ->getSession()
      ->getPage()
      ->find('css', $arg1)
      ->click();
  }

  /**
   * @Then /^I click the "([^"]*)" RadioButton$/
   */
  public function iClickTheRadiobutton($arg1)
  {
    $page = $this->getSession()->getPage();
    $radioButton = $page->find('css', $arg1);
    $radioButton->click();
  }

  /**
   * @Then /^I should be logged ([^"]*)?$/
   */
  public function iShouldBeLoggedIn($arg1)
  {
    if ($arg1 == 'in')
    {
      $this->assertPageNotContainsText('Your password or username was incorrect.');
      $this->getSession()->wait(2000, 'window.location.href.search("login") == -1');
      $this->assertElementOnPage('#logo');
      $this->assertElementNotOnPage('#btn-login');
      $this->assertElementOnPage('#btn-logout');
      $this->getSession()->getPage()->find('css', '.dropdown-toggle')->click();
    }
    if ($arg1 == 'out')
    {
      $this->getSession()->wait(2000, 'window.location.href.search("profile") == -1');
      $this->assertElementOnPage('#btn-login');
      $this->assertElementNotOnPage('#nav-dropdown');
    }
  }

  /**
   * @Given /^I( [^"]*)? log in as "([^"]*)" with the password "([^"]*)"$/
   */
  public function iAmLoggedInAsAsWithThePassword($arg1, $arg2, $arg3)
  {
    $this->visitPath('/pocketcode/login');
    $this->fillField('username', $arg2);
    $this->fillField('password', $arg3);
    $this->pressButton('Login');
    if ($arg1 == 'try to')
    {
      $this->assertPageNotContainsText('Your password or username was incorrect.');
    }
  }

  /**
   * @Given /^I wait for the server response$/
   */
  public function iWaitForTheServerResponse()
  {
    $this->getSession()->wait(5000);
  }

  /**
   * @Then /^"([^"]*)" must be selected in "([^"]*)"$/
   */
  public function mustBeSelectedIn($country, $select)
  {
    $field = $this->getSession()->getPage()->findField($select);
    Assert::assertTrue($country == $field->getValue());
  }

  /**
   * @When /^(?:|I )attach the avatar "(?P<path>[^"]*)" to "(?P<field>(?:[^"]|\\")*)"$/
   */
  public function attachFileToField($field, $path)
  {
    $field = $this->fixStepArgument($field);
    $this->getSession()->getPage()->attachFileToField($field, realpath(self::AVATAR_DIR . $path));
  }

  /**
   * @Then /^the avatar img tag should( [^"]*)? have the "([^"]*)" data url$/
   */
  public function theAvatarImgTagShouldHaveTheDataUrl($not, $name)
  {
    $name = trim($name);
    $not = trim($not);

    $pre_source = $this->getSession()->getPage()->find('css', '#avatar-img');
    $source = 0;
    if (!is_null($pre_source))
    {
      $source = $pre_source->getAttribute('src');
    }
    else
    {
      Assert::assertTrue(false, "Couldn't find avatar in #avatar-img");
    }
    $source = trim($source, '"');
    $pre_style_header = $this->getSession()->getPage()->find('css', '#menu .img-avatar');
    $styleHeader = 0;
    if (!is_null($pre_style_header))
    {
      $styleHeader = $pre_style_header->getAttribute('style');
    }
    else
    {
      //Assert::assertTrue(false, "Couldn't find avatar in menu");
    }
    $sourceHeader = preg_replace("/(.+)url\(([^)]+)\)(.+)/", '\\2', $styleHeader);
    $sourceHeader = trim($sourceHeader, '"');

    switch ($name)
    {
      case 'logo.png':
        $logoUrl = 'data:image/png;base64,' . base64_encode(file_get_contents(self::AVATAR_DIR . 'logo.png'));
        //$isSame = (($source == $logoUrl) && ($sourceHeader == $logoUrl));
        $isSame = ($source == $logoUrl);
        $not == 'not' ? Assert::assertFalse($isSame) : Assert::assertTrue($isSame);
        break;

      case 'fail.tif':
        $failUrl = 'data:image/tiff;base64,' . base64_encode(file_get_contents(self::AVATAR_DIR . 'fail.tif'));
        //$isSame = (($source == $failUrl) && ($sourceHeader == $failUrl));
        $isSame = ($source == $failUrl);
        $not == 'not' ? Assert::assertFalse($isSame) : Assert::assertTrue($isSame);
        break;

      default:
        Assert::assertTrue(false);
    }
  }

  /**
   * @Given /^the element "([^"]*)" should be visible$/
   */
  public function theElementShouldBeVisible($element)
  {
    $element = $this->getSession()->getPage()->find('css', $element);
    Assert::assertNotNull($element);
    Assert::assertTrue($element->isVisible());
  }

  /**
   * @Given /^the element "([^"]*)" should not exist$/
   */
  public function theElementShouldNotExist($element)
  {
    $this->assertSession()->elementNotExists('css', $element);
  }

  /**
   * @Given /^the element "([^"]*)" should not be visible$/
   */
  public function theElementShouldNotBeVisible($element)
  {
    $element = $this->getSession()->getPage()->find('css', $element);
    Assert::assertNotNull($element);
    Assert::assertFalse($element->isVisible());
  }

  /**
   * @When /^I press enter in the search bar$/
   */
  public function iPressEnterInTheSearchBar()
  {
    $this->getSession()->evaluateScript("$('#searchbar').trigger($.Event( 'keypress', { which: 13 } ))");
    $this->getSession()->wait(5000, '(typeof window.search != "undefined") && (window.search.searchPageLoadDone == true)');
  }

  /**
   * @When /^I click the "([^"]*)" button$/
   */
  public function iClickTheButton($arg1)
  {
    $arg1 = trim($arg1);
    $page = $this->getSession()->getPage();
    $button = null;

    switch ($arg1)
    {
      case "login":
        $button = $page->find("css", "#btn-login");
        break;
      case "logout":
        $url = $this->getSession()->getCurrentUrl();
        if (strpos($url, 'profile') != false)
        {
          $page->find("css", ".show-nav-dropdown")->click();
        }
        $button = $page->find("css", "#btn-logout");
        break;
      case "profile":
        $button = $page->find("css", "#btn-profile");
        break;
      case "forgot pw or username":
        $button = $page->find("css", "#pw-request");
        break;
      case "send":
        $button = $page->find("css", "#post-button");
        break;
      case "show-more":
        $button = $page->find("css", "#show-more-button");
        break;
      case "report-comment":
        $button = $page->find("css", "#report-button-4");
        break;
      case "delete-comment":
        $button = $page->find("css", "#delete-button-4");
        break;
      case "edit":
        $button = $page->find("css", "#edit-icon a");
        break;
      case "password-edit":
        $button = $page->find("css", "#password-button");
        break;
      case "email-edit":
        $button = $page->find("css", "#email-button");
        break;
      case "country-edit":
        $button = $page->find("css", "#country-button");
        break;
      case "name-edit":
        $button = $page->find("css", "#username-button");
        break;
      case "avatar-edit":
        $button = $page->find("css", "#avatar-button");
        break;
      case "save-edit":
        $button = $page->find("css", ".save-button");
        break;
      default:
        Assert::assertTrue(false);
    }
    Assert::assertNotNull($button, "button " . $arg1 . " not found");
    $button->click();

  }

  /**
   * @When /^I trigger Facebook login with auth_type '([^']*)'$/
   */
  public function iTriggerFacebookLogin($arg1)
  {
    $this->assertElementOnPage('#btn-login');
    $this->iClickTheButton('login');
    $this->assertPageAddress('/pocketcode/login');
    $this->assertElementOnPage('#btn-login_facebook');
    $this->getSession()->executeScript('document.getElementById("facebook_auth_type").type = "text";');
    $this->getSession()->getPage()->findById('facebook_auth_type')->setValue($arg1);

  }

  /**
   * @Then /^I should see marked "([^"]*)"$/
   */
  public function iShouldSeeMarked($arg1)
  {
    $page = $this->getSession()->getPage();
    $program = $page->find("css", $arg1);
    if (!$program->hasClass('visited-program'))
    {
      assertTrue(false);
    }
  }


  /**
   * @When /^I click Facebook login link$/
   */
  public function iClickFacebookLoginLink()
  {
    if ($this->use_real_oauth_javascript_code)
    {
      $this->clickLink('btn-login_facebook');
    }
    else
    {
      $this->setFacebookFakeData();
      $this->clickFacebookFakeButton();
    }
  }

  /**
   * @When /^I trigger Google login with approval prompt "([^"]*)"$/
   */

  public function iTriggerGoogleLogin($arg1)
  {
    $this->assertElementOnPage('#btn-login');
    $this->iClickTheButton('login');
    $this->assertPageAddress('/pocketcode/login');
    $this->getSession()->wait(200);
    $this->assertElementOnPage('#btn-login_google');
    $this->getSession()->executeScript('document.getElementById("gplus_approval_prompt").type = "text";');
    $this->getSession()->wait(200);
    $this->getSession()->getPage()->findById('gplus_approval_prompt')->setValue($arg1);
  }

  /**
   * @When /^I click Google login link "([^"]*)"$/
   */
  public function iClickGoogleLoginLink($arg1)
  {
    if ($this->use_real_oauth_javascript_code)
    {
      $this->clickLink('btn-login_google');
      if ($arg1 == 'twice')
      {
        $this->clickLink('btn-login_google');
      }
    }
    else
    {
      $this->setGooglePlusFakeData();
      $this->clickGooglePlusFakeButton();
    }
  }

  /**
   * @Then /^there should be "([^"]*)" programs in the database$/
   */
  public function thereShouldBeProgramsInTheDatabase($arg1)
  {
    /**
     * @var \Catrobat\AppBundle\Entity\ProgramManager
     */
    $program_manager = $this->kernel->getContainer()->get('programmanager');
    $programs = $program_manager->findAll();

    Assert::assertEquals($arg1, count($programs));
  }

  /**
   * @Given /^there are starter programs:$/
   */
  public function thereAreStarterPrograms(TableNode $table)
  {
    /*
   * @var $program \Catrobat\AppBundle\Entity\Program
   * @var $starter \Catrobat\AppBundle\Entity\StarterCategory
   */
    $em = $this->kernel->getContainer()->get('doctrine')->getManager();

    $starter = new StarterCategory();
    $starter->setName('Games');
    $starter->setAlias('games');
    $starter->setOrder(1);

    $programs = $table->getHash();
    $count = count($programs);
    for ($i = 0; $i < $count; ++$i)
    {
      $user = $em->getRepository('AppBundle:User')->findOneBy([
        'username' => $programs[$i]['owned by'],
      ]);
      $program = new Program();
      $program->setUser($user);
      $program->setName($programs[$i]['name']);
      $program->setDescription($programs[$i]['description']);
      $program->setViews($programs[$i]['views']);
      $program->setDownloads($programs[$i]['downloads']);
      $program->setUploadedAt(new \DateTime($programs[$i]['upload time'], new \DateTimeZone('UTC')));
      $program->setRemixMigratedAt(null);
      $program->setCatrobatVersion(1);
      $program->setCatrobatVersionName($programs[$i]['version']);
      $program->setLanguageVersion(1);
      $program->setUploadIp('127.0.0.1');
      $program->setFilesize(0);
      $program->setVisible(isset($programs[$i]['visible']) ? $programs[$i]['visible'] == 'true' : true);
      $program->setUploadLanguage('en');
      $program->setApproved(false);
      $program->setRemixRoot(true);
      $program->setRemixMigratedAt(new \DateTime());
      $em->persist($program);

      $starter->addProgram($program);
    }

    $em->persist($starter);
    $em->flush();
  }

  /**
   * @Given /^there are mediapackages:$/
   */
  public function thereAreMediapackages(TableNode $table)
  {
    /**
     * @var $em EntityManager
     */
    $em = $this->kernel->getContainer()->get('doctrine')->getManager();
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
   * @Given /^there are mediapackage categories:$/
   */
  public function thereAreMediapackageCategories(TableNode $table)
  {
    /**
     * @var $em EntityManager
     */
    $em = $this->kernel->getContainer()->get('doctrine')->getManager();
    $categories = $table->getHash();
    foreach ($categories as $category)
    {
      $new_category = new MediaPackageCategory();
      $new_category->setName($category['name']);
      $package = $em->getRepository('\Catrobat\AppBundle\Entity\MediaPackage')->findOneBy(['name' => $category['package']]);
      $new_category->setPackage([$package]);
      $em->persist($new_category);
    }
    $em->flush();
  }

  /**
   * @Given /^there are mediapackage files:$/
   */
  public function thereAreMediapackageFiles(TableNode $table)
  {
    /**
     * @var $em        EntityManager
     * @var $file_repo MediaPackageFileRepository
     */
    $em = $this->kernel->getContainer()->get('doctrine')->getManager();
    $file_repo = $this->kernel->getContainer()->get('mediapackagefilerepository');
    $files = $table->getHash();
    foreach ($files as $file)
    {
      $new_file = new MediaPackageFile();
      $new_file->setName($file['name']);
      $new_file->setDownloads(0);
      $new_file->setExtension($file['extension']);
      $new_file->setActive($file['active']);
      $category = $em->getRepository('\Catrobat\AppBundle\Entity\MediaPackageCategory')->findOneBy(['name' => $file['category']]);
      $new_file->setCategory($category);
      $new_file->setAuthor($file['author']);

      $file_repo->saveMediaPackageFile(new File(self::MEDIAPACKAGE_DIR . $file['id'] . '.' . $file['extension']), $file['id'], $file['extension']);

      $em->persist($new_file);
    }
    $em->flush();
  }

  /**
   * @Given /^there are program download statistics:$/
   */
  public function thereAreProgramDownloadStatistics(TableNode $table)
  {
    /**
     * @var $em EntityManager
     */
    $em = $this->kernel->getContainer()->get('doctrine')->getManager();
    $program_stats = $table->getHash();
    $count = count($program_stats);
    for ($i = 0; $i < $count; ++$i)
    {
      $program = $this->kernel->getContainer()->get('programmanager')->find($program_stats[$i]['program_id']);
      @$config = [
        'downloaded_at' => $program_stats[$i]['downloaded_at'],
        'ip'            => $program_stats[$i]['ip'],
        'country_code'  => $program_stats[$i]['country_code'],
        'country_name'  => $program_stats[$i]['country_name'],
        'locality'      => @$program_stats[$i]['locality'],
        'user_agent'    => @$program_stats[$i]['user_agent'],
        'username'      => @$program_stats[$i]['username'],
        'referrer'      => @$program_stats[$i]['referrer'],
      ];
      $program_statistics = new ProgramDownloads();
      $program_statistics->setProgram($program);
      $program_statistics->setDownloadedAt(new \DateTime($config['downloaded_at']) ?: new DateTime());
      $program_statistics->setIp(isset($config['ip']) ? $config['ip'] : '88.116.169.222');
      $program_statistics->setCountryCode(isset($config['country_code']) ? $config['country_code'] : 'AT');
      $program_statistics->setCountryName(isset($config['country_name']) ? $config['country_name'] : 'Austria');
      $program_statistics->setUserAgent(isset($config['user_agent']) ? $config['user_agent'] : 'okhttp');
      $program_statistics->setReferrer(isset($config['referrer']) ? $config['referrer'] : 'Facebook');

      if (isset($config['username']))
      {
        $user = $this->kernel->getContainer()->get('usermanager')->findOneBy(['username' => $config['username']]);
        $program_statistics->setUser($user);
      }

      $em->persist($program_statistics);

      $program->addProgramDownloads($program_statistics);
      $em->persist($program);
    }
    $em->flush();
  }

  /**
   * @Given /^there are reportable programs:$/
   */
  public function thereAreReportablePrograms(TableNode $table)
  {
    $em = $this->kernel->getContainer()->get('doctrine')->getManager();
    $programs = $table->getHash();
    $count = count($programs);
    for ($i = 0; $i < $count; ++$i)
    {
      $user = $em->getRepository('AppBundle:User')->findOneBy([
        'username' => $programs[$i]['owned by'],
      ]);
      $program = new Program();
      $program->setUser($user);
      $program->setName($programs[$i]['name']);
      $program->setDescription("Default Description");
      $program->setViews(1337);
      $program->setDownloads(1337);
      $program->setApkDownloads(1337);
      $program->setApkStatus(isset($programs[$i]['apk_ready']) ? ($programs[$i]['apk_ready'] === 'true' ? Program::APK_READY : Program::APK_NONE) : Program::APK_NONE);
      $program->setUploadedAt(new \DateTime("01.01.2013 12:00", new \DateTimeZone('UTC')));
      $program->setRemixMigratedAt(null);
      $program->setCatrobatVersion(1);
      $program->setCatrobatVersionName("0.8.5");
      $program->setLanguageVersion(isset($programs[$i]['language version']) ? $programs[$i]['language version'] : 1);
      $program->setUploadIp('127.0.0.1');
      $program->setFilesize(0);
      $program->setVisible(isset($programs[$i]['visible']) ? $programs[$i]['visible'] == 'true' : true);
      $program->setUploadLanguage('en');
      $program->setApproved(false);
      $program->setFbPostUrl(isset($programs[$i]['fb_post_url']) ? $programs[$i]['fb_post_url'] : '');
      $program->setRemixRoot(isset($programs[$i]['remix_root']) ? $programs[$i]['remix_root'] == 'true' : true);

      if (isset($programs[$i]['tags_id']) && $programs[$i]['tags_id'] != null)
      {
        $tag_repo = $em->getRepository('AppBundle:Tag');
        $tags = explode(',', $programs[$i]['tags_id']);
        foreach ($tags as $tag_id)
        {
          $tag = $tag_repo->find($tag_id);
          $program->addTag($tag);
        }
      }

      if (isset($programs[$i]['extensions']) && $programs[$i]['extensions'] != null)
      {
        $extension_repo = $em->getRepository('AppBundle:Extension');
        $extensions = explode(',', $programs[$i]['extensions']);
        foreach ($extensions as $extension_name)
        {
          $extension = $extension_repo->findOneByName($extension_name);
          $program->addExtension($extension);
        }
      }

      if ($program->getApkStatus() == Program::APK_READY)
      {
        /* @var $apkrepository \Catrobat\AppBundle\Services\ApkRepository */
        $apkrepository = $this->kernel->getContainer()->get('apkrepository');
        $temppath = tempnam(sys_get_temp_dir(), 'apktest');
        copy(self::FIXTUREDIR . 'test.catrobat', $temppath);
        $apkrepository->save(new File($temppath), $i);

        $file_repo = $this->kernel->getContainer()->get('filerepository');
        $file_repo->saveProgramfile(new File(self::FIXTUREDIR . 'test.catrobat'), $i);
      }

      $em->persist($program);
    }
    $em->flush();
  }


  /**
   * @When /^I download "([^"]*)"$/
   */
  public function iDownload($arg1)
  {
    $this->getClient()->request('GET', $arg1);
  }

  /**
   * @Then /^I should receive a "([^"]*)" file$/
   */
  public function iShouldReceiveAFile($extension)
  {
    $content_type = $this->getClient()->getResponse()->headers->get('Content-Type');
    Assert::assertEquals('image/' . $extension, $content_type);
  }

  /**
   * @Given /^the response code should be "([^"]*)"$/
   */
  public function theResponseCodeShouldBe($code)
  {
    $response = $this->getClient()->getResponse();
    Assert::assertEquals($code, $response->getStatusCode(), 'Wrong response code. ' . $response->getContent());
  }

  /**
   * @Then /^the media file "([^"]*)" must have the download url "([^"]*)"$/
   */
  public function theMediaFileMustHaveTheDownloadUrl($id, $file_url)
  {
    $mediafile = $this->getSession()->getPage()->find("css", ".mediafile-" . $id . " a");
    Assert::assertNotNull($mediafile, "Mediafile not found!");
    $link = $mediafile->getAttribute("href");
    Assert::assertTrue(is_int(strpos($link, $file_url)));
  }

  /**
   * @Then /^I should see media file with id "([^"]*)"$/
   */
  public function iShouldSeeMediaFileWithId($id)
  {
    $link = $this->getSession()->getPage()->find("css", ".mediafile-" . $id);
    Assert::assertNotNull($link);
  }

  /**
   * @Then /^I should not see media file with id "([^"]*)"$/
   */
  public function iShouldNotSeeMediaFileWithId($id)
  {
    $link = $this->getSession()->getPage()->find("css", ".mediafile-" . $id);
    Assert::assertNotNull($link);
  }

  /**
   * @Then /^the link of "([^"]*)" should open "([^"]*)"$/
   */
  public function theLinkOfShouldOpen($identifier, $url_type)
  {
    switch ($identifier)
    {
      case "image":
        $class_name = "image-container";
        break;

      case "download":
        $class_name = "download-container";
        break;

      default:
        break;
    }
    Assert::assertTrue(strlen($class_name) > 0);

    switch ($url_type)
    {
      case "download":
        $url_text = "pocketcode/download";
        break;

      case "popup":
        $url_text = "program.showUpdateAppPopup";
        break;

      default:
        break;
    }
    Assert::assertTrue(strlen($url_text) > 0);

    $selector = "." . $class_name . " a";
    $href_value = $this->getSession()->getPage()->find('css', $selector)->getAttribute('href');
    Assert::assertTrue(is_int(strpos($href_value, $url_text)));
  }

  /**
   * @Then /^I see the "([^"]*)" popup$/
   */
  public function iSeeThePopup($arg1)
  {
    switch ($arg1)
    {
      case "update app":
        $this->assertElementOnPage("#popup-info");
        $this->assertElementOnPage("#popup-background");
        break;

      default:
        break;
    }
  }

  /**
   * @Then /^I see not the "([^"]*)" popup$/
   */
  public function iSeeNotThePopup($arg1)
  {
    switch ($arg1)
    {
      case "update app":
        $this->assertElementNotOnPage("#popup-info");
        $this->assertElementNotOnPage("#popup-background");
        break;

      default:
        break;
    }
  }

  /**
   * @Then /^I click the program download button$/
   */
  public function iClickTheProgramDownloadButton()
  {
    $this->iClick("#url-download");
  }


  /**
   * @Then /^I click the program image$/
   */
  public function iClickTheProgramImage()
  {
    $this->iClick("#url-image");
  }

  /**
   * @Then /^I click on the program popup background$/
   */
  public function iClickOnTheProgramPopupBackground()
  {
    $this->iClick("#popup-background");
  }

  /**
   * @Then /^I click on the program popup button$/
   */
  public function iClickOnTheProgramPopupButton()
  {
    $this->iClick("#btn-close-popup");
  }

  /**
   * @Given /^I am browsing with my pocketcode app$/
   */
  public function iAmBrowsingWithMyPocketcodeApp()
  {
    $this->getMink()->setDefaultSessionName("mobile");
  }

  /**
   * @When /^I want to download the apk file of "([^"]*)"$/
   */
  public function iWantToDownloadTheApkFileOf($arg1)
  {
    $pm = $this->kernel->getContainer()->get('programmanager');
    $program = $pm->findOneByName($arg1);
    if ($program === null)
    {
      throw new \Exception('Program not found: ' + $arg1);
    }
    $router = $this->kernel->getContainer()->get('router');
    $url = $router->generate('ci_download', ['id' => $program->getId(), 'flavor' => 'pocketcode']);
    $this->getClient()->request('GET', $url, [], []);
  }

  /**
   * @Then /^I should receive the apk file$/
   */
  public function iShouldReceiveTheApkFile()
  {
    $content_type = $this->getClient()->getResponse()->headers->get('Content-Type');
    $code = $this->getClient()->getResponse()->getStatusCode();
    Assert::assertEquals(200, $code);
    Assert::assertEquals('application/vnd.android.package-archive', $content_type);
  }

  /**
   * @Then /^I should receive an application file$/
   */
  public function iShouldReceiveAnApplicationFile()
  {
    $content_type = $this->getClient()->getResponse()->headers->get('Content-Type');
    $code = $this->getClient()->getResponse()->getStatusCode();
    Assert::assertEquals(200, $code);
    Assert::assertEquals('application/zip', $content_type);
  }

  /**
   * @When /^I should see the video available at "([^"]*)"$/
   */
  public function iShouldSeeElementWithIdWithSrc($url)
  {
    $page = $this->getSession()->getPage();
    $video = $page->find('css', '#youtube-help-video');
    Assert::assertNotNull($video, "Video not found on tutorial page!");
    Assert::assertTrue(strpos($video->getAttribute('src'), $url) !== false &&
      strpos($video->getAttribute('src'), "&origin=http://localhost") !== false);
  }


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////// Getter & Setter

  /**
   * @return \Symfony\Bundle\FrameworkBundle\Client
   */
  public function getClient()
  {
    if ($this->client == null)
    {
      $this->client = $this->kernel->getContainer()->get('test.client');
    }

    return $this->client;
  }

  /**
   * @When /^I switch to popup window$/
   */
  public function iSwitchToPopupWindow()
  {
    $page = $this->getSession()->getPage();
    $window_names = $this->getSession()->getDriver()->getWindowNames();
    foreach ($window_names as $name)
    {
      echo $name;
      if ($page->find('css', '#facebook') || $page->find('css', '.google-header-bar centered')
        || $page->find('css', '#approval_container') || $page->find('css', '#gaia_firstform')
      )
      {
        break;
      }
      $this->getSession()->switchToWindow($name);
    }
  }

  /**
   * @Then /^I log in to Facebook with valid credentials$/
   */
  public function iLogInToFacebookWithEmailAndPassword()
  {
    if ($this->use_real_oauth_javascript_code)
    {
      $mail = $this->getParameterValue('facebook_testuser_mail');
      $pw = $this->getParameterValue('facebook_testuser_pw');
      echo 'Login with mail address ' . $mail . ' and pw ' . $pw . "\n";
      $page = $this->getSession()->getPage();
      if ($page->find('css', '#facebook') && $page->find('css', '#login_form'))
      {
        echo 'facebook login form appeared' . "\n";
        $page->fillField('email', $mail);
        $page->fillField('pass', $pw);
        $button = $page->findById('u_0_2');
        Assert::assertTrue($button != null);
        $button->press();
      }
      else
      {
        if ($page->find('css', '#facebook') && $page->find('css', '#u_0_1'))
        {
          echo 'facebook reauthentication login form appeared' . "\n";
          $page->fillField('pass', $pw);
          $button = $page->findById('u_0_0');
          Assert::assertTrue($button != null);
          $button->press();
        }
        else
        {
          Assert::assertTrue(false, 'No Facebook form appeared!' . "\n");
        }
      }
      $this->getSession()->switchToWindow(null);

      $this->iSwitchToPopupWindow();
      if ($page->find('css', '#facebook') && $page->find('css', '._1a_q'))
      {
        echo 'facebook authentication login form appeared' . "\n";
        $button = $page->findButton('__CONFIRM__');
        Assert::assertTrue($button != null);
        $button->press();
        $this->getSession()->switchToWindow(null);
      }
    }
    else
    {
      //simulate Facebook login by faking Javascript code and server responses from FakeOAuthService
      $this->setFacebookFakeData();
      $this->clickFacebookFakeButton();
    }
  }

  /**
   * @Then /^I log in to Google with valid credentials$/
   */
  public function iLogInToGoogleWithEmailAndPassword()
  {
    if ($this->use_real_oauth_javascript_code)
    {
      $mail = $this->getParameterValue('google_testuser_mail');
      $pw = $this->getParameterValue('google_testuser_pw');
      echo 'Login with mail address ' . $mail . ' and pw ' . $pw . "\n";
      $page = $this->getSession()->getPage();
      if ($page->find('css', '#approval_container') &&
        $page->find('css', '#submit_approve_access')
      )
      {
        $this->approveGoogleAccess();
      }
      else
      {
        if ($page->find('css', '.google-header-bar centered') &&
          $page->find('css', '.signin-card clearfix')
        )
        {
          $this->signInWithGoogleEMailAndPassword($mail, $pw);
        }
        else
        {
          if ($page->find('css', '#gaia_firstform') &&
            $page->find('css', '#Email') &&
            $page->find('css', '#Passwd-hidden')
          )
          {
            $this->signInWithGoogleEMail($mail, $pw);
          }
          else
          {
            Assert::assertTrue(false, 'No Google form appeared!' . "\n");
          }
        }
      }
    }
    else
    {
      $this->setGooglePlusFakeData();
      $this->clickGooglePlusFakeButton();
    }

  }

  private function approveGoogleAccess()
  {
    echo 'Google Approve Access form appeared' . "\n";
    $page = $this->getSession()->getPage();
    $button = $page->findById('submit_approve_access');
    Assert::assertTrue($button != null);
    $button->press();
    $this->getSession()->switchToWindow(null);
  }

  private function signInWithGoogleEMailAndPassword($mail, $pw)
  {
    echo 'Google login form with E-Mail and Password appeared' . "\n";
    $page = $this->getSession()->getPage();

    $page->fillField('Email', $mail);
    $page->fillField('Passwd', $pw);
    $button = $page->findById('signIn');
    Assert::assertTrue($button != null);
    $button->press();
    $this->approveGoogleAccess();
  }

  /**
   * @Then /^there is a user in the database:$/
   */
  public function thereIsAUserInTheDatabase(TableNode $table)
  {
    /**
     * @var $user_manager \Catrobat\AppBundle\Entity\UserManager
     */
    $user_manager = $this->kernel->getContainer()->get('usermanager');
    $users = $table->getHash();
    $user = null;
    $count = count($users);
    for ($i = 0; $i < $count; $i++)
    {
      $user = $user_manager->findUserByUsername($users[$i]["name"]);
      Assert::assertNotNull($user);
      Assert::assertTrue($user->getUsername() == $users[$i]["name"],
        'Name wrong' . $users[$i]["name"] . 'expected, but ' . $user->getUsername() . ' found.');
      Assert::assertTrue($user->getEmail() == $users[$i]["email"],
        'E-Mail wrong' . $users[$i]["email"] . 'expected, but ' . $user->getEmail() . ' found.');
      Assert::assertTrue($user->getCountry() == $users[$i]["country"],
        'Country wrong' . $users[$i]["country"] . 'expected, but ' . $user->getCountry() . ' found.');
      if ($user->getFacebookUid() != '')
      {
        Assert::assertTrue($user->getFacebookAccessToken() != '', 'no Facebook access token present');
        Assert::assertTrue($user->getFacebookUid() == $users[$i]["facebook_uid"], 'Facebook UID wrong');
        Assert::assertTrue($user->getFacebookName() == $users[$i]["facebook_name"], 'Facebook name wrong');
      }
      if ($user->getGplusUid() != '')
      {
        Assert::assertTrue($user->getGplusAccessToken() != '', 'no GPlus access token present');
        Assert::assertTrue($user->getGplusIdToken() != '', 'no GPlus id token present');
        Assert::assertTrue($user->getGplusRefreshToken() != '', 'no GPlus refresh token present');
        Assert::assertTrue($user->getGplusUid() == $users[$i]["google_uid"], 'Google UID wrong');
        Assert::assertTrue($user->getGplusName() == $users[$i]["google_name"], 'Google name wrong');
      }
    }
  }

  private function signInWithGoogleEMail($mail, $pw)
  {
    echo 'Google Signin with E-Mail form appeared' . "\n";
    $page = $this->getSession()->getPage();

    $page->fillField('Email', $mail);
    $button = $page->findById('next');
    Assert::assertTrue($button != null);
    $button->press();
    if ($page->find('css', '#gaia_firstform') &&
      $page->find('css', '#Email-hidden') &&
      $page->find('css', '#Passwd')
    )
    {
      $this->signInWithGooglePassword($pw);
    }
  }

  private function signInWithGooglePassword($pw)
  {
    echo 'Google Signin with Password form appeared' . "\n";
    $page = $this->getSession()->getPage();

    $page->fillField('Passwd', $pw);
    $button = $page->findById('signIn');
    Assert::assertTrue($button != null);
    $button->press();
    if ($page->find('css', '#approval_container') &&
      $page->find('css', '#submit_approve_access')
    )
    {
      $this->approveGoogleAccess();
    }
  }

  private function setFacebookFakeData()
  {
    //simulate Facebook login by faking Javascript code and server responses from FakeOAuthService
    $session = $this->getSession();
    $session->wait(2000, '(0 === jQuery.active)');
    $session->evaluateScript("$('#btn-facebook-testhook').removeClass('hidden');");
    $session->evaluateScript("$('#id_oauth').val(105678789764016);");
    $session->evaluateScript("$('#email_oauth').val('pocket_zlxacqt_tester@tfbnw.net');");
    $session->evaluateScript("$('#locale_oauth').val('en_US');");
  }

  private function clickFacebookFakeButton()
  {
    $page = $this->getSession()->getPage();
    $button = $page->findButton('btn-facebook-testhook');
    Assert::assertNotNull($button, 'button not found');
    $button->press();
  }

  private function setGooglePlusFakeData()
  {
    //simulate Google+ login by faking Javascript code and server responses from FakeOAuthService
    $session = $this->getSession();
    $session->wait(2000, '(0 === jQuery.active)');
    $session->evaluateScript("$('#btn-gplus-testhook').removeClass('hidden');");
    $session->evaluateScript("$('#id_oauth').val('105155320106786463089');");
    $session->evaluateScript("$('#email_oauth').val('pocketcodetester@gmail.com');");
    $session->evaluateScript("$('#locale_oauth').val('de');");
  }

  private function clickGooglePlusFakeButton()
  {
    $page = $this->getSession()->getPage();
    $button = $page->findButton('btn-gplus-testhook');
    Assert::assertNotNull($button, 'button not found');
    $button->press();
  }

  /**
   * @When /^I logout$/
   */
  public function iLogout()
  {
    $this->getSession()->getPage()->find("css", ".btn show-nav-dropdown")->click();
    $this->assertElementOnPage(".img-author-big");
    $this->getSession()->getPage()->find("css", ".img-author-big")->click();

  }

  /**
   * @Then /^I should not be logged in$/
   */
  public function iShouldNotBeLoggedIn()
  {
    $this->getSession()->wait(1000, 'window.location.href.search("profile") == -1');
    $this->assertElementOnPage("#logo");
    $this->assertElementOnPage("#btn-login");
    $this->assertElementNotOnPage("#nav-dropdown");
  }

  /**
   * @When /^I wait for a second$/
   */
  public function iWaitForASecond()
  {
    $this->getSession()->wait(1000);
  }

  /**
   * @Then /^I choose the username '([^']*)' and check button activations$/
   */
  public function iChooseTheUsernameTestingButtonEnabled($arg1)
  {
    $this->getSession()->wait(300);
    $page = $this->getSession()->getPage();

    $button = $page->findById('btn_oauth_username');
    Assert::assertNotNull($button);
    Assert::assertTrue($button->hasAttribute('disabled'));

    $page->fillField('dialog_oauth_username_input', $arg1);
    Assert::assertFalse($button->hasAttribute('disabled'));

    $page->fillField('dialog_oauth_username_input', '');
    Assert::assertTrue($button->hasAttribute('disabled'));

    $page->fillField('dialog_oauth_username_input', $arg1);
    $button->press();
    if (!$arg1 === self::ALREADY_IN_DB_USER)
    {
      $this->getSession()->wait(1000, 'window.location.href.search("login") == -1');
    }
  }

  /**
   * @Then /^I choose the username '([^']*)'$/
   */
  public function iChooseTheUsername($arg1)
  {
    $this->getSession()->wait(300);
    $page = $this->getSession()->getPage();

    $button = $page->findById('btn_oauth_username');
    Assert::assertNotNull($button);
    Assert::assertTrue($button->hasAttribute('disabled'));

    $page->fillField('dialog_oauth_username_input', $arg1);
    Assert::assertFalse($button->hasAttribute('disabled'));

    $button->press();
    if (!$arg1 === self::ALREADY_IN_DB_USER)
    {
      $this->getSession()->wait(1000, 'window.location.href.search("login") == -1');
    }
    $this->getSession()->wait(500);
  }

  private function getParameterValue($name)
  {
    $myfile = fopen("app/config/parameters.yml", "r") or die("Unable to open file!");
    while (!feof($myfile))
    {
      $line = fgets($myfile);
      if (strpos($line, $name) != false)
      {
        fclose($myfile);

        return substr(trim($line), strpos(trim($line), ':') + 2);
      }
    }
    fclose($myfile);
    Assert::assertTrue(false, 'No entry found in parameters.yml!');

    return false;
  }

  private function setOauthServiceParameter($value)
  {
    $new_content = 'parameters:' . chr(10) . '    oauth_use_real_service: ' . $value;
    file_put_contents("app/config/parameters_test.yml", $new_content);
  }

  /**
   * @Then /^I should see "([^"]*)" "([^"]*)" tutorial banners$/
   */
  public function iShouldSeeTutorialBanners($count, $view)
  {
    if ($view == "desktop")
    {
      for ($i = 1; ; $i++)
      {
        $img = $this->getSession()->getPage()->findById('tutorial-' . $i);
        if ($img == null)
        {
          break;
        }
      }
      Assert::assertEquals($count, $i - 1);
    }
    elseif ($view == "mobile")
    {
      for ($i = 1; ; $i++)
      {
        $img = $this->getSession()->getPage()->findById('tutorial-mobile-' . $i);
        if ($img == null)
        {
          break;
        }
      }
      Assert::assertEquals($count, $i - 1);
    }
    else
    {
      Assert::assertTrue(false);
    }
  }

  /**
   * @When /^I click on the "([^"]*)" banner$/
   */
  public function iClickOnTheBanner($numb)
  {
    switch ($numb)
    {
      case 'first':
        $this->iClick("#tutorial-1");
        break;
      case 'second':
        $this->iClick("#tutorial-2");
        break;
      case 'third':
        $this->iClick("#tutorial-3");
        break;
      case 'fourth':
        $this->iClick("#tutorial-4");
        break;
      case 'fifth':
        $this->iClick("#tutorial-5");
        break;
      case 'sixth':
        $this->iClick("#tutorial-6");
        break;
      default:
        Assert::assertTrue(false);
        break;
    }
  }

  /**
   * @Given /^following programs are featured:$/
   */
  public function followingProgramsAreFeatured(TableNode $table)
  {
    $em = $this->kernel->getContainer()->get('doctrine')->getManager();
    $featured = $table->getHash();
    for ($i = 0; $i < count($featured); ++$i)
    {
      $featured_entry = new FeaturedProgram();

      if ($featured[$i]['program'] != "")
      {
        $program = $this->kernel->getContainer()->get('programmanager')->findOneByName($featured[$i]['program']);
        $featured_entry->setProgram($program);
      }
      else
      {
        $url = $featured[$i]['url'];
        $featured_entry->setUrl($url);
      }

      $featured_entry->setActive($featured[$i]['active'] == 'yes');
      $featured_entry->setImageType('jpg');
      $featured_entry->setPriority($featured[$i]['priority']);
      $featured_entry->setForIos(isset($featured[$i]['ios_only']) ? $featured[$i]['ios_only'] == 'yes' : false);
      $em->persist($featured_entry);
    }
    $em->flush();
  }

  /**
   * @Then /^I should see the slider with the values "([^"]*)"$/
   */
  public function iShouldSeeTheSliderWithTheValues($values)
  {
    $slider_items = explode(',', $values);
    $owl_items = $this->getSession()->getPage()->findAll('css', 'div.carousel-item > a');
    Assert::assertEquals(count($owl_items), count($slider_items));

    for ($index = 0; $index < count($owl_items); $index++)
    {
      $url = $slider_items[$index];
      if (strpos($url, "http://") !== 0)
      {
        $program = $this->kernel->getContainer()->get('programmanager')->findOneByName($url);
        Assert::assertNotNull($program);
        Assert::assertNotNull($program->getId());
        $url = $this->kernel->getContainer()->get('router')->generate('program', ['id' => $program->getId(), 'flavor' => 'pocketcode']);
      }

      $feature_url = $owl_items[$index]->getAttribute('href');
      Assert::assertContains($url, $feature_url);
    }
  }

  /**
   * @When /^I press on the tag "([^"]*)"$/
   */
  public function iPressOnTheTag($arg1)
  {
    $arg1 = '#' . $arg1;
    $this->assertSession()->elementExists('css', $arg1);

    $this
      ->getSession()
      ->getPage()
      ->find('css', $arg1)
      ->click();
  }

  /**
   * @When /^I press on the extension "([^"]*)"$/
   */
  public function iPressOnTheExtension($arg1)
  {
    $arg1 = '#' . $arg1;
    $this->assertSession()->elementExists('css', $arg1);

    $this
      ->getSession()
      ->getPage()
      ->find('css', $arg1)
      ->click();
  }

  /**
   * @Given /^I search for "([^"]*)" with the searchbar$/
   */
  public function iSearchForWithTheSearchbar($arg1)
  {
    $this->iClick('.search-icon-header');
    $this->fillField('search-input-header', $arg1);
    $this->iClick('.catro-search-button');
  }

  /**
   * @Then /^I should see the Facebook Like button in the header$/
   */
  public function iShouldSeeTheFacebookLikeButtonInTheHeader()
  {
    $like_button = $this->getSession()->getPage()->find('css', '.fb-like');
    Assert::assertTrue($like_button != null && $like_button->isVisible(), "The Facebook Like Button is not visible!");
    Assert::assertTrue($like_button->getParent()->getParent()->getParent()->getTagName() == 'nav', "Parent is not header element");
  }

  /**
   * @Then /^I should see the Google Plus 1 button in the header$/
   */
  public function iShouldSeeTheGoogleButtonInTheHeader()
  {
    $plus_one_button = $this->getSession()->getPage()->findById('___plusone_0');
    Assert::assertTrue($plus_one_button != null && $plus_one_button->isVisible(), "The Google +1 Button is not visible!");
    Assert::assertTrue($plus_one_button->getParent()->getParent()->getParent()->getTagName() == 'nav', "Parent is not header element");
  }

  /**
   * @Then /^I should see the Facebook Like button on the bottom of the program page$/
   */
  public function iShouldSeeTheFacebookLikeButtonOnTheBottomOfTheProgramPage()
  {
    $like_button = $this->getSession()->getPage()->find('css', '.fb-like');
    Assert::assertTrue($like_button != null && $like_button->isVisible(), "The Facebook Like Button is not visible!");
    Assert::assertTrue($like_button->getParent()->getParent()->getTagName() == 'div', "Parent is not header element");
  }

  /**
   * @Then /^I should see the Google Plus (\d+) button on the bottom of the program page$/
   */
  public function iShouldSeeTheGooglePlusButtonOnTheBottomOfTheProgramPage($arg1)
  {
    $plus_one_button = $this->getSession()->getPage()->findById('___plusone_0');
    Assert::assertTrue($plus_one_button != null && $plus_one_button->isVisible(), "The Google +1 Button is not visible!");
    Assert::assertTrue($plus_one_button->getParent()->getParent()->getTagName() == 'div', "Parent is not header element");
  }

  /**
   * @Then /^I should see the Facebook Share button$/
   */
  public function iShouldSeeTheFacebookShareButton()
  {
    $share_button = $this->getSession()->getPage()->findById('share-facebook');
    Assert::assertTrue($share_button != null && $share_button->isVisible(), "The Facebook share button is not visible!");
  }

  /**
   * @Then /^I should see the Google Plus share button$/
   */
  public function iShouldSeeTheGooglePlusShareButton()
  {
    $share_button = $this->getSession()->getPage()->findById('share-gplus');
    Assert::assertTrue($share_button != null && $share_button->isVisible(), "The Google+ share button is not visible!");
  }

  /**
   * @Then /^I should see the Twitter share button$/
   */
  public function iShouldSeeTheTwitterShareButton()
  {
    $share_button = $this->getSession()->getPage()->findById('share-twitter');
    Assert::assertTrue($share_button != null && $share_button->isVisible(), "The Twitter share button is not visible!");
  }

  /**
   * @Then /^I should see the Mail share button$/
   */
  public function iShouldSeeTheMailShareButton()
  {
    $share_button = $this->getSession()->getPage()->findById('share-email');
    Assert::assertTrue($share_button != null && $share_button->isVisible(), "The E-Mail share button is not visible!");
  }

  /**
   * @Then /^I should see the WhatsApp share button$/
   */
  public function iShouldSeeTheWhatsappShareButton()
  {
    $share_button = $this->getSession()->getPage()->findById('share-whatsapp');
    Assert::assertTrue($share_button != null && $share_button->isVisible(), "The WhatsApp share button is not visible!");
  }

  /**
   * @Then /^I should see the logout button$/
   */
  public function iShouldSeeTheLogoutButton()
  {
    $logout_button = $this->getSession()->getPage()->findById('btn-logout');
    Assert::assertTrue($logout_button != null && $logout_button->isVisible(), "The Logout button is not visible!");
  }

  /**
   * @Then /^I should see the profile button$/
   */
  public function iShouldSeeTheProfileButton()
  {
    $profile_button = $this->getSession()->getPage()->findById('btn-profile');
    Assert::assertTrue($profile_button != null && $profile_button->isVisible(), "The profile button is not visible!");
  }

  /**
   * @Then /^the href with id "([^"]*)" should be void$/
   */
  public function theHrefWithIdShouldBeVoid($arg1)
  {
    $button = $this->getSession()->getPage()->findById($arg1);
    Assert::assertEquals($button->getAttribute('href'), 'javascript:void(0)');
  }

  /**
   * @Then /^the href with id "([^"]*)" should not be void$/
   */
  public function theHrefWithIdShouldNotBeVoid($arg1)
  {
    $button = $this->getSession()->getPage()->findById($arg1);
    Assert::assertNotEquals($button->getAttribute('href'), 'javascript:void(0)');
  }

  /**
   * @When /^I get page content$/
   */
  public function iGetPageContent()
  {
    var_dump($this->getSession()->getPage()->getContent());
    die;
  }

  /**
   * @Then /^There should be one database entry with type is "([^"]*)" and "([^"]*)" is "([^"]*)"$/
   */
  public function thereShouldBeOneDatabaseEntryWithTypeIsAndIs($type_name, $name_id, $id_or_value)
  {
    $em = $this->kernel->getContainer()->get('doctrine')->getManager();
    $clicks = $em->getRepository('AppBundle:ClickStatistic')->findAll();
    Assert::assertEquals(1, count($clicks), "No database entry found!");

    $click = $clicks[0];

    Assert::assertEquals($type_name, $click->getType());

    switch ($name_id)
    {
      case "tag_id":
        Assert::assertEquals($id_or_value, $click->getTag()->getId());
        break;
      case "extension_id":
        Assert::assertEquals($id_or_value, $click->getExtension()->getId());
        break;
      case "program_id":
        Assert::assertEquals($id_or_value, $click->getProgram()->getId());
        break;
      case "user_specific_recommendation":
        Assert::assertEquals(($id_or_value == 'true') ? true : false, $click->getUserSpecificRecommendation());
        break;
      default:
        Assert::assertTrue(false);
    }
  }

  /**
   * @Then /^There should be no recommended click statistic database entry$/
   */
  public function thereShouldBeNoRecommendedClickStatisticDatabaseEntry()
  {
    $em = $this->kernel->getContainer()->get('doctrine')->getManager();
    $clicks = $em->getRepository('AppBundle:ClickStatistic')->findAll();
    Assert::assertEquals(0, count($clicks), "Unexpected database entry found!");
  }

  /**
   * @Then /^There should be one homepage click database entry with type is "([^"]*)" and program id is "([^"]*)"$/
   */
  public function thereShouldBeOneHomepageClickDatabaseEntryWithTypeIsAndIs($type_name, $id)
  {
    $em = $this->kernel->getContainer()->get('doctrine')->getManager();
    $clicks = $em->getRepository('AppBundle:HomepageClickStatistic')->findAll();
    Assert::assertEquals(1, count($clicks), "No database entry found!");
    $click = $clicks[0];
    Assert::assertEquals($type_name, $click->getType());
    Assert::assertEquals($id, $click->getProgram()->getId());
  }

  /**
   * @Then /^There should be no homepage click statistic database entry$/
   */
  public function thereShouldBeNoHomepageClickStatisticDatabaseEntry()
  {
    $em = $this->kernel->getContainer()->get('doctrine')->getManager();
    $clicks = $em->getRepository('AppBundle:HomepageClickStatistic')->findAll();
    Assert::assertEquals(0, count($clicks), "Unexpected database entry found!");
  }

  /**
   * Wait for AJAX to finish.
   *
   * @Given /^I wait for AJAX to finish$/
   */
  public function iWaitForAjaxToFinish()
  {
    $this->getSession()->wait(5000, '(typeof(jQuery)=="undefined" || (0 === jQuery.active && 0 === jQuery(\':animated\').length))');
  }

  /**
   * @When /^I click on the first recommended program$/
   */
  public function iClickOnTheFirstRecommendedProgram()
  {
    $arg1 = '#program-2 .rec-programs';
    $this->assertSession()->elementExists('css', $arg1);

    $this
      ->getSession()
      ->getPage()
      ->find('css', $arg1)
      ->click();
  }

  /**
   * @When /^I click on the first recommended homepage program$/
   */
  public function iClickOnTheFirstRecommendedHomepageProgram()
  {
    $arg1 = '#program-1 .homepage-recommended-programs';
    $this->assertSession()->elementExists('css', $arg1);

    $this
      ->getSession()
      ->getPage()
      ->find('css', $arg1)
      ->click();
  }

  /**
   * @When /^I click on the first featured homepage program$/
   */
  public function iClickOnAFeaturedHomepageProgram()
  {
    $arg1 = '#feature-slider > div > div:first-child > a';
    $this->assertSession()->elementExists('css', $arg1);

    $this
      ->getSession()
      ->getPage()
      ->find('css', $arg1)
      ->click();
  }

  /**
   * @When /^I click on a newest homepage program having program id "([^"]*)"$/
   */
  public function iClickOnANewestHomepageProgram($program_id)
  {
    $arg1 = '#newest .programs #program-' . $program_id . ' .rec-programs';
    $this->assertSession()->elementExists('css', $arg1);

    $this
      ->getSession()
      ->getPage()
      ->find('css', $arg1)
      ->click();
  }

  /**
   * @When /^I click on a most downloaded homepage program having program id "([^"]*)"$/
   */
  public function iClickOnAMostDownloadedHomepageProgram($program_id)
  {
    $arg1 = '#mostDownloaded .programs #program-' . $program_id . ' .rec-programs';
    $this->assertSession()->elementExists('css', $arg1);

    $this
      ->getSession()
      ->getPage()
      ->find('css', $arg1)
      ->click();
  }

  /**
   * @When /^I click on a most viewed homepage program having program id "([^"]*)"$/
   */
  public function iClickOnAMostViewedHomepageProgram($program_id)
  {
    $arg1 = '#mostViewed .programs #program-' . $program_id . ' .rec-programs';
    $this->assertSession()->elementExists('css', $arg1);

    $this
      ->getSession()
      ->getPage()
      ->find('css', $arg1)
      ->click();
  }

  /**
   * @When /^I click on a random homepage program having program id "([^"]*)"$/
   */
  public function iClickOnARandomHomepageProgram($program_id)
  {
    $arg1 = '#random .programs #program-' . $program_id . ' .rec-programs';
    $this->assertSession()->elementExists('css', $arg1);

    $this
      ->getSession()
      ->getPage()
      ->find('css', $arg1)
      ->click();
  }

  /**
   * @When /^I click on the first recommended specific program$/
   */
  public function iClickOnTheFirstRecommendedSpecificProgram()
  {
    $arg1 = '#specific-programs-recommendations .programs #program-3 .rec-programs';
    $this->assertSession()->elementExists('css', $arg1);

    $this
      ->getSession()
      ->getPage()
      ->find('css', $arg1)
      ->click();
  }

  /**
   * @Then /^There should be recommended specific programs$/
   */
  public function thereShouldBeRecommendedSpecificPrograms()
  {
    $arg1 = '#specific-programs-recommendations .programs .rec-programs';
    $this->assertSession()->elementExists('css', $arg1);
  }

  /**
   * @Then /^There should be no recommended specific programs$/
   */
  public function thereShouldBeNoRecommendedSpecificPrograms()
  {
    $arg1 = '#specific-programs-recommendations .programs .rec-programs';
    $this->assertSession()->elementNotExists('css', $arg1);
  }

  /**
   * @Then /^I should see a recommended homepage program having ID "([^"]*)" and name "([^"]*)"$/
   */
  public function iShouldSeeARecommendedHomepageProgramHavingIdAndName($program_id, $program_name)
  {
    $arg1 = '#program-' . $program_id . ' .homepage-recommended-programs';
    $this->assertSession()->elementExists('css', $arg1);

    $arg2 = '#program-' . $program_id . ' .homepage-recommended-programs .program-name';
    Assert::assertEquals($program_name, $this
      ->getSession()
      ->getPage()
      ->find('css', $arg2)
      ->getText());
  }

  /**
   * @Then /^I should not see any recommended homepage programs$/
   */
  public function iShouldNotSeeAnyRecommendedHomepagePrograms()
  {
    $arg1 = '.homepage-recommended-programs';
    $this->assertSession()->elementNotExists('css', $arg1);
  }

  /**
   * @Then /^I should see the image "([^"]*)"$/
   */
  public function iShouldSeeTheImage($arg1)
  {
    $img = $this->getSession()->getPage()->findById('logo');

    if ($img != null)
    {
      Assert::assertEquals($img->getTagName(), 'img');
      $src = $img->getAttribute('src');
      Assert::assertTrue(strpos($src, $arg1) !== false, "<$src> does not contain $arg1");
      Assert::assertTrue($img->isVisible(), "Image is not visible.");
    }
    else
    {
      Assert::assertTrue(false, "#logo not found!");
    }
  }

  /**
   * @Then /^Element "([^"]*)" should have attribute "([^"]*)" with value "([^"]*)"$/
   */
  public function elementShouldHaveAttributeWith($arg1, $arg2, $arg3)
  {
    $element = $this->getSession()->getPage()->find('css', $arg1);

    if ($element != null)
    {
      Assert::assertTrue($element->hasAttribute($arg2), "Element has no attribute $arg2");
      $attr = $element->getAttribute($arg2);
      Assert::assertTrue(strpos($attr, $arg3) !== false, "<$attr> does not contain $arg3");
      Assert::assertTrue($element->isVisible(), "Element is not visible.");
    }
    else
    {
      Assert::assertTrue(false, "$arg1 not found!");
    }
  }

  /**
   * @Then /^I click the currently visible search icon$/
   */
  public function iClickTheCurrentlyVisibleSearchIcon()
  {
    $icons = $this->getSession()->getPage()->findAll("css", ".search-icon-header");
    foreach ($icons as $icon)
    {
      /** @var NodeElement $icon */
      if ($icon->isVisible())
      {
        $icon->click();

        return;
      }
    }
    Assert::assertTrue(false, "Tried to click .search-icon-header but no visible element was found.");
  }

  /**
   * @Then /^at least one "([^"]*)" element should be visible$/
   */
  public function atLeastOneElementShouldBeVisible($arg1)
  {
    $elements = $this->getSession()->getPage()->findAll("css", $arg1);
    foreach ($elements as $element)
    {
      /** @var NodeElement $element */
      if ($element->isVisible())
      {
        return;
      }
    }
    Assert::assertTrue(false, "No $arg1 element currently visible.");
  }

  /**
   * @Then /^no "([^"]*)" element should be visible$/
   */
  public function atLeastOneElementShouldNotBeVisible($arg1)
  {
    $elements = $this->getSession()->getPage()->findAll("css", $arg1);
    foreach ($elements as $element)
    {
      /** @var NodeElement $element */
      if ($element->isVisible())
      {
        Assert::assertTrue(false, "Found visible $arg1 element.");
      }
    }
  }

  /**
   * @Then /^I click the currently visible search button$/
   */
  public function iClickTheCurrentlyVisibleSearchButton()
  {
    $icons = $this->getSession()->getPage()->findAll("css", ".btn-search");
    foreach ($icons as $icon)
    {
      /** @var NodeElement $icon */
      if ($icon->isVisible())
      {
        $icon->click();

        return;
      }
    }
    Assert::assertTrue(false, "Tried to click .btn-search but no visible element was found.");
  }

  /**
   * @Then /^I enter "([^"]*)" into the currently visible search input$/
   */
  public function iEnterIntoTheCurrentlyVisibleSearchInput($arg1)
  {
    $fields = $this->getSession()->getPage()->findAll("css", ".input-search");
    foreach ($fields as $field)
    {
      /** @var NodeElement $field */
      if ($field->isVisible())
      {
        $field->setValue($arg1);

        return;
      }
    }
    Assert::assertTrue(false, "Tried to click .btn-search but no visible element was found.");
  }

  /**
   * @Then /^I ensure pop ups work$/
   */
  public function iEnsurePopUpsWork()
  {
    try
    {
      $this->getSession()->getDriver()->executeScript("window.confirm = function(){return true;}");
    } catch (UnsupportedDriverActionException $e)
    {
      Assert::assertTrue(false, "Driver doesn't support JS injection. For Chrome this is needed since it cant deal with pop ups");
    } catch (DriverException $e)
    {
      Assert::assertTrue(false, "Driver may not support JS injection. For Chrome this is needed since it cant deal with pop ups");
    }
  }

  /**
   * @Given /^there are programs with a large description:$/
   */
  public function thereAreProgramsWithALargeDescription(TableNode $table)
  {
    /**
     * @var $em      \Doctrine\ORM\EntityManager
     * @var $program \Catrobat\AppBundle\Entity\Program
     */
    $em = $this->kernel->getContainer()->get('doctrine')->getManager();
    $programs = $table->getHash();
    $count = count($programs);
    try
    {
      for ($i = 0; $i < $count; ++$i)
      {
        $user = $em->getRepository('AppBundle:User')->findOneBy([
          'username' => $programs[$i]['owned by'],
        ]);
        $program = new Program();
        $program->setUser($user);
        $program->setName($programs[$i]['name']);
        $program->setDescription("This a very very very very very very very very very very very very very " .
          "very very very very very very very very very very very very very very very very very very very very very " .
          "very very very very very very very very very very very very very long description.  -- " .
          "the end of the description");
        $program->setViews(0);
        $program->setDownloads(0);
        $program->setApkDownloads(0);
        $program->setApkStatus(Program::APK_NONE);
        $program->setUploadedAt(new \DateTime("now", new \DateTimeZone('UTC')));
        $program->setRemixMigratedAt(null);
        $program->setCatrobatVersion(1);
        $program->setCatrobatVersionName(1);
        $program->setLanguageVersion(1);
        $program->setUploadIp('127.0.0.1');
        $program->setFilesize(0);
        $program->setVisible(true);
        $program->setUploadLanguage('en');
        $program->setApproved(false);
        $program->setFbPostUrl('');
        $program->setRemixRoot(true);
        $em->persist($program);
      }
      $em->flush();
    } catch (\Exception $e)
    {
      Assert::assertTrue(false, "Program with a large description couldn't be loaded");
    }
  }

  /**
   * @When /^I wait for fadeEffect to finish$/
   */
  public function iWaitForFadeEffectToFinish()
  {
    $this->iWaitMilliseconds(1000);
  }


  /**
   * @Given /^there are "([^"]*)"\+ notifications for "([^"]*)"$/
   */
  public function thereAreNotificationsFor($arg1, $arg2)
  {
    /**
     * @var EntityManager $em
     * @var User          $user
     */
    try
    {
      $em = $this->kernel->getContainer()->get('doctrine')->getManager();

      for ($i = 0; $i < $arg1; $i++)
      {
        $user = $em->getRepository(User::class)->findOneBy([
          'username' => $arg2,
        ]);
        if ($user == null)
        {
          Assert::assertTrue(false, "user is null");
        }
        $to_create = new CatroNotification($user, "Random Title", "Random Text");
        $em->persist($to_create);
      }
      $em->flush();
    } catch (\Exception $e)
    {
      Assert::assertTrue(false, "database error");
    }
  }

  /**
   * @When /^User "([^"]*)" is followed by "([^"]*)"$/
   */
  public function userIsFollowed($user_id, $follow_ids)
  {
    $usermanager = $this->kernel->getContainer()->get('usermanager');
    /**
     * @var $usermanager UserManager
     * @var $user        User
     */
    $user = $usermanager->find($user_id);

    $ids = explode(",", $follow_ids);
    foreach ($ids as $id)
    {
      $followUser = $usermanager->find($id);
      $user->addFollowing($followUser);
      $usermanager->updateUser($user);
    }
  }

  /**
   * @Then /^the element "([^"]*)" should have type "([^"]*)"$/
   */
  public function theElementShouldHaveType($arg1, $arg2)
  {
    $page = $this->getMink()->getSession()->getPage();
    $type = $page->find('css', $arg1)->getAttribute("type");
    Assert::assertEquals($arg2, $type);
  }

  /**
   * @Then /^the element "([^"]*)" should not have type "([^"]*)"$/
   */
  public function theElementShouldNotHaveType($arg1, $arg2)
  {
    $page = $this->getMink()->getSession()->getPage();
    $type = $page->find('css', $arg1)->getAttribute("type");
    Assert::assertNotEquals($arg2, $type);
  }
}