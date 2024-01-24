<?php

namespace App\System\Testing\Behat\Context;

use App\System\Testing\Behat\ContextTrait;
use App\Utils\TimeUtils;
use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\DriverException;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Exception\ExpectationException;
use Behat\Mink\Exception\ResponseTextException;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\MinkExtension\Context\MinkContext;
use PHPUnit\Framework\Assert;

/**
 * Class BrowserContext.
 *
 * Configures the Mink WebBrowser and provides basic utilities to check and interact with an web page.
 */
class BrowserContext extends MinkContext implements Context
{
  use ContextTrait;

  // --------------------------------------------------------------------------------------------------------------------
  //  Session Handling
  // --------------------------------------------------------------------------------------------------------------------

  /**
   * @BeforeScenario
   */
  public function setup(): void
  {
    $this->getSession()->start();
    $this->getSession()->resizeWindow(360, 1_024);
  }

  /**
   * @Given I start a new session
   */
  public function iStartANewSession(): void
  {
    $this->getSession()->restart();
  }

  // --------------------------------------------------------------------------------------------------------------------
  //  Assert Page Content
  // --------------------------------------------------------------------------------------------------------------------
  /**
   * @Given /^the element "([^"]*)" should not exist$/
   *
   * @throws ExpectationException
   */
  public function theElementShouldNotExist(mixed $locator): void
  {
    $this->assertSession()->elementNotExists('css', $locator);
  }

  /**
   * @Given /^the element "([^"]*)" should exist$/
   *
   * @throws ElementNotFoundException
   */
  public function theElementShouldExist(mixed $locator): void
  {
    $this->assertSession()->elementExists('css', $locator);
  }

  /**
   * @Given /^the element "([^"]*)" should not be visible$/
   */
  public function theElementShouldNotBeVisible(mixed $locator): void
  {
    $element = $this->getSession()->getPage()->find('css', $locator);
    Assert::assertNotNull($element);
    Assert::assertFalse($element->isVisible());
  }

  /**
   * @Then /^the element "([^"]*)" should have (a|no) attribute "([^"]*)" with value "([^"]*)"$/
   */
  public function theElementShouldHaveAttributeWith(mixed $locator, mixed $should_have, mixed $attribute, mixed $value): void
  {
    $element = $this->getSession()->getPage()->find('css', $locator);

    Assert::assertNotNull($element, $locator.' not found!');
    Assert::assertTrue($element->hasAttribute($attribute), 'Element has no attribute '.$attribute);

    if ('a' == $should_have) {
      Assert::assertStringContainsString($value, $element->getAttribute($attribute), '<'.$attribute.'> does not contain '.$value);
    } else {
      Assert::assertStringNotContainsString($value, $element->getAttribute($attribute), '<'.$attribute.'> does contain '.$value);
    }

    Assert::assertTrue($element->isVisible(), 'Element is not visible.');
  }

  /**
   * @Then /^at least one "([^"]*)" element should be visible$/
   */
  public function atLeastOneElementShouldBeVisible(mixed $locator): void
  {
    $elements = $this->getSession()->getPage()->findAll('css', $locator);
    foreach ($elements as $e) {
      /** @var NodeElement $e */
      if ($e->isVisible()) {
        return;
      }
    }
    Assert::assertTrue(false, 'No '.$locator.' element currently visible.');
  }

  /**
   * @Then project :id is missing its files
   */
  public function projectIsMissingItsFiles(mixed $id): void
  {
    $this->getFileRepository()->deleteProjectExtractFiles($id);
    $this->getFileRepository()->deleteProjectZipFile($id);
  }

  /**
   * @Then /^no "([^"]*)" element should be visible$/
   */
  public function noElementShouldBeVisible(mixed $locator): void
  {
    $elements = $this->getSession()->getPage()->findAll('css', $locator);
    foreach ($elements as $element) {
      /* @var NodeElement $element */
      Assert::assertFalse($element->isVisible(), 'Found visible '.$locator.' element.');
    }
  }

  /**
   * @Then /^the element "([^"]*)" should have type "([^"]*)"$/
   */
  public function theElementShouldHaveType(mixed $locator, mixed $expected_type): void
  {
    $page = $this->getMink()->getSession()->getPage();
    $type = $page->find('css', $locator)->getAttribute('type');
    Assert::assertEquals($expected_type, $type);
  }

  /**
   * @Then /^the element "([^"]*)" should not have type "([^"]*)"$/
   */
  public function theElementShouldNotHaveType(mixed $element, mixed $expected_type): void
  {
    $page = $this->getMink()->getSession()->getPage();
    $type = $page->find('css', $element)->getAttribute('type');
    Assert::assertNotEquals($expected_type, $type);
  }

  /**
   * @Then /^the element "([^"]*)" should not be disabled$/
   */
  public function theElementShouldNotBeDisabled(mixed $element): void
  {
    $page = $this->getMink()->getSession()->getPage();
    $disabled = $page->find('css', $element)->getAttribute('disabled');
    Assert::assertEquals('', $disabled);
  }

  /**
   * @Then /^the element "([^"]*)" should be disabled$/
   */
  public function theElementShouldBeDisabled(mixed $element): void
  {
    $page = $this->getMink()->getSession()->getPage();
    $disabled = $page->find('css', $element)->getAttribute('disabled');
    Assert::assertEquals('disabled', $disabled);
  }

  /**
   * @Given /^the element "([^"]*)" should be visible$/
   */
  public function theElementShouldBeVisible(mixed $element): void
  {
    $element = $this->getSession()->getPage()->find('css', $element);
    Assert::assertNotNull($element);
    Assert::assertTrue($element->isVisible());
  }

  // --------------------------------------------------------------------------------------------------------------------
  //  Interacting with the web page
  // --------------------------------------------------------------------------------------------------------------------
  /**
   * @When /^I click "([^"]*)"$/
   *
   * @throws ElementNotFoundException
   */
  public function iClick(mixed $arg1): void
  {
    $arg1 = trim((string) $arg1);
    $this->assertSession()->elementExists('css', $arg1);
    $this->getSession()->getPage()->find('css', $arg1)->click();
  }

  /**
   * @When /^I click browser's back button$/
   */
  public function iClickBrowsersBackButton(): void
  {
    $this->getSession()->back();
  }

  /**
   * @Then /^I enter "([^"]*)" into visible "([^"]*)"$/
   */
  public function iEnterIntoVisibleField(mixed $text, mixed $locator): void
  {
    $fields = $this->getSession()->getPage()->findAll('css', $locator);
    Assert::assertLessThanOrEqual(1, count($fields), sprintf("No field with selector '%s' found", $locator));
    foreach ($fields as $field) {
      /** @var NodeElement $field */
      if ($field->isVisible()) {
        $field->setValue($text);
        $field->focus();

        return;
      }
    }
  }

  /**
   * Checks validity of HTML5 form field
   * Example: Then the field "username" should be valid
   * Example: Then the field "username" should not be valid.
   *
   * @Then /^the field "(?P<field>(?:[^"]|\\")*)" should (?P<not>(?:|not ))be valid$/
   *
   * @throws DriverException
   * @throws UnsupportedDriverActionException
   */
  public function fieldValidationState(mixed $field, mixed $not): void
  {
    $field = $this->fixStepArgument($field);
    $field = $this->getSession()->getPage()->findField($field);
    $valid = $this->getSession()->getDriver()->evaluateScript('return document.evaluate("'.str_replace('"', '\\"', $field->getXpath()).'", document, null, XPathResult.ANY_TYPE, null).iterateNext().checkValidity();');
    if ('not' === trim((string) $not)) {
      Assert::assertFalse($valid, 'Field needs to be invalid but was valid');
    } else {
      Assert::assertTrue($valid, 'Field needs to be valid but was invalid');
    }
  }

  /**
   * @Then /^I select package "([^"]*)" for media package category$/
   */
  public function iSelectPackageForMediaPackageCategory(mixed $arg1): void
  {
    $this->getSession()->getPage()->find('css', '.select2-selection__rendered')->click();

    $packages = $this->getSession()->getPage()->findAll('css', '.select2-results__options li');
    foreach ($packages as $package) {
      if ($package->getText() == $arg1) {
        $package->click();
        break;
      }
    }
  }

  /**
   * @Then /^I select flavor "([^"]*)" for media package file$/
   */
  public function iSelectFlavorForMediaPackageFile(mixed $arg1): void
  {
    $this->getSession()->getPage()->findAll('css', '.select2-selection__rendered')[1]->click();

    $flavors = $this->getSession()->getPage()->findAll('css', '.select2-results__options li');
    foreach ($flavors as $flavor) {
      if ($flavor->getText() == $arg1) {
        $flavor->click();
        break;
      }
    }
  }

  /**
   * @Then /^I select flavor "([^"]*)" for example project/
   */
  public function iSelectFlavorForExampleProject(mixed $arg1): void
  {
    $this->getSession()->getPage()->find('css', '.select2-container')->click();

    $flavors = $this->getSession()->getPage()->findAll('css', '.select2-results li');
    foreach ($flavors as $flavor) {
      if ($flavor->getText() == $arg1) {
        $flavor->click();
        break;
      }
    }
  }

  /**
   * Checks whether the browser downloaded a file and stored it into the default download directory.
   * The downloaded file gets deleted after the check.
   *
   * @Then I should have downloaded a file named ":name"
   *
   * @param string $name The name of the file that should have been downloaded
   *
   * @throws \Exception when an error occurs during checking if the file has been downloaded
   */
  public function iShouldHaveDownloadedAFileNamed(string $name): void
  {
    $received = false;
    $file_path = $this->getSymfonyParameterAsString('catrobat.tests.upld-dwnld-dir').'/'.$name;

    $end_time = TimeUtils::getTimestamp() + 5; // Waiting for files to be downloaded times out after 5 seconds
    while (TimeUtils::getTimestamp() < $end_time) {
      if (file_exists($file_path)) {
        $received = true;
        unlink($file_path);
        break;
      }
      usleep(125000);
    }

    Assert::assertEquals(true, $received, "File {$name} hasn't been found at location '{$file_path}'");
  }

  /**
   * @Then /^one of the "(?P<selector>[^"]*)" elements should contain "(?P<value>(?:[^"]|\\")*)"$/
   */
  public function assertOneOfTheElementsContain(string $selector, string $value): void
  {
    $contains = false;
    $elements = $this->getSession()->getPage()->findAll('css', $selector);
    foreach ($elements as $element) {
      if (str_contains($element->getText(), $value)) {
        $contains = true;
        break;
      }
    }
    Assert::assertTrue($contains, "No element '".$selector."' contains '".$value."'");
  }

  /**
   * @Then /^none of the "(?P<selector>[^"]*)" elements should contain "(?P<value>(?:[^"]|\\")*)"$/
   */
  public function assertNoneOfTheElementsContain(string $selector, string $value): void
  {
    $contains = false;
    $elements = $this->getSession()->getPage()->findAll('css', $selector);
    foreach ($elements as $element) {
      if (str_contains($element->getText(), $value)) {
        $contains = true;
        break;
      }
    }
    Assert::assertFalse($contains, "A element '".$selector."' contains '".$value."'");
  }

  /**
   * @When I scroll vertical on :id using a value of :value
   */
  public function scrollVertical(string $selectorID, string $value): void
  {
    $this->getSession()->getDriver()->evaluateScript(
      "document.getElementById(\"{$selectorID}\").scrollTop = {$value}"
    );
  }

  /**
   * @When I scroll horizontal on :id :className using a value of :value
   */
  public function scrollHorizontal(string $selectorID, string $className, string $value): void
  {
    $this->getSession()->getDriver()->evaluateScript(
      "document.getElementById(\"{$selectorID}\").getElementsByClassName(\"{$className}\")[0].scrollLeft = {$value}"
    );
  }

  /**
   * @Then /^I choose "([^"]*)" from selector "([^"]*)"$/
   */
  public function iChooseItemFromSelector(mixed $text, mixed $selector): void
  {
    $this->getSession()->getPage()->find('css', $selector)->click();

    $selected = false;
    $items = $this->getSession()->getPage()->findAll('css', '.mdc-list-item');
    foreach ($items as $item) {
      if ($item->getText() == $text) {
        $item->click();
        $selected = true;
      }
    }

    Assert::assertTrue($selected, "Item '".$text."' for '".$selector."' has not been selected");
  }

  // --------------------------------------------------------------------------------------------------------------------
  //  WAIT - Sometimes it is necessary to wait to prevent timing issues
  // --------------------------------------------------------------------------------------------------------------------
  /**
   * Try to use this function only if it is not possible to define a waiting condition.
   *
   * @When /^I wait (\d+) milliseconds$/
   */
  public function iWaitMilliseconds(mixed $milliseconds): void
  {
    $this->getSession()->wait($milliseconds);
  }

  /**
   * Waits until a page is fully loaded.
   *
   * @Given I wait for the page to be loaded
   */
  public function iWaitForThePageToBeLoaded(): void
  {
    $this->getSession()->wait(5_000, "document.readyState === 'complete'");
    $this->iWaitForAjaxToFinish();
  }

  /**
   * Wait for AJAX to finish.
   *
   * @Given /^I wait for AJAX to finish$/
   */
  public function iWaitForAjaxToFinish(): void
  {
    $this->getSession()->wait(1000);
  }

  /**
   * @Given I am on the page :page with header :header equal to :value
   */
  public function iAmOnThePageWithHeaderEqualTo(string $page, string $header, string $value): void
  {
    $this->getSession()->setRequestHeader($header, $value);
    $this->visit($page);
  }

  /**
   * @Then I wait for the element :selector to be visible
   *
   * @throws ResponseTextException
   */
  public function iWaitForTheElementToBeVisible(mixed $locator): void
  {
    $tries = 100;
    $delay = 100000; // every 1/10 second
    $element = null;
    for ($timer = 0; $timer < $tries; ++$timer) {
      if (null === $element) {
        $element = $this->getSession()->getPage()->find('css', $locator);
        if (null === $element) {
          continue;
        }
      }
      if ($element->isVisible()) {
        return;
      }
      usleep($delay);
    }

    $message = sprintf("The element '%s' was not visible after a %s micro seconds timeout", $locator, $delay * $tries);
    throw new ResponseTextException($message, $this->getSession());
  }

  /**
   * @Then I wait for the element :selector to be not visible
   *
   * @throws ResponseTextException
   */
  public function iWaitForTheElementToBeNotVisible(mixed $locator): void
  {
    $element = $this->getSession()->getPage()->find('css', $locator);
    if (null === $element) {
      return; // element does not exist, so not visible
    }
    $tries = 100;
    $delay = 100000; // every 1/10 second
    for ($timer = 0; $timer < $tries; ++$timer) {
      if (!$element->isValid() || !$element->isVisible()) {
        return;
      }
      usleep($delay);
    }

    $message = sprintf("The element '%s' was still visible after a %s micro seconds timeout", $locator, $delay * $tries);
    throw new ResponseTextException($message, $this->getSession());
  }

  /**
   * If an element is visible within a timeout, it needs to hide/be removed in the same timeout again.
   * Can be used for loading spinners, for example.
   *
   * @Then I wait for the element :selector to appear and if so to disappear again
   *
   * @throws ResponseTextException
   */
  public function iWaitForTheElementToAppearAndDisappear(mixed $locator): void
  {
    $tries = 100;
    $delay = 100_000; // every 1/10 second
    $element = null;
    for ($timer = 0; $timer < $tries; ++$timer) {
      if (null === $element) {
        $element = $this->getSession()->getPage()->find('css', $locator);
        if (null === $element) {
          continue;
        }
      }
      if ($element->isValid() && $element->isVisible()) {
        break;
      }
      usleep($delay);
    }

    if (null === $element) {
      return; // element never appeared
    }

    for ($timer = 0; $timer < $tries; ++$timer) {
      if (!$element->isValid() || !$element->isVisible()) {
        return;
      }
      usleep($delay);
    }

    $message = sprintf("The element '%s' was not visible after a %s micro seconds timeout", $locator, $delay * $tries);
    throw new ResponseTextException($message, $this->getSession());
  }

  /**
   * @Then I wait for the element :selector to contain :text
   *
   * @throws ResponseTextException
   */
  public function iWaitForTheElementToContain(mixed $locator, mixed $text): void
  {
    /** @var NodeElement $element */
    $element = $this->getSession()->getPage()->find('css', $locator);
    $tries = 100;
    $delay = 100000; // every 1/10 second
    for ($timer = 0; $timer < $tries; ++$timer) {
      if ($element->getText() === $text) {
        return;
      }
      usleep($delay);
    }

    $message = sprintf("The text '%s' was not found after a %s seconds timeout", $text, $delay * $tries);
    throw new ResponseTextException($message, $this->getSession());
  }

  // --------------------------------------------------------------------------------------------------------------------
  //  Error Logging
  // --------------------------------------------------------------------------------------------------------------------

  /**
   * @AfterStep
   */
  public function makeScreenshot(AfterStepScope $scope): void
  {
    try {
      if (!$scope->getTestResult()->isPassed()) {
        $this->saveScreenshot(time().'.png', $this->SCREENSHOT_DIR);
      }
    } catch (\Exception) {
    }
  }

  /**
   * @When /^I get page content$/
   */
  public function iGetPageContent(): never
  {
    var_dump($this->getSession()->getPage()->getContent());
    exit;
  }

  /**
   * @Then /^I click on xpath "([^"]*)"$/
   */
  public function iClickOnXpath(mixed $arg1): void
  {
    $this->assertSession()->elementExists('xpath', $arg1);
    $this->getSession()->getPage()->find('xpath', $arg1)->click();
  }
}
