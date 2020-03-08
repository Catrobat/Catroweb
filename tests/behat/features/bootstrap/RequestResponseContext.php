<?php

use App\Entity\UserManager;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpKernel\KernelInterface;


/**
 * Feature context.
 */
class RequestResponseContext implements KernelAwareContext
{
  /**
   * @var KernelInterface
   */
  private $kernel;

  /**
   * @var KernelBrowser
   */
  private $kernel_browser;

  /**
   * @var array
   */
  private $request_parameters;

  /**
   * @var array
   */
  private $request_files;

  /**
   * @var array
   */
  private $request_server;

  /**
   * @var PyStringNode
   */
  private $request_content;

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

  /**
   * @return KernelBrowser
   */
  public function getKernelBrowser()
  {
    if ($this->kernel_browser == null)
    {
      $this->kernel_browser = $this->kernel->getContainer()->get('test.client');
    }

    return $this->kernel_browser;
  }

  /**
   * @BeforeScenario
   */
  public function clearData()
  {
    $this->request_parameters = [];
    $this->request_files = [];
    $this->request_server = [];
    $this->request_content = null;
  }

  /**
   * @When /^I request "([^"]*)" "([^"]*)"$/
   *
   * @param $method
   * @param $uri
   */
  public function iRequest($method, $uri)
  {
    $this->getKernelBrowser()->request(
      $method, $uri, $this->request_parameters, $this->request_files, $this->request_server, $this->request_content
    );
  }

  /**
   * @Then /^the response status code should be "([^"]*)"$/
   * @param $status_code
   */
  public function theResponseStatusCodeShouldBe($status_code)
  {
    $response = $this->getKernelBrowser()->getResponse();
    Assert::assertEquals(
      $status_code, $response->getStatusCode(),
      'Response contains invalid status code "' . $response->getStatusCode() . '"'
    );
  }

  /**
   * @Given /^I have a request parameter "([^"]*)" with value "([^"]*)"$/
   *
   * @param $name
   * @param $value
   */
  public function iHaveARequestParameterWithValue($name, $value)
  {
    $this->request_parameters[$name] = $value;
  }

  /**
   * @Given /^I have a request header "([^"]*)" with value "([^"]*)"$/
   * @param $name
   * @param $value
   */
  public function iHaveARequestHeaderWithValue($name, $value)
  {
    $this->request_server[$name] = $value;
  }

  /**
   * @Given I have the following JSON request body:
   *
   * @param PyStringNode $content
   */
  public function iHaveTheFollowingJsonRequestBody(PyStringNode $content)
  {
    $this->request_content = $content;
  }

  /**
   * @Then /^I should get the json object:$/
   * @param PyStringNode $string
   */
  public function iShouldGetTheJsonObject(PyStringNode $string)
  {
    $response = $this->getKernelBrowser()->getResponse();
    $this->assertJsonRegex($string, $response->getContent());
  }

  private function assertJsonRegex($pattern, $json)
  {
    // allows to compare strings using a regex wildcard (.*?)
    $pattern = json_encode(json_decode($pattern)); // reformat string

    // escape chars that should not be used as regex
    $pattern = str_replace("\\", "\\\\", $pattern);
    $pattern = str_replace("[", "\[", $pattern);
    $pattern = str_replace("]", "\]", $pattern);
    $pattern = str_replace("?", "\?", $pattern);
    $pattern = str_replace("*", "\*", $pattern);
    $pattern = str_replace("(", "\(", $pattern);
    $pattern = str_replace(")", "\)", $pattern);
    $pattern = str_replace("+", "\+", $pattern);

    // define regex wildcards
    $pattern = str_replace('REGEX_STRING_WILDCARD', '(.+?)', $pattern);
    $pattern = str_replace('"REGEX_INT_WILDCARD"', '([0-9]+?)', $pattern);

    $delimter = "#";
    $json = json_encode(json_decode($json));
    Assert::assertRegExp($delimter . $pattern . $delimter, $json);
  }


  /**
   * @Given I use a valid JWT Bearer token for :username
   *
   * @param $username
   *
   * @throws JWTEncodeFailureException
   */
  public function iUseAValidJwtBearerTokenFor($username)
  {
    /**
     * @var JWTManager  $jwt_manager
     * @var UserManager $user_manager
     */
    $jwt_manager = $this->kernel->getContainer()->get('lexik_jwt_authentication.jwt_manager');
    $user_manager = $this->kernel->getContainer()->get(UserManager::class);
    $user = $user_manager->findUserByUsername($username);
    if (null !== $user)
    {
      $token = $jwt_manager->create($user);
    }
    else
    {
      $token = $this->kernel->getContainer()->get("lexik_jwt_authentication.encoder")
        ->encode(["username" => $username, "exp" => 3600]);
    }
    $this->request_server['HTTP_authorization'] = 'Bearer ' . $token;
  }

  /**
   * @Given I use an empty JWT Bearer token
   */
  public function iUseAnEmptyJwtBearerToken()
  {
    $this->request_server['HTTP_authorization'] = '';
  }

  /**
   * @Given I use an invalid JWT Bearer token
   */
  public function iUseAnInvalidJwtBearerToken()
  {
    $this->request_server['HTTP_authorization'] = 'Bearer invalid-token';
  }

  /**
   * @Given I use an expired JWT Bearer token for :username
   *
   * @param $username
   *
   * @throws JWTEncodeFailureException
   */
  public function iUseAnExpiredJwtBearerTokenFor($username)
  {
    $token = $this->kernel->getContainer()->get("lexik_jwt_authentication.encoder")
      ->encode(["username" => $username, "exp" => 1]);
    sleep(1);
    $this->request_server['HTTP_authorization'] = 'Bearer ' . $token;
  }

}