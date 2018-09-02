<?php

namespace Catrobat\AppBundle\Features\ApiLogin\Context;

use Behat\Behat\Tester\Exception\PendingException;
use Catrobat\AppBundle\Features\Helpers\BaseContext;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;

class WebContext extends BaseContext
{
  private $username;

  private $token;

  /**
   * @BeforeScenario
   */
  public function followRedirects()
  {
    $this->getClient()->followRedirects(true);
  }

  /**
   * @Given /^I have a valid upload token$/
   */
  public function iHaveAValidUploadToken()
  {
    $this->token = 'VALIDTOKEN';
    $this->username = 'TokenUser';
    $this->insertUser([
      'name'  => $this->username,
      'token' => $this->token,
    ]);
  }

  /**
   * @When /^I login with this token and my username$/
   */
  public function iLoginWithThisTokenAndMyUsername()
  {
    $uri = '/pocketcode/tokenlogin';
    $parameters = [
      'username' => $this->username,
      'token'    => $this->token,
    ];
    $this->getClient()->request('GET', $uri . '?' . http_build_query($parameters));
    Assert::assertEquals(200, $this->getClient()
      ->getResponse()
      ->getStatusCode(), $this->getClient()->getResponse());
  }

  /**
   * @Then /^I should be logged in$/
   */
  public function iShouldBeLoggedIn()
  {
    Assert::assertEquals(0, $this->getClient()->getCrawler()->filter('#btn-login')->count());
    Assert::assertContains('TokenUser', $this->getClient()
      ->getResponse()
      ->getContent());
  }

  /**
   * @Given /^I am logged in$/
   */
  public function iAmLoggedIn()
  {
    $this->iHaveAValidUploadToken();
    $this->iLoginWithThisTokenAndMyUsername();
  }

  /**
   * @Given /^I have an invalid upload token$/
   */
  public function iHaveAnInvalidUploadToken()
  {
    $this->token = "INVALID";
  }

  /**
   * @Then /^I should be logged out$/
   */
  public function iShouldBeLoggedOut()
  {
    Assert::assertGreaterThanOrEqual(1, $this->getClient()->getCrawler()->filter('#btn-login')->count());
    Assert::assertNotContains('TokenUser', $this->getClient()
      ->getResponse()
      ->getContent());

  }

  /**
   * @Given /^I there is a user with$/
   */
  public function iThereIsAUserWith(TableNode $table)
  {
    $values = $table->getRowsHash();
    $this->insertUser([
      'name'  => $values['username'],
      'token' => $values['token'],
    ]);
  }

  /**
   * @Given /^I have a HTTP Request:$/
   */
  public function iHaveAHttpRequest(TableNode $table)
  {
    $values = $table->getRowsHash();
    $this->method = $values['Method'];
    $this->url = $values['Url'];
  }

  /**
   * @Given /^I use the GET parameters:$/
   */
  public function iUseTheGetParameters(TableNode $table)
  {
    $values = $table->getRowsHash();
    $this->get_parameters = $values;
  }

  /**
   * @When /^I invoke the Request$/
   */
  public function iInvokeTheRequest()
  {
    $this->getClient()->request('GET', $this->url . '?' . http_build_query($this->get_parameters), [], [], []);
  }

  /**
   * @Then /^I should be on "([^"]*)"$/
   */
  public function iShouldBeOn($arg1)
  {
    $path = $this->getClient()->getRequest()->getPathInfo() . "?" . $this->getClient()->getRequest()->getQueryString();
    Assert::assertEquals($arg1, $path);
  }

}
