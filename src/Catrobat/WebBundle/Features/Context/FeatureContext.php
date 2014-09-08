<?php

namespace Catrobat\WebBundle\Features\Context;

use Behat\Behat\Context\CustomSnippetAcceptingContext;
use Symfony\Component\HttpKernel\KernelInterface;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\MinkExtension\Context\MinkContext;

use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Mink\Exception\Exception;

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

  /**
   * Initializes context with parameters from behat.yml.
   *
   * @param array $parameters
   */
  public function __construct($screenshot_directory)
  {
    $this->screenshot_directory = preg_replace('/([^\/]+)$/', '$1/', $screenshot_directory);
    if (!is_dir($this->screenshot_directory))
    {
      throw new Exception("No screenshot directory specified!");
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
    foreach($files as $file) {
      if(is_file($file))
        unlink($file);
    }
  }

  /**
   * @When /^I go to the website root$/
   */
  public function iGoToTheWebsiteRoot()
  {
    $this->getSession()->visit('http://catroid.local/app_dev.php/');
  }

  /**
   * @BeforeScenario @Mobile
   */
  public function resizeWindowMobile()
  {
    $this->deleteScreens();
    $this->getSession()->resizeWindow(320, 1000);
  }

  /**
   * @BeforeScenario @Tablet
   */
  public function resizeWindowTablet()
  {
    $this->deleteScreens();
    $this->getSession()->resizeWindow(768, 1000);
  }

  /**
   * @BeforeScenario @Desktop
   */
  public function resizeWindowDesktop()
  {
    $this->deleteScreens();
    $this->getSession()->resizeWindow(1280, 1000);
  }

  /**
   * @AfterScenario
   */
  public function makeScreenshot()
  {
    $this->saveScreenshot(null, $this->screenshot_directory);
  }

  /**
   * @Then /^Newest Programs should be "([^"]*)" loaded$/
   */
  public function newestProgramsShouldBeLoaded($arg1)
  {

    assertTrue($this->getSession()->evaluateScript($script));
  }

  /**
   * @Then /^I should see three containers: "([^"]*)", "([^"]*)" and "([^"]*)"$/
   */
  public function iShouldSeeThreeContainersAnd($arg1, $arg2, $arg3)
  {
    $this->assertSession()->responseContains($arg1);
    $this->assertSession()->responseContains($arg2);
    $this->assertSession()->responseContains($arg3);
  }

  /**
   * @Then /^in each of them there should be "([^"]*)" programs loaded$/
   */
  public function inEachOfThemThereShouldBeProgramsLoaded($arg1)
  {
    $script = <<<JS
    newest.loaded == $arg1 && mostDownloaded.loaded == $arg1 && mostViewed.loaded == $arg1;
JS;

    assertTrue($this->getSession()->evaluateScript($script));
  }

  /**
   * @Then /^in each of them there should be "([^"]*)" programs visible$/
   */
  public function inEachOfThemThereShouldBeProgramsVisible($arg1)
  {
    $script = <<<JS
    newest.visible == $arg1 && mostDownloaded.visible == $arg1 && mostViewed.visible == $arg1;
JS;

    assertTrue($this->getSession()->evaluateScript($script));
  }


}
