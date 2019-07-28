<?php

use App\Catrobat\Services\ApkRepository;
use App\Catrobat\Services\MediaPackageFileRepository;
use App\Entity\AchievementNotification;
use App\Entity\CatroNotification;
use App\Entity\ClickStatistic;
use App\Entity\CommentNotification;
use App\Entity\Extension;
use App\Entity\FeaturedProgram;
use App\Entity\MediaPackage;
use App\Entity\MediaPackageCategory;
use App\Entity\MediaPackageFile;
use App\Entity\Program;
use App\Entity\ProgramDownloads;
use App\Entity\ProgramLike;
use App\Entity\ProgramManager;
use App\Entity\ProgramRemixRelation;
use App\Entity\StarterCategory;
use App\Entity\Tag;
use App\Entity\User;
use App\Entity\UserComment;
use App\Entity\UserManager;
use App\Repository\ExtensionRepository;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\DriverException;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Exception\ExpectationException;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpKernel\KernelInterface;


/**
 * Feature context.
 */
class WebFeatureContext extends MinkContext implements KernelAwareContext
{
  /**
   * @var KernelInterface
   */
  private $kernel;
  /**
   * @var string|string[]|null
   */
  private $screenshot_directory;
  /**
   * @var Client
   */
  private $client;
  /**
   * @var bool
   */
  private $use_real_oauth_javascript_code;

  const AVATAR_DIR = './tests/testdata/DataFixtures/AvatarImages/';
  const MEDIAPACKAGE_DIR = './tests/testdata/DataFixtures/MediaPackage/';
  const FIXTUREDIR = './tests/testdata/DataFixtures/';
  const ALREADY_IN_DB_USER = 'AlreadyinDB';

  /**
   * Initializes context with parameters from behat.yml.
   *
   * @param array $screenshot_directory
   *
   * @throws Exception
   */
  public function __construct($screenshot_directory)
  {
    $this->screenshot_directory = preg_replace('/([^\/]+)$/', '$1/', $screenshot_directory);
    if (!is_dir($this->screenshot_directory))
    {
      throw new Exception('No screenshot directory specified!');
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

  /**
   * @AfterScenario @RealOAuth
   */
  public function deactivateRealOAuthService()
  {
    $this->setOauthServiceParameter('0');
    $this->use_real_oauth_javascript_code = false;
  }

  /**
   * @return string
   */
  public static function getAcceptedSnippetType()
  {
    return 'regex';
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
    // 15px = scroll bar width
    $this->getSession()->resizeWindow(320 + 15, 1024);
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
   *
   * @param AfterStepScope $scope
   */
  public function makeScreenshot(AfterStepScope $scope)
  {
    if (!$scope->getTestResult()->isPassed())
    {
      $this->saveScreenshot(null, $this->screenshot_directory);
    }
  }

  /**
   * @Given /^I use the user agent "([^"]*)"$/
   * @param string $user_agent
   */
  public function iUseTheUserAgent($user_agent)
  {
    $this->getSession()->setRequestHeader("User-Agent", $user_agent);
  }

  // @formatter:off
  /**
   * @Given /^I use the Catroid app with language version ([0-9]+(\.[0-9]+)?), flavor "([^"]+)", app version "([^"]+)*" and (debug|release) build type$/
   *
   * @param $lang_version
   * @param $flavor
   * @param $app_version
   * @param $build_type
   */
  // @formatter:on
  public function iUseTheUserAgentParameterized($lang_version, $flavor, $app_version, $build_type)
  {
    // see org.catrobat.catroid.ui.WebViewActivity
    $platform = "Android";
    $user_agent = "Catrobat/" . $lang_version . " " . $flavor . "/" . $app_version . " Platform/" . $platform .
      " BuildType/" . $build_type;
    $this->getSession()->setRequestHeader("User-Agent", $user_agent);
  }

  /**
   * @Given /^I use a (debug|release) build of the Catroid app$/
   * @param $build_type
   */
  public function iUseASpecificBuildTypeOfCatroidApp($build_type)
  {
    $this->iUseTheUserAgentParameterized("0.998", "PocketCode", "0.9.60", $build_type);
  }

  // @formatter:off
  /**
   * @Given /^I use an ios app$/
   */
  // @formatter:on
  public function iUseAnIOSApp()
  {
    // see org.catrobat.catroid.ui.WebViewActivity
    $platform = "iPhone";
    $user_agent =  " Platform/" . $platform;
    $this->getSession()->setRequestHeader("User-Agent", $user_agent);
  }


  /**
   * @When /^I wait (\d+) milliseconds$/
   * @param $milliseconds
   */
  public function iWaitMilliseconds($milliseconds)
  {
    $this->getSession()->wait($milliseconds);
  }

  /**
   * @When /^I open the menu$/
   */
  public function iOpenTheMenu()
  {
    $sidebar_open = $this->getSession()->getPage()->find('css', '#sidebar')->isVisible();
    if (!$sidebar_open)
    {
      $this->getSession()->getPage()->find('css', '#btn-sidebar-toggle')->click();
    }
  }

  /**
   * @Then /^I should see (\d+) "([^"]*)"$/
   * @param $element_count
   * @param $css_selector
   */
  public function iShouldSeeNumberOfElements($element_count, $css_selector)
  {
    $elements = $this->getSession()->getPage()->findAll('css', $css_selector);
    $count = 0;
    foreach ($elements as $element)
    {
      if ($element->isVisible())
      {
        $count++;
      }
    }
    Assert::assertEquals($element_count, $count);
  }

  /**
   * @Then /^I should see a node with id "([^"]*)" having name "([^"]*)" and username "([^"]*)"$/
   * @param $node_id
   * @param $expected_node_name
   * @param $expected_username
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
   * @Then /^I should see an unavailable node with id "([^"]*)"$/
   * @param $node_id
   */
  public function iShouldSeeAnUnavailableNodeWithId($node_id)
  {
    $result = $this->getSession()->
    evaluateScript('return RemixGraph.getInstance().getNodes().get("' . $node_id . '");');

    Assert::assertTrue(isset($result['id']));
    Assert::assertEquals($node_id, $result['id']);
    Assert::assertFalse(isset($result['name']));
    Assert::assertFalse(isset($result['username']));
  }

  /**
   * @Then /^I should see an edge from "([^"]*)" to "([^"]*)"$/
   * @param $from_id
   * @param $to_id
   */
  public function iShouldSeeAnEdgeFromTo($from_id, $to_id)
  {
    $result = $this->getSession()->evaluateScript("return RemixGraph.getInstance().getEdges().get().filter(
        function (edge) { return edge.from === '" . $from_id . "' && edge.to === '" . $to_id . "'; }
      );");
    Assert::assertCount(1, $result);
    Assert::assertEquals($from_id, $result[0]['from']);
    Assert::assertEquals($to_id, $result[0]['to']);
  }

  /**
   * @Then /^I should see the featured slider$/
   *
   * @throws ExpectationException
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
    Assert::assertTrue($this->getSession()->getPage()->findById('welcome-section')->isVisible());
  }


  /**
   * @Then /^I should not see the welcome section$/
   */
  public function iShouldNotSeeTheWelcomeSection()
  {
    Assert::assertNull($this->getSession()->getPage()->findById('welcome-section'));
  }

  /**
   * @Then /^I should see ([^"]*) programs$/
   * @param $arg1
   *
   * @throws ElementNotFoundException
   * @throws ExpectationException
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

      case 'random':
        $this->assertSession()->elementExists('css', '#random');
        $this->assertSession()->elementExists('css', '.program');
        break;

      case 'recommended':
        $this->assertSession()->elementExists('css', '#recommended');
        $this->assertSession()->elementExists('css', '.program');
        break;

      default:
        Assert::assertTrue(false);
    }
  }

  /**
   * @Then /^the selected language should be "([^"]*)"$/
   * @Given /^the selected language is "([^"]*)"$/
   * @param $arg1
   *
   * @throws ExpectationException
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
   * @param $arg1
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
   * @param $arg1
   * @param $arg2
   *
   * @throws ExpectationException
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
      Assert::assertTrue($this->getSession()->getPage()->find('css', '.help-split')->isVisible());
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
   * @param TableNode $table
   *
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function thereAreUsers(TableNode $table)
  {
    /**
     * @var $user_manager UserManager
     * @var $user         User
     * @var $em           EntityManager
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
      $user_manager->updateUser($user, false);
    }
    $em = $this->kernel->getContainer()->get('doctrine')->getManager();
    $em->flush();
  }

  /**
   * @Given /^there are admins:$/
   * @param TableNode $table
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
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function thereAreCatroNotifications(TableNode $table)
  {
    /**
     * @var $em   EntityManager
     * @var $user User
     */
    $em = $this->kernel->getContainer()->get('doctrine')->getManager();
    $notifications = $table->getHash();

    foreach ($notifications as $notification)
    {
      $user = $em->getRepository('App\Entity\User')->findOneBy([
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
          $to_create = new CommentNotification($user, $comment);
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
   * @param $arg1
   *
   * @throws ElementNotFoundException
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
   *
   * @param TableNode $table
   *
   * @throws Exception
   */
  public function thereArePrograms(TableNode $table)
  {
    /**
     * @var $em             EntityManager
     * @var $program        Program
     * @var $user           User
     * @var $apkrepository  ApkRepository
     * @var $tag            Tag
     * @var $extension_repo ExtensionRepository
     * @var $extension      Extension
     */
    $em = $this->kernel->getContainer()->get('doctrine')->getManager();
    $programs = $table->getHash();
    $count = count($programs);
    for ($i = 0; $i < $count; ++$i)
    {
      $user = $em->getRepository('App\Entity\User')->findOneBy([
        'username' => $programs[$i]['owned by'],
      ]);
      $program = new Program();
      $program->setUser($user);
      $program->setName($programs[$i]['name']);
      $program->setDescription(isset($programs[$i]['description']) ? $programs[$i]['description'] : '');
      $program->setViews(isset($programs[$i]['views']) ? $programs[$i]['views'] : 0);
      $program->setDownloads(isset($programs[$i]['downloads']) ? $programs[$i]['downloads'] : 0);
      $program->setApkDownloads(isset($programs[$i]['apk_downloads']) ? $programs[$i]['apk_downloads'] : 0);
      $program->setApkStatus(
        isset($programs[$i]['apk_ready']) ?
          ($programs[$i]['apk_ready'] === 'true' ? Program::APK_READY : Program::APK_NONE) :
          Program::APK_NONE
      );
      $program->setUploadedAt(
        isset($programs[$i]['upload time']) ?
          new DateTime($programs[$i]['upload time'], new DateTimeZone('UTC')) :
          null
      );
      $program->setRemixMigratedAt(null);
      $program->setCatrobatVersion(1);
      $program->setCatrobatVersionName(isset($programs[$i]['version']) ? $programs[$i]['version'] : '');
      $program->setLanguageVersion(
        isset($programs[$i]['language version']) ? $programs[$i]['language version'] : 1
      );
      $program->setUploadIp('127.0.0.1');
      $program->setFilesize(0);
      $program->setVisible(isset($programs[$i]['visible']) ? $programs[$i]['visible'] == 'true' : true);
      $program->setUploadLanguage('en');
      $program->setApproved(false);
      $program->setRemixRoot(isset($programs[$i]['remix_root']) ? $programs[$i]['remix_root'] == 'true' : true);
      $program->setPrivate(isset($programs[$i]['private']) ? $programs[$i]['private'] : 0);
      $program->setDebugBuild(isset($programs[$i]['debug']) ? $programs[$i]['debug'] == 'true' : false);

      if (isset($programs[$i]['tags_id']) && $programs[$i]['tags_id'] != null)
      {
        $tag_repo = $em->getRepository('App\Entity\Tag');
        $tags = explode(',', $programs[$i]['tags_id']);
        foreach ($tags as $tag_id)
        {
          $tag = $tag_repo->find($tag_id);
          $program->addTag($tag);
        }
      }

      if (isset($programs[$i]['extensions']) && $programs[$i]['extensions'] != null)
      {
        $extension_repo = $em->getRepository('App\Entity\Extension');
        $extensions = explode(',', $programs[$i]['extensions']);
        foreach ($extensions as $extension_name)
        {
          $extension = $extension_repo->findOneBy(["name" => $extension_name]);
          $program->addExtension($extension);
        }
      }

      if ($program->getApkStatus() == Program::APK_READY)
      {
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
   * @Given /^there are comments:$/
   * @param TableNode $table
   *
   * @throws Exception
   */
  public function thereAreComments(TableNode $table)
  {
    /**
     * @var $new_comment             UserComment
     * @var $entity_manager          EntityManager
     */
    $entity_manager = $this->kernel->getContainer()->get('doctrine')->getManager();
    $program_manager = $this->kernel->getContainer()->get('programmanager');
    $comments = $table->getHash();

    foreach ($comments as $comment)
    {
      $new_comment = new UserComment();

      $new_comment->setUploadDate(new DateTime($comment['upload_date'], new DateTimeZone('UTC')));
      $new_comment->setProgram($program_manager->find($comment['program_id']));
      $new_comment->setProgramId($comment['program_id']);
      $new_comment->setUserId($comment['user_id']);
      $new_comment->setUsername($comment['user_name']);
      $new_comment->setIsReported(false);
      $new_comment->setText($comment['text']);
      $entity_manager->persist($new_comment);
    }
    $entity_manager->flush();
  }

  /**
   * @Given /^there are likes:$/
   * @param TableNode $table
   *
   * @throws Exception
   */
  public function thereAreLikes(TableNode $table)
  {
    /**
     * @var $user    User
     * @var $program Program
     * @var $em      EntityManager
     */
    $em = $this->kernel->getContainer()->get('doctrine')->getManager();
    $likes = $table->getHash();

    foreach ($likes as $like)
    {
      $user = $this->kernel->getContainer()->get('usermanager')->findOneBy(['username' => $like['username']]);
      $program = $this->kernel->getContainer()->get('programrepository')->find($like['program_id']);

      $program_like = new ProgramLike($program, $user, $like['type']);
      $program_like->setCreatedAt(new DateTime($like['created at'], new DateTimeZone('UTC')));

      $em->persist($program_like);
    }
    $em->flush();
  }

  /**
   * @Given /^there are forward remix relations:$/
   * @param TableNode $table
   *
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function thereAreForwardRemixRelations(TableNode $table)
  {
    /**
     * @var $em EntityManager
     */
    $em = $this->kernel->getContainer()->get('doctrine')->getManager();
    $relations = $table->getHash();

    foreach ($relations as $relation)
    {
      /**
       * @var $ancestor_program   Program
       * @var $descendant_program Program
       */
      $ancestor_program = $em->getRepository(Program::class)->find($relation['ancestor_id']);
      $descendant_program = $em->getRepository(Program::class)->find($relation['descendant_id']);

      $forward_relation = new ProgramRemixRelation(
        $ancestor_program, $descendant_program, intval($relation['depth'])
      );
      $em->persist($forward_relation);
    }
    $em->flush();
  }

  /**
   * @Given /^there are featured programs:$/
   * @param TableNode $table
   *
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function thereAreFeaturedPrograms(TableNode $table)
  {
    /**
     * @var $program Program
     * @var $em      EntityManager
     */
    $em = $this->kernel->getContainer()->get('doctrine')->getManager();
    $relations = $table->getHash();

    foreach ($relations as $relation)
    {
      $program = $em->getRepository(Program::class)->find($relation['program_id']);

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
   * @param $arg1
   */
  public function iWriteInTextbox($arg1)
  {
    $textarea = $this->getSession()->getPage()->find('css', '.msg');
    Assert::assertNotNull($textarea, "Textarea not found");
    $textarea->setValue($arg1);
  }


  /**
   * @Given /^there are tags:$/
   * @param TableNode $table
   *
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function thereAreTags(TableNode $table)
  {
    $tags = $table->getHash();
    /** @var EntityManager $em */
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
   * @Given /^there are extensions:$/
   * @param TableNode $table
   *
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function thereAreExtensions(TableNode $table)
  {
    $extensions = $table->getHash();
    /** @var EntityManager $em */
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
   * @param $arg1
   *
   * @throws ElementNotFoundException
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
   * @param $arg1
   */
  public function iClickTheRadiobutton($arg1)
  {
    $page = $this->getSession()->getPage();
    $radioButton = $page->find('css', $arg1);
    $radioButton->click();
  }

  /**
   * @Then /^I should be logged ([^"]*)?$/
   * @param $arg1
   */
  public function iShouldBeLoggedIn($arg1)
  {
    if ($arg1 == 'in')
    {
      $this->assertPageNotContainsText('Your password or username was incorrect.');
      $this->getSession()->wait(2000, 'window.location.href.search("login") == -1');
      $this->assertElementNotOnPage('#btn-login');
      $this->assertElementOnPage('#btn-logout');
    }
    if ($arg1 == 'out')
    {
      $this->getSession()->wait(2000, 'window.location.href.search("profile") == -1');
      $this->assertElementOnPage('#btn-login');
      $this->assertElementNotOnPage('#btn-logout');
    }
  }

  /**
   * @Given /^I( [^"]*)? log in as "([^"]*)" with the password "([^"]*)"$/
   * @param $arg1
   * @param $arg2
   * @param $arg3
   */
  public function iAmLoggedInAsAsWithThePassword($arg1, $arg2, $arg3)
  {
    $this->visitPath('/pocketcode/login');
    $this->fillField('_username', $arg2);
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
   * @param $country
   * @param $select
   */
  public function mustBeSelectedIn($country, $select)
  {
    $field = $this->getSession()->getPage()->findField($select);
    Assert::assertTrue($country == $field->getValue());
  }

  /**
   * @When /^(?:|I )attach the avatar "(?P<path>[^"]*)" to "(?P<field>(?:[^"]|\\")*)"$/
   * @param $field
   * @param $path
   *
   * @throws ElementNotFoundException
   */
  public function attachFileToField($field, $path)
  {
    $field = $this->fixStepArgument($field);
    $this->getSession()->getPage()->attachFileToField($field, realpath(self::AVATAR_DIR . $path));
  }

  /**
   * @Then /^the avatar img tag should( [^"]*)? have the "([^"]*)" data url$/
   * @param $not
   * @param $name
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

    switch ($name)
    {
      case 'logo.png':
        $logoUrl = 'data:image/png;base64,' . base64_encode(file_get_contents(self::AVATAR_DIR . 'logo.png'));
        $isSame = ($source === $logoUrl);
        $not == 'not' ? Assert::assertFalse($isSame) : Assert::assertTrue($isSame);
        break;

      case 'fail.tif':
        $failUrl = 'data:image/tiff;base64,' . base64_encode(file_get_contents(self::AVATAR_DIR . 'fail.tif'));
        $isSame = ($source === $failUrl);
        $not == 'not' ? Assert::assertFalse($isSame) : Assert::assertTrue($isSame);
        break;

      default:
        Assert::assertTrue(false);
    }
  }

  /**
   * @Given /^the element "([^"]*)" should be visible$/
   * @param $element
   */
  public function theElementShouldBeVisible($element)
  {
    $element = $this->getSession()->getPage()->find('css', $element);
    Assert::assertNotNull($element);
    Assert::assertTrue($element->isVisible());
  }

  /**
   * @Given /^the element "([^"]*)" should not exist$/
   * @param $element
   *
   * @throws ExpectationException
   */
  public function theElementShouldNotExist($element)
  {
    $this->assertSession()->elementNotExists('css', $element);
  }

  /**
   * @Given /^the element "([^"]*)" should exist$/
   * @param $element
   *
   * @throws ElementNotFoundException
   */
  public function theElementShouldExist($element)
  {
    $this->assertSession()->elementExists('css', $element);
  }

  /**
   * @Given /^the element "([^"]*)" should not be visible$/
   * @param $element
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
    $this->getSession()->wait(5000,
      '(typeof window.search != "undefined") && (window.search.searchPageLoadDone == true)'
    );
  }

  /**
   * @When /^I click the "([^"]*)" button$/
   * @param $arg1
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
   * @Then /^I should see marked "([^"]*)"$/
   * @param $arg1
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
   * @When /^I trigger Google login with approval prompt "([^"]*)"$/
   * @param $arg1
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
   * @param $arg1
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
   * @param $arg1
   */
  public function thereShouldBeProgramsInTheDatabase($arg1)
  {
    /**
     * @var ProgramManager
     */
    $program_manager = $this->kernel->getContainer()->get('programmanager');
    $programs = $program_manager->findAll();

    Assert::assertEquals($arg1, count($programs));
  }

  /**
   * @Given /^there are starter programs:$/
   * @param TableNode $table
   *
   * @throws Exception
   */
  public function thereAreStarterPrograms(TableNode $table)
  {
    /**
     * @var $user User
     * @var $em   EntityManager
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
      $user = $em->getRepository('App\Entity\User')->findOneBy([
        'username' => $programs[$i]['owned by'],
      ]);
      $program = new Program();
      $program->setUser($user);
      $program->setName($programs[$i]['name']);
      $program->setDescription($programs[$i]['description']);
      $program->setViews($programs[$i]['views']);
      $program->setDownloads($programs[$i]['downloads']);
      $program->setUploadedAt(new DateTime($programs[$i]['upload time'], new DateTimeZone('UTC')));
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
      $program->setRemixMigratedAt(new DateTime());
      $program->setDebugBuild(false);
      $em->persist($program);

      $starter->addProgram($program);
    }

    $em->persist($starter);
    $em->flush();
  }

  /**
   * @Given /^there are mediapackages:$/
   * @param TableNode $table
   *
   * @throws ORMException
   * @throws OptimisticLockException
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
   * @param TableNode $table
   *
   * @throws ORMException
   * @throws OptimisticLockException
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
      $package = $em->getRepository('\App\Entity\MediaPackage')->findOneBy(['name' => $category['package']]);
      $new_category->setPackage([$package]);
      $em->persist($new_category);
    }
    $em->flush();
  }

  /**
   * @Given /^there are mediapackage files:$/
   * @param TableNode $table
   *
   * @throws ORMException
   * @throws OptimisticLockException
   * @throws ImagickException
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
      $category = $em->getRepository('\App\Entity\MediaPackageCategory')
        ->findOneBy(['name' => $file['category']]);
      $new_file->setCategory($category);
      if (!empty($file['flavor']))
      {
        $new_file->setFlavor($file['flavor']);
      }
      $new_file->setAuthor($file['author']);

      $file_path = self::MEDIAPACKAGE_DIR . $file['id'] . '.' . $file['extension'];
      $file_repo->saveMediaPackageFile(new File($file_path), $file['id'], $file['extension']);

      $em->persist($new_file);
    }
    $em->flush();
  }

  /**
   * @Given /^there are program download statistics:$/
   * @param TableNode $table
   *
   * @throws ORMException
   * @throws OptimisticLockException
   * @throws Exception
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
      /** @var Program $program */
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
      $program_statistics->setDownloadedAt(new DateTime($config['downloaded_at']) ?: new DateTime());
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
   *
   * @param TableNode $table
   *
   * @throws Exception
   */
  public function thereAreReportablePrograms(TableNode $table)
  {
    /**
     * @var $user User
     * @var $tag  Tag
     * @var $em   EntityManager
     */
    $em = $this->kernel->getContainer()->get('doctrine')->getManager();
    $programs = $table->getHash();
    $count = count($programs);
    for ($i = 0; $i < $count; ++$i)
    {
      $user = $em->getRepository('App\Entity\User')->findOneBy([
        'username' => $programs[$i]['owned by'],
      ]);
      $program = new Program();
      $program->setUser($user);
      $program->setName($programs[$i]['name']);
      $program->setDescription("Default Description");
      $program->setViews(1337);
      $program->setDownloads(1337);
      $program->setApkDownloads(1337);
      $program->setApkStatus(
        isset($programs[$i]['apk_ready']) ?
          ($programs[$i]['apk_ready'] === 'true' ? Program::APK_READY : Program::APK_NONE) :
          Program::APK_NONE
      );
      $program->setUploadedAt(new DateTime("01.01.2013 12:00", new DateTimeZone('UTC')));
      $program->setRemixMigratedAt(null);
      $program->setCatrobatVersion(1);
      $program->setCatrobatVersionName("0.8.5");
      $program->setLanguageVersion(
        isset($programs[$i]['language version']) ? $programs[$i]['language version'] : 1
      );
      $program->setUploadIp('127.0.0.1');
      $program->setFilesize(0);
      $program->setVisible(isset($programs[$i]['visible']) ? $programs[$i]['visible'] == 'true' : true);
      $program->setUploadLanguage('en');
      $program->setApproved(false);
      $program->setRemixRoot(isset($programs[$i]['remix_root']) ? $programs[$i]['remix_root'] == 'true' : true);
      $program->setDebugBuild(isset($programs[$i]['debug']) ? $programs[$i]['debug'] : false);

      if (isset($programs[$i]['tags_id']) && $programs[$i]['tags_id'] != null)
      {
        $tag_repo = $em->getRepository(Tag::class);
        $tags = explode(',', $programs[$i]['tags_id']);
        foreach ($tags as $tag_id)
        {
          $tag = $tag_repo->find($tag_id);
          $program->addTag($tag);
        }
      }

      if (isset($programs[$i]['extensions']) && $programs[$i]['extensions'] != null)
      {
        /**
         * @var $extension_repo ExtensionRepository
         * @var $extension      Extension
         */
        $extension_repo = $em->getRepository(Extension::class);
        $extensions = explode(',', $programs[$i]['extensions']);
        foreach ($extensions as $extension_name)
        {
          $extension = $extension_repo->findOneBy(["name" => $extension_name]);
          $program->addExtension($extension);
        }
      }

      if ($program->getApkStatus() == Program::APK_READY)
      {
        /**
         * @var $apkrepository ApkRepository
         */
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
   * @param $arg1
   */
  public function iDownload($arg1)
  {
    $this->getClient()->request('GET', $arg1);
  }

  /**
   * @Then /^I should receive a "([^"]*)" file$/
   * @param $extension
   */
  public function iShouldReceiveAFile($extension)
  {
    $content_type = $this->getClient()->getResponse()->headers->get('Content-Type');
    Assert::assertEquals('image/' . $extension, $content_type);
  }

  /**
   * @Then /^I should receive a file named "([^"]*)"$/
   * @param $name
   */
  public function iShouldReceiveAFileNamed($name)
  {
    $content_disposition = $this->getClient()->getResponse()->headers->get('Content-Disposition');
    Assert::assertEquals('attachment; filename="' . $name . '"', $content_disposition);
  }

  /**
   * @Given /^the response code should be "([^"]*)"$/
   * @param $code
   */
  public function theResponseCodeShouldBe($code)
  {
    $response = $this->getClient()->getResponse();
    Assert::assertEquals($code, $response->getStatusCode(), 'Wrong response code. ' . $response->getContent());
  }

  /**
   * @Then /^the media file "([^"]*)" must have the download url "([^"]*)"$/
   * @param $id
   * @param $file_url
   */
  public function theMediaFileMustHaveTheDownloadUrl($id, $file_url)
  {
    $mediafile = $this->getSession()->getPage()->find("css", "#mediafile-" . $id);
    Assert::assertNotNull($mediafile, "Mediafile not found!");
    $link = $mediafile->getAttribute("href");
    Assert::assertTrue(is_int(strpos($link, $file_url)));
  }

  /**
   * @Then /^I should see media file with id "([^"]*)"$/
   * @param $id
   */
  public function iShouldSeeMediaFileWithId($id)
  {
    $link = $this->getSession()->getPage()->find("css", "#mediafile-" . $id);
    Assert::assertNotNull($link);
  }

  /**
   * @Then /^I should not see media file with id "([^"]*)"$/
   * @param $id
   */
  public function iShouldNotSeeMediaFileWithId($id)
  {
    $link = $this->getSession()->getPage()->find("css", "#mediafile-" . $id);
    Assert::assertNull($link);
  }

  /**
   * @Then /^I should see media file with id ([0-9]+) in category "([^"]*)"$/
   * @param $id
   * @param $category
   */
  public function iShouldSeeMediaFileWithIdInCategory($id, $category)
  {
    $link = $this->getSession()->getPage()
      ->find("css", '[data-name="' . $category . '"]')
      ->find("css", "#mediafile-" . $id);
    Assert::assertNotNull($link);
  }

  /**
   * @Then /^I should see ([0-9]+) media files? in category "([^"]*)"$/
   * @param $count
   * @param $category
   */
  public function iShouldSeeNumberOfMediaFilesInCategory($count, $category)
  {
    $elements = $this->getSession()->getPage()
      ->find("css", '[data-name="' . $category . '"]')
      ->findAll("css", ".mediafile");
    Assert::assertEquals($count, count($elements));
  }

  /**
   * @Then /^the link of "([^"]*)" should open "([^"]*)"$/
   * @param $identifier
   * @param $url_type
   */
  public function theLinkOfShouldOpen($identifier, $url_type)
  {
    $class_name = "";
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

    $url_text = "";
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
   * @param $arg1
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
   * @param $arg1
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
   *
   * @throws ElementNotFoundException
   */
  public function iClickTheProgramDownloadButton()
  {
    $this->iClick("#url-download");
  }


  /**
   * @Then /^I click the program image$/
   *
   * @throws ElementNotFoundException
   */
  public function iClickTheProgramImage()
  {
    $this->iClick("#url-image");
  }

  /**
   * @Then /^I click on the program popup background$/
   *
   * @throws ElementNotFoundException
   */
  public function iClickOnTheProgramPopupBackground()
  {
    $this->iClick("#popup-background");
  }

  /**
   * @Then /^I click on the program popup button$/
   *
   * @throws ElementNotFoundException
   */
  public function iClickOnTheProgramPopupButton()
  {
    $this->iClick("#btn-close-popup");
  }

  /**
   * @When /^I want to download the apk file of "([^"]*)"$/
   * @param $arg1
   *
   * @throws Exception
   */
  public function iWantToDownloadTheApkFileOf($arg1)
  {
    /**
     * @var ProgramManager $program_manager
     * @var Program        $program
     */
    $program_manager = $this->kernel->getContainer()->get('programmanager');
    $program = $program_manager->findOneByName($arg1);
    if ($program === null)
    {
      throw new Exception('Program not found: ' . $arg1);
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
   * @param $url
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
   * @return Client
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
   * @Then /^I log in to Google with valid credentials$/
   *
   * @throws ElementNotFoundException
   */
  public function iLogInToGoogleWithEmailAndPassword()
  {
    if ($this->use_real_oauth_javascript_code)
    {
      $mail = $this->getParameterValue('google_testuser_mail');
      $password = $this->getParameterValue('google_testuser_pw');
      echo 'Login with mail address ' . $mail . ' and pw ' . $password . "\n";
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
          $this->signInWithGoogleEMailAndPassword($mail, $password);
        }
        else
        {
          if ($page->find('css', '#gaia_firstform') &&
            $page->find('css', '#Email') &&
            $page->find('css', '#Passwd-hidden')
          )
          {
            $this->signInWithGoogleEMail($mail, $password);
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

  /**
   *
   */
  private function approveGoogleAccess()
  {
    echo 'Google Approve Access form appeared' . "\n";
    $page = $this->getSession()->getPage();
    $button = $page->findById('submit_approve_access');
    Assert::assertTrue($button != null);
    $button->press();
    $this->getSession()->switchToWindow(null);
  }

  /**
   * @param $mail
   * @param $password
   *
   * @throws ElementNotFoundException
   */
  private function signInWithGoogleEMailAndPassword($mail, $password)
  {
    echo 'Google login form with E-Mail and Password appeared' . "\n";
    $page = $this->getSession()->getPage();

    $page->fillField('Email', $mail);
    $page->fillField('Passwd', $password);
    $button = $page->findById('signIn');
    Assert::assertTrue($button != null);
    $button->press();
    $this->approveGoogleAccess();
  }

  /**
   * @Then /^there is a user in the database:$/
   * @param TableNode $table
   */
  public function thereIsAUserInTheDatabase(TableNode $table)
  {
    /**
     * @var $user_manager UserManager
     * @var $user         User
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

  /**
   * @param $mail
   * @param $password
   *
   * @throws ElementNotFoundException
   */
  private function signInWithGoogleEMail($mail, $password)
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
      $this->signInWithGooglePassword($password);
    }
  }

  /**
   * @param $password
   *
   * @throws ElementNotFoundException
   */
  private function signInWithGooglePassword($password)
  {
    echo 'Google Signin with Password form appeared' . "\n";
    $page = $this->getSession()->getPage();

    $page->fillField('Passwd', $password);
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


  /**
   *
   */
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

  /**
   *
   */
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
   * @param $arg1
   *
   * @throws ElementNotFoundException
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
   * @param $arg1
   *
   * @throws ElementNotFoundException
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

  /**
   * @param $name
   *
   * @return bool|string
   */
  private function getParameterValue($name)
  {
    $my_file = fopen("app/config/parameters.yml", "r") or die("Unable to open file!");
    while (!feof($my_file))
    {
      $line = fgets($my_file);
      if (strpos($line, $name) != false)
      {
        fclose($my_file);

        return substr(trim($line), strpos(trim($line), ':') + 2);
      }
    }
    fclose($my_file);
    Assert::assertTrue(false, 'No entry found in parameters.yml!');

    return false;
  }

  /**
   * @param $value
   */
  private function setOauthServiceParameter($value)
  {
    $new_content = 'parameters:' . chr(10) . '    oauth_use_real_service: ' . $value;
    file_put_contents("config/packages/test/parameters.yml", $new_content);
  }

  /**
   * @Then /^I should see "([^"]*)" "([^"]*)" tutorial banners$/
   * @param $count
   * @param $view
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
   * @param $numb
   *
   * @throws ElementNotFoundException
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
   * @param TableNode $table
   *
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function followingProgramsAreFeatured(TableNode $table)
  {
    /** @var EntityManager $em */
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
   * @param $values
   */
  public function iShouldSeeTheSliderWithTheValues($values)
  {
    $slider_items = explode(',', $values);
    $owl_items = $this->getSession()->getPage()->findAll('css', 'div.carousel-item > a');
    $owl_items_count = count($owl_items);
    Assert::assertEquals($owl_items_count, count($slider_items));

    for ($index = 0; $index < $owl_items_count; $index++)
    {
      $url = $slider_items[$index];
      if (strpos($url, "http://") !== 0)
      {
        /** @var Program $program */
        $program = $this->kernel->getContainer()->get('programmanager')->findOneByName($url);
        Assert::assertNotNull($program);
        Assert::assertNotNull($program->getId());
        $url = $this->kernel->getContainer()->get('router')
          ->generate('program', ['id' => $program->getId(), 'flavor' => 'pocketcode']);
      }

      $feature_url = $owl_items[$index]->getAttribute('href');
      Assert::assertContains($url, $feature_url);
    }
  }

  /**
   * @When /^I press on the tag "([^"]*)"$/
   * @param $arg1 string  The name of the tag
   *
   * @throws ElementNotFoundException
   */
  public function iPressOnTheTag($arg1)
  {
    $xpath = '//*[@id="tags"]/div/a[normalize-space()="' . $arg1 . '"]';
    $this->assertSession()->elementExists('xpath', $xpath);

    $this
      ->getSession()
      ->getPage()
      ->find('xpath', $xpath)
      ->click();
  }

  /**
   * @When /^I press on the extension "([^"]*)"$/
   * @param $arg1 string  The name of the extension
   *
   * @throws ElementNotFoundException
   */
  public function iPressOnTheExtension($arg1)
  {
    $xpath = '//*[@id="extensions"]/div/a[normalize-space()="' . $arg1 . '"]';
    $this->assertSession()->elementExists('xpath', $xpath);

    $this
      ->getSession()
      ->getPage()
      ->find('xpath', $xpath)
      ->click();
  }

  /**
   * @Given /^I search for "([^"]*)" with the searchbar$/
   * @param $arg1
   *
   * @throws ElementNotFoundException
   */
  public function iSearchForWithTheSearchbar($arg1)
  {
    $this->iClick('.search-icon-header');
    $this->fillField('search-input-header', $arg1);
    $this->iClick('#btn-search-header');
  }


  /**
   * @Then /^I should see the Google Plus 1 button in the header$/
   */
  public function iShouldSeeTheGoogleButtonInTheHeader()
  {
    $plus_one_button = $this->getSession()->getPage()->findById('___plusone_0');
    Assert::assertTrue(
      $plus_one_button != null && $plus_one_button->isVisible(),
      "The Google +1 Button is not visible!"
    );
    Assert::assertTrue(
      $plus_one_button->getParent()->getParent()->getParent()->getTagName() == 'nav',
      "Parent is not header element"
    );
  }


  /**
   * @Then /^I should see the logout button$/
   */
  public function iShouldSeeTheLogoutButton()
  {
    $logout_button = $this->getSession()->getPage()->findById('btn-logout');
    Assert::assertTrue(
      $logout_button != null && $logout_button->isVisible(),
      "The Logout button is not visible!"
    );
  }

  /**
   * @Then /^I should see the profile button$/
   */
  public function iShouldSeeTheProfileButton()
  {
    $profile_button = $this->getSession()->getPage()->findById('btn-profile');
    Assert::assertTrue(
      $profile_button != null && $profile_button->isVisible(),
      "The profile button is not visible!"
    );
  }

  /**
   * @Then /^the href with id "([^"]*)" should be void$/
   * @param $arg1
   */
  public function theHrefWithIdShouldBeVoid($arg1)
  {
    $button = $this->getSession()->getPage()->findById($arg1);
    Assert::assertEquals($button->getAttribute('href'), 'javascript:void(0)');
  }

  /**
   * @Then /^the href with id "([^"]*)" should not be void$/
   * @param $arg1
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
   * @param $type_name
   * @param $name_id
   * @param $id_or_value
   */
  public function thereShouldBeOneDatabaseEntryWithTypeIsAndIs($type_name, $name_id, $id_or_value)
  {
    /**
     * @var $click ClickStatistic
     * @var $em    EntityManager
     */
    $em = $this->kernel->getContainer()->get('doctrine')->getManager();
    $clicks = $em->getRepository('App\Entity\ClickStatistic')->findAll();
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
    /** @var EntityManager $em */
    $em = $this->kernel->getContainer()->get('doctrine')->getManager();
    $clicks = $em->getRepository('App\Entity\ClickStatistic')->findAll();
    Assert::assertEquals(0, count($clicks), "Unexpected database entry found!");
  }

  /**
   * @Then /^There should be one homepage click database entry with type is "([^"]*)" and program id is "([^"]*)"$/
   *
   * @param $type_name
   * @param $id
   */
  public function thereShouldBeOneHomepageClickDatabaseEntryWithTypeIsAndIs($type_name, $id)
  {
    /**
     * @var $click ClickStatistic
     * @var $em    EntityManager
     */
    $em = $this->kernel->getContainer()->get('doctrine')->getManager();
    $clicks = $em->getRepository('App\Entity\HomepageClickStatistic')->findAll();
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
    /** @var EntityManager $em */
    $em = $this->kernel->getContainer()->get('doctrine')->getManager();
    $clicks = $em->getRepository('App\Entity\HomepageClickStatistic')->findAll();
    Assert::assertEquals(0, count($clicks), "Unexpected database entry found!");
  }

  /**
   * Wait for AJAX to finish.
   *
   * @Given /^I wait for AJAX to finish$/
   */
  public function iWaitForAjaxToFinish()
  {
    $this->getSession()->wait(5000,
      '(typeof(jQuery)=="undefined" || (0 === jQuery.active && 0 === jQuery(\':animated\').length))'
    );
  }

  /**
   * @When /^I click on the first recommended program$/
   *
   * @throws ElementNotFoundException
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
   *
   * @throws ElementNotFoundException
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
   *
   * @throws ElementNotFoundException
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
   * @param $program_id
   *
   * @throws ElementNotFoundException
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
   * @param $program_id
   *
   * @throws ElementNotFoundException
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
   *
   * @param $program_id
   *
   * @throws ElementNotFoundException
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
   *
   * @param $program_id
   *
   * @throws ElementNotFoundException
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
   *
   * @throws ElementNotFoundException
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
   *
   * @throws ElementNotFoundException
   */
  public function thereShouldBeRecommendedSpecificPrograms()
  {
    $arg1 = '#specific-programs-recommendations .programs .rec-programs';
    $this->assertSession()->elementExists('css', $arg1);
  }

  /**
   * @Then /^There should be no recommended specific programs$/
   *
   * @throws ExpectationException
   */
  public function thereShouldBeNoRecommendedSpecificPrograms()
  {
    $this->assertSession()->elementNotExists('css',
      '#specific-programs-recommendations .programs .rec-programs');
  }

  /**
   * @Then /^I should see a recommended homepage program having ID "([^"]*)" and name "([^"]*)"$/
   * @param $program_id
   * @param $program_name
   *
   * @throws ElementNotFoundException
   */
  public function iShouldSeeARecommendedHomepageProgramHavingIdAndName($program_id, $program_name)
  {
    $this->assertSession()->elementExists('css',
      '#program-' . $program_id . ' .homepage-recommended-programs');

    Assert::assertEquals($program_name, $this
      ->getSession()
      ->getPage()
      ->find('css', '#program-' . $program_id . ' .homepage-recommended-programs .program-name')
      ->getText());
  }

  /**
   * @Then /^I should not see any recommended homepage programs$/
   *
   * @throws ExpectationException
   */
  public function iShouldNotSeeAnyRecommendedHomepagePrograms()
  {
    $this->assertSession()->elementNotExists('css', '.homepage-recommended-programs');
  }

  /**
   * @Then /^I should see the image "([^"]*)"$/
   * @param $arg1
   */
  public function iShouldSeeTheImage($arg1)
  {
    $img = $this->getSession()->getPage()->findById('logo');

    if ($img != null)
    {
      Assert::assertEquals($img->getTagName(), 'img');
      $src = $img->getAttribute('src');
      Assert::assertTrue(strpos($src, $arg1) !== false, '<' . $src . '> does not contain ' . $arg1);
      Assert::assertTrue($img->isVisible(), "Image is not visible.");
    }
    else
    {
      Assert::assertTrue(false, "#logo not found!");
    }
  }

  /**
   * @Then /^Element "([^"]*)" should have attribute "([^"]*)" with value "([^"]*)"$/
   * @param $arg1
   * @param $arg2
   * @param $arg3
   */
  public function elementShouldHaveAttributeWith($arg1, $arg2, $arg3)
  {
    $element = $this->getSession()->getPage()->find('css', $arg1);

    if ($element != null)
    {
      Assert::assertTrue($element->hasAttribute($arg2), 'Element has no attribute ' . $arg2);
      $attr = $element->getAttribute($arg2);
      Assert::assertTrue(
        strpos($attr, $arg3) !== false,
        '<' . $attr . '> does not contain ' . $arg3
      );
      Assert::assertTrue($element->isVisible(), "Element is not visible.");
    }
    else
    {
      Assert::assertTrue(false, $arg1 . ' not found!');
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
   * @param $arg1
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
    Assert::assertTrue(false, 'No ' . $arg1 . ' element currently visible.');
  }

  /**
   * @Then /^no "([^"]*)" element should be visible$/
   * @param $arg1
   */
  public function atLeastOneElementShouldNotBeVisible($arg1)
  {
    $elements = $this->getSession()->getPage()->findAll("css", $arg1);
    foreach ($elements as $element)
    {
      /** @var NodeElement $element */
      if ($element->isVisible())
      {
        Assert::assertTrue(false, 'Found visible ' . $arg1 . ' element.');
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
   * @param $arg1
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
      Assert::assertTrue(
        false,
        "Driver doesn't support JS injection. For Chrome this is needed since it cant deal with pop ups"
      );
    } catch (DriverException $e)
    {
      Assert::assertTrue(
        false,
        "Driver may not support JS injection. For Chrome this is needed since it cant deal with pop ups"
      );
    }
  }

  /**
   * @Given /^there are programs with a large description:$/
   * @param TableNode $table
   */
  public function thereAreProgramsWithALargeDescription(TableNode $table)
  {
    /**
     * @var $em      EntityManager
     * @var $program Program
     */
    $em = $this->kernel->getContainer()->get('doctrine')->getManager();
    $programs = $table->getHash();
    $count = count($programs);
    try
    {
      for ($i = 0; $i < $count; ++$i)
      {
        /**
         * @var $user User
         */
        $user = $em->getRepository('App\Entity\User')->findOneBy([
          'username' => $programs[$i]['owned by'],
        ]);
        $program = new Program();
        $program->setUser($user);
        $program->setName($programs[$i]['name']);
        $description = str_repeat("10 chars !", 950);
        $program->setDescription($description . "the end of the description");
        $program->setViews(0);
        $program->setDownloads(0);
        $program->setApkDownloads(0);
        $program->setApkStatus(Program::APK_NONE);
        $program->setUploadedAt(new DateTime("now", new DateTimeZone('UTC')));
        $program->setRemixMigratedAt(null);
        $program->setCatrobatVersion(1);
        $program->setCatrobatVersionName(1);
        $program->setLanguageVersion(1);
        $program->setUploadIp('127.0.0.1');
        $program->setFilesize(0);
        $program->setVisible(true);
        $program->setUploadLanguage('en');
        $program->setApproved(false);
        $program->setRemixRoot(true);
        $program->setDebugBuild(isset($programs[$i]['debug']) ? $programs[$i]['debug'] : false);
        $em->persist($program);
      }
      $em->flush();
    } catch (Exception $e)
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
   * @param $arg1
   * @param $arg2
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
    } catch (Exception $e)
    {
      Assert::assertTrue(false, "database error");
    }
  }

  /**
   * @When /^User "([^"]*)" is followed by "([^"]*)"$/
   * @param $user_id
   * @param $follow_ids
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
   * @param $arg1
   * @param $arg2
   */
  public function theElementShouldHaveType($arg1, $arg2)
  {
    $page = $this->getMink()->getSession()->getPage();
    $type = $page->find('css', $arg1)->getAttribute("type");
    Assert::assertEquals($arg2, $type);
  }

  /**
   * @Then /^the element "([^"]*)" should not have type "([^"]*)"$/
   * @param $arg1
   * @param $arg2
   */
  public function theElementShouldNotHaveType($arg1, $arg2)
  {
    $page = $this->getMink()->getSession()->getPage();
    $type = $page->find('css', $arg1)->getAttribute("type");
    Assert::assertNotEquals($arg2, $type);
  }

  /**
   * @Given the app version is :appVersion
   *
   * @param $appVersion
   */
  public function theAppVersionIs($appVersion)
  {
    putenv("APP_VERSION=" . $appVersion);
  }

  /**
   * @Given the random program section is empty
   */
  public function theRandomProgramSectionIsEmpty()
  {
    $this->getSession()->evaluateScript(
      'document.getElementById("random").style.display = "none";'
    );
  }
}