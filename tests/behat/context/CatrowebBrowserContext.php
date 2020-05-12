<?php

namespace Tests\behat\context;

use App\Catrobat\Services\ApkRepository;
use App\Catrobat\Services\Ci\JenkinsDispatcher;
use App\Catrobat\Services\StatisticsService;
use App\Catrobat\Services\TestEnv\FixedTokenGenerator;
use App\Catrobat\Services\TokenGenerator;
use App\Entity\CatroNotification;
use App\Entity\ClickStatistic;
use App\Entity\GameJam;
use App\Entity\Program;
use App\Entity\UserComment;
use App\Entity\UserLikeSimilarityRelation;
use App\Entity\UserRemixSimilarityRelation;
use App\Repository\GameJamRepository;
use App\Utils\TimeUtils;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Exception\ExpectationException;
use Behat\Mink\Exception\ResponseTextException;
use DateInterval;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use PHPUnit\Framework\Assert;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class CatrowebBrowserContext.
 *
 * Extends the basic browser utilities with Catroweb specific actions.
 */
class CatrowebBrowserContext extends BrowserContext
{
  const AVATAR_DIR = './tests/testdata/DataFixtures/AvatarImages/';

  const ALREADY_IN_DB_USER = 'AlreadyinDB';

  private bool $use_real_oauth_javascript_code = false;

  private GameJam $game_jam;

  private ?Program $my_program;

  // -------------------------------------------------------------------------------------------------------------------
  //  Initialization
  // -------------------------------------------------------------------------------------------------------------------

  /**
   * Initializes context with parameters from behat.yml.
   */
  public function __construct()
  {
    $this->setOauthServiceParameter('0');
    setlocale(LC_ALL, 'en');
  }

  public static function getAcceptedSnippetType(): string
  {
    return 'regex';
  }

  // -------------------------------------------------------------------------------------------------------------------
  //  Hook
  // -------------------------------------------------------------------------------------------------------------------

  /**
   * @BeforeScenario
   *
   * @throws Exception
   */
  public function initACL(): void
  {
    $application = new Application($this->getKernel());
    $application->setAutoExit(false);

    $input = new ArrayInput(['command' => 'sonata:admin:setup-acl']);

    $return = $application->run($input, new NullOutput());
    Assert::assertNotNull($return, 'Oh no!');
  }

  /**
   * @BeforeScenario @RealGeocoder
   */
  public function activateRealGeocoderService(): void
  {
    $this->getSymfonyService(StatisticsService::class)->useRealService(true);
  }

  /**
   * @AfterScenario @RealGeocoder
   */
  public function deactivateRealGeocoderService(): void
  {
    $this->getSymfonyService(StatisticsService::class)->useRealService(false);
  }

  /**
   * @AfterScenario
   */
  public function disableProfiler(): void
  {
    $this->getSymfonyService('profiler')->disable();
  }

  /**
   * @BeforeScenario @RealOAuth
   */
  public function activateRealOAuthService(): void
  {
    $this->setOauthServiceParameter('1');
    $this->use_real_oauth_javascript_code = true;
  }

  /**
   * @AfterScenario @RealOAuth
   */
  public function deactivateRealOAuthService(): void
  {
    $this->setOauthServiceParameter('0');
    $this->use_real_oauth_javascript_code = false;
  }

  //--------------------------------------------------------------------------------------------------------------------
  //  Authentication
  //--------------------------------------------------------------------------------------------------------------------

  /**
   * @Given /^I( [^"]*)? log in as "([^"]*)" with the password "([^"]*)"$/
   * @Given /^I( [^"]*)? log in as "([^"]*)"$/
   *
   * @param mixed $try_to
   * @param mixed $username
   * @param mixed $password
   */
  public function iAmLoggedInAsWithThePassword($try_to, $username, $password = '123456'): void
  {
    $this->visit('/app/login');
    $this->iWaitForThePageToBeLoaded();
    $this->fillField('_username', $username);
    $this->fillField('password', $password);
    $this->pressButton('Login');
    $this->iWaitForThePageToBeLoaded();
    if ('try to' === $try_to)
    {
      $this->assertPageNotContainsText('Your password or username was incorrect');
    }
    $this->getUserDataFixtures()->setCurrentUserByUsername($username);
  }

  /**
   * @Given I am logged in
   */
  public function iAmLoggedIn(): void
  {
    $this->insertUser(['name' => 'Catrobat', 'password' => '123456']);
    $this->iAmLoggedInAsWithThePassword('', 'Catrobat');
  }

  /**
   * @When /^I logout$/
   */
  public function iLogout(): void
  {
    $this->assertElementOnPage('#btn-logout');
    $this->getSession()->getPage()->find('css', '#btn-logout')->click();
    $this->getUserDataFixtures()->setCurrentUser(null);
  }

  /**
   * @Then /^I should be logged (in|out)$/
   *
   * @param mixed $arg1
   */
  public function iShouldBeLoggedIn($arg1): void
  {
    if ('in' === $arg1)
    {
      $this->assertPageNotContainsText('Your password or username was incorrect.');
      $this->getSession()->wait(2_000, 'window.location.href.search("login") == -1');
      $this->assertElementNotOnPage('#btn-login');
      $this->assertElementOnPage('#btn-logout');
    }
    if ('out' == $arg1)
    {
      $this->iShouldNotBeLoggedIn();
    }
  }

  /**
   * @Then /^I should not be logged in$/
   */
  public function iShouldNotBeLoggedIn(): void
  {
    $this->getSession()->wait(1_000, 'window.location.href.search("profile") == -1');
    $this->assertElementOnPage('#btn-login');
    $this->assertElementNotOnPage('#btn-logout');
  }

  /**
   * @Then /^I should see the logout button$/
   */
  public function iShouldSeeTheLogoutButton(): void
  {
    $logout_button = $this->getSession()->getPage()->findById('btn-logout');
    Assert::assertTrue(
      null != $logout_button && $logout_button->isVisible(),
      'The Logout button is not visible!'
    );
  }

  /**
   * @Given /^I use a (debug|release) build of the Catroid app$/
   *
   * @param mixed $build_type
   */
  public function iUseASpecificBuildTypeOfCatroidApp($build_type): void
  {
    $this->iUseTheUserAgentParameterized('0.998', 'PocketCode', '0.9.60', $build_type);
  }

  /**
   * @Given /^I use an ios app$/
   */
  public function iUseAnIOSApp(): void
  {
    // see org.catrobat.catroid.ui.WebViewActivity
    $platform = 'iPhone';
    $user_agent = ' Platform/'.$platform;
    $this->iUseTheUserAgent($user_agent);
  }

  /**
   * @Given /^I use a specific "([^"]*)" themed app$/
   *
   * @param mixed $theme
   */
  public function iUseASpecificThemedApp($theme): void
  {
    $this->iUseTheUserAgentParameterized('0.998', 'PocketCode', '0.9.60',
      'release', $theme);
  }

  //--------------------------------------------------------------------------------------------------------------------
  //  Everything -> ToDo CleanUp
  //--------------------------------------------------------------------------------------------------------------------

  /**
   * @Then the logos src should be :logo_src
   *
   * @param mixed $logo_src
   */
  public function theLogosSrcShouldBe($logo_src): void
  {
    $image = $this->getSession()->getPage()->findAll('css', '#logo');
    $img_url = $image[0]->getAttribute('src');
    Assert::assertNotFalse(strpos($img_url, (string) $logo_src));
  }

  /**
   * @Then the logos src should not be :logo_src
   *
   * @param mixed $logo_src
   */
  public function theLogosSrcShouldNotBe($logo_src): void
  {
    $image = $this->getSession()->getPage()->findAll('css', '#logo');
    $img_url = $image[0]->getAttribute('src');
    Assert::assertFalse(strpos($img_url, (string) $logo_src));
  }

  /**
   * @Given /^I set the cookie "([^"]+)" to "([^"]*)"$/
   */
  public function iSetTheCookie(string $cookie_name, string $cookie_value): void
  {
    if ('NULL' === $cookie_value)
    {
      $cookie_value = null;
    }
    $this->getSession()->setCookie($cookie_name, $cookie_value);
  }

  /**
   * @When /^I open the menu$/
   */
  public function iOpenTheMenu(): void
  {
    $sidebar_open = $this->getSession()->getPage()->find('css', '#sidebar')->isVisible();
    if (!$sidebar_open)
    {
      $this->getSession()->getPage()->find('css', '#top-app-bar__btn-sidebar-toggle')->click();
    }
    $this->iWaitForAjaxToFinish();
  }

  /**
   * @Then /^I should see (\d+) "([^"]*)"$/
   *
   * @param mixed $element_count
   * @param mixed $css_selector
   */
  public function iShouldSeeNumberOfElements($element_count, $css_selector): void
  {
    $elements = $this->getSession()->getPage()->findAll('css', $css_selector);
    $count = 0;
    foreach ($elements as $element)
    {
      if ($element->isVisible())
      {
        ++$count;
      }
    }
    Assert::assertEquals($element_count, $count);
  }

  /**
   * @Then /^I should see a node with id "([^"]*)" having name "([^"]*)" and username "([^"]*)"$/
   *
   * @param mixed $node_id
   * @param mixed $expected_node_name
   * @param mixed $expected_username
   */
  public function iShouldSeeANodeWithNameAndUsername($node_id, $expected_node_name, $expected_username): void
  {
    /** @var array $result */
    $result = $this->getSession()->evaluateScript(
      "return { nodeName: RemixGraph.getInstance().getNodes().get('".$node_id."').name,
                     username: RemixGraph.getInstance().getNodes().get('".$node_id."').username };"
    );
    $actual_node_name = is_array($result['nodeName']) ? implode('', $result['nodeName']) : $result['nodeName'];
    $actual_username = $result['username'];
    Assert::assertEquals($expected_node_name, $actual_node_name);
    Assert::assertEquals($expected_username, $actual_username);
  }

  /**
   * @Then /^I should see an unavailable node with id "([^"]*)"$/
   *
   * @param mixed $node_id
   */
  public function iShouldSeeAnUnavailableNodeWithId($node_id): void
  {
    /** @var array $result */
    $result = $this->getSession()->evaluateScript(
      'return RemixGraph.getInstance().getNodes().get("'.$node_id.'");'
    );

    Assert::assertTrue(isset($result['id']));
    Assert::assertEquals($node_id, $result['id']);
    Assert::assertFalse(isset($result['name']));
    Assert::assertFalse(isset($result['username']));
  }

  /**
   * @Then /^I should see an edge from "([^"]*)" to "([^"]*)"$/
   *
   * @param mixed $from_id
   * @param mixed $to_id
   */
  public function iShouldSeeAnEdgeFromTo($from_id, $to_id): void
  {
    /** @var array $result */
    $result = $this->getSession()->evaluateScript(
      "return RemixGraph.getInstance().getEdges().get().filter(
        function (edge) { return edge.from === '".$from_id."' && edge.to === '".$to_id."'; }
      );"
    );
    Assert::assertCount(1, $result);
    Assert::assertEquals($from_id, $result[0]['from']);
    Assert::assertEquals($to_id, $result[0]['to']);
  }

  /**
   * @Then /^I should see the featured slider$/
   *
   * @throws ExpectationException
   */
  public function iShouldSeeTheFeaturedSlider(): void
  {
    $this->assertSession()->responseContains('featured');
    Assert::assertTrue($this->getSession()->getPage()->findById('feature-slider')->isVisible());
  }

  /**
   * @Then /^I should see the welcome section$/
   */
  public function iShouldSeeTheWelcomeSection(): void
  {
    Assert::assertTrue($this->getSession()->getPage()->findById('welcome-section')->isVisible());
  }

  /**
   * @Then /^I should not see the welcome section$/
   */
  public function iShouldNotSeeTheWelcomeSection(): void
  {
    Assert::assertNull($this->getSession()->getPage()->findById('welcome-section'));
  }

  /**
   * @Then /^I should see ([^"]*) programs$/
   *
   * @param mixed $arg1
   *
   * @throws ElementNotFoundException
   * @throws ExpectationException
   */
  public function iShouldSeePrograms($arg1): void
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

      case 'scratchRemixes':
        $this->assertSession()->elementExists('css', '#scratchRemixes');
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
   *
   * @param mixed $arg1
   *
   * @throws ExpectationException
   */
  public function theSelectedLanguageShouldBe($arg1): void
  {
    switch ($arg1) {
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
   *
   * @param mixed $arg1
   */
  public function iSwitchTheLanguageTo($arg1): void
  {
    switch ($arg1) {
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
   *
   * @param mixed $arg1
   * @param mixed $arg2
   *
   * @throws ExpectationException
   */
  public function iShouldSeeAHelpImage($arg1, $arg2): void
  {
    $arg1 = trim($arg1);

    $this->assertSession()->responseContains('help-desktop');
    $this->assertSession()->responseContains('help-mobile');

    if ('big' === $arg1)
    {
      Assert::assertTrue($this->getSession()->getPage()->find('css', '.help-desktop')->isVisible());
      Assert::assertFalse($this->getSession()->getPage()->find('css', '.help-mobile')->isVisible());
    }
    elseif ('small' === $arg1)
    {
      Assert::assertFalse($this->getSession()->getPage()->find('css', '.help-desktop')->isVisible());
      Assert::assertTrue($this->getSession()->getPage()->find('css', '.help-mobile')->isVisible());
    }
    elseif ('' === $arg1)
    {
      Assert::assertTrue($this->getSession()->getPage()->find('css', '.help-split')->isVisible());
    }
    else
    {
      Assert::assertTrue(false);
    }

    $img = null;
    $path = null;

    switch ($arg2) {
      case 'Hour of Code':
        if ('big' === $arg1)
        {
          $img = $this->getSession()->getPage()->findById('hour-of-code-desktop');
          $path = '/images/help/hour_of_code.png';
        }
        elseif ('small' === $arg1)
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
        if ('big' === $arg1)
        {
          $img = $this->getSession()->getPage()->findById('alice-tut-desktop');
          $path = '/images/help/alice_tut.png';
        }
        elseif ('small' === $arg1)
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
        if ('big' === $arg1)
        {
          $img = $this->getSession()->getPage()->findById('step-by-step-desktop');
          $path = '/images/help/step_by_step.png';
        }
        elseif ('small' === $arg1)
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
        if ('big' === $arg1)
        {
          $img = $this->getSession()->getPage()->findById('edu-desktop');
          $path = '/images/help/edu_site.png';
        }
        elseif ('small' === $arg1)
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
        if ('big' === $arg1)
        {
          $img = $this->getSession()->getPage()->findById('discuss-desktop');
          $path = '/images/help/discuss.png';
        }
        elseif ('small' === $arg1)
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

    if (null != $img)
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
   * @Then /^I click on the first "([^"]*)" button$/
   *
   * @param mixed $arg1
   *
   * @throws ElementNotFoundException
   */
  public function iClickOnTheFirstButton($arg1): void
  {
    $this->assertSession()->elementExists('css', $arg1);

    $this
      ->getSession()
      ->getPage()
      ->find('css', $arg1)
      ->click()
    ;
    $this->getSession()->wait(500);
  }

  /**
   * @Then /^I click on the column with the name "([^"]*)"$/
   *
   * @param mixed $arg1
   *
   * @throws Exception
   */
  public function iClickOntheColumnName($arg1): void
  {
    $page = $this->getSession()->getPage();
    switch ($arg1)
    {
      case 'Name':
        $page
          ->find('xpath', '//div[1]/div/section[2]/div[2]/div/div/div[1]/table/thead/tr/th[3]/a')
          ->click()
        ;
        break;
      case 'Views':
        $page
          ->find('xpath', '//div[1]/div/section[2]/div[2]/div/div/div[1]/table/thead/tr/th[5]/a')
          ->click()
        ;
        break;
      case 'Downloads':
        $page
          ->find('xpath', '//div[1]/div/section[2]/div[2]/div/div/div[1]/table/thead/tr/th[6]/a')
          ->click()
        ;
        break;
      case 'Upload Time':
        $page
          ->find('xpath', '//div[1]/div/section[2]/div[2]/div/div/div[1]/table/thead/tr/th[1]/a')
          ->click()
        ;
        break;

      default:
        throw new Exception('Wrong Option');
    }
  }

  /**
   * @Then /^I change the visibility of the project number "([^"]*)" in the list to "([^"]*)"$/
   *
   * @param mixed $program_number
   * @param mixed $visibility
   *
   * @throws Exception
   */
  public function iChangeTheVisibilityOfTheProgram($program_number, $visibility): void
  {
    ///param program number contains the number of the program position in the list on the admin page
    ///
    $page = $this->getSession()->getPage();

    ///click the visibility button (yes/no)
    $page
      ->find('xpath', '//div[1]/div/section[2]/div[2]/div/div/div[1]/table/tbody/tr['.$program_number.']/td[9]/span')
      ->click()
    ;

    $this->iSelectTheOptionInThePopup($visibility);
  }

  /**
   * @Then /^I change the approval of the project number "([^"]*)" in the list to "([^"]*)"$/
   *
   * @param mixed $program_number
   * @param mixed $approved
   *
   * @throws Exception
   */
  public function iChangeTheApprovalOfTheProject($program_number, $approved): void
  {
    ///param program number contains the number of the program position in the list on the admin page
    ///
    $page = $this->getSession()->getPage();
    ///click the visibility button (yes/no)
    $page
      ->find('xpath', '//div[1]/div/section[2]/div[2]/div/div/div[1]/table/tbody/tr['.$program_number.']/td[8]/span')
      ->click()
    ;

    $this->iSelectTheOptionInThePopup($approved);
  }

  /**
   * @Then /^I change the flavor of the project number "([^"]*)" in the list to "([^"]*)"$/
   *
   * @param mixed $program_number
   * @param mixed $flavor
   *
   * @throws Exception
   */
  public function iChangeTheFlavorOfTheProject($program_number, $flavor): void
  {
    ///param program number contains the number of the program position in the list on the admin page

    $page = $this->getSession()->getPage();
    ///click the visibility button (yes/no)
    $page
      ->find('xpath', '//div[1]/div/section[2]/div[2]/div/div/div[1]/table/tbody/tr['.$program_number.']/td[4]/span')
      ->click()
    ;
    //click the input on the popup to show yes or no option
    $page
      ->find('css', '.editable-input')
      ->click()
    ;

    switch ($flavor)
    {
      case 'pocketcode':
        $page
          ->find('css', 'select.form-control > option:nth-child(1)')
          ->click()
        ;
        break;
      case 'pocketalice':
        $page
          ->find('css', 'select.form-control > option:nth-child(2)')
          ->click()
        ;
        break;
      case 'pocketgalaxy':
        $page
          ->find('css', 'select.form-control > option:nth-child(3)')
          ->click()
        ;
        break;
      case 'phirocode':
        $page
          ->find('css', 'select.form-control > option:nth-child(4)')
          ->click()
        ;
        break;
      case 'luna':
        $page
          ->find('css', 'select.form-control > option:nth-child(5)')
          ->click()
        ;
        break;
      case 'create@school':
        $page
          ->find('css', 'select.form-control > option:nth-child(6)')
          ->click()
        ;
        break;
      case 'embroidery':
        $page
          ->find('css', 'select.form-control > option:nth-child(7)')
          ->click()
        ;
        break;
      case 'arduino':
        $page
          ->find('css', 'select.form-control > option:nth-child(8)')
          ->click()
        ;
        break;
      default:
        throw new Exception('Wrong flavor');
    }

    //click button to confirm the selection
    $page
      ->find('css', 'button.btn-sm:nth-child(1)')
      ->click()
    ;
  }

  /**
   * @Then /^I click on the username "([^"]*)"$/
   *
   * @param mixed $username
   *
   * @throws ElementNotFoundException
   */
  public function iClickOnTheUsername($username): void
  {
    $this->assertSession()->elementExists('xpath', "//a[contains(text(),'".$username."')]");

    $page = $this->getSession()->getPage();
    $page
      ->find('xpath', "//a[contains(text(),'".$username."')]")
      ->click()
    ;
  }

  /**
   * @Then /^I click on the program name "([^"]*)"$/
   *
   * @param mixed $program_name
   *
   * @throws ElementNotFoundException
   */
  public function iClickOnTheProgramName($program_name): void
  {
    $this->assertSession()->elementExists('xpath', "//a[contains(text(),'".$program_name."')]");

    $page = $this->getSession()->getPage();
    $page
      ->find('xpath', "//a[contains(text(),'".$program_name."')]")
      ->click()
    ;
  }

  /**
   * @Then /^I click on the show button of the program number "([^"]*)" in the list$/
   *
   * @param mixed $program_number
   *
   * @throws ElementNotFoundException
   */
  public function iClickOnTheShowButton($program_number): void
  {
    $page = $this->getSession()->getPage();
    $this->assertSession()->elementExists('xpath',
      '//div[1]/div/section[2]/div[2]/div/div/div[1]/table/tbody/tr['.$program_number.']/td[10]/div/a');

    $page
      ->find('xpath', '//div[1]/div/section[2]/div[2]/div/div/div[1]/table/tbody/tr['.$program_number.']/td[10]/div/a')
      ->click()
    ;
  }

  /**
   * @When /^I report program (\d+) with category "([^"]*)" and note "([^"]*)" in Browser$/
   *
   * @param mixed $program_id
   * @param mixed $category
   * @param mixed $note
   *
   * @throws ElementNotFoundException
   */
  public function iReportProgramWithNoteInBrowser($program_id, $category, $note): void
  {
    $this->visit('app/project/'.$program_id);
    $this->iWaitForThePageToBeLoaded();
    $this->iClick('#top-app-bar__btn-report-project');
    $this->iWaitForAjaxToFinish();
    $this->fillField('report-reason', $note);
    switch ($category) {
      case 'copyright':
        $this->iClickTheRadiobutton('#report-copyright');
        break;
      case 'inappropriate':
        $this->iClickTheRadiobutton('#report-inappropriate');
        break;
      case 'spam':
        $this->iClickTheRadiobutton('#report-spam');
        break;
      case 'dislike':
        $this->iClickTheRadiobutton('#report-dislike');
        break;
    }

    $this->iClick('.swal2-confirm');
    $this->iWaitForAjaxToFinish();
    $this->assertPageContainsText('Your report was successfully sent!');
  }

  /**
   * @Given /^I write "([^"]*)" in textbox$/
   *
   * @param mixed $arg1
   */
  public function iWriteInTextbox($arg1): void
  {
    $textarea = $this->getSession()->getPage()->find('css', '.msg');
    Assert::assertNotNull($textarea, 'Textarea not found');
    $textarea->setValue($arg1);
  }

  /**
   * @Given /^I write "([^"]*)" in textarea$/
   *
   * @param mixed $arg1
   */
  public function iWriteInTextarea($arg1): void
  {
    $textarea = $this->getSession()->getPage()->find('css', '#edit-credits');
    Assert::assertNotNull($textarea, 'Textarea not found');
    $textarea->setValue($arg1);
  }

  /**
   * @Then /^I click the "([^"]*)" RadioButton$/
   *
   * @param mixed $arg1
   */
  public function iClickTheRadiobutton($arg1): void
  {
    $page = $this->getSession()->getPage();
    $radioButton = $page->find('css', $arg1);
    $radioButton->click();
  }

  /**
   * @Then /^comments or catro notifications should not exist$/
   */
  public function commentsOrCatroNotificationsShouldNotExist(): void
  {
    $em = $this->getManager();
    $comments = $em->getRepository(UserComment::class)->findAll();
    $notifications = $em->getRepository(CatroNotification::class)->findAll();
    Assert::assertTrue(!$comments && !$notifications);
  }

  /**
   * @Then /^"([^"]*)" must be selected in "([^"]*)"$/
   *
   * @param mixed $country
   * @param mixed $select
   */
  public function mustBeSelectedIn($country, $select): void
  {
    $field = $this->getSession()->getPage()->findField($select);
    Assert::assertTrue($country === $field->getValue());
  }

  /**
   * @When /^(?:|I )attach the avatar "(?P<path>[^"]*)" to "(?P<field>(?:[^"]|\\")*)"$/
   *
   * @param mixed $field
   * @param mixed $path
   *
   * @throws ElementNotFoundException
   */
  public function attachFileToField($field, $path): void
  {
    $field = $this->fixStepArgument($field);
    $this->getSession()->getPage()->attachFileToField($field, realpath(self::AVATAR_DIR.$path));
  }

  /**
   * @Then /^the avatar img tag should( [^"]*)? have the "([^"]*)" data url$/
   *
   * @param mixed $not
   * @param mixed $name
   */
  public function theAvatarImgTagShouldHaveTheDataUrl($not, $name): void
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

    switch ($name) {
      case 'logo.png':
        $logoUrl = 'data:image/png;base64,'.base64_encode(file_get_contents(self::AVATAR_DIR.'logo.png'));
        $isSame = ($source === $logoUrl);
        'not' === $not ? Assert::assertFalse($isSame) : Assert::assertTrue($isSame);
        break;

      case 'fail.tif':
        $failUrl = 'data:image/tiff;base64,'.base64_encode(file_get_contents(self::AVATAR_DIR.'fail.tif'));
        $isSame = ($source === $failUrl);
        'not' === $not ? Assert::assertFalse($isSame) : Assert::assertTrue($isSame);
        break;

      default:
        Assert::assertTrue(false);
    }
  }

  /**
   * @Then /^the project img tag should( [^"]*)? have the "([^"]*)" data url$/
   *
   * @param mixed $not
   * @param mixed $name
   *
   * @throws Exception
   */
  public function theProjectImgTagShouldHaveTheDataUrl($not, $name): void
  {
    $name = trim($name);
    $not = trim($not);

    $pre_source = $this->getSession()->getPage()->find('css', '#project-thumbnail-big');
    $source = 0;
    if (!is_null($pre_source))
    {
      $source = $pre_source->getAttribute('src');
    }
    else
    {
      Assert::assertTrue(false, "Couldn't find avatar in project-thumbnail-big");
    }
    $source = trim($source, '"');

    switch ($name) {
      case 'logo.png':
        $logoUrl = 'data:image/png;base64,'.base64_encode(file_get_contents(self::AVATAR_DIR.'logo.png'));
        $isSame = ($source === $logoUrl);
        'not' === $not ? Assert::assertFalse($isSame) : Assert::assertTrue($isSame);
        break;

      case 'fail.tif':
        $failUrl = 'data:image/tiff;base64,'.base64_encode(file_get_contents(self::AVATAR_DIR.'fail.tif'));
        $isSame = ($source === $failUrl);
        'not' === $not ? Assert::assertFalse($isSame) : Assert::assertTrue($isSame);
        break;

      default:
        Assert::assertTrue(false);
    }
  }

  /**
   * @When /^I press enter in the search bar$/
   */
  public function iPressEnterInTheSearchBar(): void
  {
    $this->getSession()->executeScript("$('#top-app-bar__search-form').trigger('submit')");
  }

  /**
   * @When /^I click the "([^"]*)" button$/
   *
   * @param mixed $arg1
   */
  public function iClickTheButton($arg1): void
  {
    $arg1 = trim($arg1);
    $page = $this->getSession()->getPage();
    $button = null;

    switch ($arg1) {
      case 'login':
        $button = $page->find('css', '#btn-login');
        break;
      case 'logout':
        $button = $page->find('css', '#btn-logout');
        break;
      case 'profile':
        $button = $page->find('css', '#btn-profile');
        break;
      case 'forgot pw or username':
        $button = $page->find('css', '#pw-request');
        break;
      case 'send':
        $button = $page->find('css', '#post-button');
        break;
      case 'show-more':
        $button = $page->find('css', '#show-more-button');
        break;
      case 'report-comment':
        $button = $page->find('css', '#report-button-4');
        break;
      case 'delete-comment':
        $button = $page->find('css', '#delete-button-4');
        break;
      case 'edit':
        $button = $page->find('css', '#edit-icon a');
        break;
      case 'password-edit':
        $button = $page->find('css', '#password-button');
        break;
      case 'email-edit':
        $button = $page->find('css', '#email-button');
        break;
      case 'country-edit':
        $button = $page->find('css', '#country-button');
        break;
      case 'name-edit':
        $button = $page->find('css', '#username-button');
        break;
      case 'avatar-edit':
        $button = $page->find('css', '#avatar-button');
        break;
      case 'save-edit':
        $button = $page->find('css', '.save-button');
        break;
      default:
        Assert::assertTrue(false);
    }
    Assert::assertNotNull($button, 'button '.$arg1.' not found');
    $button->click();
  }

  /**
   * @Then /^I should see marked "([^"]*)"$/
   *
   * @param mixed $arg1
   */
  public function iShouldSeeMarked($arg1): void
  {
    $page = $this->getSession()->getPage();
    $program = $page->find('css', $arg1);
    if (!$program->hasClass('visited-program'))
    {
      Assert::assertTrue(false);
    }
  }

  /**
   * @When /^I trigger Google login with approval prompt "([^"]*)"$/
   *
   * @param mixed $arg1
   */
  public function iTriggerGoogleLogin($arg1): void
  {
    $this->assertElementOnPage('#btn-login');
    $this->iClickTheButton('login');
    $this->assertPageAddress('/app/login');
    $this->getSession()->wait(200);
    $this->assertElementOnPage('#btn-login_google');
    $this->getSession()->executeScript('document.getElementById("gplus_approval_prompt").type = "text";');
    $this->getSession()->wait(200);
    $this->getSession()->getPage()->findById('gplus_approval_prompt')->setValue($arg1);
  }

  /**
   * @When /^I click Google login link "([^"]*)"$/
   *
   * @param mixed $arg1
   */
  public function iClickGoogleLoginLink($arg1): void
  {
    if ($this->use_real_oauth_javascript_code)
    {
      $this->clickLink('btn-login_google');
      if ('twice' === $arg1)
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
   * @Then /^the media file "([^"]*)" must have the download url "([^"]*)"$/
   *
   * @param mixed $id
   * @param mixed $file_url
   */
  public function theMediaFileMustHaveTheDownloadUrl($id, $file_url): void
  {
    $mediafile = $this->getSession()->getPage()->find('css', '#mediafile-'.$id);
    Assert::assertNotNull($mediafile, 'Mediafile not found!');
    $link = $mediafile->getAttribute('href');
    Assert::assertTrue(is_int(strpos($link, (string) $file_url)));
  }

  /**
   * @Then /^I should see media file with id "([^"]*)"$/
   *
   * @param mixed $id
   */
  public function iShouldSeeMediaFileWithId($id): void
  {
    $link = $this->getSession()->getPage()->find('css', '#mediafile-'.$id);
    Assert::assertNotNull($link);
  }

  /**
   * @Then /^I should not see media file with id "([^"]*)"$/
   *
   * @param mixed $id
   */
  public function iShouldNotSeeMediaFileWithId($id): void
  {
    $link = $this->getSession()->getPage()->find('css', '#mediafile-'.$id);
    Assert::assertNull($link);
  }

  /**
   * @Then /^I should see media file with id ([0-9]+) in category "([^"]*)"$/
   *
   * @param mixed $id
   * @param mixed $category
   */
  public function iShouldSeeMediaFileWithIdInCategory($id, $category): void
  {
    $link = $this->getSession()->getPage()
      ->find('css', '[data-name="'.$category.'"]')
      ->find('css', '#mediafile-'.$id)
    ;
    Assert::assertNotNull($link);
  }

  /**
   * @Then /^I should see ([0-9]+) media files? in category "([^"]*)"$/
   *
   * @param mixed $count
   * @param mixed $category
   */
  public function iShouldSeeNumberOfMediaFilesInCategory($count, $category): void
  {
    $elements = $this->getSession()->getPage()
      ->find('css', '[data-name="'.$category.'"]')
      ->findAll('css', '.mediafile')
    ;
    Assert::assertEquals($count, count($elements));
  }

  /**
   * @Then /^the link of "([^"]*)" should open "([^"]*)"$/
   *
   * @param mixed $identifier
   * @param mixed $url_type
   */
  public function theLinkOfShouldOpen($identifier, $url_type): void
  {
    $class_name = '';
    switch ($identifier) {
      case 'image':
        $class_name = 'image-container';
        break;

      case 'download':
        $class_name = 'download-container';
        break;

      default:
        break;
    }
    Assert::assertTrue(strlen($class_name) > 0);

    $url_text = '';
    switch ($url_type) {
      case 'download':
        $url_text = 'app/download';
        break;

      case 'popup':
        $url_text = 'program.showUpdateAppPopup';
        break;

      default:
        break;
    }
    Assert::assertTrue(strlen($url_text) > 0);

    $selector = '.'.$class_name.' a';
    $href_value = $this->getSession()->getPage()->find('css', $selector)->getAttribute('href');
    Assert::assertTrue(is_int(strpos($href_value, (string) $url_text)));
  }

  /**
   * @Then /^I see the "([^"]*)" popup$/
   *
   * @param mixed $arg1
   */
  public function iSeeThePopup($arg1): void
  {
    if ('update app' == $arg1)
    {
      $this->assertElementOnPage('#popup-info');
      $this->assertElementOnPage('#popup-background');
    }
  }

  /**
   * @Then /^I see not the "([^"]*)" popup$/
   *
   * @param mixed $arg1
   */
  public function iSeeNotThePopup($arg1): void
  {
    if ('update app' == $arg1)
    {
      $this->assertElementNotOnPage('#popup-info');
      $this->assertElementNotOnPage('#popup-background');
    }
  }

  /**
   * @Then /^I click the program download button$/
   *
   * @throws ElementNotFoundException
   */
  public function iClickTheProgramDownloadButton(): void
  {
    $this->iClick('#url-download');
  }

  /**
   * @Then /^I click the program image$/
   *
   * @throws ElementNotFoundException
   */
  public function iClickTheProgramImage(): void
  {
    $this->iClick('#url-image');
  }

  /**
   * @Then /^I click on the program popup background$/
   *
   * @throws ElementNotFoundException
   */
  public function iClickOnTheProgramPopupBackground(): void
  {
    $this->iClick('#popup-background');
  }

  /**
   * @Then /^I click on the program popup button$/
   *
   * @throws ElementNotFoundException
   */
  public function iClickOnTheProgramPopupButton(): void
  {
    $this->iClick('#btn-close-popup');
  }

  /**
   * @When /^I should see the video available at "([^"]*)"$/
   *
   * @param mixed $url
   */
  public function iShouldSeeElementWithIdWithSrc($url): void
  {
    $page = $this->getSession()->getPage();
    $video = $page->find('css', '#youtube-help-video');
    Assert::assertNotNull($video, 'Video not found on tutorial page!');
    Assert::assertTrue(false !== strpos($video->getAttribute('src'), (string) $url));
  }

  /**
   * @Then /^I log in to Google with valid credentials$/
   *
   * @throws ElementNotFoundException
   */
  public function iLogInToGoogleWithEmailAndPassword(): void
  {
    if ($this->use_real_oauth_javascript_code)
    {
      $mail = 'google_testuser_mail';
      $password = 'google_testuser_mail';
      echo 'Login with mail address '.$mail.' and pw '.$password."\n";
      $page = $this->getSession()->getPage();
      if ($page->find('css', '#approval_container') &&
        $page->find('css', '#submit_approve_access'))
      {
        $this->approveGoogleAccess();
      }
      elseif ($page->find('css', '.google-header-bar centered') &&
        $page->find('css', '.signin-card clearfix'))
      {
        $this->signInWithGoogleEMailAndPassword($mail, $password);
      }
      elseif ($page->find('css', '#gaia_firstform') &&
        $page->find('css', '#Email') &&
        $page->find('css', '#Passwd-hidden'))
      {
        $this->signInWithGoogleEMail($mail, $password);
      }
      else
      {
        Assert::assertTrue(false, 'No Google form appeared!
');
      }
    }
    else
    {
      $this->setGooglePlusFakeData();
      $this->clickGooglePlusFakeButton();
    }
  }

  /**
   * @Then /^I choose the username '([^']*)'$/
   *
   * @param mixed $arg1
   *
   * @throws ElementNotFoundException
   */
  public function iChooseTheUsername($arg1): void
  {
    $this->getSession()->wait(300);
    $page = $this->getSession()->getPage();

    $button = $page->findById('btn_oauth_username');
    Assert::assertNotNull($button);
    Assert::assertTrue($button->hasAttribute('disabled'));

    $page->fillField('dialog_oauth_username_input', $arg1);
    Assert::assertFalse($button->hasAttribute('disabled'));

    $button->press();
    if (self::ALREADY_IN_DB_USER !== $arg1)
    {
      $this->getSession()->wait(1_000, 'window.location.href.search("login") == -1');
    }
    $this->getSession()->wait(500);
  }

  /**
   * @Then /^I should see "([^"]*)" "([^"]*)" tutorial banners$/
   *
   * @param mixed $count
   * @param mixed $view
   */
  public function iShouldSeeTutorialBanners($count, $view): void
  {
    if ('desktop' === $view)
    {
      for ($i = 1;; ++$i)
      {
        $img = $this->getSession()->getPage()->findById('tutorial-'.$i);
        if (null === $img)
        {
          break;
        }
      }
      Assert::assertEquals($count, $i - 1);
    }
    elseif ('mobile' === $view)
    {
      for ($i = 1;; ++$i)
      {
        $img = $this->getSession()->getPage()->findById('tutorial-mobile-'.$i);
        if (null === $img)
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
   *
   * @param mixed $numb
   *
   * @throws ElementNotFoundException
   */
  public function iClickOnTheBanner($numb): void
  {
    switch ($numb) {
      case 'first':
        $this->iClick('#tutorial-1');
        break;
      case 'second':
        $this->iClick('#tutorial-2');
        break;
      case 'third':
        $this->iClick('#tutorial-3');
        break;
      case 'fourth':
        $this->iClick('#tutorial-4');
        break;
      case 'fifth':
        $this->iClick('#tutorial-5');
        break;
      case 'sixth':
        $this->iClick('#tutorial-6');
        break;
      default:
        Assert::assertTrue(false);
        break;
    }
  }

  /**
   * @Then /^I should see the slider with the values "([^"]*)"$/
   *
   * @param mixed $values
   */
  public function iShouldSeeTheSliderWithTheValues($values): void
  {
    $slider_items = explode(',', $values);
    $owl_items = $this->getSession()->getPage()->findAll('css', 'div.carousel-item > a');
    $owl_items_count = count($owl_items);
    Assert::assertEquals($owl_items_count, count($slider_items));

    for ($index = 0; $index < $owl_items_count; ++$index)
    {
      $url = $slider_items[$index];
      if (0 !== strpos($url, 'http://'))
      {
        $program = $this->getProgramManager()->findOneByName($url);
        Assert::assertNotNull($program);
        Assert::assertNotNull($program->getId());
        $url = $this->getRouter()->generate('program', ['id' => $program->getId(), 'flavor' => 'pocketcode']);
      }

      $feature_url = $owl_items[$index]->getAttribute('href');
      Assert::assertContains($url, $feature_url);
    }
  }

  /**
   * @When /^I press on the tag "([^"]*)"$/
   *
   * @param mixed $arg1
   *
   * @throws ElementNotFoundException
   */
  public function iPressOnTheTag($arg1): void
  {
    $xpath = '//*[@id="tags"]/div/a[normalize-space()="'.$arg1.'"]';
    $this->assertSession()->elementExists('xpath', $xpath);

    $this
      ->getSession()
      ->getPage()
      ->find('xpath', $xpath)
      ->click()
    ;
  }

  /**
   * @When /^I press on the extension "([^"]*)"$/
   *
   * @param mixed $name
   *
   * @throws ElementNotFoundException
   */
  public function iPressOnTheExtension($name): void
  {
    $xpath = '//*[@id="extensions"]/div/a[normalize-space()="'.$name.'"]';
    $this->assertSession()->elementExists('xpath', $xpath);

    $this
      ->getSession()
      ->getPage()
      ->find('xpath', $xpath)
      ->click()
    ;
  }

  /**
   * @Given /^I search for "([^"]*)" with the searchbar$/
   *
   * @param mixed $arg1
   *
   * @throws ElementNotFoundException
   */
  public function iSearchForWithTheSearchBar($arg1): void
  {
    $this->iClick('#top-app-bar__btn-search');
    $this->fillField('search-input-header', $arg1);
    $this->iClick('#top-app-bar__btn-search-header');
  }

  /**
   * @Then /^I should see the Google Plus 1 button in the header$/
   */
  public function iShouldSeeTheGoogleButtonInTheHeader(): void
  {
    $plus_one_button = $this->getSession()->getPage()->findById('___plusone_0');
    Assert::assertTrue(
      null != $plus_one_button && $plus_one_button->isVisible(),
      'The Google +1 Button is not visible!'
    );
    Assert::assertTrue(
      'nav' === $plus_one_button->getParent()->getParent()->getParent()->getTagName(),
      'Parent is not header element'
    );
  }

  /**
   * @Then /^I should see the profile button$/
   */
  public function iShouldSeeTheProfileButton(): void
  {
    $profile_button = $this->getSession()->getPage()->findById('btn-profile');
    Assert::assertTrue(
      null != $profile_button && $profile_button->isVisible(),
      'The profile button is not visible!'
    );
  }

  /**
   * @Then /^the onclick href in button with id "([^"]*)" should be void$/
   *
   * @param mixed $arg1
   */
  public function theHrefWithIdShouldBeVoid($arg1): void
  {
    $button = $this->getSession()->getPage()->findById($arg1);
    Assert::assertContains('javascript:void(0)', $button->getAttribute('onclick'));
  }

  /**
   * @Then /^the onclick href in button with id "([^"]*)" should not be void$/
   *
   * @param mixed $arg1
   */
  public function theHrefWithIdShouldNotBeVoid($arg1): void
  {
    $button = $this->getSession()->getPage()->findById($arg1);
    Assert::assertNotContains('javascript:void(0)', $button->getAttribute('onclick'));
  }

  /**
   * @Then /^There should be one database entry with type is "([^"]*)" and "([^"]*)" is "([^"]*)"$/
   *
   * @param mixed $type_name
   * @param mixed $name_id
   * @param mixed $id_or_value
   */
  public function thereShouldBeOneDatabaseEntryWithTypeIsAndIs($type_name, $name_id, $id_or_value): void
  {
    $em = $this->getManager();
    $clicks = $em->getRepository(ClickStatistic::class)->findAll();
    Assert::assertEquals(1, count($clicks), 'No database entry found!');

    $click = $clicks[0];

    Assert::assertEquals($type_name, $click->getType());

    switch ($name_id) {
      case 'tag_id':
        Assert::assertEquals($id_or_value, $click->getTag()->getId());
        break;
      case 'extension_id':
        Assert::assertEquals($id_or_value, $click->getExtension()->getId());
        break;
      case 'program_id':
        Assert::assertEquals($id_or_value, $click->getProgram()->getId());
        break;
      case 'user_specific_recommendation':
        Assert::assertEquals('true' === $id_or_value, $click->getUserSpecificRecommendation());
        break;
      default:
        Assert::assertTrue(false);
    }
  }

  /**
   * @When /^I click on the first recommended program$/
   *
   * @throws ElementNotFoundException
   */
  public function iClickOnTheFirstRecommendedProgram(): void
  {
    $arg1 = '#program-2 .rec-programs';
    $this->assertSession()->elementExists('css', $arg1);

    $this
      ->getSession()
      ->getPage()
      ->find('css', $arg1)
      ->click()
    ;
  }

  /**
   * @When /^I click on the first recommended homepage program$/
   *
   * @throws ElementNotFoundException
   */
  public function iClickOnTheFirstRecommendedHomepageProgram(): void
  {
    $arg1 = '.homepage-recommended-programs';
    $this->assertSession()->elementExists('css', $arg1);

    $this
      ->getSession()
      ->getPage()
      ->find('css', $arg1)
      ->click()
    ;
  }

  /**
   * @When /^I click on the first featured homepage program$/
   *
   * @throws ElementNotFoundException
   */
  public function iClickOnAFeaturedHomepageProgram(): void
  {
    $arg1 = '#feature-slider > div > div:first-child > a';
    $this->assertSession()->elementExists('css', $arg1);

    $this
      ->getSession()
      ->getPage()
      ->find('css', $arg1)
      ->click()
    ;
  }

  /**
   * @When /^I click on a newest homepage program having program id "([^"]*)"$/
   *
   * @param mixed $program_id
   *
   * @throws ElementNotFoundException
   */
  public function iClickOnANewestHomepageProgram($program_id): void
  {
    $arg1 = '#newest .programs #program-'.$program_id.' .rec-programs';
    $this->assertSession()->elementExists('css', $arg1);

    $this
      ->getSession()
      ->getPage()
      ->find('css', $arg1)
      ->click()
    ;
  }

  /**
   * @When /^I click on a most downloaded homepage program having program id "([^"]*)"$/
   *
   * @param mixed $program_id
   *
   * @throws ElementNotFoundException
   */
  public function iClickOnAMostDownloadedHomepageProgram($program_id): void
  {
    $arg1 = '#mostDownloaded .programs #program-'.$program_id.' .rec-programs';
    $this->assertSession()->elementExists('css', $arg1);

    $this
      ->getSession()
      ->getPage()
      ->find('css', $arg1)
      ->click()
    ;
  }

  /**
   * @When /^I click on a most viewed homepage program having program id "([^"]*)"$/
   *
   * @param mixed $program_id
   *
   * @throws ElementNotFoundException
   */
  public function iClickOnAMostViewedHomepageProgram($program_id): void
  {
    $arg1 = '#mostViewed .programs #program-'.$program_id.' .rec-programs';
    $this->assertSession()->elementExists('css', $arg1);

    $this
      ->getSession()
      ->getPage()
      ->find('css', $arg1)
      ->click()
    ;
  }

  /**
   * @When /^I click on a random homepage program having program id "([^"]*)"$/
   *
   * @param mixed $program_id
   *
   * @throws ElementNotFoundException
   */
  public function iClickOnARandomHomepageProgram($program_id): void
  {
    $arg1 = '#random .programs #program-'.$program_id.' .rec-programs';
    $this->assertSession()->elementExists('css', $arg1);

    $this
      ->getSession()
      ->getPage()
      ->find('css', $arg1)
      ->click()
    ;
  }

  /**
   * @When /^I click on the first recommended specific program$/
   *
   * @throws ElementNotFoundException
   */
  public function iClickOnTheFirstRecommendedSpecificProgram(): void
  {
    $arg1 = '#specific-programs-recommendations .programs #program-3 .rec-programs';
    $this->assertSession()->elementExists('css', $arg1);

    $this
      ->getSession()
      ->getPage()
      ->find('css', $arg1)
      ->click()
    ;
  }

  /**
   * @Then /^There should be recommended specific programs$/
   *
   * @throws ElementNotFoundException
   */
  public function thereShouldBeRecommendedSpecificPrograms(): void
  {
    $arg1 = '#specific-programs-recommendations .programs .rec-programs';
    $this->assertSession()->elementExists('css', $arg1);
  }

  /**
   * @Then /^There should be no recommended specific programs$/
   *
   * @throws ExpectationException
   */
  public function thereShouldBeNoRecommendedSpecificPrograms(): void
  {
    $this->assertSession()->elementNotExists('css',
      '#specific-programs-recommendations .programs .rec-programs');
  }

  /**
   * @Then /^I should see a recommended homepage program having ID "([^"]*)" and name "([^"]*)"$/
   *
   * @param mixed $program_id
   * @param mixed $program_name
   *
   * @throws ElementNotFoundException
   */
  public function iShouldSeeARecommendedHomepageProgramHavingIdAndName($program_id, $program_name): void
  {
    $this->assertSession()->elementExists('css',
      '#program-'.$program_id.' .homepage-recommended-programs');

    Assert::assertEquals($program_name, $this
      ->getSession()
      ->getPage()
      ->find('css', '#program-'.$program_id.' .homepage-recommended-programs .program-name')
      ->getText());
  }

  /**
   * @Then /^I should not see any recommended homepage programs$/
   *
   * @throws ExpectationException
   */
  public function iShouldNotSeeAnyRecommendedHomepagePrograms(): void
  {
    $this->assertSession()->elementNotExists('css', '.homepage-recommended-programs');
  }

  /**
   * @Then /^I should see the image "([^"]*)"$/
   *
   * @param mixed $arg1
   */
  public function iShouldSeeTheImage($arg1): void
  {
    $img = $this->getSession()->getPage()->findById('logo');

    if (null != $img)
    {
      Assert::assertEquals($img->getTagName(), 'img');
      $src = $img->getAttribute('src');
      Assert::assertTrue(false !== strpos($src, (string) $arg1), '<'.$src.'> does not contain '.$arg1);
      Assert::assertTrue($img->isVisible(), 'Image is not visible.');
    }
    else
    {
      Assert::assertTrue(false, '#logo not found!');
    }
  }

  /**
   * @Then /^I click the currently visible search icon$/
   */
  public function iClickTheCurrentlyVisibleSearchIcon(): void
  {
    $icon = $this->getSession()->getPage()->findById('top-app-bar__btn-search');
    if ($icon->isVisible())
    {
      $icon->click();

      return;
    }
    Assert::assertTrue(false, 'Tried to click #top-app-bar__btn-search but no visible element was found.');
  }

  /**
   * @Given the random program section is empty
   */
  public function theRandomProgramSectionIsEmpty(): void
  {
    $this->getSession()->evaluateScript(
      'document.getElementById("random").style.display = "none";'
    );
  }

  /**
   * @Given I use a valid JWT token for :username
   *
   * @param mixed $username
   */
  public function iUseAValidJwtTokenFor($username): void
  {
    $user = $this->getUserManager()->findUserByUsername($username);
    $token = $this->getJwtManager()->create($user);
    $this->getSession()->setRequestHeader('Authorization', 'Bearer '.$token);
  }

  /**
   * @Given I use an invalid JWT token for :username
   */
  public function iUseAnInvalidJwtTokenFor(): void
  {
    $token = 'invalidToken';
    $this->getSession()->setRequestHeader('Authorization', 'Bearer '.$token);
  }

  /**
   * @Given I use an empty JWT token for :username
   */
  public function iUseAnEmptyJwtTokenFor(): void
  {
    $this->getSession()->setRequestHeader('Authorization', '');
  }

  /**
   * @Given I have a project zip :project_zip_name
   *
   * @param mixed $project_zip_name
   */
  public function iHaveAProject($project_zip_name): void
  {
    $filesystem = new Filesystem();
    $original_file = $this->FIXTURES_DIR.$project_zip_name;
    $target_file = sys_get_temp_dir().'/program_generated.catrobat';
    $filesystem->copy($original_file, $target_file, true);
  }

  /**
   * @Given I have a program
   */
  public function iHaveAProgram(): void
  {
    $this->generateProgramFileWith([]);
  }

  /**
   * @Given /^I am using pocketcode with language version "([^"]*)"$/
   *
   * @param mixed $version
   */
  public function iAmUsingPocketcodeWithLanguageVersion($version): void
  {
    $this->generateProgramFileWith([
      'catrobatLanguageVersion' => $version,
    ]);
  }

  /**
   * @Given I have an embroidery project
   */
  public function iHaveAnEmbroideryProject(): void
  {
    $this->generateProgramFileWith([], true);
  }

  /**
   * @Given /^I am using pocketcode for "([^"]*)" with version "([^"]*)"$/
   *
   * @param mixed $platform
   * @param mixed $version
   */
  public function iAmUsingPocketcodeForWithVersion($platform, $version): void
  {
    $this->generateProgramFileWith([
      'platform' => $platform,
      'applicationVersion' => $version,
    ]);
  }

  /**
   * @Given /^All programs are from the same user$/
   */
  public function allProgramsAreFromTheSameUser(): void
  {
  }

  /**
   * @Given /^the token to upload an apk file is "([^"]*)"$/
   */
  public function theTokenToUploadAnApkFileIs(): void
  {
    // Defined in config_test.yml
  }

  /**
   * @Given /^the jenkins job id is "([^"]*)"$/
   */
  public function theJenkinsJobIdIs(): void
  {
    // Defined in config_test.yml
  }

  /**
   * @Given /^the jenkins token is "([^"]*)"$/
   */
  public function theJenkinsTokenIs(): void
  {
    // Defined in config_test.yml
  }

  /**
   * @Then /^following parameters are sent to jenkins:$/
   */
  public function followingParametersAreSentToJenkins(TableNode $table): void
  {
    $parameter_defs = $table->getHash();
    $expected_parameters = [];
    foreach ($parameter_defs as $parameter_def)
    {
      $expected_parameters[$parameter_def['parameter']] = $parameter_def['value'];
    }
    $dispatcher = $this->getSymfonyService(JenkinsDispatcher::class);
    $parameters = $dispatcher->getLastParameters();

    foreach ($expected_parameters as $i => $expected_parameter)
    {
      Assert::assertRegExp(
        $expected_parameter,
        $parameters[$i]
      );
    }
  }

  /**
   * @Then /^the program apk status will.* be flagged "([^"]*)"$/
   *
   * @param mixed $arg1
   */
  public function theProgramApkStatusWillBeFlagged($arg1): void
  {
    $pm = $this->getProgramManager();
    $program = $pm->find('1');
    switch ($arg1) {
      case 'pending':
        Assert::assertEquals(Program::APK_PENDING, $program->getApkStatus());
        break;
      case 'ready':
        Assert::assertEquals(Program::APK_READY, $program->getApkStatus());
        break;
      case 'none':
        Assert::assertEquals(Program::APK_NONE, $program->getApkStatus());
        break;
      default:
        throw new PendingException('Unknown state: '.$arg1);
    }
  }

  /**
   * @Given /^I requested jenkins to build it$/
   */
  public function iRequestedJenkinsToBuildIt(): void
  {
  }

  /**
   * @Then /^it will be stored on the server$/
   */
  public function itWillBeStoredOnTheServer(): void
  {
    $directory = $this->getSymfonyParameter('catrobat.apk.dir');
    $finder = new Finder();
    $finder->in($directory)->depth(0);
    Assert::assertEquals(1, $finder->count());
  }

  /**
   * @Given /^the program apk status is flagged "([^"]*)"$/
   *
   * @param mixed $arg1
   *
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function theProgramApkStatusIsFlagged($arg1): void
  {
    $pm = $this->getProgramManager();
    $program = $pm->find('1');
    switch ($arg1) {
      case 'pending':
        $program->setApkStatus(Program::APK_PENDING);
        break;
      case 'ready':
        $program->setApkStatus(Program::APK_READY);
        /* @var $apk_repository ApkRepository */
        $apk_repository = $this->getSymfonyService(ApkRepository::class);
        $apk_repository->save(new File($this->getTempCopy($this->FIXTURES_DIR.'/test.catrobat')), $program->getId());
        break;
      default:
        $program->setApkStatus(Program::APK_NONE);
    }
    $pm->save($program);
  }

  /**
   * @Then /^no build request will be sent to jenkins$/
   */
  public function noBuildRequestWillBeSentToJenkins(): void
  {
    $dispatcher = $this->getSymfonyService(JenkinsDispatcher::class);
    $parameters = $dispatcher->getLastParameters();
    Assert::assertNull($parameters);
  }

  /**
   * @Then /^the apk file will be deleted$/
   */
  public function theApkFileWillBeDeleted(): void
  {
    $directory = $this->getSymfonyParameter('catrobat.apk.dir');
    $finder = new Finder();
    $finder->in($directory)->depth(0);
    Assert::assertEquals(0, $finder->count());
  }

  /**
   * @Then /^I should see the reported table:$/
   *
   * @throws ResponseTextException
   */
  public function shouldSeeReportedTable(TableNode $table): void
  {
    $user_stats = $table->getHash();
    foreach ($user_stats as $user_stat)
    {
      $this->assertSession()->pageTextContains($user_stat['#Reported Comments']);
      $this->assertSession()->pageTextContains($user_stat['#Reported Programs']);
      $this->assertSession()->pageTextContains($user_stat['Username']);
      $this->assertSession()->pageTextContains($user_stat['Email']);
    }
  }

  /**
   * @Then /^I should see the table with all projects in the following order:$/
   *
   * @throws ResponseTextException
   */
  public function shouldSeeReportedProgramsTable(TableNode $table): void
  {
    $user_stats = $table->getHash();
    $td = $this->getSession()->getPage()->findAll('css', '.table tbody tr');

    $actual_values = [];
    foreach ($td as $value)
    {
      $actual_values[] = $value->getText();
    }

    Assert::assertEquals(count($actual_values), count($user_stats), 'Wrong number of projects in table');

    $counter = 0;
    foreach ($user_stats as $user_stat)
    {
      Assert::assertEquals(implode(' ', $user_stat), $actual_values[$counter]);
      ++$counter;
    }
  }

  /**
   * @Then /^I should see the starter programs table:$/
   *
   * @throws ResponseTextException
   */
  public function shouldSeeStarterProgramsTable(TableNode $table): void
  {
    $user_stats = $table->getHash();
    foreach ($user_stats as $user_stat)
    {
      $this->assertSession()->pageTextContains($user_stat['Starter Category']);
      $this->assertSession()->pageTextContains($user_stat['Category Alias']);
      $this->assertSession()->pageTextContains($user_stat['Programs']);
      $this->assertSession()->pageTextContains($user_stat['Order']);
    }
  }

  /**
   * @Then /^I should see the reported programs table:$/
   *
   * @throws ResponseTextException
   */
  public function seeReportedProgramsTable(TableNode $table): void
  {
    $user_stats = $table->getHash();
    foreach ($user_stats as $user_stat)
    {
      $this->assertSession()->pageTextContains($user_stat['Note']);
      $this->assertSession()->pageTextContains($user_stat['State']);
      $this->assertSession()->pageTextContains($user_stat['Category']);
      $this->assertSession()->pageTextContains($user_stat['Reporting User']);
      $this->assertSession()->pageTextContains($user_stat['Program']);
      $this->assertSession()->pageTextContains($user_stat['Program Visible']);
    }
  }

  /**
   * @Given /^there is a file "([^"]*)" with size "([^"]*)" bytes in the APK-folder$/
   *
   * @param mixed $filename
   * @param mixed $size
   */
  public function thereIsAFileWithSizeBytesInTheApkFolder($filename, $size): void
  {
    $this->generateFileInPath($this->getSymfonyParameter('catrobat.apk.dir'), $filename, $size);
  }

  /**
   * @Then /^program with id "([^"]*)" should have no apk$/
   *
   * @param mixed $program_id
   */
  public function programWithIdShouldHaveNoApk($program_id): void
  {
    $program_manager = $this->getProgramManager();
    $program = $program_manager->find($program_id);
    Assert::assertEquals(Program::APK_NONE, $program->getApkStatus());
  }

  /**
   * @Given /^there is a file "([^"]*)" with size "([^"]*)" bytes in the backup-folder$/
   *
   * @param mixed $filename
   * @param mixed $size
   */
  public function thereIsAFileWithSizeBytesInTheBackupFolder($filename, $size): void
  {
    $this->generateFileInPath($this->getSymfonyParameter('catrobat.backup.dir'),
      $filename, $size);
  }

  /**
   * @Given /^there is a file "([^"]*)" with size "([^"]*)" bytes in the extracted-folder$/
   *
   * @param mixed $filename
   * @param mixed $size
   */
  public function thereIsAFileWithSizeBytesInTheExtractedFolder($filename, $size): void
  {
    $this->generateFileInPath($this->getSymfonyParameter('catrobat.file.extract.dir'),
      $filename, $size);
  }

  /**
   * @Given /^there is no file in the backup-folder$/
   */
  public function thereIsNoFileInTheBackupFolder(): void
  {
    $backupDirectory = $this->getSymfonyParameter('catrobat.backup.dir');

    $files = glob($backupDirectory.'/*');
    foreach ($files as $file)
    {
      $ext = pathinfo($file, PATHINFO_EXTENSION);
      if (('zip' === $ext || 'tar' === $ext) && is_file($file))
      {
        unlink($file);
      }
    }
  }

  /**
   * @Then /^program with id "([^"]*)" should have no directory_hash$/
   *
   * @param mixed $program_id
   */
  public function programWithIdShouldHaveNoDirectoryHash($program_id): void
  {
    $program_manager = $this->getProgramManager();
    $program = $program_manager->find($program_id);
    Assert::assertEquals('null', $program->getDirectoryHash());
  }

  /**
   * @Given /^we assume the next generated token will be "([^"]*)"$/
   *
   * @param mixed $token
   */
  public function weAssumeTheNextGeneratedTokenWillBe($token): void
  {
    $token_generator = $this->getSymfonyService(TokenGenerator::class);
    $token_generator->setTokenGenerator(new FixedTokenGenerator($token));
  }

  /**
   * @Then the resources should not contain the unnecessary files
   */
  public function theResourcesShouldNotContainTheUnnecessaryFiles(): void
  {
    $files = new RecursiveIteratorIterator(
      new RecursiveDirectoryIterator($this->EXTRACT_RESOURCES_DIR, RecursiveDirectoryIterator::SKIP_DOTS),
      RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($files as $file)
    {
      $filename = $file->getFilename();
      Assert::assertNotContains('remove_me', $filename);
    }
  }

  /**
   * @Given /^I am a valid user$/
   */
  public function iAmAValidUser(): void
  {
    $this->insertUser([
      'name' => 'BehatGeneratedName',
      'token' => 'BehatGeneratedToken',
      'password' => 'BehatGeneratedPassword',
    ]);
  }

  /**
   * @Then /^I should get following like similarities:$/
   */
  public function iShouldGetFollowingLikePrograms(TableNode $table): void
  {
    $all_like_similarities = $this->getUserLikeSimilarityRelationRepository()->findAll();
    $all_like_similarities_count = count($all_like_similarities);
    $expected_like_similarities = $table->getHash();
    Assert::assertEquals(count($expected_like_similarities), $all_like_similarities_count,
      'Wrong number of returned similarity entries');
    for ($i = 0; $i < $all_like_similarities_count; ++$i)
    {
      /** @var UserLikeSimilarityRelation $like_similarity */
      $like_similarity = $all_like_similarities[$i];
      Assert::assertEquals(
        $expected_like_similarities[$i]['first_user_id'],
        $like_similarity->getFirstUserId(),
        'Wrong value for first_user_id or wrong order of results'
      );
      Assert::assertEquals(
        $expected_like_similarities[$i]['second_user_id'],
        $like_similarity->getSecondUserId(),
        'Wrong value for second_user_id'
      );
      Assert::assertEquals(
        round($expected_like_similarities[$i]['similarity'], 3),
        round($like_similarity->getSimilarity(), 3),
        'Wrong value for similarity'
      );
    }
  }

  /**
   * @Then /^I should get following remix similarities:$/
   */
  public function iShouldGetFollowingRemixPrograms(TableNode $table): void
  {
    $all_remix_similarities = $this->getUserRemixSimilarityRelationRepository()->findAll();
    $all_remix_similarities_count = count($all_remix_similarities);
    $expected_remix_similarities = $table->getHash();
    Assert::assertEquals(count($expected_remix_similarities), $all_remix_similarities_count,
      'Wrong number of returned similarity entries');
    for ($i = 0; $i < $all_remix_similarities_count; ++$i)
    {
      /** @var UserRemixSimilarityRelation $remix_similarity */
      $remix_similarity = $all_remix_similarities[$i];
      Assert::assertEquals(
        $expected_remix_similarities[$i]['first_user_id'], $remix_similarity->getFirstUserId(),
        'Wrong value for first_user_id or wrong order of results'
      );
      Assert::assertEquals(
        $expected_remix_similarities[$i]['second_user_id'], $remix_similarity->getSecondUserId(),
        'Wrong value for second_user_id'
      );
      Assert::assertEquals(round($expected_remix_similarities[$i]['similarity'], 3),
        round($remix_similarity->getSimilarity(), 3),
        'Wrong value for similarity');
    }
  }

  /**
   * @Given the next generated token will be :token
   *
   * @param mixed $token
   */
  public function theNextGeneratedTokenWillBe($token): void
  {
    $token_generator = $this->getSymfonyService(TokenGenerator::class);
    $token_generator->setTokenGenerator(new FixedTokenGenerator($token));
  }

  /**
   * @Given /^the current time is "([^"]*)"$/
   *
   * @param mixed $time
   *
   * @throws Exception
   */
  public function theCurrentTimeIs($time): void
  {
    $date = new DateTime($time, new DateTimeZone('UTC'));
    TimeUtils::freezeTime($date);
  }

  /**
   * @Given /^I have a program with Arduino, Lego and Phiro extensions$/
   */
  public function iHaveAProgramWithArduinoLegoAndPhiroExtensions(): void
  {
    $filesystem = new Filesystem();
    $original_file = $this->FIXTURES_DIR.'extensions.catrobat';
    $target_file = sys_get_temp_dir().'/program_generated.catrobat';
    $filesystem->copy($original_file, $target_file, true);
  }

  /**
   * @Then /^We can\'t test anything here$/
   *
   * @throws Exception
   */
  public function weCantTestAnythingHere(): void
  {
    throw new Exception(':(');
  }

  /**
   * @Given There is an ongoing game jam
   *
   * @throws Exception
   */
  public function thereIsAnOngoingGameJam(): void
  {
    $this->game_jam = $this->insertDefaultGameJam();
  }

  /**
   * @Given /^I submitted a program to the gamejam$/
   *
   * @throws Exception
   */
  public function iSubmittedAProgramToTheGamejam(): void
  {
    if (null == $this->game_jam)
    {
      $this->game_jam = $this->insertDefaultGameJam([
        'formurl' => 'https://localhost/url/to/form',
      ]);
    }
    if (null === $this->my_program)
    {
      $this->my_program = $this->insertProject([
        'name' => 'My Program',
        'gamejam' => $this->game_jam,
        'owned by' => $this->getUserDataFixtures()->getCurrentUser(),
      ]);
    }
  }

  /**
   * @Given /^I filled out the google form$/
   */
  public function iFilledOutTheGoogleForm(): void
  {
    $project = $this->getProgramManager()->find('1');
    $this->my_program = $project;
    $this->my_program->setAcceptedForGameJam(true);
    $this->getManager()->persist($this->my_program);
    $this->getManager()->flush();
  }

  /**
   * @Given /^There is no ongoing game jam$/
   */
  public function thereIsNoOngoingGameJam(): void
  {
  }

  /**
   * @Then /^I do not see a form to edit my profile$/
   *
   * @throws ExpectationException
   */
  public function iDoNotSeeAFormToEditMyProfile(): void
  {
    $this->assertSession()->elementNotExists('css', '#profile-form');
  }

  /**
   * @Given /^I have a program named "([^"]*)"$/
   *
   * @param mixed $arg1
   *
   * @throws Exception
   */
  public function iHaveAProgramNamed($arg1): void
  {
    $this->insertProject([
      'name' => $arg1,
      'owned by' => $this->getUserDataFixtures()->getCurrentUser(),
    ]);
  }

  /**
   * @Then /^I do not see a button to change the profile picture$/
   *
   * @throws ExpectationException
   */
  public function iDoNotSeeAButtonToChangeTheProfilePicture(): void
  {
    $this->assertSession()->elementNotExists('css', '#avatar-upload');
  }

  /**
   * @Given /^There is an ongoing game jam without flavor $/
   *
   * @throws Exception
   */
  public function thereIsAnOngoingGameJamWithoutFlavor(): void
  {
    $this->game_jam = $this->insertDefaultGameJam([
      'flavor' => 'no flavor',
    ]);
  }

  /**
   * @Given /^I am not logged in$/
   */
  public function iAmNotLoggedIn(): void
  {
    $user = $this->insertUser([
      'name' => 'Generated',
      'password' => 'generated',
    ]);
    $this->getUserDataFixtures()->setCurrentUser($user);
  }

  /**
   * @Then The game is not yet accepted
   */
  public function theGameIsNotYetAccepted(): void
  {
    $program = $this->getProgramManager()->find('1');
    Assert::assertFalse($program->isAcceptedForGameJam());
  }

  /**
   * @Then My game should be accepted
   */
  public function myGameShouldBeAccepted(): void
  {
    $program = $this->getProgramManager()->find('1');
    Assert::assertTrue($program->isAcceptedForGameJam());
  }

  /**
   * @Then My game should still be accepted
   */
  public function myGameShouldStillBeAccepted(): void
  {
    $program = $this->getProgramManager()->find('1');
    Assert::assertTrue($program->isAcceptedForGameJam());
  }

  /**
   * @Given I did not fill out the google form
   */
  public function iDidNotFillOutTheGoogleForm(): void
  {
  }

  /**
   * @Given The form url of the current jam is
   *
   * @throws Exception
   */
  public function theFormUrlOfTheCurrentJamIs(PyStringNode $string): void
  {
    $this->insertDefaultGameJam([
      'formurl' => $string->getRaw(),
    ]);
  }

  /**
   * @Given I am :arg1 with email :arg2
   *
   * @param mixed $arg1
   * @param mixed $arg2
   */
  public function iAmWithEmail($arg1, $arg2): void
  {
    $user = $this->insertUser([
      'name' => $arg1,
      'email' => sprintf('%s', $arg2),
    ]);
    $this->getUserDataFixtures()->setCurrentUser($user);
  }

  /**
   * @Given There are following gamejam programs:
   *
   * @throws Exception
   */
  public function thereAreFollowingGamejamPrograms(TableNode $table): void
  {
    $programs = $table->getHash();
    foreach ($programs as $program)
    {
      @$gamejam = $program['GameJam'];
      if (null == $gamejam)
      {
        $gamejam = $this->game_jam;
      }
      else
      {
        $gamejam = $this->getSymfonyService(GameJamRepository::class)->findOneByName($gamejam);
      }
      @$config = [
        'name' => $program['Name'],
        'gamejam' => ('yes' == $program['Submitted']) ? $gamejam : null,
        'accepted' => 'yes' == $program['Accepted'],
      ];
      $this->insertProject($config);
    }
  }

  /**
   * @Given There are following gamejams:
   *
   * @throws Exception
   */
  public function thereAreFollowingGamejams(TableNode $table): void
  {
    $jams = $table->getHash();
    foreach ($jams as $jam)
    {
      $config = ['name' => $jam['Name']];
      $start = $jam['Starts in'];
      if (null != $start)
      {
        $config['start'] = $this->getDateFromNow((int) $start);
      }
      $end = $jam['Ends in'];
      if (null != $end)
      {
        $config['end'] = $this->getDateFromNow((int) $end);
      }
      $this->insertDefaultGameJam($config);
      $this->insertProject($config);
    }
  }

  /**
   * @Then A copy of this program will be stored on the server
   */
  public function aCopyOfThisProgramWillBeStoredOnTheServer(): void
  {
    $dir = $this->getSymfonyParameter('catrobat.snapshot.dir');
    $finder = new Finder();
    Assert::assertEquals(1, $finder->files()->in($dir)->count(), 'Snapshot was not stored!');
  }

  /**
   * @When I visit my profile
   */
  public function iVisitMyProfile(): void
  {
    $this->visit('/app/user');
  }

  /**
   * @Given /^I have a limited account$/
   */
  public function iHaveALimitedAccount(): void
  {
    $user = $this->getUserDataFixtures()->getCurrentUser();
    $user->setLimited(true);
    $this->getManager()->persist($user);
    $this->getManager()->flush();
  }

  /**
   * @Then I see the program :arg1
   *
   * @param mixed $arg1
   */
  public function iSeeTheProgram($arg1): void
  {
    $this->assertPageContainsText($arg1);
  }

  /**
   * @Then I do not see a delete button
   */
  public function iDoNotSeeADeleteButton(): void
  {
    $this->assertElementNotOnPage('.img-delete');
  }

  /**
   * @Then the button :button should be disabled until download is finished
   */
  public function theButtonShouldBeDisabledUntilDownloadIsFinished(string $button): void
  {
    $this->theElementShouldBeVisible($button);
  }

  //--------------------------------------------------------------------------------------------------------------------
  //  User Agent
  //--------------------------------------------------------------------------------------------------------------------

  private function iUseTheUserAgent(string $user_agent): void
  {
    $this->getSession()->setRequestHeader('User-Agent', $user_agent);
  }

  private function iUseTheUserAgentParameterized(string $lang_version, string $flavor, string $app_version, string $build_type, string $theme = 'pocketcode'): void
  {
    // see org.catrobat.catroid.ui.WebViewActivity
    $platform = 'Android';
    $user_agent = 'Catrobat/'.$lang_version.' '.$flavor.'/'.$app_version.' Platform/'.$platform.
      ' BuildType/'.$build_type.' Theme/'.$theme;
    $this->iUseTheUserAgent($user_agent);
  }

  /**
   * @param mixed $value
   */
  private function setOauthServiceParameter($value): void
  {
    // api-deprecated
  }

  private function approveGoogleAccess(): void
  {
    echo 'Google Approve Access form appeared';
    $page = $this->getSession()->getPage();
    $button = $page->findById('submit_approve_access');
    Assert::assertTrue(null != $button);
    $button->press();
    $this->getSession()->switchToWindow(null);
  }

  /**
   * @param mixed $mail
   * @param mixed $password
   *
   * @throws ElementNotFoundException
   */
  private function signInWithGoogleEMailAndPassword($mail, $password): void
  {
    echo 'Google login form with E-Mail and Password appeared
';
    $page = $this->getSession()->getPage();

    $page->fillField('Email', $mail);
    $page->fillField('Passwd', $password);

    $button = $page->findById('signIn');
    Assert::assertTrue(null != $button);
    $button->press();
    $this->approveGoogleAccess();
  }

  /**
   * @param mixed $mail
   * @param mixed $password
   *
   * @throws ElementNotFoundException
   */
  private function signInWithGoogleEMail($mail, $password): void
  {
    echo 'Google Signin with E-Mail form appeared
';
    $page = $this->getSession()->getPage();

    $page->fillField('Email', $mail);

    $button = $page->findById('next');
    Assert::assertTrue(null != $button);
    $button->press();
    if ($page->find('css', '#gaia_firstform') &&
      $page->find('css', '#Email-hidden') &&
      $page->find('css', '#Passwd')
    ) {
      $this->signInWithGooglePassword($password);
    }
  }

  /**
   * @param mixed $password
   *
   * @throws ElementNotFoundException
   */
  private function signInWithGooglePassword($password): void
  {
    echo 'Google Signin with Password form appeared
';
    $page = $this->getSession()->getPage();

    $page->fillField('Passwd', $password);

    $button = $page->findById('signIn');
    Assert::assertTrue(null != $button);
    $button->press();
    if ($page->find('css', '#approval_container') &&
      $page->find('css', '#submit_approve_access')
    ) {
      $this->approveGoogleAccess();
    }
  }

  private function setGooglePlusFakeData(): void
  {
    //simulate Google+ login by faking Javascript code and server responses from FakeOAuthService
    $session = $this->getSession();
    $session->wait(2_000, '(0 === jQuery.active)');
    $session->evaluateScript("$('#btn-gplus-testhook').removeClass('hidden');");
    $session->evaluateScript("$('#id_oauth').val('105155320106786463089');");
    $session->evaluateScript("$('#email_oauth').val('pocketcodetester@gmail.com');");
    $session->evaluateScript("$('#locale_oauth').val('de');");
  }

  private function clickGooglePlusFakeButton(): void
  {
    $page = $this->getSession()->getPage();
    $button = $page->findButton('btn-gplus-testhook');
    Assert::assertNotNull($button, 'button not found');
    $button->press();
  }

  /**
   * @param mixed $path
   * @param mixed $filename
   * @param mixed $size
   */
  private function generateFileInPath($path, $filename, $size): void
  {
    $full_filename = $path.'/'.$filename;
    $dirname = dirname($full_filename);
    if (!is_dir($dirname))
    {
      mkdir($dirname, 0755, true);
    }
    $file_path = fopen($full_filename, 'w'); // open in write mode.
    fseek($file_path, $size - 1, SEEK_CUR); // seek to SIZE-1
    fwrite($file_path, 'a'); // write a dummy char at SIZE position
    fclose($file_path); // close the file.
  }

  /**
   * @param mixed $days
   *
   * @throws Exception
   */
  private function getDateFromNow($days): DateTime
  {
    $date = TimeUtils::getDateTime();
    if ($days < 0)
    {
      $days = abs($days);
      $date->sub(new DateInterval('P'.$days.'D'));
    }
    else
    {
      $date->add(new DateInterval('P'.$days.'D'));
    }

    return $date;
  }

  /**
   * @param mixed $option
   *
   * @throws Exception
   */
  private function iSelectTheOptionInThePopup($option): void
  {
    $page = $this->getSession()->getPage();
    //click the input on the popup to show yes or no option
    $page
      ->find('css', '.editable-input')
      ->click()
  ;

    //click yes or no option
    if ('yes' == $option)
    {
      $page
        ->find('css', 'select.form-control > option:nth-child(2)')
        ->click()
    ;
    }
    else
    {
      $page
        ->find('css', 'select.form-control > option:nth-child(1)')
        ->click()
    ;
    }
    //click button to confirm the selection
    $page
      ->find('css', 'button.btn-sm:nth-child(1)')
      ->click()
  ;
  }
}
