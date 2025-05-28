<?php

declare(strict_types=1);

namespace App\System\Testing\Behat\Context;

use App\DB\Entity\Flavor;
use App\DB\Entity\Project\Program;
use App\DB\Entity\User\Comment\UserComment;
use App\DB\Entity\User\Notifications\CatroNotification;
use App\DB\Entity\User\RecommenderSystem\UserLikeSimilarityRelation;
use App\DB\Entity\User\RecommenderSystem\UserRemixSimilarityRelation;
use App\Project\Apk\ApkRepository;
use App\Project\Apk\JenkinsDispatcher;
use App\Security\TokenGenerator;
use App\System\Testing\FixedTokenGenerator;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Exception\ExpectationException;
use Behat\Mink\Exception\ResponseTextException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailureException;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class CatrowebBrowserContext.
 *
 * Extends the basic browser utilities with Catroweb specific actions.
 */
class CatrowebBrowserContext extends BrowserContext
{
  final public const string AVATAR_DIR = './tests/TestData/DataFixtures/AvatarImages/';

  final public const string ALREADY_IN_DB_USER = 'AlreadyinDB';

  protected bool $use_real_oauth_javascript_code = false;

  protected ?Program $my_project = null;

  // -------------------------------------------------------------------------------------------------------------------
  //  Initialization
  // -------------------------------------------------------------------------------------------------------------------

  /**
   * Initializes context with parameters from behat.yaml.
   */
  public function __construct(KernelInterface $kernel)
  {
    parent::__construct($kernel);
    setlocale(LC_ALL, 'en');
  }

  // -------------------------------------------------------------------------------------------------------------------
  //  Hook
  // -------------------------------------------------------------------------------------------------------------------

  /**
   * @BeforeScenario
   *
   * @throws \Exception
   */
  public function initACL(): void
  {
    $application = new Application($this->getKernel());
    $application->setAutoExit(false);

    $input = new ArrayInput(['command' => 'sonata:admin:setup-acl']);
    $application->run($input, new NullOutput());
  }

  // --------------------------------------------------------------------------------------------------------------------
  //  Authentication
  // --------------------------------------------------------------------------------------------------------------------
  /**
   * @Given /^I( [^"]*)? log in as "([^"]*)" with the password "([^"]*)"$/
   * @Given /^I( [^"]*)? log in as "([^"]*)"$/
   */
  public function iAmLoggedInAsWithThePassword(string $try_to, string $username, string $password = '123456'): void
  {
    $this->visit('/app/login');
    $this->iWaitForThePageToBeLoaded();
    $this->fillField('_username', $username);
    $this->fillField('_password', $password);
    $this->pressButton('Login');
    $this->iWaitForThePageToBeLoaded();
    if ('try to' === $try_to) {
      $this->assertPageNotContainsText('Your password or username was incorrect');
    }

    $this->getUserDataFixtures()->setCurrentUserByUsername($username);
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
   * @throws ExpectationException
   * @throws ExpectationException
   */
  public function iShouldBeLogged(string $arg1): void
  {
    if ('in' === $arg1) {
      $this->assertPageNotContainsText('Your password or username was incorrect.');
      $this->getSession()->wait(2_000, 'window.location.href.search("login") == -1');
      $this->cookieShouldExist('BEARER');
    }

    if ('out' === $arg1) {
      $this->getSession()->wait(1_000, 'window.location.href.search("profile") == -1');
      $this->cookieShouldNotExist('BEARER');
    }
  }

  /**
   * @Given /^I use a (debug|release) build of the Catroid app$/
   */
  public function iUseASpecificBuildTypeOfCatroidApp(string $build_type): void
  {
    $this->iUseTheUserAgentParameterized('0.998', Flavor::POCKETCODE, '0.9.60', $build_type);
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

  // --------------------------------------------------------------------------------------------------------------------
  //  Everything -> ToDo CleanUp
  // --------------------------------------------------------------------------------------------------------------------

  /**
   * @Given /^I set the cookie "([^"]+)" to "([^"]*)"$/
   */
  public function iSetTheCookie(string $cookie_name, string $cookie_value): void
  {
    if ('NULL' === $cookie_value) {
      $cookie_value = null;
    }

    $this->getSession()->setCookie($cookie_name, $cookie_value);
  }

  /**
   * @Given /^cookie "([^"]+)" should exist"$/
   *
   * @throws ExpectationException
   */
  public function cookieShouldExist(string $cookie_name): void
  {
    $this->assertSession()->cookieExists($cookie_name);
  }

  /**
   * @Given /^cookie "([^"]+)" should not exist"$/
   */
  public function cookieShouldNotExist(string $cookie_name): void
  {
    $cookie = $this->getSession()->getCookie($cookie_name);
    Assert::assertNull($cookie);
  }

  /**
   * @Given /^cookie "([^"]+)" with value "([^"]*)" should exist"$/
   *
   * @throws ExpectationException
   */
  public function cookieWithValueShouldExist(string $cookie_name, string $cookie_value): void
  {
    $this->cookieShouldExist($cookie_name);
    $cookie = $this->getSession()->getCookie($cookie_name);
    if (null !== $cookie) {
      $this->assertSession()->cookieEquals($cookie_name, $cookie_value);
    }
  }

  /**
   * @When /^I open the menu$/
   */
  public function iOpenTheMenu(): void
  {
    $sidebar_open = $this->getSession()->getPage()->find('css', '#sidebar')->isVisible();
    if (!$sidebar_open) {
      $this->getSession()->getPage()->find('css', '#top-app-bar__btn-sidebar-toggle')->click();
    }

    $this->iWaitForAjaxToFinish();
  }

  /**
   * @Then /^I should see (\d+) "([^"]*)"$/
   */
  public function iShouldSeeNumberOfElements(string $element_count, string $css_selector): void
  {
    $elements = $this->getSession()->getPage()->findAll('css', $css_selector);
    $count = 0;
    foreach ($elements as $element) {
      if ($element->isVisible()) {
        ++$count;
      }
    }

    Assert::assertEquals($element_count, $count);
  }

  /**
   * @Then /^I should see a node with id "([^"]*)" having name "([^"]*)" and username "([^"]*)"$/
   */
  public function iShouldSeeANodeWithNameAndUsername(string $node_id, string $expected_node_name, string $expected_username): void
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
   */
  public function iShouldSeeAnUnavailableNodeWithId(string $node_id): void
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
   */
  public function iShouldSeeAnEdgeFromTo(string $from_id, string $to_id): void
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
   * @Given I enter :value into the :fieldName field
   */
  public function iEnterValueIntoNamedField(string $value, string $fieldName): void
  {
    $field = $this->getSession()->getPage()->findField($fieldName);
    $field->setValue($value);
  }

  /**
   * @When /^I select option (\d+) from the dropdown "([^"]*)"$/
   *
   * @throws \Exception
   */
  public function selectOptionFromDropdown(int $index, string $dropdownId): void
  {
    $session = $this->getSession();
    $element = $session->getPage()->find('css', '#'.$dropdownId);

    if (!$element) {
      throw new \Exception('Dropdown element not found');
    }

    $options = $element->findAll('css', 'option');

    $options[$index - 1]->click();
  }

  /**
   * @When /^I switch to the new tab$/
   */
  public function iSwitchToNewTab(): void
  {
    $windowNames = $this->getSession()->getWindowNames();
    $currentWindowName = $this->getSession()->getWindowName();

    // Find the name of the new window/tab
    $newWindowName = '';
    foreach ($windowNames as $windowName) {
      if ($windowName !== $currentWindowName) {
        $newWindowName = $windowName;
      }
    }

    // Switch to the new window/tab
    $this->getSession()->switchToWindow($newWindowName);
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
   * @Then /^the selected language should be "([^"]*)"$/
   *
   * @Given /^the selected language is "([^"]*)"$/
   *
   * @throws ExpectationException
   */
  public function theSelectedLanguageShouldBe(string $arg1): void
  {
    switch ($arg1) {
      case 'English':
        $cookie = $this->getSession()->getCookie('hl');
        if (!empty($cookie)) {
          $this->assertSession()->cookieEquals('hl', 'en');
        }

        break;

      case 'Deutsch':
        $this->assertSession()->cookieEquals('hl', 'de_DE');
        break;

      case 'French':
        $this->assertSession()->cookieEquals('hl', 'fr_FR');
        break;

      default:
        throw new \InvalidArgumentException('Invalid language: '.$arg1);
    }
  }

  /**
   * @Then /^I switch the language to "([^"]*)"$/
   */
  public function iSwitchTheLanguageTo(string $arg1): void
  {
    match ($arg1) {
      'English' => $this->getSession()->setCookie('hl', 'en'),
      'Deutsch' => $this->getSession()->setCookie('hl', 'de_DE'),
      'Russisch' => $this->getSession()->setCookie('hl', 'ru_RU'),
      'French' => $this->getSession()->setCookie('hl', 'fr_FR'),
      default => throw new \InvalidArgumentException('Invalid language: '.$arg1),
    };
    $this->reload();
  }

  /**
   * @Then /^I click on the first "([^"]*)" button$/
   *
   * @throws ElementNotFoundException
   */
  public function iClickOnTheFirstButton(string $arg1): void
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
   * @throws \Exception
   */
  public function iClickOnTheColumnName(string $arg1): void
  {
    $page = $this->getSession()->getPage();
    switch ($arg1) {
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
      case 'Id':
        $page
          ->find('xpath', '//div[1]/div/section[2]/div[2]/div/div/div[1]/table/thead/tr/th[1]/a')
          ->click()
        ;
        break;
      case 'Word':
        $page
          ->find('xpath', '//div[1]/div/section[2]/div[2]/div/form/div/div[1]/table/thead/tr/th[3]/a')
          ->click()
        ;
        break;
      case 'Clicked At':
        $page
          ->find('xpath', '//div/div/section[2]/div[2]/div/div/div[1]/table/thead/tr/th[9]/a')
          ->click()
        ;
        break;
      case \Locale::class:
        $page
          ->find('xpath', '//div/div/section[2]/div[2]/div/div/div[1]/table/thead/tr/th[10]/a')
          ->click()
        ;
        break;
      case 'Type':
        $page
          ->find('xpath', '//div/div/section[2]/div[2]/div/div/div[1]/table/thead/tr/th[2]/a')
          ->click()
        ;
        break;
      case 'Tag':
        $page
          ->find('xpath', '//div/div/section[2]/div[2]/div/div/div[1]/table/thead/tr/th[7]/a')
          ->click()
        ;
        break;
      case 'User Agent':
        $page
          ->find('xpath', '//div/div/section[2]/div[2]/div/div/div[1]/table/thead/tr/th[11]/a')
          ->click()
        ;
        break;
      case 'Referrer':
        $page
          ->find('xpath', '//div/div/section[2]/div[2]/div/div/div[1]/table/thead/tr/th[12]/a')
          ->click()
        ;
        break;

      default:
        throw new \Exception('Wrong Option');
    }
  }

  /**
   * @Then /^I change the visibility of the project number "([^"]*)" in the list to "([^"]*)"$/
   *
   * @throws \Exception
   */
  public function iChangeTheVisibilityOfTheProject(string $project_number, string $visibility): void
  {
    // /param project_number contains the number of the project position in the list on the admin page
    // /
    $page = $this->getSession()->getPage();

    // /click the visibility button (yes/no)
    $page
      ->find('xpath', '//div[1]/div/section[2]/div[2]/div/div/div[1]/table/tbody/tr['.$project_number.']/td[10]/span')
      ->click()
    ;

    $this->iSelectTheOptionInThePopup($visibility);
  }

  /**
   * @Then /^I change the visibility of the project number "([^"]*)" in the approve list to "([^"]*)"$/
   *
   * @throws \Exception
   */
  public function iChangeTheVisibilityOfTheProjectInTheApproveList(string $project_number, string $visibility): void
  {
    // /param project_number contains the number of the project position in the list on the admin page
    // /
    $page = $this->getSession()->getPage();

    // /click the visibility button (yes/no)
    $page
      ->find('xpath', '//div[1]/div/section[2]/div[2]/div/div/div[1]/table/tbody/tr['.$project_number.']/td[5]/span')
      ->click()
    ;

    $this->iSelectTheOptionInThePopup($visibility);
  }

  /**
   * @Then /^I change the approval of the project number "([^"]*)" in the list to "([^"]*)"$/
   *
   * @throws \Exception
   */
  public function iChangeTheApprovalOfTheProject(string $project_number, string $approved): void
  {
    // /param project_number contains the number of the project position in the list on the admin page
    // /
    $page = $this->getSession()->getPage();
    // /click the visibility button (yes/no)
    $page
      ->find('xpath', '//div[1]/div/section[2]/div[2]/div/div/div[1]/table/tbody/tr['.$project_number.']/td[9]/span')
      ->click()
    ;

    $this->iSelectTheOptionInThePopup($approved);
  }

  /**
   * @Then /^I change the approval of the project number "([^"]*)" in the approve list to "([^"]*)"$/
   *
   * @throws \Exception
   */
  public function iChangeTheApprovalOfTheProjectInApproveList(string $project_number, string $approved): void
  {
    // /param project_number contains the number of the project position in the list on the admin page
    // /
    $page = $this->getSession()->getPage();
    // /click the visibility button (yes/no)
    $page
      ->find('xpath', '//div[1]/div/section[2]/div[2]/div/div/div[1]/table/tbody/tr['.$project_number.']/td[6]/span')
      ->click()
    ;

    $this->iSelectTheOptionInThePopup($approved);
  }

  /**
   * @Then /^I change the flavor of the project number "([^"]*)" in the list to "([^"]*)"$/
   *
   * @throws \Exception
   */
  public function iChangeTheFlavorOfTheProject(string $project_number, string $flavor): void
  {
    // /param project_number contains the number of the project position in the list on the admin page

    $page = $this->getSession()->getPage();
    // /click the visibility button (yes/no)
    $page
      ->find('xpath', '//div[1]/div/section[2]/div[2]/div/div/div[1]/table/tbody/tr['.$project_number.']/td[4]/span')
      ->click()
    ;
    // click the input on the popup to show yes or no option
    $page
      ->find('css', '.editable-input')
      ->click()
    ;

    match ($flavor) {
      Flavor::POCKETCODE => $page
        ->find('css', 'select.form-control > option:nth-child(1)')
        ->click(),
      Flavor::POCKETALICE => $page
        ->find('css', 'select.form-control > option:nth-child(2)')
        ->click(),
      Flavor::POCKETGALAXY => $page
        ->find('css', 'select.form-control > option:nth-child(3)')
        ->click(),
      Flavor::PHIROCODE => $page
        ->find('css', 'select.form-control > option:nth-child(4)')
        ->click(),
      Flavor::LUNA => $page
        ->find('css', 'select.form-control > option:nth-child(5)')
        ->click(),
      Flavor::CREATE_AT_SCHOOL => $page
        ->find('css', 'select.form-control > option:nth-child(6)')
        ->click(),
      Flavor::EMBROIDERY => $page
        ->find('css', 'select.form-control > option:nth-child(7)')
        ->click(),
      Flavor::ARDUINO => $page
        ->find('css', 'select.form-control > option:nth-child(8)')
        ->click(),
      default => throw new \Exception('Wrong flavor'),
    };

    // click button to confirm the selection
    $page
      ->find('css', 'button.btn-sm:nth-child(1)')
      ->click()
    ;
  }

  /**
   * @Then /^I change upload of the entry number "([^"]*)" in the list to "([^"]*)"$/
   *
   * @throws \Exception
   */
  public function iChangeUploadOfTheEntry(string $project_number, string $approved): void
  {
    $page = $this->getSession()->getPage();
    $page
      ->find('xpath', '//div[1]/div/section[2]/div[2]/div/form/div/div/table/tbody/tr['.$project_number.']/td[4]/span')
      ->click()
    ;

    $this->iSelectTheOptionInThePopup($approved);
    $this->iWaitForAjaxToFinish();
  }

  /**
   * @Then /^I change report of the entry number "([^"]*)" in the list to "([^"]*)"$/
   *
   * @throws \Exception
   */
  public function iChangeReportOfTheEntry(string $project_number, string $approved): void
  {
    $page = $this->getSession()->getPage();
    $page
      ->find('xpath', '//div[1]/div/section[2]/div[2]/div/form/div/div/table/tbody/tr['.$project_number.']/td[5]/span')
      ->click()
    ;

    $this->iSelectTheOptionInThePopup($approved);
    $this->iWaitForAjaxToFinish();
  }

  /**
   * @Then /^I click action button "([^"]*)" of the entry number "([^"]*)"$/
   *
   * @throws \Exception
   */
  public function iClickActionButtonOfEntry(string $action_button, string $entry_number): void
  {
    $page = $this->getSession()->getPage();
    switch ($action_button) {
      case 'edit':
        $page
          ->find('xpath', '//div[1]/div/section[2]/div[2]/div/form/div/div/table/tbody/tr['.$entry_number.']/td[6]/div/a[1]')
          ->click()
        ;
        break;
      case 'delete':
        $page
          ->find('xpath', '//div[1]/div/section[2]/div[2]/div/form/div/div/table/tbody/tr['.$entry_number.']/td[6]/div/a[2]')
          ->click()
        ;
        break;
    }
  }

  /**
   * @Then /^I check the batch action box of entry "([^"]*)"$/
   *
   * @throws \Exception
   */
  public function iCheckBatchActionBoxOfEntry(string $entry_number): void
  {
    $page = $this->getSession()->getPage();
    $page
      ->find('xpath', '//div[1]/div/section[2]/div[2]/div/form/div/div/table/tbody/tr['.$entry_number.']/td/div')
      ->click()
    ;
  }

  /**
   * @Then /^I click on the username "([^"]*)"$/
   *
   * @throws ElementNotFoundException
   */
  public function iClickOnTheUsername(string $username): void
  {
    $this->assertSession()->elementExists('xpath', "//a[contains(text(),'".$username."')]");

    $page = $this->getSession()->getPage();
    $page
      ->find('xpath', "//a[contains(text(),'".$username."')]")
      ->click()
    ;
  }

  /**
   * @Then /^I click on the project name "([^"]*)"$/
   *
   * @throws ElementNotFoundException
   */
  public function iClickOnTheProjectName(string $project_name): void
  {
    $this->assertSession()->elementExists('xpath', "//a[contains(text(),'".$project_name."')]");

    $page = $this->getSession()->getPage();
    $page
      ->find('xpath', "//a[contains(text(),'".$project_name."')]")
      ->click()
    ;
  }

  /**
   * @Then /^I click on the show button of the project number "([^"]*)" in the list$/
   *
   * @throws ElementNotFoundException
   */
  public function iClickOnTheShowButton(string $project_number): void
  {
    $page = $this->getSession()->getPage();
    $this->assertSession()->elementExists('xpath',
      '//div[1]/div/section[2]/div[2]/div/div/div[1]/table/tbody/tr['.$project_number.']/td[12]/div/a');

    $page
      ->find('xpath', '//div[1]/div/section[2]/div[2]/div/div/div[1]/table/tbody/tr['.$project_number.']/td[12]/div/a')
      ->click()
    ;
  }

  /**
   * @Then /^I click on the show button of project with id "([^"]*)" in the approve list$/
   */
  public function iClickOnTheShowButtonInTheApproveList(string $project_id): void
  {
    $page = $this->getSession()->getPage();
    $page->find('xpath', "//a[contains(@href,'/admin/project/approval/".$project_id."/show')]")->click();
  }

  /**
   * @Then /^I click on the code view button$/
   */
  public function iClickOnTheCodeViewButtonInTheApproveList(): void
  {
    $page = $this->getSession()->getPage();
    $page->findById('code-view')->click();
  }

  /**
   * @Then /^I click on the edit button of the extension number "([^"]*)" in the extensions list$/
   *
   * @throws ElementNotFoundException
   */
  public function iClickOnTheEditButtonInAllExtensions(string $project_number): void
  {
    $page = $this->getSession()->getPage();
    $this->assertSession()->elementExists('xpath',
      '//div[1]/div/section[2]/div[2]/div/div/div[1]/table/tbody/tr['.$project_number.']/td[4]/div/a');

    $page
      ->find('xpath', '//div[1]/div/section[2]/div[2]/div/div/div[1]/table/tbody/tr['.$project_number.']/td[4]/div/a')
      ->click()
    ;
  }

  /**
   * @Then /^I click on the add new button$/
   *
   * @throws ElementNotFoundException
   */
  public function iClickOnTheAddNewButton(): void
  {
    $page = $this->getSession()->getPage();
    $this->assertSession()->elementExists('xpath',
      "//a[contains(text(),'Add new')]");

    $page
      ->find('xpath', "//a[contains(text(),'Add new')]")
      ->click()
    ;
  }

  /**
   * @When /^I report project (\d+) with category "([^"]*)" and note "([^"]*)" in Browser$/
   *
   * @throws ElementNotFoundException
   */
  public function iReportProjectWithNoteInBrowser(string $project_id, string $category, string $note): void
  {
    $this->visit('app/project/'.$project_id);
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
   */
  public function iWriteInTextbox(string $arg1): void
  {
    $textarea = $this->getSession()->getPage()->find('css', '#comment-message');
    Assert::assertNotNull($textarea, 'Textarea not found');
    $textarea->setValue($arg1);
  }

  /**
   * @Given /^I write "([^"]*)" in textarea$/
   */
  public function iWriteInTextarea(string $arg1): void
  {
    $textarea = $this->getSession()->getPage()->find('css', '#edit-text');
    Assert::assertNotNull($textarea, 'Textarea not found');
    $textarea->setValue($arg1);
  }

  /**
   * @Then /^I click the "([^"]*)" RadioButton$/
   */
  public function iClickTheRadiobutton(string $arg1): void
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
    Assert::assertTrue([] === $comments && [] === $notifications);
  }

  /**
   * @When /^(?:|I )attach the avatar "(?P<path>[^"]*)" to "(?P<field>(?:[^"]|\\")*)"$/
   *
   * @throws ElementNotFoundException
   */
  public function attachAvatarToField(string $field, string $path): void
  {
    $field = $this->fixStepArgument($field);
    $this->getSession()->getPage()->attachFileToField($field, realpath(self::AVATAR_DIR.$path));
  }

  /**
   * @Then /^the avatar img tag should( [^"]*)? have the "([^"]*)" data url$/
   */
  public function theAvatarImgTagShouldHaveTheDataUrl(string $not, string $name): void
  {
    $name = trim($name);
    $not = trim($not);

    $pre_source = $this->getSession()->getPage()->find('css', '.profile__basic-info__avatar__img');
    Assert::assertNotNull($pre_source, "Couldn't find .profile__basic-info__avatar__img");
    $source = $pre_source->getAttribute('src') ?? '';
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

      case 'default':
        $defaultSource = 'images/default/avatar_default.png';
        'not' === $not ? Assert::assertStringNotContainsString($defaultSource, $source) : Assert::assertStringContainsString($defaultSource, $source);
        break;

      default:
        throw new \InvalidArgumentException('Invalid image name: '.$name);
    }
  }

  /**
   * @When /^I press enter in the search bar$/
   *
   * @throws ElementNotFoundException
   */
  public function iPressEnterInTheSearchBar(): void
  {
    // Hacky solution since triggering the submit event is not working
    $arg1 = trim('#top-app-bar__search-form__submit');
    $this->assertSession()->elementExists('css', $arg1);
    $this->getSession()->getPage()->find('css', $arg1)->click();
  }

  /**
   * @Then /^I should see media file with id "([^"]*)"$/
   */
  public function iShouldSeeMediaFileWithId(string $id): void
  {
    $link = $this->getSession()->getPage()->find('css', '#mediafile-'.$id);
    Assert::assertNotNull($link);
  }

  /**
   * @Then /^I should not see media file with id "([^"]*)"$/
   */
  public function iShouldNotSeeMediaFileWithId(string $id): void
  {
    $link = $this->getSession()->getPage()->find('css', '#mediafile-'.$id);
    Assert::assertNull($link);
  }

  /**
   * @Then /^I should see media file with id ([0-9]+) in category "([^"]*)"$/
   */
  public function iShouldSeeMediaFileWithIdInCategory(string $id, string $category): void
  {
    $link = $this->getSession()->getPage()
      ->find('css', '[data-name="'.$category.'"]')
      ->find('css', '#mediafile-'.$id)
    ;
    Assert::assertNotNull($link);
  }

  /**
   * @Then /^I should see ([0-9]+) media files? in category "([^"]*)"$/
   */
  public function iShouldSeeNumberOfMediaFilesInCategory(string $count, string $category): void
  {
    $elements = $this->getSession()->getPage()
      ->find('css', '[data-name="'.$category.'"]')
      ->findAll('css', '.mediafile')
    ;
    Assert::assertEquals($count, count($elements));
  }

  /**
   * @When /^I should see the video available at "([^"]*)"$/
   */
  public function iShouldSeeTheVideoAvailableAt(string $url): void
  {
    $page = $this->getSession()->getPage();
    $video = $page->find('css', '.video-container > iframe');
    Assert::assertNotNull($video, 'Video not found!');
    Assert::assertTrue(str_contains((string) $video->getAttribute('src'), $url));
  }

  /**
   * @Then /^I should see the slider with the values "([^"]*)"$/
   */
  public function iShouldSeeTheSliderWithTheValues(string $values): void
  {
    $slider_items = explode(',', $values);
    $owl_items = $this->getSession()->getPage()->findAll('css', '.carousel-item');
    $owl_items_count = count($owl_items);
    Assert::assertEquals($owl_items_count, count($slider_items));

    for ($index = 0; $index < $owl_items_count; ++$index) {
      $url = $slider_items[$index];
      if (!str_starts_with($url, 'http://')) {
        $project = $this->getProjectManager()->findOneByName($url);
        Assert::assertNotNull($project);
        Assert::assertNotNull($project->getId());
        $url = $this->getRouter()->generate('program', ['id' => $project->getId(), 'theme' => Flavor::POCKETCODE]);
      }

      $feature_url = $owl_items[$index]->getAttribute('href');
      Assert::assertStringContainsString($url, $feature_url);
    }
  }

  /**
   * @When /^I press on the tag "([^"]*)"$/
   *
   * @throws ElementNotFoundException
   */
  public function iPressOnTheTag(string $arg1): void
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
   * @throws ElementNotFoundException
   */
  public function iPressOnTheExtension(string $name): void
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
   * @Then /^I click the currently visible search icon$/
   */
  public function iClickTheCurrentlyVisibleSearchIcon(): void
  {
    $icon = $this->getSession()->getPage()->findById('top-app-bar__btn-search');
    Assert::assertTrue($icon->isVisible(), 'Tried to click #top-app-bar__btn-search but no visible element was found.');
    $icon->click();
  }

  /**
   * @Given I use a valid JWT token for :username
   *
   * @throws JWTEncodeFailureException
   */
  public function iUseAValidJwtTokenFor(string $username): void
  {
    $user = $this->getUserManager()->findUserByUsername($username);
    $token = $this->getJwtManager()->create($user);
    $this->getSession()->setRequestHeader('Authorization', 'Bearer '.$token);
  }

  /**
   * @Given I use a valid BEARER cookie for :username
   *
   * @throws JWTEncodeFailureException
   */
  public function iUseAValidBEARERCookieFor(string $username): void
  {
    $user = $this->getUserManager()->findUserByUsername($username);
    $token = $this->getJwtManager()->create($user);
    $this->getSession()->setCookie('BEARER', $token);
  }

  /**
   * @Given I use an invalid JWT authorization header for :username
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
   */
  public function iHaveAProjectZip(string $project_zip_name): void
  {
    $filesystem = new Filesystem();
    $original_file = $this->FIXTURES_DIR.$project_zip_name;
    $target_file = sys_get_temp_dir().'/project_generated.catrobat';
    $filesystem->copy($original_file, $target_file, true);
  }

  /**
   * @Given I have a project
   *
   * @throws \Exception
   */
  public function iHaveAProject(): void
  {
    $this->generateProjectFileWith([]);
  }

  /**
   * @Given /^I am using pocketcode with language version "([^"]*)"$/
   *
   * @throws \Exception
   */
  public function iAmUsingPocketcodeWithLanguageVersion(string $version): void
  {
    $this->generateProjectFileWith([
      'catrobatLanguageVersion' => $version,
    ]);
  }

  /**
   * @Given I have an embroidery project
   *
   * @throws \Exception
   */
  public function iHaveAnEmbroideryProject(): void
  {
    $this->generateProjectFileWith([], true);
  }

  /**
   * @Given /^I am using pocketcode for "([^"]*)" with version "([^"]*)"$/
   *
   * @throws \Exception
   */
  public function iAmUsingPocketcodeForWithVersion(string $platform, string $version): void
  {
    $this->generateProjectFileWith([
      'platform' => $platform,
      'applicationVersion' => $version,
    ]);
  }

  /**
   * @Given /^the token to upload an apk file is "([^"]*)"$/
   */
  public function theTokenToUploadAnApkFileIs(): void
  {
    // Defined in config_test.yaml
  }

  /**
   * @Given /^the jenkins job id is "([^"]*)"$/
   */
  public function theJenkinsJobIdIs(): void
  {
    // Defined in config_test.yaml
  }

  /**
   * @Given /^the jenkins token is "([^"]*)"$/
   */
  public function theJenkinsTokenIs(): void
  {
    // Defined in config_test.yaml
  }

  /**
   * @Then /^following parameters are sent to jenkins:$/
   *
   * @throws \Exception
   */
  public function followingParametersAreSentToJenkins(TableNode $table): void
  {
    $parameter_defs = $table->getHash();
    $expected_parameters = [];
    foreach ($parameter_defs as $parameter_def) {
      $expected_parameters[$parameter_def['parameter']] = $parameter_def['value'];
    }

    $dispatcher = $this->getSymfonyService(JenkinsDispatcher::class);
    $parameters = $dispatcher->getLastParameters();

    foreach ($expected_parameters as $i => $expected_parameter) {
      Assert::assertMatchesRegularExpression(
        $expected_parameter,
        $parameters[$i]
      );
    }
  }

  /**
   * @Then /^the project apk status will.* be flagged "([^"]*)"$/
   */
  public function theProjectApkStatusWillBeFlagged(string $arg1): void
  {
    $pm = $this->getProjectManager();
    $project = $pm->find('1');
    match ($arg1) {
      'pending' => Assert::assertEquals(Program::APK_PENDING, $project->getApkStatus()),
      'ready' => Assert::assertEquals(Program::APK_READY, $project->getApkStatus()),
      'none' => Assert::assertEquals(Program::APK_NONE, $project->getApkStatus()),
      default => throw new PendingException('Unknown state: '.$arg1),
    };
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
    $directory = $this->getSymfonyParameterAsString('catrobat.apk.dir');
    $finder = new Finder();
    $finder->in($directory)->depth(0);
    Assert::assertEquals(1, $finder->count());
  }

  /**
   * @Given /^the project apk status is flagged "([^"]*)"$/
   *
   * @throws \Exception
   */
  public function theProjectApkStatusIsFlagged(string $arg1): void
  {
    $pm = $this->getProjectManager();
    $project = $pm->find('1');
    switch ($arg1) {
      case 'pending':
        $project->setApkStatus(Program::APK_PENDING);
        break;
      case 'ready':
        $project->setApkStatus(Program::APK_READY);
        /* @var $apk_repository ApkRepository */
        $apk_repository = $this->getSymfonyService(ApkRepository::class);
        $apk_repository->save(new File(strval($this->getTempCopy($this->FIXTURES_DIR.'/test.catrobat'))), $project->getId());
        break;
      default:
        $project->setApkStatus(Program::APK_NONE);
    }

    $pm->save($project);
  }

  /**
   * @Then /^no build request will be sent to jenkins$/
   *
   * @throws \Exception
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
    foreach ($user_stats as $user_stat) {
      $this->assertSession()->pageTextContains($user_stat['#Reported Comments']);
      $this->assertSession()->pageTextContains($user_stat['#Reported Projects']);
      $this->assertSession()->pageTextContains($user_stat['Username']);
      $this->assertSession()->pageTextContains($user_stat['Email']);
    }
  }

  /**
   * @Then /^I should see the notifications table:$/
   *
   * @throws ResponseTextException
   */
  public function shouldSeeNotificationTable(TableNode $table): void
  {
    $user_stats = $table->getHash();
    foreach ($user_stats as $user_stat) {
      $this->assertSession()->pageTextContains($user_stat['User']);
      $this->assertSession()->pageTextContains($user_stat['User Email']);
      $this->assertSession()->pageTextContains($user_stat['Upload']);
      $this->assertSession()->pageTextContains($user_stat['Report']);
    }
  }

  /**
   * @Then /^I should see the table with all projects in the following order:$/
   */
  public function shouldSeeFollowingTable(TableNode $table): void
  {
    $user_stats = $table->getHash();
    $td = $this->getSession()->getPage()->findAll('css', '.table tbody tr');

    $actual_values = [];
    foreach ($td as $value) {
      $actual_values[] = $value->getText();
    }

    Assert::assertEquals(count($actual_values), count($user_stats), 'Wrong number of projects in table');

    $counter = 0;
    foreach ($user_stats as $user_stat) {
      $user_stat = array_filter($user_stat);
      Assert::assertEquals(implode(' ', $user_stat), $actual_values[$counter]);
      ++$counter;
    }
  }

  /**
   * @Then /^I should see the example table:$/
   *
   * @throws ResponseTextException
   */
  public function shouldSeeExampleTable(TableNode $table): void
  {
    $user_stats = $table->getHash();
    foreach ($user_stats as $user_stat) {
      $this->assertSession()->pageTextContains($user_stat['Id']);
      $this->assertSession()->pageTextContains($user_stat['Project']);
      $this->assertSession()->pageTextContains($user_stat['Flavor']);
      $this->assertSession()->pageTextContains($user_stat['Priority']);
    }
  }

  /**
   * @Then /^I should see the following not approved projects:$/
   */
  public function seeNotApprovedProjects(TableNode $table): void
  {
    $user_stats = $table->getHash();
    $td = $this->getSession()->getPage()->findAll('css', '.table tbody tr');

    $actual_values = [];
    foreach ($td as $value) {
      $actual_values[] = $value->getText();
    }

    Assert::assertEquals(count($actual_values), count($user_stats), 'Wrong number of projects in table');

    $counter = 0;
    foreach ($user_stats as $user_stat) {
      Assert::assertEquals(implode(' ', $user_stat), $actual_values[$counter]);
      ++$counter;
    }
  }

  /**
   * @Then /^I should see the reported projects table:$/
   *
   * @throws ResponseTextException
   */
  public function seeReportedProjectsTable(TableNode $table): void
  {
    $user_stats = $table->getHash();
    foreach ($user_stats as $user_stat) {
      $this->assertSession()->pageTextContains($user_stat['Note']);
      $this->assertSession()->pageTextContains($user_stat['State']);
      $this->assertSession()->pageTextContains($user_stat['Category']);
      $this->assertSession()->pageTextContains($user_stat['Reporting User']);
      $this->assertSession()->pageTextContains($user_stat['Project']);
      $this->assertSession()->pageTextContains($user_stat['Project Visible']);
    }
  }

  /**
   * @Then /^I should see the ready apks table:$/
   *
   * @throws ResponseTextException
   */
  public function seeReadyApksTable(TableNode $table): void
  {
    $user_stats = $table->getHash();
    foreach ($user_stats as $user_stat) {
      $this->assertSession()->pageTextContains($user_stat['Id']);
      $this->assertSession()->pageTextContains($user_stat['User']);
      $this->assertSession()->pageTextContains($user_stat['Name']);
      $this->assertSession()->pageTextContains($user_stat['Apk Request Time']);
    }
  }

  /**
   * @Then /^I should see the ready maintenance information table:$/
   *
   * @throws ResponseTextException
   */
  public function seeReadyMaintenanceInformationTable(TableNode $table): void
  {
    $user_stats = $table->getHash();
    foreach ($user_stats as $user_stat) {
      $this->assertSession()->pageTextContains($user_stat['Feature Name']);
      $this->assertSession()->pageTextContains($user_stat['Active']);
      $this->assertSession()->pageTextContains($user_stat['LTM Code']);
      $this->assertSession()->pageTextContains($user_stat['Maintenance Start']);
      $this->assertSession()->pageTextContains($user_stat['Maintenance End']);
      $this->assertSession()->pageTextContains($user_stat['Additional Information']);
      $this->assertSession()->pageTextContains($user_stat['Icon']);
      $this->assertSession()->pageTextContains($user_stat['Actions']);
    }
  }

  /**
   * @Then /^I should see the pending apk table:$/
   *
   * @throws ResponseTextException
   */
  public function seePendingApkTable(TableNode $table): void
  {
    $user_stats = $table->getHash();
    foreach ($user_stats as $user_stat) {
      $this->assertSession()->pageTextContains($user_stat['Id']);
      $this->assertSession()->pageTextContains($user_stat['User']);
      $this->assertSession()->pageTextContains($user_stat['Name']);
      $this->assertSession()->pageTextContains($user_stat['Apk Request Time']);
      $this->assertSession()->pageTextContains($user_stat['Apk Status']);
    }
  }

  /**
   * @Then /^I should see the survey table:$/
   *
   * @throws ResponseTextException
   */
  public function seeSurveyTable(TableNode $table): void
  {
    $survey_stats = $table->getHash();
    foreach ($survey_stats as $survey_stat) {
      $this->assertSession()->pageTextContains($survey_stat['Language Code']);
      $this->assertSession()->pageTextContains($survey_stat['Url']);
      $this->assertSession()->pageTextContains($survey_stat['Active']);
    }
  }

  /**
   * @Then /^I should see the achievements table:$/
   *
   * @throws ResponseTextException
   */
  public function seeAchievementTable(TableNode $table): void
  {
    $this->assertSession()->pageTextContains('Priority');
    $this->assertSession()->pageTextContains('Internal Title');
    $this->assertSession()->pageTextContains('Internal Description');
    $this->assertSession()->pageTextContains('Color');
    $this->assertSession()->pageTextContains('Enabled');
    $this->assertSession()->pageTextContains('Unlocked by');

    $data = $table->getHash();
    foreach ($data as $entry) {
      $this->assertSession()->pageTextContains($entry['Priority']);
      $this->assertSession()->pageTextContains($entry['Internal Title']);
      $this->assertSession()->pageTextContains($entry['Internal Description']);
      $this->assertSession()->pageTextContains($entry['Color']);
      $this->assertSession()->pageTextContains($entry['Enabled']);
      $this->assertSession()->pageTextContains($entry['Unlocked by']);
    }
  }

  /**
   * @Then /^I should see the tags table:$/
   *
   * @throws ResponseTextException
   */
  public function seeTagsTable(TableNode $table): void
  {
    $this->assertSession()->pageTextContains('Internal Title');
    $this->assertSession()->pageTextContains('Enabled');
    $this->assertSession()->pageTextContains('Projects with tag');

    $data = $table->getHash();
    foreach ($data as $entry) {
      $this->assertSession()->pageTextContains($entry['Internal Title']);
      $this->assertSession()->pageTextContains($entry['Enabled']);
      $this->assertSession()->pageTextContains($entry['Projects with tag']);
    }
  }

  /**
   * @Then /^I should see the extensions table:$/
   *
   * @throws ResponseTextException
   */
  public function seeExtensionsTable(TableNode $table): void
  {
    $this->assertSession()->pageTextContains('Internal Title');
    $this->assertSession()->pageTextContains('Enabled');
    $this->assertSession()->pageTextContains('Projects with extension');

    $data = $table->getHash();
    foreach ($data as $entry) {
      $this->assertSession()->pageTextContains($entry['Internal Title']);
      $this->assertSession()->pageTextContains($entry['Enabled']);
      $this->assertSession()->pageTextContains($entry['Projects with extension']);
    }
  }

  /**
   * @Then /^I should see the cron jobs table:$/
   *
   * @throws ResponseTextException
   */
  public function seeCronJobTable(TableNode $table): void
  {
    $this->assertSession()->pageTextContains('Name');
    $this->assertSession()->pageTextContains('State');
    $this->assertSession()->pageTextContains('Cron Interval');
    $this->assertSession()->pageTextContains('Start At');
    $this->assertSession()->pageTextContains('End At');
    $this->assertSession()->pageTextContains('Result Code');

    $survey_stats = $table->getHash();
    foreach ($survey_stats as $survey_stat) {
      $this->assertSession()->pageTextContains($survey_stat['Name']);
      $this->assertSession()->pageTextContains($survey_stat['State']);
      $this->assertSession()->pageTextContains($survey_stat['Cron Interval']);
      $this->assertSession()->pageTextContains($survey_stat['Start At']);
      $this->assertSession()->pageTextContains($survey_stat['End At']);
      $this->assertSession()->pageTextContains($survey_stat['Result Code']);
    }
  }

  /**
   * @Then /^I should see the media package categories table:$/
   *
   * @throws ResponseTextException
   */
  public function seeMediaPackageCategoriesTable(TableNode $table): void
  {
    $user_stats = $table->getHash();
    foreach ($user_stats as $user_stat) {
      $this->assertSession()->pageTextContains($user_stat['Id']);
      $this->assertSession()->pageTextContains($user_stat['Name']);
      $this->assertSession()->pageTextContains($user_stat['Package']);
      $this->assertSession()->pageTextContains($user_stat['Priority']);
    }
  }

  /**
   * @Then /^I should see the media packages table:$/
   *
   * @throws ResponseTextException
   */
  public function seeMediaPackages(TableNode $table): void
  {
    $user_stats = $table->getHash();
    foreach ($user_stats as $user_stat) {
      $this->assertSession()->pageTextContains($user_stat['name']);
      $this->assertSession()->pageTextContains($user_stat['name_url']);
    }
  }

  /**
   * @Then /^I should see the media package files table:$/
   *
   * @throws ResponseTextException
   */
  public function seeMediaPackageFilesTable(TableNode $table): void
  {
    $user_stats = $table->getHash();
    foreach ($user_stats as $user_stat) {
      $this->assertSession()->pageTextContains($user_stat['Id']);
      $this->assertSession()->pageTextContains($user_stat['Name']);
      $this->assertSession()->pageTextContains($user_stat['Category']);
      $this->assertSession()->pageTextContains($user_stat['Author']);
      $this->assertSession()->pageTextContains($user_stat['Flavors']);
      $this->assertSession()->pageTextContains($user_stat['Downloads']);
      $this->assertSession()->pageTextContains($user_stat['Active']);
    }
  }

  /**
   * @Given /^there is a file "([^"]*)" with size "([^"]*)" bytes in the APK-folder$/
   */
  public function thereIsAFileWithSizeBytesInTheApkFolder(string $filename, string $size): void
  {
    $this->generateFileInPath($this->getSymfonyParameter('catrobat.apk.dir'), $filename, $size);
  }

  /**
   * @Then /^project with id "([^"]*)" should have no apk$/
   */
  public function projectWithIdShouldHaveNoApk(string $project_id): void
  {
    $project_manager = $this->getProjectManager();
    $project = $project_manager->find($project_id);
    Assert::assertEquals(Program::APK_NONE, $project->getApkStatus());
  }

  /**
   * @Given /^there is a file "([^"]*)" with size "([^"]*)" bytes in the compressed-folder$/
   */
  public function thereIsAFileWithSizeBytesInTheExtractedFolder(string $filename, string $size): void
  {
    $this->generateFileInPath($this->getSymfonyParameter('catrobat.file.storage.dir'),
      $filename, $size);
  }

  /**
   * @Then the resources should not contain the unnecessary files
   */
  public function theResourcesShouldNotContainTheUnnecessaryFiles(): void
  {
    $files = new \RecursiveIteratorIterator(
      new \RecursiveDirectoryIterator($this->EXTRACT_RESOURCES_DIR, \FilesystemIterator::SKIP_DOTS),
      \RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($files as $file) {
      $filename = $file->getFilename();
      Assert::assertStringNotContainsString('remove_me', $filename);
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
  public function iShouldGetFollowingLikeProjects(TableNode $table): void
  {
    $all_like_similarities = $this->getUserLikeSimilarityRelationRepository()->findAll();
    $all_like_similarities_count = count($all_like_similarities);
    $expected_like_similarities = $table->getHash();
    Assert::assertEquals(count($expected_like_similarities), $all_like_similarities_count,
      'Wrong number of returned similarity entries');
    for ($i = 0; $i < $all_like_similarities_count; ++$i) {
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
        round((float) $expected_like_similarities[$i]['similarity'], 3),
        round($like_similarity->getSimilarity(), 3),
        'Wrong value for similarity'
      );
    }
  }

  /**
   * @Then /^I should get following remix similarities:$/
   */
  public function iShouldGetFollowingRemixProjects(TableNode $table): void
  {
    $all_remix_similarities = $this->getUserRemixSimilarityRelationRepository()->findAll();
    $all_remix_similarities_count = count($all_remix_similarities);
    $expected_remix_similarities = $table->getHash();
    Assert::assertEquals(count($expected_remix_similarities), $all_remix_similarities_count,
      'Wrong number of returned similarity entries');
    for ($i = 0; $i < $all_remix_similarities_count; ++$i) {
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
      Assert::assertEquals(round((float) $expected_remix_similarities[$i]['similarity'], 3),
        round($remix_similarity->getSimilarity(), 3),
        'Wrong value for similarity');
    }
  }

  /**
   * @Given the next generated token will be :token
   *
   * @throws \Exception
   */
  public function theNextGeneratedTokenWillBe(string $token): void
  {
    $token_generator = $this->getSymfonyService(TokenGenerator::class);
    $token_generator->setTokenGenerator(new FixedTokenGenerator($token));
  }

  /**
   * @Given /^I have a project with arduino, mindstorms and phiro extensions$/
   */
  public function iHaveAProjectWithArduinoMindstormsAndPhiroExtensions(): void
  {
    $filesystem = new Filesystem();
    $original_file = $this->FIXTURES_DIR.'extensions.catrobat';
    $target_file = sys_get_temp_dir().'/project_generated.catrobat';
    $filesystem->copy($original_file, $target_file, true);
  }

  /**
   * @Then /^We can\'t test anything here$/
   *
   * @throws \Exception
   */
  public function weCantTestAnythingHere(): never
  {
    throw new \Exception(':(');
  }

  /**
   * @Then the button :button should be disabled until download is finished
   */
  public function theButtonShouldBeDisabledUntilDownloadIsFinished(string $button): void
  {
    $this->theElementShouldBeVisible($button);
  }

  /**
   * @Then /^I should see the featured table:$/
   *
   * @throws ResponseTextException
   */
  public function iShouldSeeTheFeaturedTable(TableNode $table): void
  {
    $user_stats = $table->getHash();
    foreach ($user_stats as $user_stat) {
      $this->assertSession()->pageTextContains($user_stat['Id']);
      $this->assertSession()->pageTextContains($user_stat['Project']);
      $this->assertSession()->pageTextContains($user_stat['Url']);
      $this->assertSession()->pageTextContains($user_stat['Flavor']);
      $this->assertSession()->pageTextContains($user_stat['Priority']);
    }
  }

  /**
   * @Given /^I click on the "([^"]*)" link$/
   */
  public function iClickOnTheLink(string $arg1): void
  {
    $page = $this->getSession()->getPage();
    $link = $page->findLink($arg1);
    $link->click();
  }

  /**
   * @Then /^I write "([^"]*)" in textarea with label "([^"]*)"$/
   */
  public function iWriteInTextareaWithLabel(string $arg1, string $arg2): void
  {
    $textarea = $this->getSession()->getPage()->findField($arg2);
    Assert::assertNotNull($textarea, 'Textarea not found');
    $textarea->setValue($arg1);
  }

  /**
   * @Then /^I click on the button named "([^"]*)"/
   *
   * @throws ElementNotFoundException
   */
  public function iClickOnTheButton(string $arg1): void
  {
    $this->assertSession()->elementExists('named', ['button', $arg1]);

    $this
      ->getSession()
      ->getPage()
      ->find('named', ['button', $arg1])
      ->click()
    ;
  }

  /**
   * @Then /^I should see the user table:$/
   *
   * @throws ResponseTextException
   */
  public function iShouldSeeTheUserTable(TableNode $table): void
  {
    $user_stats = $table->getHash();
    foreach ($user_stats as $user_stat) {
      $this->assertSession()->pageTextContains($user_stat['username']);
      $this->assertSession()->pageTextContains($user_stat['email']);
      $this->assertSession()->pageTextContains($user_stat['groups']);
      $this->assertSession()->pageTextContains($user_stat['enabled']);
      $this->assertSession()->pageTextContains($user_stat['createdAt']);
    }
  }

  // --------------------------------------------------------------------------------------------------------------------
  //  User Agent
  // --------------------------------------------------------------------------------------------------------------------

  protected function iUseTheUserAgent(string $user_agent): void
  {
    $this->getSession()->setRequestHeader('User-Agent', $user_agent);
  }

  protected function iUseTheUserAgentParameterized(string $lang_version, string $flavor, string $app_version, string $build_type, string $theme = Flavor::POCKETCODE): void
  {
    // see org.catrobat.catroid.ui.WebViewActivity
    $platform = 'Android';
    $user_agent = 'Catrobat/'.$lang_version.' '.$flavor.'/'.$app_version.' Platform/'.$platform.
      ' BuildType/'.$build_type.' Theme/'.$theme;
    $this->iUseTheUserAgent($user_agent);
  }

  protected function generateFileInPath(string $path, string $filename, string $size): void
  {
    $full_filename = $path.'/'.$filename;
    $dirname = dirname($full_filename);
    if (!is_dir($dirname)) {
      mkdir($dirname, 0755, true);
    }

    $file_path = fopen($full_filename, 'w'); // open in write mode.
    fseek($file_path, (int) $size - 1, SEEK_CUR); // seek to SIZE-1
    fwrite($file_path, 'a'); // write a dummy char at SIZE position
    fclose($file_path); // close the file.
  }

  /**
   * @throws \Exception
   */
  protected function iSelectTheOptionInThePopup(string $option): void
  {
    $page = $this->getSession()->getPage();
    // click the input on the popup to show yes or no option
    $page
      ->find('css', '.editable-input')
      ->click()
    ;

    // click yes or no option
    if ('yes' === $option) {
      $page
        ->find('css', 'select.form-control > option:nth-child(2)')
        ->click()
      ;
    } else {
      $page
        ->find('css', 'select.form-control > option:nth-child(1)')
        ->click()
      ;
    }

    // click button to confirm the selection
    $page
      ->find('css', 'button.btn-sm:nth-child(1)')
      ->click()
    ;
  }
}
