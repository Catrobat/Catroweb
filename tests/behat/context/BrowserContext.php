<?php

use App\Catrobat\Services\TestEnv\SymfonySupport;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\DriverException;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Exception\ExpectationException;
use Behat\Mink\Exception\ResponseTextException;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use PHPUnit\Framework\Assert;

/**
 * Class BrowserContext.
 *
 * Configures the Mink WebBrowser and provides basic utilities to check and interact with an web page.
 */
class BrowserContext extends MinkContext implements KernelAwareContext
{
  use SymfonySupport;

  //--------------------------------------------------------------------------------------------------------------------
  //  Session Handling
  //--------------------------------------------------------------------------------------------------------------------

  /**
   * @BeforeScenario
   */
  public function setup()
  {
    $this->getMink()->restartSessions();
    $this->getSession()->resizeWindow(320 + 15, 1024);
  }

  /**
   * @AfterScenario
   */
  public function resetSession()
  {
    $this->getSession()->getDriver()->reset();
  }

  /**
   * @Given I start a new session
   */
  public function iStartANewSession()
  {
    $this->getMink()->restartSessions();
  }

  /**
   * @Then /^I ensure pop ups work$/
   */
  public function iEnsurePopUpsWork()
  {
    try
    {
      $this->getSession()->getDriver()->executeScript('window.confirm = function(){return true;}');
    }
    catch (UnsupportedDriverActionException | DriverException $e)
    {
      Assert::assertTrue(
        false,
        "Driver doesn't support JS injection. For Chrome this is needed since it cant deal with pop ups"
      );
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  //  Assert Page Content
  //--------------------------------------------------------------------------------------------------------------------

  /**
   * @Given /^the element "([^"]*)" should not exist$/
   *
   * @param mixed $locator
   *
   * @throws ExpectationException
   */
  public function theElementShouldNotExist($locator)
  {
    $this->assertSession()->elementNotExists('css', $locator);
  }

  /**
   * @Given /^the element "([^"]*)" should exist$/
   *
   * @param mixed $locator
   *
   * @throws ElementNotFoundException
   */
  public function theElementShouldExist($locator)
  {
    $this->assertSession()->elementExists('css', $locator);
  }

  /**
   * @Given /^the element "([^"]*)" should not be visible$/
   *
   * @param mixed $locator
   */
  public function theElementShouldNotBeVisible($locator)
  {
    $element = $this->getSession()->getPage()->find('css', $locator);
    Assert::assertNotNull($element);
    Assert::assertFalse($element->isVisible());
  }

  /**
   * @Then /^the element "([^"]*)" should have attribute "([^"]*)" with value "([^"]*)"$/
   *
   * @param $locator
   * @param $attribute
   * @param $value
   */
  public function theElementShouldHaveAttributeWith($locator, $attribute, $value)
  {
    $element = $this->getSession()->getPage()->find('css', $locator);

    Assert::assertNotNull($element, $locator.' not found!');
    Assert::assertTrue($element->hasAttribute($attribute), 'Element has no attribute '.$attribute);
    Assert::assertContains($value, $element->getAttribute($attribute), '<'.$attribute.'> does not contain '.$value);
    Assert::assertTrue($element->isVisible(), 'Element is not visible.');
  }

  /**
   * @Then /^at least one "([^"]*)" element should be visible$/
   *
   * @param $locator
   */
  public function atLeastOneElementShouldBeVisible($locator)
  {
    $elements = $this->getSession()->getPage()->findAll('css', $locator);
    foreach ($elements as $e)
    {
      /** @var NodeElement $e */
      if ($e->isVisible())
      {
        return;
      }
    }
    Assert::assertTrue(false, 'No '.$locator.' element currently visible.');
  }

  /**
   * @Then /^no "([^"]*)" element should be visible$/
   *
   * @param $locator
   */
  public function atLeastOneElementShouldNotBeVisible($locator)
  {
    $elements = $this->getSession()->getPage()->findAll('css', $locator);
    foreach ($elements as $element)
    {
      /* @var NodeElement $element */
      Assert::assertFalse($element->isVisible(), 'Found visible '.$locator.' element.');
    }
  }

  /**
   * @Then /^the element "([^"]*)" should have type "([^"]*)"$/
   *
   * @param $locator
   * @param $expected_type
   */
  public function theElementShouldHaveType($locator, $expected_type)
  {
    $page = $this->getMink()->getSession()->getPage();
    $type = $page->find('css', $locator)->getAttribute('type');
    Assert::assertEquals($expected_type, $type);
  }

  /**
   * @Then /^the element "([^"]*)" should not have type "([^"]*)"$/
   *
   * @param $element
   * @param $expected_type
   */
  public function theElementShouldNotHaveType($element, $expected_type)
  {
    $page = $this->getMink()->getSession()->getPage();
    $type = $page->find('css', $element)->getAttribute('type');
    Assert::assertNotEquals($expected_type, $type);
  }

  /**
   * @Given /^the element "([^"]*)" should be visible$/
   *
   * @param $element
   */
  public function theElementShouldBeVisible($element)
  {
    $element = $this->getSession()->getPage()->find('css', $element);
    Assert::assertNotNull($element);
    Assert::assertTrue($element->isVisible());
  }

  //--------------------------------------------------------------------------------------------------------------------
  //  Interacting with the web page
  //--------------------------------------------------------------------------------------------------------------------

  /**
   * @When /^I click "([^"]*)"$/
   *
   * @param $arg1
   *
   * @throws ElementNotFoundException
   */
  public function iClick($arg1)
  {
    $arg1 = trim($arg1);
    $this->assertSession()->elementExists('css', $arg1);
    $this->getSession()->getPage()->find('css', $arg1)->click();
  }

  /**
   * @When /^I click browser's back button$/
   */
  public function iClickBrowsersBackButton()
  {
    $this->getSession()->back();
  }

  /**
   * @Then /^I enter "([^"]*)" into visible "([^"]*)"$/
   *
   * @param mixed $text
   * @param mixed $locator
   */
  public function iEnterIntoVisibleField($text, $locator)
  {
    $fields = $this->getSession()->getPage()->findAll('css', $locator);
    Assert::assertLessThanOrEqual(1, count($fields), "No field with selector '{$locator}' found");
    foreach ($fields as $field)
    {
      /** @var NodeElement $field */
      if ($field->isVisible())
      {
        $field->setValue($text);

        return;
      }
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  //  WAIT - Sometimes it is necessary to wait to prevent timing issues
  //--------------------------------------------------------------------------------------------------------------------

  /**
   * Try to use this function only if it is not possible to define a waiting condition.
   *
   * @When /^I wait (\d+) milliseconds$/
   *
   * @param $milliseconds
   */
  public function iWaitMilliseconds($milliseconds)
  {
    $this->getSession()->wait($milliseconds);
  }

  /**
   * Waits until a page is fully loaded.
   *
   * @Given I wait for the page to be loaded
   */
  public function iWaitForThePageToBeLoaded()
  {
    $this->getSession()->wait(15000, "document.readyState === 'complete'");
    $this->iWaitForAjaxToFinish();
  }

  /**
   * Wait for AJAX to finish.
   *
   * @Given /^I wait for AJAX to finish$/
   */
  public function iWaitForAjaxToFinish()
  {
    $this->getSession()->wait(15000,
      '(typeof(jQuery)=="undefined" || (0 === jQuery.active && 0 === jQuery(\':animated\').length))'
    );
  }

  /**
   * @Then I wait for the element :selector to be visible
   *
   * @param $locator
   *
   * @throws ResponseTextException
   */
  public function iWaitForTheElementToBeVisible($locator)
  {
    /** @var NodeElement $element */
    $element = $this->getSession()->getPage()->find('css', $locator);
    $timeout_in_seconds = 15;
    for ($timer = 0; $timer < $timeout_in_seconds; ++$timer)
    {
      if ($element->isVisible())
      {
        return;
      }
      sleep(1);
    }

    $message = "The element '{$locator}' was not visible after a {$timeout_in_seconds} seconds timeout";
    throw new ResponseTextException($message, $this->getSession());
  }

  /**
   * @Then I wait for the element :selector to contain :text
   *
   * @param $locator
   * @param $text
   *
   * @throws ResponseTextException
   */
  public function iWaitForTheElementToContain($locator, $text)
  {
    /** @var NodeElement $element */
    $element = $this->getSession()->getPage()->find('css', $locator);
    $timeout_in_seconds = 15;
    for ($timer = 0; $timer < $timeout_in_seconds; ++$timer)
    {
      if ($element->getText() === $text)
      {
        return;
      }
      sleep(1);
    }

    $message = "The text '{$text}' was not found after a {$timeout_in_seconds} seconds timeout";
    throw new ResponseTextException($message, $this->getSession());
  }

  //--------------------------------------------------------------------------------------------------------------------
  //  Error Logging
  //--------------------------------------------------------------------------------------------------------------------

  /**
   * @AfterStep
   */
  public function makeScreenshot(AfterStepScope $scope)
  {
    if (!$scope->getTestResult()->isPassed())
    {
      $this->saveScreenshot(null, $this->SCREENSHOT_DIR);
    }
  }

  /**
   * @When /^I get page content$/
   */
  public function iGetPageContent()
  {
    var_dump($this->getSession()->getPage()->getContent());
    die;
  }
}
