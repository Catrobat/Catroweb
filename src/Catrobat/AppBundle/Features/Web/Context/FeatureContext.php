<?php

namespace Catrobat\AppBundle\Features\Web\Context;

use Behat\Behat\Context\CustomSnippetAcceptingContext;
use Behat\Behat\Tester\Exception\PendingException;
use Catrobat\AppBundle\Entity\MediaPackage;
use Catrobat\AppBundle\Entity\MediaPackageCategory;
use Catrobat\AppBundle\Entity\MediaPackageFile;
use Catrobat\AppBundle\Entity\Program;
use Catrobat\AppBundle\Entity\ProgramManager;
use Catrobat\AppBundle\Entity\StarterCategory;
use Catrobat\AppBundle\Entity\User;
use Catrobat\AppBundle\Entity\UserLDAPManager;
use Catrobat\AppBundle\Entity\UserManager;
use Catrobat\AppBundle\Services\MediaPackageFileRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpKernel\KernelInterface;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Catrobat\AppBundle\Features\Helpers\BaseContext;

require_once 'PHPUnit/Framework/Assert/Functions.php';

//
// Require 3rd-party libraries here:
//
//   require_once 'PHPUnit/Autoload.php';
//   require_once 'PHPUnit/Framework/Assert/Functions.php';
//

/**
 * Feature context.
 */
class FeatureContext extends MinkContext implements KernelAwareContext, CustomSnippetAcceptingContext
{
    private $kernel;
    private $screenshot_directory;
    private $client;

    const AVATAR_DIR = './testdata/DataFixtures/AvatarImages/';
    const MEDIAPACKAGE_DIR = './testdata/DataFixtures/MediaPackage/';
    const FIXTUREDIR = './testdata/DataFixtures/';

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
      if (!is_dir($this->screenshot_directory)) {
          throw new \Exception('No screenshot directory specified!');
      }
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

    public static function getAcceptedSnippetType()
    {
        return 'regex';
    }

    private function deleteScreens()
    {
        $files = glob($this->screenshot_directory.'*');
        foreach ($files as $file) {
            if (is_file($file)) {
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
   * @BeforeScenario
   */
  public function setup()
  {
      $this->getSession()->resizeWindow(1280, 1000);
  }

  /**
   * @AfterScenario
   */
  public function resetSession()
  {
      $this->getSession()->getDriver()->reset();
  }

  /**
   * @AfterStep
   */
  public function makeScreenshot(AfterStepScope $scope)
  {
      if (!$scope->getTestResult()->isPassed()) {
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
   * @Then /^I should see the featured slider$/
   */
  public function iShouldSeeTheFeaturedSlider()
  {
      $this->assertSession()->responseContains('featured');
      assertTrue($this->getSession()->getPage()->findById('featuredPrograms')->isVisible());
  }

  /**
   * @Then /^I should see ([^"]*) programs$/
   */
  public function iShouldSeePrograms($arg1)
  {
      $arg1 = trim($arg1);

      switch ($arg1) {
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
        assertTrue(false);
    }
  }

  /**
   * @Then /^the selected language should be "([^"]*)"$/
   */
  public function theSelectedLanguageShouldBe($arg1)
  {
      switch ($arg1) {
      case 'English':
        $cookie = $this->getSession()->getCookie('hl');
        if (!empty($cookie)) {
            $this->assertSession()->cookieEquals('hl', 'en');
        }
        break;

      case 'Deutsch':
        $this->assertSession()->cookieEquals('hl', 'de');
        break;

      default:
        assertTrue(false);
    }
  }

  /**
   * @Then /^I switch the language to "([^"]*)"$/
   */
  public function iSwitchTheLanguageTo($arg1)
  {
      switch ($arg1) {
      case 'English':
        $this->getSession()->setCookie('hl', 'en');
        break;
      case 'Deutsch':
        $this->getSession()->setCookie('hl', 'de');
        break;
      default:
        assertTrue(false);
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

      if ($arg1 == 'big') {
          assertTrue($this->getSession()->getPage()->find('css', '.help-desktop')->isVisible());
          assertFalse($this->getSession()->getPage()->find('css', '.help-mobile')->isVisible());
      } elseif ($arg1 == 'small') {
          assertFalse($this->getSession()->getPage()->find('css', '.help-desktop')->isVisible());
          assertTrue($this->getSession()->getPage()->find('css', '.help-mobile')->isVisible());
      } elseif ($arg1 == '') {
          assertTrue($this->getSession()->getPage()->find('css', '.help-split-desktop')->isVisible());
      } else {
          assertTrue(false);
      }

      $img = null;
      $path = null;

      switch ($arg2) {
      case 'Hour of Code':
        if ($arg1 == 'big') {
            $img = $this->getSession()->getPage()->findById('hour-of-code-desktop');
            $path = '/images/help/hour_of_code.png';
        } elseif ($arg1 == 'small') {
            $img = $this->getSession()->getPage()->findById('hour-of-code-mobile');
            $path = '/images/help/hour_of_code_mobile.png';
        } else {
            assertTrue(false);
        }
        break;
      case 'Game Design':
          if ($arg1 == 'big') {
              $img = $this->getSession()->getPage()->findById('alice-tut-desktop');
              $path = '/images/help/alice_tut.png';
          } elseif ($arg1 == 'small') {
              $img = $this->getSession()->getPage()->findById('alice-tut-mobile');
              $path = '/images/help/alice_tut_mobile.png';
          } else {
              assertTrue(false);
          }
          break;
      case 'Step By Step':
        if ($arg1 == 'big') {
            $img = $this->getSession()->getPage()->findById('step-by-step-desktop');
            $path = '/images/help/step_by_step.png';
        } elseif ($arg1 == 'small') {
            $img = $this->getSession()->getPage()->findById('step-by-step-mobile');
            $path = '/images/help/step_by_step_mobile.png';
        } else {
            assertTrue(false);
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
      case 'Discussion':
        if ($arg1 == 'big') {
            $img = $this->getSession()->getPage()->findById('discuss-desktop');
            $path = '/images/help/discuss.png';
        } elseif ($arg1 == 'small') {
            $img = $this->getSession()->getPage()->findById('discuss-mobile');
            $path = '/images/help/discuss_mobile.png';
        } else {
            assertTrue(false);
        }
        break;
      default:
        assertTrue(false);
        break;

    }

      if ($img != null) {
          assertEquals($img->getTagName(), 'img');
          assertEquals($img->getAttribute('src'), $path);
          assertTrue($img->isVisible());
      } else {
          assertTrue(false);
      }
  }

  /**
   * @Given /^there are users:$/
   */
  public function thereAreUsers(TableNode $table)
  {
      /**
     * @var $user_manager UserManager
     * @var $user User
     */
    $user_manager = $this->kernel->getContainer()->get('usermanager');
      $users = $table->getHash();
      $user = null;
      for ($i = 0; $i < count($users); ++$i ) {
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
      $user_manager->updateUser($user, true);
  }

  /**
   * @Given /^there are programs:$/
   */
  public function thereArePrograms(TableNode $table)
  {
      /*
     * @var $program \Catrobat\AppBundle\Entity\Program
     */
    $em = $this->kernel->getContainer()->get('doctrine')->getManager();
      $programs = $table->getHash();
      for ($i = 0; $i < count($programs); ++$i ) {
          $user = $em->getRepository('AppBundle:User')->findOneBy(array(
        'username' => $programs[$i]['owned by'],
      ));
          $program = new Program();
          $program->setUser($user);
          $program->setName($programs[$i]['name']);
          $program->setDescription($programs[$i]['description']);
          $program->setViews($programs[$i]['views']);
          $program->setDownloads($programs[$i]['downloads']);
          $program->setApkDownloads($programs[$i]['apk_downloads']);
          $program->setApkStatus(isset($programs[$i]['apk_ready']) ? ($programs[$i]['apk_ready'] === 'true' ? Program::APK_READY : Program::APK_NONE) : Program::APK_NONE);
          $program->setUploadedAt(new \DateTime($programs[$i]['upload time'], new \DateTimeZone('UTC')));
          $program->setCatrobatVersion(1);
          $program->setCatrobatVersionName($programs[$i]['version']);
          $program->setLanguageVersion(isset($programs[$i]['language version']) ? $programs[$i]['language version'] : 1);
          $program->setUploadIp('127.0.0.1');
          $program->setRemixCount(0);
          $program->setFilesize(0);
          $program->setVisible(isset($programs[$i]['visible']) ? $programs[$i]['visible'] == 'true' : true);
          $program->setUploadLanguage('en');
          $program->setApproved(false);
          $program->setFbPostUrl(isset($programs[$i]['fb_post_url']) ? $programs[$i]['fb_post_url'] : '');

          if($program->getApkStatus() == Program::APK_READY) {
            /* @var $apkrepository \Catrobat\AppBundle\Services\ApkRepository */
            $apkrepository = $this->kernel->getContainer()->get('apkrepository');
            $temppath = tempnam(sys_get_temp_dir(), 'apktest');
            copy(self::FIXTUREDIR.'test.catrobat', $temppath);
            $apkrepository->save(new File($temppath), $i);

            $file_repo = $this->kernel->getContainer()->get('filerepository');
            $file_repo->saveProgramfile(new File(self::FIXTUREDIR.'test.catrobat'), $i);
          }

          $em->persist($program);
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
   * @Then /^I should be logged ([^"]*)?$/
   */
  public function iShouldBeLoggedIn($arg1)
  {
      if ($arg1 == 'in') {
          $this->assertPageNotContainsText('Your password or username was incorrect.');
          $this->getSession()->wait(10000, 'window.location.href.search("login") == -1');
          $this->getSession()->wait(1000);
          $this->assertElementOnPage('#logo');
          $this->assertElementNotOnPage('#btn-login');
          $this->assertElementOnPage('#nav-dropdown');
          $this->getSession()->getPage()->find('css', '.show-nav-dropdown')->click();
          $this->assertElementOnPage('#nav-dropdown');
      }
      if ($arg1 == 'out') {
          $this->getSession()->wait(10000, 'window.location.href.search("profile") == -1');
          $this->getSession()->wait(1000);
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
      if ($arg1 == 'try to') {
          $this->assertPageNotContainsText('Your password or username was incorrect.');
      }
  }

  /**
   * @Given /^I wait for the server response$/
   */
  public function iWaitForTheServerResponse()
  {
      $this->getSession()->wait(5000, '(0 === jQuery.active)');
  }

  /**
   * @Then /^"([^"]*)" must be selected in "([^"]*)"$/
   */
  public function mustBeSelectedIn($country, $select)
  {
      $field = $this->getSession()->getPage()->findField($select);
      assertTrue($country == $field->getValue());
  }

  /**
   * @When /^(?:|I )attach the avatar "(?P<path>[^"]*)" to "(?P<field>(?:[^"]|\\")*)"$/
   */
  public function attachFileToField($field, $path)
  {
      $field = $this->fixStepArgument($field);
      $this->getSession()->getPage()->attachFileToField($field, realpath(self::AVATAR_DIR.$path));
  }

  /**
   * @Then /^the avatar img tag should( [^"]*)? have the "([^"]*)" data url$/
   */
  public function theAvatarImgTagShouldHaveTheDataUrl($not, $name)
  {
      $name = trim($name);
      $not = trim($not);

      $source = $this->getSession()->getPage()->find('css', '#profile-avatar > img')->getAttribute('src');
      $source = trim($source, '"');
      $styleHeader = $this->getSession()->getPage()->find('css', '#menu .img-avatar')->getAttribute('style');
      $sourceHeader = preg_replace("/(.+)url\(([^)]+)\)(.+)/", '\\2', $styleHeader);
      $sourceHeader = trim($sourceHeader, '"');

      switch ($name) {
      case 'logo.png':
        $logoUrl = 'data:image/png;base64,'.base64_encode(file_get_contents(self::AVATAR_DIR.'logo.png'));
        $isSame = (($source == $logoUrl) && ($sourceHeader == $logoUrl));
        $not == 'not' ? assertFalse($isSame) : assertTrue($isSame);
        break;

      case 'fail.tif':
        $failUrl = 'data:image/tiff;base64,'.base64_encode(file_get_contents(self::AVATAR_DIR.'fail.tif'));
        $isSame = (($source == $failUrl) && ($sourceHeader == $failUrl));
        $not == 'not' ? assertFalse($isSame) : assertTrue($isSame);
        break;

      default:
        assertTrue(false);
    }
  }

  /**
   * @Given /^the element "([^"]*)" should be visible$/
   */
  public function theElementShouldBeVisible($element)
  {
      $element = $this->getSession()->getPage()->find('css', $element);
      assertNotNull($element);
      assertTrue($element->isVisible());
  }

  /**
   * @Given /^the element "([^"]*)" should not be visible$/
   */
  public function theElementShouldNotBeVisible($element)
  {
      $element = $this->getSession()->getPage()->find('css', $element);
      assertNotNull($element);
      assertFalse($element->isVisible());
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

        switch($arg1) {
            case "login":
                $button = $page->find("css", "#btn-login");
                break;
            case "logout":
                $url = $this->getSession()->getCurrentUrl();
                if(strpos($url, 'profile') != false) {
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
            default:
                assertTrue(false);
        }
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
      $this->assertElementOnPage('#header-logo');
      $this->assertElementOnPage('#btn-login_facebook');
      $this->getSession()->executeScript('document.getElementById("facebook_auth_type").type = "text";');
      $this->getSession()->getPage()->findById('facebook_auth_type')->setValue($arg1);

     }

    /**
     * @When /^I click Facebook login link$/
     */
    public function iClickFacebookLoginLink()
    {
      $this->clickLink('btn-login_facebook');
      $this->getSession()->wait(2000);
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
        $this->assertElementOnPage('#header-logo');
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
        $this->clickLink('btn-login_google');
        $this->getSession()->wait(1500);
        if($arg1 == 'twice') {
            $this->clickLink('btn-login_google');
            $this->getSession()->wait(2500);
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

      assertEquals($arg1, count($programs));
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
      for ($i = 0; $i < count($programs); ++$i ) {
          $user = $em->getRepository('AppBundle:User')->findOneBy(array(
        'username' => $programs[$i]['owned by'],
      ));
          $program = new Program();
          $program->setUser($user);
          $program->setName($programs[$i]['name']);
          $program->setDescription($programs[$i]['description']);
          $program->setViews($programs[$i]['views']);
          $program->setDownloads($programs[$i]['downloads']);
          $program->setUploadedAt(new \DateTime($programs[$i]['upload time'], new \DateTimeZone('UTC')));
          $program->setCatrobatVersion(1);
          $program->setCatrobatVersionName($programs[$i]['version']);
          $program->setLanguageVersion(1);
          $program->setUploadIp('127.0.0.1');
          $program->setRemixCount(0);
          $program->setFilesize(0);
          $program->setVisible(isset($programs[$i]['visible']) ? $programs[$i]['visible'] == 'true' : true);
          $program->setUploadLanguage('en');
          $program->setApproved(false);
          $em->persist($program);

          $starter->addProgram($program);
      }

      $em->persist($starter);
      $em->flush();
  }

  /**
   * @Then /^the copy link should be "([^"]*)"$/
   */
  public function theCopyLinkShouldBe($url)
  {
      assertEquals($this->getSession()->getPage()->findField('copy-link')->getValue(), $this->locatePath($url));
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
    foreach($packages as $package) {
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
    foreach($categories as $category) {
      $new_category = new MediaPackageCategory();
      $new_category->setName($category['name']);
      $package = $em->getRepository('\Catrobat\AppBundle\Entity\MediaPackage')->findOneBy(array('name' => $category['package']));
      $new_category->setPackage($package);
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
     * @var $em EntityManager
     * @var $file_repo MediaPackageFileRepository
     */
    $em = $this->kernel->getContainer()->get('doctrine')->getManager();
    $file_repo = $this->kernel->getContainer()->get('mediapackagefilerepository');
    $files = $table->getHash();
    foreach($files as $file) {
      $new_file = new MediaPackageFile();
      $new_file->setName($file['name']);
      $new_file->setDownloads(0);
      $new_file->setExtension($file['extension']);
      $new_file->setActive($file['active']);
      $category = $em->getRepository('\Catrobat\AppBundle\Entity\MediaPackageCategory')->findOneBy(array('name' => $file['category']));
      $new_file->setCategory($category);

      $file_repo->saveMediaPackageFile(new File(self::MEDIAPACKAGE_DIR.$file['id'].'.'.$file['extension']), $file['id'], $file['extension']);

      $em->persist($new_file);
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
    assertEquals('image/'.$extension, $content_type);
  }

  /**
   * @Given /^the response code should be "([^"]*)"$/
   */
  public function theResponseCodeShouldBe($code)
  {
    $response = $this->getClient()->getResponse();
    assertEquals($code, $response->getStatusCode(), 'Wrong response code. '.$response->getContent());
  }

  /**
   * @Then /^the media file "([^"]*)" must have the download url "([^"]*)"$/
   */
  public function theMediaFileMustHaveTheDownloadUrl($id, $file_url)
  {
    $link = $this->getSession()->getPage()->find("css", ".mediafile-".$id." a")->getAttribute("href");
    assertTrue(is_int(strpos($link, $file_url)));
  }

  /**
   * @Then /^I should see media file with id "([^"]*)"$/
   */
  public function iShouldSeeMediaFileWithId($id)
  {
    $link = $this->getSession()->getPage()->find("css", ".mediafile-".$id);
    assertNotNull($link);
  }

  /**
   * @Then /^I should not see media file with id "([^"]*)"$/
   */
  public function iShouldNotSeeMediaFileWithId($id)
  {
    $link = $this->getSession()->getPage()->find("css", ".mediafile-".$id);
    assertNotNull($link);
  }

  /**
   * @Then /^the link of "([^"]*)" should open "([^"]*)"$/
   */
  public function theLinkOfShouldOpen($identifier, $url_type)
  {
    switch ($identifier) {
      case "image":
        $class_name = "image-container";
        break;

      case "download":
        $class_name = "download-container";
        break;

      default:
        break;
    }
    assertTrue(strlen($class_name) > 0);

    switch ($url_type) {
      case "download":
        $url_text = "pocketcode/download";
        break;

      case "popup":
        $url_text = "program.showUpdateAppPopup";
        break;

      default:
        break;
    }
    assertTrue(strlen($url_text) > 0);

    $selector = "." . $class_name . " a";
    $href_value = $this->getSession()->getPage()->find('css', $selector)->getAttribute('href');
    assertTrue(is_int(strpos($href_value, $url_text)));
  }

  /**
   * @Then /^I see the "([^"]*)" popup$/
   */
  public function iSeeThePopup($arg1)
  {
    switch($arg1) {
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
    switch($arg1) {
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
   * @Then /^I click on the propgram popup button$/
   */
  public function iClickOnThePropgramPopupButton()
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
    if ($program === null) {
      throw new \Exception('Program not found: ' + $arg1);
    }
    $router = $this->kernel->getContainer()->get('router');
    $url = $router->generate('ci_download', array('id' => $program->getId(), 'flavor' => 'pocketcode'));
    $this->getClient()->request('GET', $url, array(), array());
  }

  /**
   * @Then /^I should receive the apk file$/
   */
  public function iShouldReceiveTheApkFile()
  {
    $content_type = $this->getClient()->getResponse()->headers->get('Content-Type');
    $code = $this->getClient()->getResponse()->getStatusCode();
    assertEquals(200, $code);
    assertEquals('application/vnd.android.package-archive', $content_type);
  }

  /**
   * @Then /^I should receive an application file$/
   */
  public function iShouldReceiveAnApplicationFile()
  {
    $content_type = $this->getClient()->getResponse()->headers->get('Content-Type');
    $code = $this->getClient()->getResponse()->getStatusCode();
    assertEquals(200, $code);
    assertEquals('application/zip', $content_type);
  }

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////// Getter & Setter

  /**
   * @return \Symfony\Bundle\FrameworkBundle\Client
   */
  public function getClient()
  {
    if ($this->client == null) {
      $this->client = $this->kernel->getContainer()->get('test.client');
    }

    return $this->client;
  }

    /**
   * @When /^I switch to popup window$/
   */
  public function iSwitchToPopupWindow()
  {
      $this->getSession()->wait(6000);
      $page = $this->getSession()->getPage();
      $window_names = $this->getSession()->getDriver()->getWindowNames();
      foreach ($window_names as $name) {
          echo $name;
          if($page->find('css', '#facebook') || $page->find('css', '.google-header-bar centered')
              || $page->find('css', '#approval_container') || $page->find('css', '#gaia_firstform')) {
              break;
          }
          $this->getSession()->switchToWindow($name);
      }
      $this->getSession()->wait(1000);
  }

    /**
     * @Then /^I log in to Facebook with valid credentials$/
     */
    public function iLogInToFacebookWithEmailAndPassword()
    {
        $mail = $this->getParameterValue('facebook_testuser_mail');
        $pw = $this->getParameterValue('facebook_testuser_pw');
        echo 'Login with mail address ' . $mail . ' and pw ' . $pw . "\n";
        $this->getSession()->wait(1000);
        $page = $this->getSession()->getPage();
        if($page->find('css', '#facebook') && $page->find('css', '#login_form')) {
            echo 'facebook login form appeared' . "\n";
            $page->fillField('email',$mail);
            $page->fillField('pass',$pw);
            $button = $page->findById('u_0_2');
            assertTrue($button != null);
            $button->press();
        } else if($page->find('css', '#facebook') && $page->find('css', '#u_0_1')) {
            echo 'facebook reauthentication login form appeared' . "\n";
            $page->fillField('pass',$pw);
            $button = $page->findById('u_0_0');
            assertTrue($button != null);
            $this->getSession()->wait(500);
            $button->press();
        } else {
            assertTrue(false, 'No Facebook form appeared!' . "\n");
        }
        $this->getSession()->switchToWindow(null);
        $this->getSession()->wait(1000);

        $this->iSwitchToPopupWindow();
        if($page->find('css', '#facebook') && $page->find('css', '._1a_q') ) {
            echo 'facebook authentication login form appeared' . "\n";
            $button = $page->findButton('__CONFIRM__');
            assertTrue($button != null);
            $this->getSession()->wait(500);
            $button->press();
            $this->getSession()->switchToWindow(null);
            $this->getSession()->wait(1000);
        }
    }

    /**
     * @Then /^I log in to Google with valid credentials$/
     */
    public function iLogInToGoogleWithEmailAndPassword()
    {
        $mail = $this->getParameterValue('google_testuser_mail');
        $pw = $this->getParameterValue('google_testuser_pw');
        echo 'Login with mail address ' . $mail . ' and pw ' . $pw . "\n";
        $this->getSession()->wait(3000);
        $page = $this->getSession()->getPage();
        if($page->find('css', '#approval_container') &&
            $page->find('css', '#submit_approve_access')) {
                $this->approveGoogleAccess();
        } else if($page->find('css', '.google-header-bar centered') &&
            $page->find('css', '.signin-card clearfix')) {
            $this->signInWithGoogleEMailAndPassword($mail, $pw);
        } else if($page->find('css', '#gaia_firstform') &&
                  $page->find('css', '#Email') &&
                  $page->find('css', '#Passwd-hidden')
                  ) {
                $this->signInWithGoogleEMail($mail, $pw);
        }
        else {
            assertTrue(false, 'No Google form appeared!' . "\n");
        }
        $this->getSession()->wait(1000);
    }

    private function approveGoogleAccess(){
        echo 'Google Approve Access form appeared' . "\n";
        $page = $this->getSession()->getPage();
        $button = $page->findById('submit_approve_access');
        assertTrue($button != null);
        $this->getSession()->wait(1500);
        $button->press();
        $this->getSession()->switchToWindow(null);
    }

    private function signInWithGoogleEMailAndPassword($mail, $pw){
        echo 'Google login form with E-Mail and Password appeared' . "\n";
        $page = $this->getSession()->getPage();

        $page->fillField('Email',$mail);
        $page->fillField('Passwd',$pw);
        $button = $page->findById('signIn');
        assertTrue($button != null);
        $button->press();
        $this->getSession()->wait(2000);

        $this->approveGoogleAccess();
    }

    private function signInWithGoogleEMail($mail, $pw){
        echo 'Google Signin with E-Mail form appeared' . "\n";
        $page = $this->getSession()->getPage();

        $page->fillField('Email',$mail);
        $button = $page->findById('next');
        assertTrue($button != null);
        $button->press();
        $this->getSession()->wait(2000);
        if($page->find('css', '#gaia_firstform') &&
            $page->find('css', '#Email-hidden') &&
            $page->find('css', '#Passwd')
        ) {
            $this->signInWithGooglePassword($pw);
        }
    }

    private function signInWithGooglePassword($pw){
        echo 'Google Signin with Password form appeared' . "\n";
        $page = $this->getSession()->getPage();

        $page->fillField('Passwd',$pw);
        $button = $page->findById('signIn');
        assertTrue($button != null);
        $button->press();
        $this->getSession()->wait(2000);
        if($page->find('css', '#approval_container') &&
            $page->find('css', '#submit_approve_access')) {
            $this->approveGoogleAccess();
        }
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
        for($i = 0; $i < count($users); $i++)
        {
            $user = $user_manager->findUserByUsername($users[$i]["name"]);
            assertNotNull($user);
            assertTrue($user->getUsername() == $users[$i]["name"],
                'Name wrong' . $users[$i]["name"] . 'expected, but ' . $user->getUsername() . ' found.');
            assertTrue($user->getEmail() == $users[$i]["email"],
                'E-Mail wrong' . $users[$i]["email"] . 'expected, but ' . $user->getEmail() . ' found.');
            assertTrue($user->getCountry() == $users[$i]["country"],
                'Country wrong' . $users[$i]["country"] . 'expected, but ' . $user->getCountry() . ' found.');
            if($user->getFacebookUid() != ''){
                assertTrue($user->getFacebookAccessToken() != '', 'no Facebook access token present');
                assertTrue($user->getFacebookUid() == $users[$i]["facebook_uid"], 'Facebook UID wrong');
                assertTrue($user->getFacebookName() == $users[$i]["facebook_name"], 'Facebook name wrong');
            }
            if($user->getGplusUid() != ''){
                assertTrue($user->getGplusAccessToken() != '', 'no GPlus access token present');
                assertTrue($user->getGplusIdToken() != '', 'no GPlus id token present');
                assertTrue($user->getGplusRefreshToken() != '', 'no GPlus refresh token present');
                assertTrue($user->getGplusUid() == $users[$i]["google_uid"], 'Google UID wrong');
                assertTrue($user->getGplusName() == $users[$i]["google_name"], 'Google name wrong');
            }
        }
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
        $this->getSession()->wait(10000, 'window.location.href.search("profile") == -1');
        $this->getSession()->wait(1000);
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
     * @Then /^I choose the username '([^']*)'$/
     */
    public function iChooseTheUsername($arg1)
    {
        $this->getSession()->wait(2000);
        $page = $this->getSession()->getPage();

        $button = $page->findById('btn_oauth_username');
        assertTrue($button != null);
        assertTrue($button->hasAttribute('disabled'));

        $page->fillField('dialog_oauth_username_input',$arg1);
        $this->getSession()->wait(400);
        assertFalse($button->hasAttribute('disabled'));

        $page->fillField('dialog_oauth_username_input','');
        $this->getSession()->wait(400);
        assertTrue($button->hasAttribute('disabled'));

        $page->fillField('dialog_oauth_username_input',$arg1);
        $this->getSession()->wait(400);
        $button->press();
        $this->getSession()->wait(10000, 'window.location.href.search("login") == -1');
        $this->getSession()->wait(2000);
    }

    private function getParameterValue($name) {
        $myfile = fopen("app/config/parameters.yml", "r") or die("Unable to open file!");
        while(!feof($myfile)) {
            $line = fgets($myfile);
            if(strpos($line, $name) != false) {
                fclose($myfile);
                return substr(trim($line), strpos(trim($line), ':') + 2);
            }
        }
        fclose($myfile);
        assertTrue(false, 'No entry found in parameters.yml!');
    }

    /**
     * @Then /^I should see "([^"]*)" "([^"]*)" tutorial banners$/
     */
    public function iShouldSeeTutorialBanners($count, $view)
    {
        if($view == "desktop") {
            for ($i = 1; ; $i++) {
                $img = $this->getSession()->getPage()->findById('tutorial-' . $i);
                if ($img == null)
                    break;
            }
            assertEquals($count,$i-1);
        } elseif ($view == "mobile") {
            for ($i = 1; ; $i++) {
                $img = $this->getSession()->getPage()->findById('tutorial-mobile-' . $i);
                if ($img == null)
                    break;
            }
            assertEquals($count,$i-1);
        } else {
            assertTrue(false);
        }
    }

    /**
     * @When /^I click on the "([^"]*)" banner$/
     */
    public function iClickOnTheBanner($numb)
    {
        switch ($numb) {
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
                assertTrue(false);
                break;
        }
    }

    /**
     * @Then /^a link to the Facebook post should be displayed$/
     */
    public function aLinkToTheFacebookPostShouldBeDisplayed()
    {
        assertTrue($this->getSession()->getPage()->findById('facebook-post-link')->isVisible());
    }

    /**
     * @Then /^a link to the Facebook post should not be displayed$/
     */
    public function aLinkToTheFacebookPostShouldNotBeDisplayed()
    {
        assertFalse($this->getSession()->getPage()->findById('facebook-post-link')->isVisible());
    }
}
