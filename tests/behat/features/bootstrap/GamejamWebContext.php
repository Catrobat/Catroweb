<?php

use Behat\Behat\Tester\Exception\PendingException;
use App\Entity\GameJam;
use App\Entity\Program;
use App\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use PHPUnit\Framework\Assert;


/**
 * Class WebContext
 * @package App\Catrobat\Features\GameJam\Context
 */
class GamejamWebContext extends BaseContext
{

  /**
   * @var User
   */
  private $i;

  /**
   * @var Program
   */
  private $my_program;

  /**
   * @var GameJam
   */
  private $gamejam;

  /**
   * @var
   */
  private $response;

  /**
   * WebContext constructor.
   *
   * @param $error_directory
   */
  public function __construct($error_directory)
  {
    parent::__construct();
    $this->setErrorDirectory($error_directory);
  }

  /**
   * @Given There is an ongoing game jam
   *
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function thereIsAnOngoingGameJam()
  {
    $this->gamejam = $this->getSymfonySupport()->insertDefaultGamejam();
  }

  /**
   * @Given /^I am logged in$/
   */
  public function iAmLoggedIn()
  {
    $this->i = $this->getSymfonySupport()->insertUser([
      'name'     => 'Generated',
      'password' => 'generated',
    ]);
    $this->getSymfonySupport()
      ->getClient()
      ->setServerParameter('PHP_AUTH_USER', 'Generated');
    $this->getSymfonySupport()
      ->getClient()
      ->setServerParameter('PHP_AUTH_PW', 'generated');
  }

  /**
   * @When /^I visit the details page of my program$/
   *
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function iVisitTheDetailsPageOfMyProgram()
  {
    if ($this->my_program == null)
    {
      $this->getSymfonySupport()->insertProgram($this->i, [
        'name' => 'My Program',
      ]);
    }
    $this->response = $this->getSymfonySupport()
      ->getClient()
      ->request("GET", "/app/program/1");
  }

  /**
   * @Then /^There should be a button to submit it to the jam$/
   */
  public function thereShouldBeAButtonToSubmitItToTheJam()
  {
    Assert::assertEquals(200, $this->getClient()
      ->getResponse()
      ->getStatusCode());
    Assert::assertEquals(1, $this->response->filter("#gamejam-submission")->count());
  }

  /**
   * @Then /^There should not be a button to submit it to the jam$/
   */
  public function thereShouldNotBeAButtonToSubmitItToTheJam()
  {
    Assert::assertEquals(200, $this->getClient()
      ->getResponse()
      ->getStatusCode());
    Assert::assertEquals(0, $this->response->filter("#gamejam-submission")->count());
  }

  /**
   * @Then /^There should be a div with whats the gamejam$/
   */
  public function thereShouldBeADivWithWhatsTheGamejam()
  {
    Assert::assertEquals(200, $this->getClient()
      ->getResponse()
      ->getStatusCode());
    Assert::assertEquals(1, $this->response->filter("#gamejam-whats")->count());
  }

  /**
   * @Then /^There should not be a div with whats the gamejam$/
   */
  public function thereShouldNotBeADivWithWhatsTheGamejam()
  {
    Assert::assertEquals(200, $this->getClient()
      ->getResponse()
      ->getStatusCode());
    Assert::assertEquals(0, $this->response->filter("#gamejam-whats")->count());
  }

  /**
   * @When /^I submit my program to a gamejam$/
   *
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function iSubmitMyProgramToAGamejam()
  {
    $this->iAmLoggedIn();

    if ($this->gamejam == null)
    {
      $this->gamejam = $this->getSymfonySupport()->insertDefaultGamejam([
        'formurl' => 'https://localhost/url/to/form',
      ]);
    }
    if ($this->my_program == null)
    {
      $this->my_program = $this->getSymfonySupport()->insertProgram($this->i, [
        'name' => 'My Program',
      ]);
    }

    $this->getClient()->followRedirects(false);
    $this->response = $this->getSymfonySupport()
      ->getClient()
      ->request("GET", "/app/program/1");
    $link = $this->response->filter("#gamejam-submission")
      ->parents()
      ->link();
    $this->response = $this->getClient()->click($link);
  }

  /**
   * @Then /^I should be redirected to the google form$/
   */
  public function iShouldBeRedirectedToTheGoogleForm()
  {
    Assert::assertTrue($this->getClient()->getResponse() instanceof RedirectResponse);
    Assert::assertEquals("https://localhost/url/to/form", $this->getClient()->getResponse()->headers->get('location'));
  }

  /**
   * @Given /^I submitted a program to the gamejam$/
   *
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function iSubmittedAProgramToTheGamejam()
  {
    if ($this->gamejam == null)
    {
      $this->gamejam = $this->getSymfonySupport()->insertDefaultGamejam([
        'formurl' => 'https://localhost/url/to/form',
      ]);
    }
    if ($this->my_program == null)
    {
      $this->my_program = $this->getSymfonySupport()->insertProgram($this->i, [
        'name'    => 'My Program',
        'gamejam' => $this->gamejam,
      ]);
    }
  }

  /**
   * @Given /^I submit a program to this gamejam$/
   *
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function iSubmitAProgramToThisGamejam()
  {
    if ($this->my_program == null)
    {
      $this->my_program = $this->getSymfonySupport()->insertProgram($this->i, [
        'name' => 'My Program',
      ]);
    }
    $this->response = $this->getSymfonySupport()
      ->getClient()
      ->request("GET", "/app/program/1");
    $link = $this->response->filter("#gamejam-submission")
      ->parents()
      ->link();
    $this->response = $this->getClient()->click($link);
  }

  /**
   * @Given /^I filled out the google form$/
   *
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function iFilledOutTheGoogleForm()
  {
    $this->my_program = $this->getProgramManger()->find(1);
    $this->my_program->setAcceptedForGameJam(true);
    $this->getManager()->persist($this->my_program);
    $this->getManager()->flush();
  }

  /**
   * @When /^I visit the details page of a program from another user$/
   *
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function iVisitTheDetailsPageOfAProgramFromAnotherUser()
  {
    $other = $this->getSymfonySupport()->insertUser([
      'name' => 'other',
    ]);
    $this->getSymfonySupport()->insertProgram($other, [
      'name' => 'other program',
    ]);
    $this->response = $this->getSymfonySupport()
      ->getClient()
      ->request("GET", "/app/program/1");
  }

  /**
   * @Given /^There is no ongoing game jam$/
   */
  public function thereIsNoOngoingGameJam()
  {
  }

  /**
   * @Given /^I have a limited account$/
   *
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function iHaveALimitedAccount()
  {
    $this->i->setLimited(true);
    $this->getSymfonySupport()
      ->getManager()
      ->persist($this->i);
    $this->getSymfonySupport()
      ->getManager()
      ->flush($this->i);
  }

  /**
   * @When /^I visit my profile$/
   */
  public function iVisitMyProfile()
  {
    $profile_url = $this->getSymfonySupport()
      ->getRouter()
      ->generate("profile", [
        "flavor" => "pocketcode",
      ]);
    $this->response = $this->getClient()->request("GET", $profile_url);
  }

  /**
   * @Then /^I do not see a form to edit my profile$/
   */
  public function iDoNotSeeAFormToEditMyProfile()
  {
    Assert::assertEquals(200, $this->getClient()
      ->getResponse()
      ->getStatusCode());
    Assert::assertEquals(0, $this->response->filter("#profile-form")->count());
  }

  /**
   * @Given /^I have a program named "([^"]*)"$/
   * @param $arg1
   *
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function iHaveAProgramNamed($arg1)
  {
    $this->getSymfonySupport()->insertProgram($this->i, [
      'name' => $arg1,
    ]);
  }

  /**
   * @Then /^I see the program "([^"]*)"$/
   * @param $arg1
   */
  public function iSeeTheProgram($arg1)
  {
  }

  /**
   * @Then /^I do not see a delete button$/
   */
  public function iDoNotSeeADeleteButton()
  {
    throw new PendingException();
  }

  /**
   * @Then /^I do not see a button to change the profile picture$/
   */
  public function iDoNotSeeAButtonToChangeTheProfilePicture()
  {
    Assert::assertEquals(200, $this->getClient()
      ->getResponse()
      ->getStatusCode());
    Assert::assertEquals(0, $this->response->filter("#avatar-upload")->count());
  }

  /**
   * @Given /^There is an ongoing game jam with the hashtag "([^"]*)"$/
   * @param $hashtag
   *
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function thereIsAnOngoingGameJamWithTheHashtag($hashtag)
  {
    $this->gamejam = $this->getSymfonySupport()->insertDefaultGamejam([
      'hashtag' => $hashtag,
    ]);
  }

  /**
   * @Given /^There is an ongoing game jam without flavor$/
   *
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function thereIsAnOngoingGameJamWithoutFlavor()
  {
    $this->gamejam = $this->getSymfonySupport()->insertDefaultGamejam([
      'flavor' => 'no flavor',
    ]);
  }


  /**
   * @Then /^I should see the hashtag "([^"]*)" in the program description$/
   * @param $hashtag
   */
  public function iShouldSeeTheHashtagInTheProgramDescription($hashtag)
  {
    Assert::assertContains($hashtag, $this->getClient()
      ->getResponse()
      ->getContent());
  }

  /**
   * @Given /^I am not logged in$/
   */
  public function iAmNotLoggedIn()
  {
    $this->i = $this->getSymfonySupport()->insertUser([
      'name'     => 'Generated',
      'password' => 'generated',
    ]);
  }

  /**
   * @When /^I submit the program$/
   */
  public function iSubmitTheProgram()
  {
    $this->getClient()->followRedirects(true);
    $link = $this->response->filter("#gamejam-submission")
      ->parents()
      ->link();
    $this->response = $this->getClient()->click($link);
  }

  /**
   * @When /^I login$/
   */
  public function iLogin()
  {
    $loginButton = $this->response;
    $form = $loginButton->selectButton('Login')->form();
    $form['_username'] = 'Generated';
    $form['_password'] = 'generated';
    $this->response = $this->getClient()->submit($form);
  }

  /**
   * @Then /^I should be on the details page of my program$/
   */
  public function iShouldBeRedirectedToTheDetailsPageOfMyProgram()
  {
    Assert::assertEquals("/app/program/1", $this->getClient()->getRequest()->getPathInfo());
  }

  /**
   * @Then /^I should see the message "([^"]*)"$/
   * @param $arg1
   */
  public function iShouldSeeAMessage($arg1)
  {
    Assert::assertContains($arg1, $this->getClient()->getResponse()->getContent());
  }

}
