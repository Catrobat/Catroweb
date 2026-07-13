<?php

declare(strict_types=1);

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
  //  Shadow-DOM aware form fields (@material/web custom elements)
  // --------------------------------------------------------------------------------------------------------------------
  //
  // Since #7031 text fields / selects are rendered as @material/web custom elements
  // (md-filled-text-field, md-outlined-select, ...). Their native <input> lives inside the
  // element's SHADOW DOM, so Mink's NamedSelector (which only matches light-DOM
  // input/textarea/select) can neither find nor fill them.
  //
  // The host custom element carries the id / name / value, e.g.:
  //     <md-filled-text-field id="username" name="_username" value="...">
  //
  // Legacy MDC markup exposed a light-DOM `<input id="{id}__input">`; feature files therefore
  // still reference locators such as "username__input". The single, documented mapping applied
  // here is: {id}__input  ->  host element with id {id}. Resolution order for a field locator:
  //     1. element with that id
  //     2. (if the locator ends with "__input") element with the id minus the "__input" suffix
  //     3. element carrying that name attribute
  //
  // Setting `.value` on the host + dispatching bubbling/composed input & change events mirrors a
  // real user edit: form-associated md-* elements update their submitted value and Stimulus
  // controllers listening for input/change react correctly.
  private const string FIELD_RESOLVER_JS = <<<'JS'
    function catrowebIsField(el) {
      return !!el && (el.matches('input, textarea, select') || 0 === el.tagName.indexOf('MD-'));
    }
    function catrowebResolveField(locator) {
      var el = document.getElementById(locator);
      if (catrowebIsField(el)) { return el; }
      if (locator.length > 7 && '__input' === locator.slice(-7)) {
        el = document.getElementById(locator.slice(0, -7));
        if (catrowebIsField(el)) { return el; }
      }
      // getElementsByName also matches e.g. <meta name="description">, which would
      // swallow the value silently — only form controls count.
      var named = document.getElementsByName(locator);
      for (var i = 0; i < named.length; i++) {
        if (catrowebIsField(named[i])) { return named[i]; }
      }
      return null;
    }
    JS;

  /**
   * Fills a form field, transparently falling back to shadow-DOM aware handling for md-* elements.
   *
   * NOTE: no step annotations here — Behat inherits the parent MinkContext patterns through
   * getPrototype() (AnnotatedContextReader::readMethodCallees), so repeating them would register
   * duplicate step definitions and abort every suite.
   *
   * @param string $field
   * @param string $value
   */
  #[\Override]
  public function fillField($field, $value): void
  {
    $field = $this->fixStepArgument($field);
    $value = $this->fixStepArgument($value);

    try {
      $this->getSession()->getPage()->fillField($field, $value);
    } catch (ElementNotFoundException $exception) {
      if (!$this->setMaterialFieldValue($field, $value)) {
        throw $exception;
      }
    }
  }

  /**
   * @param string $field
   * @param string $value
   */
  #[\Override]
  public function assertFieldContains($field, $value): void
  {
    $field = $this->fixStepArgument($field);
    $value = $this->fixStepArgument($value);

    try {
      $this->assertSession()->fieldValueEquals($field, $value);
    } catch (ElementNotFoundException $exception) {
      $actual = $this->getMaterialFieldValue($field);
      if (null === $actual) {
        throw $exception;
      }
      Assert::assertSame($value, $actual, sprintf('Field "%s" should contain "%s" but contains "%s".', $field, $value, $actual));
    }
  }

  /**
   * @param string $field
   * @param string $value
   */
  #[\Override]
  public function assertFieldNotContains($field, $value): void
  {
    $field = $this->fixStepArgument($field);
    $value = $this->fixStepArgument($value);

    try {
      $this->assertSession()->fieldValueNotEquals($field, $value);
    } catch (ElementNotFoundException $exception) {
      $actual = $this->getMaterialFieldValue($field);
      if (null === $actual) {
        throw $exception;
      }
      Assert::assertNotSame($value, $actual, sprintf('Field "%s" should not contain "%s".', $field, $value));
    }
  }

  /**
   * @param string $text
   */
  #[\Override]
  public function assertPageContainsText($text): void
  {
    try {
      parent::assertPageContainsText($text);
    } catch (ResponseTextException $exception) {
      // Validation messages on md-* fields render inside their shadow DOM (errorText),
      // which Mink's page-text extraction cannot see.
      if (!$this->materialFieldErrorTextsContain($this->fixStepArgument($text))) {
        throw $exception;
      }
    }
  }

  /**
   * Sets the value of a shadow-DOM md-* form field via JavaScript. Returns false if no element
   * could be resolved for the given locator.
   */
  protected function setMaterialFieldValue(string $locator, string $value): bool
  {
    $script = sprintf(
      'return (function() { %s var el = catrowebResolveField(%s); if (!el) { return false; }'
      .' el.value = %s;'
      .' el.dispatchEvent(new Event("input", {bubbles: true, composed: true}));'
      .' el.dispatchEvent(new Event("change", {bubbles: true, composed: true}));'
      .' return true; })();',
      self::FIELD_RESOLVER_JS,
      json_encode($locator, JSON_THROW_ON_ERROR),
      json_encode($value, JSON_THROW_ON_ERROR)
    );

    return true === $this->getSession()->getDriver()->evaluateScript($script);
  }

  /**
   * Checks whether any md-* text field currently shows the given validation message.
   */
  protected function materialFieldErrorTextsContain(string $text): bool
  {
    $script = "return Array.from(document.querySelectorAll('md-filled-text-field, md-outlined-text-field'))"
      .".map(function(el) { return el.errorText || ''; }).join('\\n');";
    $errors = (string) $this->getSession()->getDriver()->evaluateScript($script);

    return false !== stripos($errors, $text);
  }

  /**
   * Reads the value of a shadow-DOM md-* form field via JavaScript. Returns null if no element
   * could be resolved for the given locator.
   */
  protected function getMaterialFieldValue(string $locator): ?string
  {
    $script = sprintf(
      'return (function() { %s var el = catrowebResolveField(%s); return el ? String(el.value) : null; })();',
      self::FIELD_RESOLVER_JS,
      json_encode($locator, JSON_THROW_ON_ERROR)
    );

    $value = $this->getSession()->getDriver()->evaluateScript($script);

    return null === $value ? null : (string) $value;
  }

  // --------------------------------------------------------------------------------------------------------------------
  //  Session Handling
  // --------------------------------------------------------------------------------------------------------------------

  /**
   * @BeforeScenario
   */
  public function setup(): void
  {
    $this->getSession()->start();
    $this->getSession()->resizeWindow(412, 823);
    $this->getSession()->visit($this->getMinkParameter('base_url'));
    $this->getSession()->executeScript("localStorage.removeItem('oauthSignIn')");
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
  public function theElementShouldNotExist(string $locator): void
  {
    $this->assertSession()->elementNotExists('css', $locator);
  }

  /**
   * @Given /^the element "([^"]*)" should exist$/
   *
   * @throws ElementNotFoundException
   */
  public function theElementShouldExist(string $locator): void
  {
    $this->assertSession()->elementExists('css', $locator);
  }

  /**
   * @Given /^the element "([^"]*)" should not be visible$/
   */
  public function theElementShouldNotBeVisible(string $locator): void
  {
    // See theElementShouldBeVisible: poll to bridge Lit's async update cycle.
    $element = null;
    for ($attempt = 0; $attempt < 20; ++$attempt) {
      $element = $this->getSession()->getPage()->find('css', $locator);
      if (null !== $element && !$element->isVisible()) {
        return;
      }
      usleep(100_000);
    }
    Assert::assertNotNull($element, sprintf('Element "%s" not found.', $locator));
    Assert::assertFalse($element->isVisible(), sprintf('Element "%s" should not be visible.', $locator));
  }

  /**
   * @Then /^the element "([^"]*)" should have (a|no) attribute "([^"]*)" with value "([^"]*)"$/
   */
  public function theElementShouldHaveAttributeWith(string $locator, string $should_have, string $attribute, string $value): void
  {
    $element = $this->getSession()->getPage()->find('css', $locator);

    Assert::assertNotNull($element, $locator.' not found!');
    Assert::assertTrue($element->hasAttribute($attribute), 'Element has no attribute '.$attribute);

    if ('a' === $should_have) {
      Assert::assertStringContainsString($value, $element->getAttribute($attribute), '<'.$attribute.'> does not contain '.$value);
    } else {
      Assert::assertStringNotContainsString($value, $element->getAttribute($attribute), '<'.$attribute.'> does contain '.$value);
    }

    Assert::assertTrue($element->isVisible(), 'Element is not visible.');
  }

  /**
   * @Then /^the element "([^"]*)" should have type "([^"]*)"$/
   */
  public function theElementShouldHaveType(string $locator, string $expected_type): void
  {
    $page = $this->getMink()->getSession()->getPage();
    $element = $page->find('css', $locator);
    Assert::assertNotNull($element, sprintf('Element "%s" not found.', $locator));
    Assert::assertEquals($expected_type, $element->getAttribute('type'));
  }

  /**
   * @Then /^the element "([^"]*)" should not have type "([^"]*)"$/
   */
  public function theElementShouldNotHaveType(string $element, string $expected_type): void
  {
    $page = $this->getMink()->getSession()->getPage();
    $node = $page->find('css', $element);
    Assert::assertNotNull($node, sprintf('Element "%s" not found.', $element));
    Assert::assertNotEquals($expected_type, $node->getAttribute('type'));
  }

  /**
   * @Then /^the element "([^"]*)" should not be disabled$/
   */
  public function theElementShouldNotBeDisabled(string $element): void
  {
    $node = $this->getMink()->getSession()->getPage()->find('css', $element);
    Assert::assertNotNull($node, sprintf('Element "%s" not found.', $element));
    Assert::assertFalse($node->hasAttribute('disabled'), sprintf('Element "%s" should not be disabled.', $element));
  }

  /**
   * @Then /^the element "([^"]*)" should be disabled$/
   */
  public function theElementShouldBeDisabled(string $element): void
  {
    // JS-driven `el.disabled = true` reflects as an empty-string attribute (md-* elements and
    // native inputs alike), so assert attribute presence rather than the literal "disabled".
    $node = $this->getMink()->getSession()->getPage()->find('css', $element);
    Assert::assertNotNull($node, sprintf('Element "%s" not found.', $element));
    Assert::assertTrue($node->hasAttribute('disabled'), sprintf('Element "%s" should be disabled.', $element));
  }

  /**
   * @Given /^the element "([^"]*)" should be visible$/
   */
  public function theElementShouldBeVisible(string $element): void
  {
    // md-* components apply state changes in Lit's async update cycle (unlike the synchronous
    // MDC handlers), so poll briefly instead of asserting the instant the step runs.
    $node = null;
    for ($attempt = 0; $attempt < 20; ++$attempt) {
      $node = $this->getSession()->getPage()->find('css', $element);
      if (null !== $node && $node->isVisible()) {
        return;
      }
      usleep(100_000);
    }
    Assert::assertNotNull($node, sprintf('Element "%s" not found.', $element));
    Assert::assertTrue($node->isVisible(), sprintf('Element "%s" is not visible.', $element));
  }

  /**
   * @Given /^one of the elements "([^"]*)" or "([^"]*)" should be visible$/
   */
  public function oneOfTheElementsOrShouldBeVisible(string $firstLocator, string $secondLocator): void
  {
    $firstElement = $this->getSession()->getPage()->find('css', $firstLocator);
    $secondElement = $this->getSession()->getPage()->find('css', $secondLocator);

    $firstVisible = null !== $firstElement && $firstElement->isVisible();
    $secondVisible = null !== $secondElement && $secondElement->isVisible();

    Assert::assertTrue(
      $firstVisible || $secondVisible,
      sprintf('Neither "%s" nor "%s" is visible.', $firstLocator, $secondLocator)
    );
  }

  // --------------------------------------------------------------------------------------------------------------------
  //  Interacting with the web page
  // --------------------------------------------------------------------------------------------------------------------
  /**
   * @When /^I click "([^"]*)"$/
   *
   * @throws ElementNotFoundException
   */
  public function iClick(string $arg1): void
  {
    $arg1 = trim($arg1);
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
  public function iEnterIntoVisibleField(string $text, string $locator): void
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
  public function fieldValidationState(string $field, string $not): void
  {
    $field = $this->fixStepArgument($field);
    $node = $this->getSession()->getPage()->findField($field);

    if (null !== $node) {
      $valid = $this->getSession()->getDriver()->evaluateScript('return document.evaluate("'.str_replace('"', '\"', $node->getXpath()).'", document, null, XPathResult.ANY_TYPE, null).iterateNext().checkValidity();');
    } else {
      // Shadow-DOM md-* field: the host is form-associated and implements checkValidity().
      $script = sprintf(
        'return (function() { %s var el = catrowebResolveField(%s); return el ? el.checkValidity() : null; })();',
        self::FIELD_RESOLVER_JS,
        json_encode($field, JSON_THROW_ON_ERROR)
      );
      $valid = $this->getSession()->getDriver()->evaluateScript($script);
      Assert::assertNotNull($valid, sprintf('Field "%s" not found.', $field));
    }
    if ('not' === trim($not)) {
      Assert::assertFalse($valid, 'Field needs to be invalid but was valid');
    } else {
      Assert::assertTrue($valid, 'Field needs to be valid but was invalid');
    }
  }

  /**
   * @Then /^I select package "([^"]*)" for media package category$/
   */
  public function iSelectPackageForMediaPackageCategory(string $arg1): void
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
  public function iSelectFlavorForMediaPackageFile(string $arg1): void
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
  public function iSelectFlavorForExampleProject(string $arg1): void
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

    Assert::assertEquals(true, $received, sprintf('File %s hasn\'t been found at location \'%s\'', $name, $file_path));
  }

  /**
   * @Then /^one of the "(?P<selector>[^"]*)" elements should contain "(?P<value>(?:[^"]|\\")*)"$/
   */
  public function assertOneOfTheElementsContain(string $selector, string $value): void
  {
    $elements = $this->getSession()->getPage()->findAll('css', $selector);

    if (array_any($elements, fn ($element): bool => $element->isVisible() && str_contains((string) $element->getText(), $value))) {
      return;
    }

    throw new ExpectationException("No element '{$selector}' contains '{$value}'", $this->getSession());
  }

  /**
   * @Then /^the element "(?P<selector>[^"]*)" should contain "(?P<value>(?:[^"]|\\")*)"$/
   */
  public function theElementSelectorShouldContain(string $selector, string $value): void
  {
    $element = $this->getSession()->getPage()->find('css', $selector);

    if (null === $element) {
      throw new ExpectationException("Element '{$selector}' was not found", $this->getSession());
    }

    if (!$element->isVisible() || !str_contains((string) $element->getText(), $value)) {
      throw new ExpectationException("Element '{$selector}' does not contain '{$value}'", $this->getSession());
    }
  }

  /**
   * @Then /^none of the "(?P<selector>[^"]*)" elements should contain "(?P<value>(?:[^"]|\\")*)"$/
   */
  public function assertNoneOfTheElementsContain(string $selector, string $value): void
  {
    $elements = $this->getSession()->getPage()->findAll('css', $selector);

    foreach ($elements as $element) {
      if (!$element->isVisible()) {
        continue;
      }
      if (str_contains($element->getText(), $value)) {
        throw new ExpectationException("An element '{$selector}' contains '{$value}' (text: '{$element->getText()}')", $this->getSession());
      }
    }
  }

  /**
   * @When I scroll to the bottom of the page
   */
  public function iScrollToTheBottomOfThePage(): void
  {
    $this->getSession()->executeScript(
      'window.scrollTo(0, Math.max(document.body.scrollHeight, document.documentElement.scrollHeight));'
    );
  }

  /**
   * @When I scroll vertical on :id using a value of :value
   */
  public function scrollVertical(string $selectorID, string $value): void
  {
    $this->getSession()->getDriver()->evaluateScript(
      sprintf('document.getElementById("%s").scrollTop = %s', $selectorID, $value)
    );
  }

  /**
   * @When I scroll horizontal on :id :className using a value of :value
   */
  public function scrollHorizontal(string $selectorID, string $className, string $value): void
  {
    $this->getSession()->getDriver()->evaluateScript(
      sprintf('document.getElementById("%s").getElementsByClassName("%s")[0].scrollLeft = %s', $selectorID, $className, $value)
    );
  }

  /**
   * @Then /^I choose "([^"]*)" from selector "([^"]*)"$/
   */
  public function iChooseItemFromSelector(string $text, string $selector): void
  {
    $node = $this->getSession()->getPage()->find('css', $selector);
    Assert::assertNotNull($node, sprintf('Selector "%s" not found.', $selector));

    // md-select renders its menu inside shadow DOM, but its md-select-option children live in
    // the light DOM: pick by option label, set the host value and fire the events a user
    // interaction would (Select.js syncs its hidden input from the change event).
    $script = sprintf(
      'return (function() {'
      .' var sel = document.querySelector(%s);'
      .' if (!sel) { return false; }'
      .' var opt = Array.from(sel.querySelectorAll("md-select-option"))'
      .'   .find(function(o) { return o.textContent.trim() === %s; });'
      .' if (!opt) { return false; }'
      .' sel.value = opt.getAttribute("value") || opt.value;'
      .' sel.dispatchEvent(new Event("input", {bubbles: true, composed: true}));'
      .' sel.dispatchEvent(new Event("change", {bubbles: true}));'
      .' return true; })();',
      json_encode($selector, JSON_THROW_ON_ERROR),
      json_encode($text, JSON_THROW_ON_ERROR)
    );
    $selected = true === $this->getSession()->getDriver()->evaluateScript($script);

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
  public function iWaitMilliseconds(string $milliseconds): void
  {
    $this->getSession()->wait((int) $milliseconds);
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
  public function iWaitForTheElementToBeVisible(string $locator): void
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
   * If an element is visible within a timeout, it needs to hide/be removed in the same timeout again.
   * Can be used for loading spinners, for example.
   *
   * @Then I wait for the element :selector to appear and if so to disappear again
   *
   * @throws ResponseTextException
   */
  public function iWaitForTheElementToAppearAndDisappear(string $locator): void
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
  public function iWaitForTheElementToContain(string $locator, string $text): void
  {
    $tries = 100;
    $delay = 100_000; // every 1/10 second
    for ($timer = 0; $timer < $tries; ++$timer) {
      $element = $this->getSession()->getPage()->find('css', $locator);
      if (null !== $element && str_contains($element->getText(), $text)) {
        return;
      }

      usleep($delay);
    }

    $message = sprintf("The text '%s' was not found in element '%s' after %s seconds timeout", $text, $locator, ($delay * $tries) / 1_000_000);
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
   *
   * @throws ElementNotFoundException
   */
  public function iClickOnXpath(string $arg1): void
  {
    $this->assertSession()->elementExists('xpath', $arg1);
    $this->getSession()->getPage()->find('xpath', $arg1)->click();
  }
}
