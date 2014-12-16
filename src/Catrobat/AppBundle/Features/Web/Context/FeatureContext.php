<?php

namespace Catrobat\AppBundle\Features\Web\Context;

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
  /*
   * @var \Symfony\Component\HttpKernel\Client
   */
  private $client;

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

  /**
   * @Then /^I should see a( [^"]*)? help image "([^"]*)"$/
   */
  public function iShouldSeeAHelpImage($arg1, $arg2)
  {
    $arg1 = trim($arg1);

    $this->assertSession()->responseContains("help-desktop");
    $this->assertSession()->responseContains("help-mobile");

    if($arg1 == "big") {
      assertTrue($this->getSession()->getPage()->find("css",".help-desktop")->isVisible());
      assertFalse($this->getSession()->getPage()->find("css",".help-mobile")->isVisible());
    }
    else if($arg1 == "small") {
      assertFalse($this->getSession()->getPage()->find("css",".help-desktop")->isVisible());
      assertTrue($this->getSession()->getPage()->find("css",".help-mobile")->isVisible());
    }
    else if($arg1 == "")
      assertTrue($this->getSession()->getPage()->find("css",".help-split-desktop")->isVisible());
    else
      assertTrue(false);

    $img = null;
    $path = null;

    switch($arg2) {
      case "Hour of Code":
        if($arg1 == "big") {
          $img = $this->getSession()->getPage()->findById("hour-of-code-desktop");
          $path = "/images/help/hour_of_code.png";
        }
        else if($arg1 == "small") {
          $img = $this->getSession()->getPage()->findById("hour-of-code-mobile");
          $path = "/images/help/hour_of_code_mobile.png";
        }
        else
          assertTrue(false);
        break;
      case "Step By Step":
        if($arg1 == "big") {
          $img = $this->getSession()->getPage()->findById("step-by-step-desktop");
          $path = "/images/help/step_by_step.png";
        }
        else if($arg1 == "small") {
          $img = $this->getSession()->getPage()->findById("step-by-step-mobile");
          $path = "/images/help/step_by_step_mobile.png";
        }
        else
          assertTrue(false);
        break;
      case "Tutorials":
        $img = $this->getSession()->getPage()->findById("tutorials");
        $path = "/images/help/tutorials.png";
        break;
      case "Starters":
        $img = $this->getSession()->getPage()->findById("starters");
        $path = "/images/help/starters.png";
        break;
      case "Discussion":
        if($arg1 == "big") {
          $img = $this->getSession()->getPage()->findById("discuss-desktop");
          $path = "/images/help/discuss.png";
        }
        else if($arg1 == "small") {
          $img = $this->getSession()->getPage()->findById("discuss-mobile");
          $path = "/images/help/discuss_mobile.png";
        }
        else
          assertTrue(false);
        break;
      default:
        assertTrue(false);
        break;

    }

    if($img != null) {
      assertEquals($img->getTagName(), "img");
      assertEquals($img->getAttribute("src"), $path);
      assertTrue($img->isVisible());
    }
    else
      assertTrue(false);

  }


}
