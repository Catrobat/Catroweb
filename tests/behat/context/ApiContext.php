<?php

namespace Tests\behat\context;

use App\Api\Exceptions\APIVersionNotSupportedException;
use App\Catrobat\Services\TestEnv\SymfonySupport;
use App\Entity\FeaturedProgram;
use App\Entity\Program;
use App\Entity\ProgramRemixBackwardRelation;
use App\Entity\ProgramRemixRelation;
use App\Entity\ScratchProgramRemixRelation;
use App\Entity\User;
use App\Utils\MyUuidGenerator;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use PHPUnit\Framework\Assert;
use RuntimeException;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\SwiftmailerBundle\DataCollector\MessageDataCollector;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Profiler\Profile;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Class ApiContext.
 *
 * Basic request/response handling.
 *
 * USAGE for sending a request:
 *
 * 1) Fill required fields. Those are cleared at the beginning of a new Scenario. Use $this->clearRequest() if needed additionally.
 *     - $this->method
 *     - $this->uri
 *     - $this->$request_headers
 *     - $this->$request_parameters
 *     - $this->request_files
 *     - $this->request_content
 *
 * 2) Call methods
 *     - $this->iRequest() to send the request with the above parameters
 *     - $this->iRequestWith(string $method, string $uri) to send a request with the above parameters and the specified
 *        $method and uri.
 *     - $this->iGetFrom($uri) to send the request with the above parameters and the specified uri.
 *     - $this->iPostTo($uri) to send the request with the above parameters and the specified uri.
 *
 * 3) Retrieve the response by using $this->getKernelBrowser()->getResponse().
 */
class ApiContext implements KernelAwareContext
{
  use SymfonySupport;

  /**
   * Name of the user which is used for the next requests.
   */
  private ?string $username = null;

  private string $method;

  private string $url;

  private DataFixturesContext $dataFixturesContext;

  /**
   * @var mixed[]|string[]|bool[]
   */
  private array $request_parameters;

  /**
   * @var mixed[]|UploadedFile[]
   */
  private array $request_files;

  /**
   * @var mixed[]|string[]|bool[]
   */
  private array $request_headers;

  private ?string $request_content;

  /**
   * @var string[]
   */
  private array $stored_json = [];

  private ?KernelBrowser $kernel_browser = null;

  // to df ->function
  private array $checked_catrobat_remix_forward_ancestor_relations;

  private array $checked_catrobat_remix_forward_descendant_relations;

  private array $checked_catrobat_remix_backward_relations;

  private Program $my_program;

  private array $program_structure = ['id', 'name', 'author', 'description',
    'version', 'views', 'download', 'private', 'flavor',
    'uploaded', 'uploaded_string', 'screenshot_large',
    'screenshot_small', 'project_url', 'download_url', 'filesize', ];

  private array $user_structure = ['id', 'username', 'email', 'country',
    'projects', 'followers', 'following', ];

  private array $featured_program_structure = ['id', 'name', 'author', 'featured_image'];

  private array $media_file_structure = ['id', 'name', 'flavor', 'package', 'category',
    'author', 'extension', 'download_url', ];

  private array $new_uploaded_projects = [];

  public function getKernelBrowser(): KernelBrowser
  {
    if (null === $this->kernel_browser)
    {
      $this->kernel_browser = $this->getSymfonyService('test.client');
    }

    return $this->kernel_browser;
  }

  // -------------------------------------------------------------------------------------------------------------------
  //  Hook
  // -------------------------------------------------------------------------------------------------------------------

  /**
   * @BeforeScenario
   */
  public function followRedirects(): void
  {
    $this->getKernelBrowser()->followRedirects(true);
  }

  /**
   * @BeforeScenario
   */
  public function generateSessionCookie(): void
  {
    $client = $this->getKernelBrowser();

    $session = $this->getKernelBrowser()
      ->getContainer()
      ->get('session')
    ;

    $cookie = new Cookie($session->getName(), $session->getId());
    $client->getCookieJar()->set($cookie);
  }

  /**
   * @BeforeScenario
   */
  public function clearRequest(): void
  {
    $this->method = 'GET';
    $this->url = '/';
    $this->request_parameters = [];
    $this->request_files = [];
    $this->request_headers = [];
    $this->request_content = null;
  }

  /** @BeforeScenario */
  public function gatherContexts(BeforeScenarioScope $scope): void
  {
    $environment = $scope->getEnvironment();
    /* @phpstan-ignore-next-line */
    $this->dataFixturesContext = $environment->getContext(DataFixturesContext::class);
  }

  /**
   * @Given /^I activate the Profiler$/
   */
  public function iActivateTheProfiler(): void
  {
    $this->getKernelBrowser()->enableProfiler();
  }

  /**
   * Sends a request. The following fields:
   *   $this->request_parameters
   *   $this->request_files
   *   $this->request_headers
   *   $this->request_content
   * are automatically added to the request.
   *
   * Get the response from $this->getKernelBrowser()->getResponse()
   *
   * @When /^I request "([^"]*)" "([^"]*)"$/
   * @When /^I :method :url with these parameters$/
   *
   * @param string $method The desired HTTP method
   * @param string $uri    The requested URI
   */
  public function iRequestWith($method, $uri): void
  {
    $this->request_parameters = (null == $this->request_parameters) ? [] : $this->request_parameters;
    $this->request_files = (null == $this->request_files) ? [] : $this->request_files;
    $this->request_headers = (null == $this->request_headers) ? [] : $this->request_headers;
    $this->request_content = (null == $this->request_content) ? '' : $this->request_content;

    if (0 == strcasecmp($method, 'GET'))
    {
      $this->getKernelBrowser()->request(
        $method, $uri, $this->request_parameters, [], $this->request_headers, $this->request_content
      );
    }
    else
    {
      $this->getKernelBrowser()->request(
        $method, $uri, $this->request_parameters, $this->request_files, $this->request_headers, $this->request_content
      );
    }
  }

  /**
   * Sends a request. The following fields:
   *   $this->method
   *   $this->uri
   *   $this->request_parameters
   *   $this->request_files
   *   $this->request_headers
   *   $this->request_content
   * are automatically added to the request.
   *
   * Get the response from $this->getKernelBrowser()->getResponse()
   *
   * @When /^such a Request is invoked$/
   * @When /^a Request is invoked$/
   * @When /^the Request is invoked$/
   * @When /^I invoke the Request$/
   */
  public function iRequest(): void
  {
    $this->iRequestWith($this->method, $this->url);
  }

  /**
   * Sends a GET request. The following fields:
   *   $this->uri
   *   $this->request_parameters
   *   $this->request_headers
   *   $this->request_content
   * are automatically added to the request.
   *
   * Get the response from $this->getKernelBrowser()->getResponse()
   *
   * @When /^I GET "([^"]*)"$/
   * @When I GET :url with these parameters
   * @When /^I GET from the api "([^"]*)"$/
   * @When /^I download "([^"]*)"$/
   * @When /^I get the recent programs with "([^"]*)"$/
   * @When /^I get the most downloaded programs with "([^"]*)"$/
   * @When /^I get the most viewed programs with "([^"]*)"$/
   * @When /^I GET the tag list from "([^"]*)" with these parameters$/
   *
   * @param mixed $url
   */
  public function iGetFrom($url): void
  {
    $this->iRequestWith('GET', $url);
  }

  /**
   * Sends a POST request. The following fields:
   *   $this->request_parameters
   *   $this->request_headers
   *   $this->files
   *   $this->request_content
   * are automatically added to the request.
   *
   * Get the response from $this->getKernelBrowser()->getResponse()
   *
   * @When /^I POST these parameters to "([^"]*)"$/
   *
   * @param mixed $url
   */
  public function iPostTo($url): void
  {
    $this->iRequestWith('POST', $url);
  }

  /**
   * @Given /^I search for "([^"]*)"$/
   *
   * @param mixed $arg1
   */
  public function iSearchFor($arg1): void
  {
    $this->iHaveAParameterWithValue('q', $arg1);
    $this->iGetFrom('/app/api/projects/search.json');
  }

  /**
   * @When /^I search similar programs for program id "([^"]*)"$/
   *
   * @param mixed $id
   */
  public function iSearchSimilarProgramsForProgramId($id): void
  {
    $this->iHaveAParameterWithValue('program_id', $id);
    if (!isset($this->request_parameters['limit']))
    {
      $this->iHaveAParameterWithValue('limit', '1');
    }
    if (!isset($this->request_parameters['offset']))
    {
      $this->iHaveAParameterWithValue('offset', '0');
    }
    $this->iGetFrom('/app/api/projects/recsys.json');
  }

  /**
   * @When /^I want to download the apk file of "([^"]*)"$/
   *
   * @param mixed $arg1
   *
   * @throws Exception
   */
  public function iWantToDownloadTheApkFileOf($arg1): void
  {
    $program_manager = $this->getProgramManager();

    $program = $program_manager->findOneByName($arg1);
    if (null === $program)
    {
      throw new Exception('Program not found: '.$arg1);
    }
    $router = $this->getRouter();
    $url = $router->generate('ci_download', ['id' => $program->getId(), 'flavor' => 'pocketcode']);
    $this->iGetFrom($url);
  }

  /**
   * @When /^I get the user\'s programs with "([^"]*)"$/
   *
   * @param mixed $url
   */
  public function iGetTheUserSProgramsWith($url): void
  {
    /** @var User|null $user */
    $user = $this->getUserManager()->findAll()[0];

    $this->iHaveAParameterWithValue('user_id', $user->getId());
    $this->iGetFrom($url);
  }

  /**
   * @Given /^I am "([^"]*)"$/
   *
   * @param mixed $username
   */
  public function iAm($username): void
  {
    $this->username = $username;
  }

  /**
   * @When I upload a valid Catrobat project, API version :api_version
   * @When I upload a valid Catrobat project with the same name, API version :api_version
   *
   * @param string $api_version The API version to be used
   *
   * @throws APIVersionNotSupportedException when the specified API version is not supported
   */
  public function iUploadAValidCatrobatProject(string $api_version): void
  {
    $this->uploadProject($this->FIXTURES_DIR.'test.catrobat', null, $api_version);
  }

  /**
   * @When user :username uploads a valid Catrobat project, API version :api_version
   *
   * @param string $username    The name of the user who initiates the upload
   * @param string $api_version The API version to be used
   *
   * @throws APIVersionNotSupportedException when the specified API version is not supported
   */
  public function userUploadsAValidCatrobatProject(string $username, string $api_version): void
  {
    /** @var User|null $user */
    $user = $this->getUserManager()->findUserByUsername($username);
    $this->uploadProject($this->FIXTURES_DIR.'test.catrobat', $user, $api_version);
  }

  /**
   * @When /^I upload another program using token "([^"]*)"$/
   *
   * @param mixed $arg1
   *
   * @throws Exception when an error occurs during uploading
   */
  public function iUploadAnotherProgramUsingToken($arg1): void
  {
    $this->iHaveAValidCatrobatFile('1');
    $this->iHaveAParameterWithTheMdChecksumOfTheUploadFile('fileChecksum', '1');
    $this->request_parameters['username'] = $this->username;
    $this->request_parameters['token'] = $arg1;
    $this->iPostTo('/app/api/upload/upload.json');
  }

  /**
   * @When /^jenkins uploads the apk file to the given upload url$/
   */
  public function jenkinsUploadsTheApkFileToTheGivenUploadUrl(): void
  {
    $filepath = $this->FIXTURES_DIR.'test.catrobat';
    Assert::assertTrue(file_exists($filepath), 'File not found');
    $temp_path = $this->getTempCopy($filepath);
    $this->request_files = [
      new UploadedFile($temp_path, 'test.apk'),
    ];
    $id = 1;
    $url = '/app/ci/upload/'.$id.'?token=UPLOADTOKEN';
    $this->iPostTo($url);
  }

  /**
   * @When /^I report program (\d+) with category "([^"]*)" and note "([^"]*)"$/
   *
   * @param mixed $program_id
   * @param mixed $category
   * @param mixed $note
   */
  public function iReportProgramWithNote($program_id, $category, $note): void
  {
    $url = '/app/api/reportProject/reportProject.json';
    $this->request_parameters = [
      'program' => $program_id,
      'category' => $category,
      'note' => $note,
    ];
    $this->iPostTo($url);
  }

  /**
   * @When /^I POST login with user "([^"]*)" and password "([^"]*)"$/
   *
   * @param mixed $uname
   * @param mixed $pwd
   */
  public function iPostLoginUserWithPassword($uname, $pwd): void
  {
    $csrfToken = $this->getSymfonyService('security.csrf.token_manager')
      ->getToken('authenticate')->getValue();

    $session = $this->getKernelBrowser()
      ->getContainer()
      ->get('session')
    ;
    $session->set('_csrf_token', $csrfToken);
    $session->set('something', $csrfToken);
    $session->save();
    $cookie = new Cookie($session->getName(), $session->getId());
    $this->getKernelBrowser()
      ->getCookieJar()
      ->set($cookie)
    ;

    $this->iHaveAParameterWithValue('_username', $uname);
    $this->iHaveAParameterWithValue('_password', $pwd);
    $this->iHaveAParameterWithValue('_csrf_token', $csrfToken);
    $this->iPostTo('/app/login_check');
  }

  /**
   * @When /^I try to register without (.*)$/
   *
   * @param mixed $missing_parameter
   */
  public function iTryToRegisterWithout($missing_parameter): void
  {
    $this->prepareValidRegistrationParameters();
    switch ($missing_parameter)
    {
      case 'a country':
        unset($this->request_parameters['registrationCountry']);
        break;
      case 'a password':
        unset($this->request_parameters['registrationPassword']);
        break;
      default:
        throw new PendingException();
    }
    $this->iPostTo('/app/api/loginOrRegister/loginOrRegister.json');
  }

  /**
   * @When /^I try to register$/
   */
  public function iTryToRegister(): void
  {
    $this->iPostTo('/app/api/loginOrRegister/loginOrRegister.json');
  }

  /**
   * @When /^I register a new user$/
   */
  public function iRegisterANewUser(): void
  {
    $this->prepareValidRegistrationParameters();
    $this->iPostTo('/app/api/loginOrRegister/loginOrRegister.json');
  }

  /**
   * @When /^I try to register another user with the same email adress$/
   */
  public function iTryToRegisterAnotherUserWithTheSameEmailAdress(): void
  {
    $this->prepareValidRegistrationParameters();
    $this->request_parameters['registrationUsername'] = 'AnotherUser';
    $this->iPostTo('/app/api/loginOrRegister/loginOrRegister.json');
  }

  /**
   * @When /^I report the program$/
   */
  public function iReportTheProgram(): void
  {
    $this->iHaveAParameterWithValue('note', 'Bad Project');
    $this->iPostTo('/app/api/reportProject/reportProject.json');
  }

  /**
   * @Then /^I should receive a "([^"]*)" file$/
   *
   * @param mixed $extension
   */
  public function iShouldReceiveAFile($extension): void
  {
    $content_type = $this->getKernelBrowser()->getResponse()->headers->get('Content-Type');
    Assert::assertEquals('image/'.$extension, $content_type);
  }

  /**
   * @Then /^I should receive a file named "([^"]*)"$/
   *
   * @param mixed $name
   */
  public function iShouldReceiveAFileNamed($name): void
  {
    $content_disposition = $this->getKernelBrowser()->getResponse()->headers->get('Content-Disposition');
    Assert::assertEquals('attachment; filename="'.$name.'"', $content_disposition);
  }

  /**
   * @Then /^I should receive the apk file$/
   */
  public function iShouldReceiveTheApkFile(): void
  {
    $content_type = $this->getKernelBrowser()->getResponse()->headers->get('Content-Type');
    $code = $this->getKernelBrowser()->getResponse()->getStatusCode();
    Assert::assertEquals(200, $code);
    Assert::assertEquals('application/vnd.android.package-archive', $content_type);
  }

  /**
   * @Then /^I should receive an application file$/
   */
  public function iShouldReceiveAnApplicationFile(): void
  {
    $content_type = $this->getKernelBrowser()->getResponse()->headers->get('Content-Type');
    $code = $this->getKernelBrowser()->getResponse()->getStatusCode();
    Assert::assertEquals(200, $code);
    Assert::assertEquals('application/zip', $content_type);
  }

  /**
   * @Then the uploaded program should be a remix root, API version :api_version
   *
   * @param string $api_version The version of the API to be used
   *
   * @throws APIVersionNotSupportedException when the specified API version is not supported
   */
  public function theUploadedProgramShouldBeARemixRoot(string $api_version): void
  {
    $this->theProgramShouldBeARemixRoot($this->getIDOfLastUploadedProject($api_version));
  }

  /**
   * @Then the uploaded project should exist in the database, API version :api_version
   *
   * @param string $api_version The version of the API to be used
   *
   * @throws APIVersionNotSupportedException when the requested $api_version is not supported
   */
  public function theUploadedProjectShouldExistInTheDatabase(string $api_version): void
  {
    // Trying to find the id of the last uploaded project in the database
    $em = $this->kernel->getContainer()->get('doctrine')->getManager();
    $uploaded_program = $em->getRepository('App\Entity\Program')->findOneBy([
      'id' => $this->getIDOfLastUploadedProject($api_version),
    ]);

    Assert::assertNotNull($uploaded_program);
  }

  /**
   * @Then /^the response status code should be "([^"]*)"$/
   *
   * @param mixed $status_code
   */
  public function theResponseStatusCodeShouldBe($status_code): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    Assert::assertEquals(
      $status_code, $response->getStatusCode(),
      'Response contains invalid status code "'.$response->getStatusCode().'"'
    );
  }

  /**
   * @Then /^the response should be in json format$/
   */
  public function theResponseShouldBeInJsonFormat(): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    Assert::assertJson($response->getContent());
  }

  /**
   * @Given /^I have a request parameter "([^"]*)" with value "([^"]*)"$/
   *
   * @param mixed $name
   * @param mixed $value
   */
  public function iHaveARequestParameterWithValue($name, $value): void
  {
    $this->request_parameters[$name] = $value;
  }

  /**
   * @Given /^I have a request header "([^"]*)" with value "([^"]*)"$/
   */
  public function iHaveARequestHeaderWithValue(string $name, string $value): void
  {
    $this->request_headers[$name] = $value;
  }

  /**
   * @Given I have the following JSON request body:
   */
  public function iHaveTheFollowingJsonRequestBody(PyStringNode $content): void
  {
    $this->request_content = $content->__toString();
  }

  /**
   * @Then /^I should get the json object:$/
   */
  public function iShouldGetTheJsonObject(PyStringNode $string): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    $this->assertJsonRegex($string, $response->getContent());
  }

  /**
   * @Then the response content must be empty
   */
  public function theResponseContentMustBeEmpty(): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    Assert::assertEmpty($response->getContent());
  }

  /**
   * @Given I use a valid JWT Bearer token for :username
   *
   * @param mixed $username
   *
   * @throws JWTEncodeFailureException
   */
  public function iUseAValidJwtBearerTokenFor($username): void
  {
    /** @var JWTManager $jwt_manager */
    $jwt_manager = $this->getJwtManager();
    $user_manager = $this->getUserManager();
    $user = $user_manager->findUserByUsername($username);
    if (null !== $user)
    {
      $token = $jwt_manager->create($user);
    }
    else
    {
      $token = $this->getJwtEncoder()->encode(['username' => $username, 'exp' => 3600]);
    }
    $this->request_headers['HTTP_authorization'] = 'Bearer '.$token;
  }

  /**
   * @Given I use an empty JWT Bearer token
   */
  public function iUseAnEmptyJwtBearerToken(): void
  {
    $this->request_headers['HTTP_authorization'] = '';
  }

  /**
   * @Given I use an invalid JWT Bearer token
   */
  public function iUseAnInvalidJwtBearerToken(): void
  {
    $this->request_headers['HTTP_authorization'] = 'Bearer invalid-token';
  }

  /**
   * @Given I use an expired JWT Bearer token for :username
   *
   * @param mixed $username
   *
   * @throws JWTEncodeFailureException
   */
  public function iUseAnExpiredJwtBearerTokenFor($username): void
  {
    $token = $this->getJwtEncoder()->encode(['username' => $username, 'exp' => 1]);
    sleep(1);
    $this->request_headers['HTTP_authorization'] = 'Bearer '.$token;
  }

  public function getSymfonyProfile(): Profile
  {
    $profile = $this->getKernelBrowser()->getProfile();
    if (!$profile)
    {
      throw new RuntimeException('The profiler is disabled. Activate it by setting '.'framework.profiler.only_exceptions to false in '.'your config');
    }

    return $profile;
  }

  /**
   * @When I upload a program with :program_attribute, API version :api_version
   *
   * @param string $api_version The API version to be used
   *
   * @throws APIVersionNotSupportedException when the specified API version is not supported
   */
  public function iUploadAProgramWith(string $program_attribute, string $api_version): void
  {
    switch ($program_attribute)
    {
      case 'a rude word in the description':
        $filename = 'program_with_rudeword_in_description.catrobat';
        break;
      case 'a rude word in the name':
        $filename = 'program_with_rudeword_in_name.catrobat';
        break;
      case 'a missing code.xml':
        $filename = 'program_with_missing_code_xml.catrobat';
        break;
      case 'an invalid code.xml':
        $filename = 'program_with_invalid_code_xml.catrobat';
        break;
      case 'a missing image':
        $filename = 'program_with_missing_image.catrobat';
        break;
      case 'an additional image':
        $filename = 'program_with_extra_image.catrobat';
        break;
      case 'an extra file':
        $filename = 'program_with_too_many_files.catrobat';
        break;
      case 'valid parameters':
        $filename = 'base.catrobat';
        break;
      case 'tags':
        $filename = 'program_with_tags.catrobat';
        break;
      default:
        throw new PendingException('No case defined for "'.$program_attribute.'"');
    }
    $this->uploadProject($this->FIXTURES_DIR.'GeneratedFixtures/'.$filename, null, $api_version);
  }

  /**
   * @When I upload an invalid program file, API version :api_version
   *
   * @param string $api_version The version of the API to be used
   *
   * @throws APIVersionNotSupportedException when the specified API version is not supported
   */
  public function iUploadAnInvalidProgramFile(string $api_version): void
  {
    $this->uploadProject($this->FIXTURES_DIR.'/invalid_archive.catrobat', null, $api_version);
  }

  /**
   * @When I upload this generated program with id :id, API version :api_version
   *
   * @param string $id          The desired id of the uploaded project
   * @param string $api_version The version of the API to be used
   *
   * @throws APIVersionNotSupportedException when the specified API version is not supported
   */
  public function iUploadThisGeneratedProgramWithId(string $id, string $api_version): void
  {
    $this->uploadProject(sys_get_temp_dir().'/program_generated.catrobat', null, $api_version, $id);
  }

  /**
   * @Given I upload the program with ":name" as name, API version :api_version
   * @Given I upload the program with ":name" as name again, API version :api_version
   *
   * @param mixed  $name
   * @param string $api_version The API version to be used
   *
   * @throws APIVersionNotSupportedException when the specified API version is not supported
   */
  public function iUploadTheProgramWithAsName($name, string $api_version): void
  {
    $this->generateProgramFileWith([
      'name' => $name,
    ]);
    $this->uploadProject(sys_get_temp_dir().'/program_generated.catrobat', null, $api_version);
  }

  /**
   * @Then the uploaded program should not be a remix root, API version :api_version
   *
   * @param string $api_version The version of the API to be used
   *
   * @throws APIVersionNotSupportedException when the specified $api_version is not supported
   */
  public function theUploadedProgramShouldNotBeARemixRoot(string $api_version): void
  {
    $this->theProgramShouldNotBeARemixRoot($this->getIDOfLastUploadedProject($api_version));
  }

  /**
   * @Then the uploaded program should have remix migration date NOT NULL, API version :api_version
   *
   * @param string $api_version The version of the API to be used
   *
   * @throws APIVersionNotSupportedException when the specified $api_version is not supported
   */
  public function theUploadedProgramShouldHaveMigrationDateNotNull(string $api_version): void
  {
    $program_manager = $this->getProgramManager();
    $uploaded_program = $program_manager->find($this->getIDOfLastUploadedProject($api_version));
    Assert::assertNotNull($uploaded_program->getRemixMigratedAt());
  }

  /**
   * @Given the uploaded program should have a Scratch parent having id :id, API version :api_version
   *
   * @param mixed  $scratch_parent_id
   * @param string $api_version       The version of the API to be used
   *
   * @throws APIVersionNotSupportedException when the specified API version is not supported
   */
  public function theUploadedProgramShouldHaveAScratchParentHavingScratchID($scratch_parent_id, string $api_version): void
  {
    $this->theProgramShouldHaveAScratchParentHavingScratchID($this->getIDOfLastUploadedProject($api_version), $scratch_parent_id);
  }

  /**
   * @Given the uploaded program should have no further Scratch parents, API version :api_version
   *
   * @param string $api_version The version of the API to be used
   *
   * @throws APIVersionNotSupportedException when the specified API version is not supported
   */
  public function theUploadedProgramShouldHaveNoFurtherScratchParents(string $api_version): void
  {
    $this->theProgramShouldHaveNoFurtherScratchParents($this->getIDOfLastUploadedProject($api_version));
  }

  /**
   * @Then the uploaded program should have a Catrobat forward ancestor having id :id and depth :depth, API version :api_version
   *
   * @param mixed  $id
   * @param mixed  $depth
   * @param string $api_version The API version to be used
   *
   * @throws APIVersionNotSupportedException when the specified $api_version is not supported
   */
  public function theUploadedProgramShouldHaveACatrobatForwardAncestorHavingIdAndDepth($id, $depth, string $api_version): void
  {
    $this->theProgramShouldHaveACatrobatForwardAncestorHavingIdAndDepth($this->getIDOfLastUploadedProject($api_version),
      $id, $depth);
  }

  /**
   * @Then the uploaded program should have a Catrobat forward ancestor having its own id and depth :depth, API version :api_version
   *
   * @param mixed  $depth
   * @param string $api_version The version of the API to be used
   *
   * @throws APIVersionNotSupportedException when the specified API version is not supported
   */
  public function theUploadedProgramShouldHaveACatrobatForwardAncestorHavingItsOwnIdAndDepth($depth, string $api_version): void
  {
    $this->theProgramShouldHaveACatrobatForwardAncestorHavingIdAndDepth($this->getIDOfLastUploadedProject($api_version), $this->getIDOfLastUploadedProject($api_version), $depth);
  }

  /**
   * @Then the uploaded program should have a Catrobat backward parent having id :id, API version :api_version
   *
   * @param mixed  $id
   * @param string $api_version The version of the API to be used
   *
   * @throws APIVersionNotSupportedException when the specified API version is not supported
   */
  public function theUploadedProgramShouldHaveACatrobatBackwardParentHavingId($id, $api_version): void
  {
    $this->theProgramShouldHaveACatrobatBackwardParentHavingId($this->getIDOfLastUploadedProject($api_version), $id);
  }

  /**
   * @Then the uploaded program should have no Catrobat forward ancestors except self-relation, API version :api_version
   *
   * @param string $api_version The version of the API to be used
   *
   * @throws APIVersionNotSupportedException when the specified API version is not supported
   */
  public function theUploadedProgramShouldHaveNoCatrobatForwardAncestorsExceptSelfRelation(string $api_version): void
  {
    $this->theProgramShouldHaveNoCatrobatForwardAncestorsExceptSelfRelation($this->getIDOfLastUploadedProject($api_version));
  }

  /**
   * @Then the uploaded program should have no Catrobat backward parents, API version :api_version
   *
   * @param string $api_version The version of the API to be used
   *
   * @throws APIVersionNotSupportedException when the specified API version is not supported
   */
  public function theUploadedProgramShouldHaveNoCatrobatBackwardParents($api_version): void
  {
    $this->theProgramShouldHaveNoCatrobatBackwardParents($this->getIDOfLastUploadedProject($api_version));
  }

  /**
   * @Then the uploaded program should have no further Catrobat backward parents, API version :api_version
   *
   * @param string $api_version The version of the API to be used
   *
   * @throws APIVersionNotSupportedException when the specified API version is not supported
   */
  public function theUploadedProgramShouldHaveNoFurtherCatrobatBackwardParents(string $api_version): void
  {
    $this->theProgramShouldHaveNoFurtherCatrobatBackwardParents($this->getIDOfLastUploadedProject($api_version));
  }

  /**
   * @Then the uploaded program should have no Catrobat ancestors except self-relation, API version :api_version
   *
   * @param string $api_version The version of the API to be used
   *
   * @throws APIVersionNotSupportedException when the specified API version is not supported
   */
  public function theUploadedProgramShouldHaveNoCatrobatAncestors(string $api_version): void
  {
    $this->theProgramShouldHaveNoCatrobatAncestors($this->getIDOfLastUploadedProject($api_version));
  }

  /**
   * @Then the uploaded program should have no Scratch parents, API version :api_version
   *
   * @param string $api_version The version of the API to be used
   *
   * @throws APIVersionNotSupportedException when the specified API version is not supported
   */
  public function theUploadedProgramShouldHaveNoScratchParents(string $api_version): void
  {
    $this->theProgramShouldHaveNoScratchParents($this->getIDOfLastUploadedProject($api_version));
  }

  /**
   * @Then the uploaded program should have a Catrobat forward descendant having id :id and depth :depth, API version :api_version
   *
   * @param mixed  $id
   * @param mixed  $depth
   * @param string $api_version The version of the API to be used
   *
   * @throws APIVersionNotSupportedException when the specified API version is not supported
   */
  public function theUploadedProgramShouldHaveCatrobatForwardDescendantHavingIdAndDepth($id, $depth, string $api_version): void
  {
    $this->theProgramShouldHaveCatrobatForwardDescendantHavingIdAndDepth($this->getIDOfLastUploadedProject($api_version), $id, $depth);
  }

  /**
   * @Then the uploaded program should have no Catrobat forward descendants except self-relation, API version :api_version
   *
   * @param string $api_version The version of the API to be used
   *
   * @throws APIVersionNotSupportedException when the specified API version is not supported
   */
  public function theUploadedProgramShouldHaveNoCatrobatForwardDescendantsExceptSelfRelation(string $api_version): void
  {
    $this->theProgramShouldHaveNoCatrobatForwardDescendantsExceptSelfRelation($this->getIDOfLastUploadedProject($api_version));
  }

  /**
   * @Then the uploaded program should have no further Catrobat forward descendants, API version :api_version
   *
   * @param string $api_version The version of the API to be used
   *
   * @throws APIVersionNotSupportedException when the specified API version is not supported
   */
  public function theUploadedProgramShouldHaveNoFurtherCatrobatForwardDescendants(string $api_version): void
  {
    $this->theProgramShouldHaveNoFurtherCatrobatForwardDescendants($this->getIDOfLastUploadedProject($api_version));
  }

  /**
   * @Then the uploaded program should have RemixOf :value in the xml, API version :api_version
   *
   * @param mixed  $value
   * @param string $api_version The version of the API to be used
   *
   * @throws ORMException
   * @throws OptimisticLockException
   * @throws APIVersionNotSupportedException when the specified API version is not supported
   */
  public function theUploadedProgramShouldHaveRemixOfInTheXml($value, string $api_version): void
  {
    $this->theProgramShouldHaveRemixofInTheXml($this->getIDOfLastUploadedProject($api_version), $value);
  }

  /**
   * @Given /^I want to upload a program$/
   */
  public function iWantToUploadAProgram(): void
  {
  }

  /**
   * @Given /^I have no parameters$/
   */
  public function iHaveNoParameters(): void
  {
  }

  /**
   * @Then /^I should get no programs$/
   */
  public function iShouldGetNoPrograms(): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    Assert::assertEquals(200, $response->getStatusCode());
    $responseArray = json_decode($response->getContent(), true);
    $returned_programs = $responseArray['CatrobatProjects'];
    Assert::assertEmpty($returned_programs, 'Projects were returned');
  }

  /**
   * @Then /^I should get following programs:$/
   */
  public function iShouldGetFollowingPrograms(TableNode $table): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    Assert::assertEquals(200, $response->getStatusCode());
    $responseArray = json_decode($response->getContent(), true);
    $returned_programs = $responseArray['CatrobatProjects'];
    $expected_programs = $table->getHash();
    Assert::assertEquals(count($expected_programs), count($returned_programs), 'Wrong number of returned programs');
    for ($i = 0; $i < count($expected_programs); ++$i)
    {
      $found = false;
      for ($j = 0; $j < count($returned_programs); ++$j)
      {
        if ($expected_programs[$i]['name'] === $returned_programs[$j]['ProjectName'])
        {
          $found = true;
        }
      }
      Assert::assertTrue($found, $expected_programs[$i]['name'].' was not found in the returned programs');
    }
  }

  /**
   * @Then the uploaded program should have no further Catrobat forward ancestors, API version :api_version
   *
   * @param string $api_version The version of the API to be used
   *
   * @throws APIVersionNotSupportedException when the specified API version is not supported
   */
  public function theUploadedProgramShouldHaveNoFurtherCatrobatForwardAncestors(string $api_version): void
  {
    $this->theProgramShouldHaveNoFurtherCatrobatForwardAncestors($this->getIDOfLastUploadedProject($api_version));
  }

  /**
   * @When /^I start an apk generation of my program$/
   */
  public function iStartAnApkGenerationOfMyProgram(): void
  {
    $id = 1;
    $this->iGetFrom('/app/ci/build/'.$id);
    $response = $this->getKernelBrowser()->getResponse();
    Assert::assertEquals(200, $response->getStatusCode(), 'Wrong response code. '.$response->getContent());
  }

  /**
   * @Then /^the apk file will not be found$/
   */
  public function theApkFileWillNotBeFound(): void
  {
    $code = $this->getKernelBrowser()
      ->getResponse()
      ->getStatusCode()
    ;
    Assert::assertEquals(404, $code);
  }

  /**
   * @When /^I report a build error$/
   */
  public function iReportABuildError(): void
  {
    $id = 1;
    $url = '/app/ci/failed/'.$id.'?token=UPLOADTOKEN';
    $this->iGetFrom($url);
    Assert::assertEquals(200, $this->getKernelBrowser()
      ->getResponse()
      ->getStatusCode());
  }

  /**
   * @When /^I get the most recent programs$/
   */
  public function iGetTheMostRecentPrograms(): void
  {
    $this->iGetFrom('/app/api/projects/recent.json');
  }

  /**
   * @When /^I get the most recent programs with limit "([^"]*)" and offset "([^"]*)"$/
   *
   * @param mixed $limit
   * @param mixed $offset
   */
  public function iGetTheMostRecentProgramsWithLimitAndOffset($limit, $offset): void
  {
    $this->request_parameters = [
      'limit' => $limit,
      'offset' => $offset,
    ];
    $this->iGetFrom('/app/api/projects/recent.json');
  }

  /**
   * @When /^I have downloaded a valid program$/
   */
  public function iHaveDownloadedAValidProgram(): void
  {
    $id = 1;
    $this->iGetFrom('/app/download/'.$id.'.catrobat');
    $this->iShouldReceiveAProjectFile();
    $this->theResponseCodeShouldBe(200);
  }

  /**
   * @Then /^will get the following JSON:$/
   */
  public function willGetTheFollowingJson(PyStringNode $string): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    Assert::assertEquals(200, $response->getStatusCode());

    $pattern = json_encode(json_decode($string));
    $pattern = str_replace('\\', '\\\\', $pattern);
    Assert::assertMatchesRegularExpression($pattern, $response->getContent());
  }

  /**
   * @Then /^I should see (\d+) outgoing emails$/
   *
   * @param mixed $email_amount
   */
  public function iShouldSeeOutgoingEmailsInTheProfiler($email_amount): void
  {
    $profile = $this->getSymfonyProfile();
    /** @var MessageDataCollector $collector */
    $collector = $profile->getCollector('swiftmailer');
    Assert::assertEquals($email_amount, $collector->getMessageCount());
  }

  /**
   * @Then /^I should see a email with recipient "([^"]*)"$/
   *
   * @param mixed $recipient
   */
  public function iShouldSeeAEmailWithRecipient($recipient): void
  {
    $profile = $this->getSymfonyProfile();
    /** @var MessageDataCollector $collector */
    $collector = $profile->getCollector('swiftmailer');
    foreach ($collector->getMessages() as $message)
    {
      /** @var Swift_Message $message */
      if ($recipient === array_keys($message->getTo())[0])
      {
        return;
      }
    }
    Assert::assertTrue(false, "Didn't find ".$recipient.' in recipients.');
  }

  /**
   * @Given /^I am a user with role "([^"]*)"$/
   *
   * @param mixed $role
   */
  public function iAmAUserWithRole($role): void
  {
    $this->insertUser([
      'role' => $role,
      'name' => 'generatedBehatUser',
    ]);

    $client = $this->getKernelBrowser();
    $client->getCookieJar()->set(new Cookie(session_name(), 'true'));

    $session = $client->getContainer()->get('session');

    $user = $this->getUserManager()->findUserByUsername('generatedBehatUser');

    $providerKey = $this->getSymfonyParameter('fos_user.firewall_name');

    $token = new UsernamePasswordToken($user, null, $providerKey, $user->getRoles());
    $session->set('_security_'.$providerKey, serialize($token));
    $session->save();

    $cookie = new Cookie($session->getName(), $session->getId());
    $client->getCookieJar()->set($cookie);
  }

  /**
   * @Then /^the client response should contain "([^"]*)"$/
   *
   * @param mixed $needle
   */
  public function theResponseShouldContain($needle): void
  {
    if (false === strpos($this->getKernelBrowser()
      ->getResponse()
      ->getContent(), $needle)
    ) {
      Assert::assertTrue(false, $needle.' not found in the response ');
    }
  }

  /**
   * @Then /^the response should contain a location header with URL of the uploaded project$/
   */
  public function theResponseShouldContainALocationHeaderWithURLOfTheUploadedProject(): void
  {
    $em = $this->kernel->getContainer()->get('doctrine')->getManager();
    $uploaded_program = $em->getRepository('App\Entity\Program')->findOneBy([
      'name' => 'test',
    ]);

    $location_header = $this->getKernelBrowser()->getResponse()->headers->get('Location');

    Assert::assertNotNull($location_header);
    Assert::assertNotNull($uploaded_program);
    Assert::assertEquals('http://localhost/app/project/'.$uploaded_program->getId(), $location_header);
  }

  /**
   * @Then /^the client response should contain the elements:$/
   */
  public function theResponseShouldContainTheElements(TableNode $table): void
  {
    $program_stats = $table->getHash();
    foreach ($program_stats as $program_stat)
    {
      $this->theResponseShouldContain($program_stat['id']);
      $this->theResponseShouldContain($program_stat['downloaded_at']);
      $this->theResponseShouldContain($program_stat['ip']);
      $this->theResponseShouldContain($program_stat['country_code']);
      $this->theResponseShouldContain($program_stat['country_name']);
      $this->theResponseShouldContain($program_stat['user_agent']);
      $this->theResponseShouldContain($program_stat['user']);
      $this->theResponseShouldContain($program_stat['referrer']);
    }
  }

  /**
   * @Then /^the client response should not contain "([^"]*)"$/
   *
   * @param mixed $needle
   */
  public function theResponseShouldNotContain($needle): void
  {
    Assert::assertStringNotContainsString($needle, $this->getKernelBrowser()->getResponse()->getContent());
  }

  /**
   * @When /^I update this program$/
   */
  public function iUpdateThisProgram(): void
  {
    $pm = $this->getProgramManager();
    $program = $pm->find('1');
    if (null === $program)
    {
      throw new Exception('last program not found');
    }
    $file = $this->generateProgramFileWith([
      'name' => $program->getName(),
    ]);
    $this->uploadProject($file, $program->getUser(), '2');
  }

  /**
   * @Given /^I am a logged in as super admin$/
   */
  public function iAmALoggedInAsSuperAdmin(): void
  {
    $this->iAmAUserWithRole('ROLE_SUPER_ADMIN');
  }

  /**
   * @Given /^I am logged in as normal user$/
   */
  public function iAmLoggedInAsNormalUser(): void
  {
    $this->iAmAUserWithRole('ROLE_USER');
  }

  /**
   * @Given /^I am a logged in as admin$/
   */
  public function iAmALoggedInAsAdmin(): void
  {
    $this->iAmAUserWithRole('ROLE_ADMIN');
  }

  /**
   * @Then /^URI from "([^"]*)" should be "([^"]*)"$/
   *
   * @param mixed $arg1
   * @param mixed $arg2
   */
  public function uriFromShouldBe($arg1, $arg2): void
  {
    $link = $this->getKernelBrowser()->getCrawler()->selectLink($arg1)->link();

    if (!strcmp($link->getUri(), $arg2))
    {
      Assert::assertTrue(false, 'expected: '.$arg2.'  get: '.$link->getURI());
    }
  }

  /**
   * @Then /^the response Header should contain the key "([^"]*)" with the value '([^']*)'$/
   *
   * @param mixed $headerKey
   * @param mixed $headerValue
   */
  public function theResponseHeadershouldContainTheKeyWithTheValue($headerKey, $headerValue): void
  {
    $headers = $this->getKernelBrowser()->getResponse()->headers;
    Assert::assertEquals($headerValue, $headers->get($headerKey),
      'expected: '.$headerKey.': '.$headerValue.
      "\nget: ".$headerKey.': '.$headers->get($headerKey));
  }

  /**
   * @Then the returned json object with id :id will be:
   *
   * @param mixed $id
   */
  public function theReturnedJsonObjectWithIdWillBe($id, PyStringNode $string): void
  {
    $response = $this->getKernelBrowser()->getResponse();

    $res_array = (array) json_decode($response->getContent());

    $res_array['projectId'] = $id;

    Assert::assertJsonStringEqualsJsonString($string->getRaw(), json_encode($res_array), '');
  }

  /**
   * @Then /^the response code will be "([^"]*)"$/
   *
   * @param mixed $code
   */
  public function theResponseCodeWillBe($code): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    Assert::assertEquals($code, $response->getStatusCode(), 'Wrong response code. '.$response->getContent());
  }

  /**
   * @When /^searching for "([^"]*)"$/
   *
   * @param mixed $arg1
   */
  public function searchingFor($arg1): void
  {
    $this->method = 'GET';
    $this->url = '/app/api/projects/search.json';
    $this->request_parameters = ['q' => $arg1, 'offset' => 0, 'limit' => 10];
    $this->iRequest();
  }

  /**
   * @Then /^the program should get (.*)$/
   *
   * @param mixed $result
   */
  public function theProgramShouldGet($result): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    $response_array = json_decode($response->getContent(), true);
    $code = $response_array['statusCode'];
    switch ($result)
    {
      case 'accepted':
        Assert::assertEquals(200, $code, 'Program was rejected (Status code 200)');
        break;
      case 'rejected':
        Assert::assertNotEquals(200, $code, 'Program was NOT rejected');
        break;
      default:
        new PendingException();
    }
  }

  /**
   * @Then /^I should get a total of (\d+) projects$/
   *
   * @param mixed $arg1
   */
  public function iShouldGetATotalOfProjects($arg1): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    $responseArray = json_decode($response->getContent(), true);
    Assert::assertEquals(
      $arg1, $responseArray['CatrobatInformation']['TotalProjects'],
      'Wrong number of total projects'
    );
  }

  /**
   * @Then /^I should get (\d+) projects$/
   *
   * @param mixed $arg1
   */
  public function iShouldGetProjects($arg1): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    $responseArray = json_decode($response->getContent(), true);
    Assert::assertEquals(
      $arg1, count($responseArray['CatrobatProjects']),
      'Wrong number of total projects'
    );
  }

  /**
   * @Then /^I should get user-specific recommended projects$/
   */
  public function iShouldGetUserSpecificRecommendedProjects(): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    $responseArray = json_decode($response->getContent(), true);
    Assert::assertTrue(
      isset($responseArray['isUserSpecificRecommendation']),
      'No isUserSpecificRecommendation parameter found in response!'
    );
    Assert::assertTrue(
      $responseArray['isUserSpecificRecommendation'],
      'isUserSpecificRecommendation parameter has wrong value. Is false, but should be true!'
    );
  }

  /**
   * @Then /^I should get no user-specific recommended projects$/
   */
  public function iShouldGetNoUserSpecificRecommendedProjects(): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    $responseArray = json_decode($response->getContent(), true);
    Assert::assertFalse(
      isset($responseArray['isUserSpecificRecommendation']),
      'Unexpected isUserSpecificRecommendation parameter found in response!'
    );
  }

  /**
   * @Given /^I use the limit "([^"]*)"$/
   *
   * @param mixed $arg1
   */
  public function iUseTheLimit($arg1): void
  {
    $this->iHaveAParameterWithValue('limit', $arg1);
  }

  /**
   * @Given /^I use the offset "([^"]*)"$/
   *
   * @param mixed $arg1
   */
  public function iUseTheOffset($arg1): void
  {
    $this->iHaveAParameterWithValue('offset', $arg1);
  }

  /**
   * @Then /^I should get programs in the following order:$/
   */
  public function iShouldGetProgramsInTheFollowingOrder(TableNode $table): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    $responseArray = json_decode($response->getContent(), true);
    $returned_programs = $responseArray['CatrobatProjects'];
    $expected_programs = $table->getHash();

    Assert::assertEquals(count($returned_programs), count($expected_programs));

    for ($i = 0; $i < count($returned_programs); ++$i)
    {
      Assert::assertEquals(
        $expected_programs[$i]['Name'], $returned_programs[$i]['ProjectName'],
        'Wrong order of results'
      );
    }
  }

  /**
   * @Then /^I should get (\d+) programs in random order:$/
   *
   * @param mixed $program_count
   */
  public function iShouldGetProgramsInRandomOrder($program_count, TableNode $table): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    $response_array = json_decode($response->getContent(), true);
    $random_programs = $response_array['CatrobatProjects'];
    $random_programs_count = count($random_programs);
    $expected_programs = $table->getHash();
    $expected_programs_count = count($expected_programs);
    Assert::assertEquals($program_count, $random_programs_count, 'Wrong number of random programs');

    for ($i = 0; $i < $random_programs_count; ++$i)
    {
      $program_found = false;
      for ($j = 0; $j < $expected_programs_count; ++$j)
      {
        if (0 === strcmp($random_programs[$i]['ProjectName'], $expected_programs[$j]['Name']))
        {
          $program_found = true;
        }
      }
      Assert::assertEquals($program_found, true, 'Program does not exist in the database');
    }
  }

  /**
   * @Then /^I should get the programs "([^"]*)" in random order$/
   *
   * @param string $program_list
   */
  public function iShouldGetTheProgramsInRandomOrder($program_list): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    $response_array = json_decode($response->getContent(), true);
    $random_programs = $response_array['CatrobatProjects'];
    $random_programs_count = count($random_programs);
    $expected_programs = explode(',', $program_list);
    $expected_programs_count = count($expected_programs);
    Assert::assertEquals($expected_programs_count, $random_programs_count, 'Wrong number of random programs');

    for ($i = 0; $i < $random_programs_count; ++$i)
    {
      $program_found = false;
      for ($j = 0; $j < $expected_programs_count; ++$j)
      {
        if (0 === strcmp($random_programs[$i]['ProjectName'], $expected_programs[$j]))
        {
          $program_found = true;
        }
      }
      Assert::assertEquals($program_found, true, 'Program does not exist in the database');
    }
  }

  /**
   * @Then /^I should get the programs "([^"]*)"$/
   *
   * @param string $program_list
   */
  public function iShouldGetThePrograms($program_list): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    $responseArray = json_decode($response->getContent(), true);
    $returned_programs = $responseArray['CatrobatProjects'];
    $expected_programs = explode(',', $program_list);

    for ($i = 0; $i < count($returned_programs); ++$i)
    {
      $found = false;
      for ($j = 0; $j < count($expected_programs); ++$j)
      {
        if ($expected_programs[$j] === $returned_programs[$i]['ProjectName'])
        {
          $found = true;
        }
      }
      Assert::assertTrue($found);
    }
  }

  /**
   * @Given /^the response code should be "([^"]*)"$/
   *
   * @param mixed $code
   */
  public function theResponseCodeShouldBe($code): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    Assert::assertEquals($code, $response->getStatusCode(), 'Wrong response code. '.$response->getContent());
  }

  /**
   * @Given /^I store the following json object as "([^"]*)":$/
   */
  public function iStoreTheFollowingJsonObjectAs(string $name, PyStringNode $json): void
  {
    $this->stored_json[$name] = $json->getRaw();
  }

  /**
   * @Then /^I should get the stored json object "([^"]*)"$/
   */
  public function iShouldGetTheStoredJsonObject(string $name): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    $this->assertJsonRegex($this->stored_json[$name], $response->getContent());
  }

  /**
   * @Then /^the response should contain the following project:$/
   */
  public function responseShouldContainTheFollowingProject(TableNode $table): void
  {
    $response = $this->getKernelBrowser()->getResponse();

    $returned_program = json_decode($response->getContent(), true);

    $expected_program = $table->getHash();
    $stored_programs = $this->getStoredPrograms($expected_program);
    $stored_program = $this->findProgram($stored_programs, $returned_program['name']);

    $this->assertProgramsEqual($stored_program, $returned_program);
  }

  /**
   * @Then /^the response should contain the following projects:$/
   * @Then /^the response should contain projects in the following order:$/
   */
  public function responseShouldContainProjectsInTheFollowingOrder(TableNode $table): void
  {
    $response = $this->getKernelBrowser()->getResponse();

    $returned_programs = json_decode($response->getContent(), true);
    $expected_programs = $table->getHash();
    $stored_programs = $this->getStoredPrograms($expected_programs);
    Assert::assertEquals(count($returned_programs), count($expected_programs), 'Number of returned programs should be '.count($expected_programs));

    foreach ($returned_programs as $returned_program)
    {
      $stored_program = $this->findProgram($stored_programs, $returned_program['name']);
      $this->assertProgramsEqual($stored_program, $returned_program);
    }
  }

  /**
   * @Then /^the response should contain featured projects in the following order:$/
   */
  public function responseShouldContainFeaturedProjectsInTheFollowingOrder(TableNode $table): void
  {
    $response = $this->getKernelBrowser()->getResponse();

    $returned_programs = json_decode($response->getContent(), true);
    $expected_programs = $table->getHash();
    $stored_programs = $this->getStoredFeaturedPrograms($expected_programs);
    Assert::assertEquals(count($returned_programs), count($expected_programs),
      'Number of returned programs should be '.count($expected_programs));

    foreach ($returned_programs as $returned_program)
    {
      $stored_program = $this->findProgram($stored_programs, $returned_program['name']);
      foreach ($this->featured_program_structure as $key)
      {
        Assert::assertNotEmpty($stored_program);
        Assert::assertEquals($returned_program[$key], $stored_program[$key]);
      }
    }
  }

  /**
   * @Then /^the response should have the projects model structure$/
   */
  public function responseShouldHaveProjectsModelStructure(): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    $responseArray = json_decode($response->getContent(), true);

    foreach ($responseArray as $program)
    {
      Assert::assertEquals(count($this->program_structure), count($program),
        'Number of program fields should be '.count($this->program_structure));
      foreach ($this->program_structure as $key)
      {
        Assert::assertArrayHasKey($key, $program, 'Program should contain '.$key);
        Assert::assertEquals($this->checkProjectFieldsValue($program, $key), true);
      }
    }
  }

  /**
   * @Then /^the response should contain the following users:$/
   * @Then /^the response should contain users in the following order:$/
   */
  public function responseShouldContainUsersInTheFollowingOrder(TableNode $table): void
  {
    $response = $this->getKernelBrowser()->getResponse();

    $returned_users = json_decode($response->getContent(), true);
    $expected_users = $table->getHash();
    $stored_users = $this->getStoredUsers($expected_users);

    Assert::assertEquals(count($returned_users), count($expected_users), 'Number of returned users should be '.count($expected_users));

    foreach ($returned_users as $returned_user)
    {
      $stored_user = $this->findUser($stored_users, $returned_user['username']);
      $this->assertUsersEqual($stored_user, $returned_user);
    }
  }

  /**
   * @Then /^the response should contain the following user:$/
   */
  public function responseShouldContainTheFollowingUser(TableNode $table): void
  {
    $response = $this->getKernelBrowser()->getResponse();

    $returned_user = json_decode($response->getContent(), true);
    $expected_user = $table->getHash();
    $stored_users = $this->getStoredUsers($expected_user);

    $stored_user = $this->findUser($stored_users, $returned_user['username']);
    $this->assertUsersEqual($stored_user, $returned_user);
  }

  /**
   * @Then /^the response should have the users model structure$/
   */
  public function responseShouldHaveUsersModelStructure(): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    $returned_users = json_decode($response->getContent(), true);

    foreach ($returned_users as $user)
    {
      Assert::assertEquals(count($this->user_structure), count($user),
        'Number of user fields should be '.count($this->user_structure));
      foreach ($this->user_structure as $key)
      {
        Assert::assertArrayHasKey($key, $user, 'User should contain '.$key);
        Assert::assertEquals($this->checkUserFieldsValue($user, $key), true);
      }
    }
  }

  /**
   * @Then /^the response should have the user model structure$/
   */
  public function responseShouldHaveUserModelStructure(): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    $user = json_decode($response->getContent(), true);

    Assert::assertEquals(count($this->user_structure), count($user),
      'Number of user fields should be '.count($this->user_structure));
    foreach ($this->user_structure as $key)
    {
      Assert::assertArrayHasKey($key, $user, 'User should contain '.$key);
      Assert::assertEquals($this->checkUserFieldsValue($user, $key), true);
    }
  }

  /**
   * @Then /^the response should have the project model structure$/
   */
  public function responseShouldHaveProjectModelStructure(): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    $program = json_decode($response->getContent(), true);

    Assert::assertEquals(count($program), count($this->program_structure),
      'Number of program fields should be '.count($this->program_structure));
    foreach ($this->program_structure as $key)
    {
      Assert::assertArrayHasKey($key, $program, 'Program should contain '.$key);
      Assert::assertEquals($this->checkProjectFieldsValue($program, $key), true);
    }
  }

  /**
   * @Then /^the response should have the featured projects model structure$/
   */
  public function responseShouldHaveFeaturedProjectsModelStructure(): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    $returned_programs = json_decode($response->getContent(), true);

    foreach ($returned_programs as $program)
    {
      Assert::assertEquals(count($program), count($this->featured_program_structure),
        'Number of program fields should be '.count($this->featured_program_structure));
      foreach ($this->featured_program_structure as $key)
      {
        Assert::assertArrayHasKey($key, $program, 'Program should contain '.$key);
        Assert::assertEquals($this->checkFeaturedProjectFieldsValue($program, $key), true);
      }
    }
  }

  /**
   * @Then /^the response should have the media files model structure$/
   */
  public function responseShouldHaveMediaFilesModelStructure(): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    $returned_media_files = json_decode($response->getContent(), true);

    foreach ($returned_media_files as $program)
    {
      Assert::assertEquals(count($program), count($this->media_file_structure),
        'Number of program fields should be '.count($this->media_file_structure));
      foreach ($this->media_file_structure as $key)
      {
        Assert::assertArrayHasKey($key, $program, 'Program should contain '.$key);
        Assert::assertEquals($this->checkMediaFileFieldsValue($program, $key), true);
      }
    }
  }

  /**
   * @Then /^the response should contain media files in the following order:$/
   */
  public function responseShouldContainMediaFilesInTheFollowingOrder(TableNode $table): void
  {
    $response = $this->getKernelBrowser()->getResponse();

    $returned_files = json_decode($response->getContent(), true);
    $expected_files = $table->getHash();
    $stored_files = $this->getStoredMediaFiles($expected_files);

    Assert::assertEquals(count($returned_files), count($expected_files),
      'Number of returned programs should be '.count($expected_files));
    foreach ($returned_files as $returned_file)
    {
      $stored_file = $this->findProgram($stored_files, $returned_file['name']);
      foreach ($this->media_file_structure as $key)
      {
        Assert::assertNotEmpty($stored_file);
        Assert::assertEquals($returned_file[$key], $stored_file[$key]);
      }
    }
  }

  /**
   * @Then /^the response should contain (\d+) projects$/
   */
  public function responseShouldContainNumberProjects(int $projects): void
  {
    $response = $this->getKernelBrowser()->getResponse();

    $returned_programs = json_decode($response->getContent(), true);

    Assert::assertEquals(count($returned_programs), $projects,
      'Number of returned programs should be '.count($returned_programs));
  }

  /**
   * @Then /^I should get (\d+) programs in the following order:$/
   *
   * @param mixed $program_count
   */
  public function iShouldGetScratchProgramsInTheFollowingOrder($program_count, TableNode $table): void
  {
    $response = $this->getKernelBrowser()->getResponse();

    $responseArray = json_decode($response->getContent(), true);
    $programs = $table->getHash();

    $returned_programs = $responseArray['CatrobatProjects'];
    $scratch_programs_count = count($programs);
    Assert::assertEquals($program_count, $scratch_programs_count, 'Wrong number of Scratch programs');

    $expected_programs = $table->getHash();
    Assert::assertEquals(count($returned_programs), count($expected_programs),
      'Number of returned programs should be '.count($returned_programs));

    for ($i = 0; $i < count($returned_programs); ++$i)
    {
      Assert::assertEquals(
        $expected_programs[$i]['Name'], $returned_programs[$i]['ProjectName'],
        'Wrong order of results'
      );
    }
  }

  /**
   * @Given /^I have a parameter "([^"]*)" with the md5checksum of "([^"]*)"$/
   */
  public function iHaveAParameterWithTheMdChecksumOf(string $parameter): void
  {
    $this->request_parameters[$parameter] = md5_file($this->request_files[0]->getPathname());
  }

  /**
   * @Then /^I should get (\d+) projects in the following order:$/
   */
  public function iShouldGetScratchProjectsInTheFollowingOrder(int $program_count, TableNode $table): void
  {
    $response = $this->getKernelBrowser()->getResponse();

    $returned_programs = json_decode($response->getContent(), true);
    $programs = $table->getHash();

    $scratch_programs_count = count($programs);
    Assert::assertEquals($program_count, $scratch_programs_count, 'Wrong number of Scratch programs');

    $expected_programs = $table->getHash();
    Assert::assertEquals(count($returned_programs), count($expected_programs),
      'Number of returned programs should be '.count($returned_programs));

    for ($i = 0; $i < count($returned_programs); ++$i)
    {
      Assert::assertEquals(
        $expected_programs[$i]['Name'], $returned_programs[$i]['name'],
        'Wrong order of results'
      );
    }
  }

  /**
   * @Given /^I have a parameter "([^"]*)" with an invalid md5checksum of my file$/
   *
   * @param mixed $parameter
   */
  public function iHaveAParameterWithAnInvalidMdchecksumOfMyFile($parameter): void
  {
    $this->request_parameters[$parameter] = 'INVALIDCHECKSUM';
  }

  /**
   * @Given /^I have a parameter ":parameter" with the md5checksum of the file to be uploaded, API version :api_version$/
   *
   * @param string $parameter   The HTTP request parameter holding the checksum
   * @param string $api_version The version of the API which should be used
   *
   * @throws APIVersionNotSupportedException When the specified $api_version is not supported
   */
  public function iHaveAParameterWithTheMdChecksumOfTheUploadFile(string $parameter, string $api_version): void
  {
    if ('1' == $api_version)
    {
      $this->request_parameters[$parameter] = md5_file($this->request_files[0]->getPathname());
    }
    elseif ('2' == $api_version)
    {
      $this->request_parameters[$parameter] = md5_file($this->request_files['file']->getPathname());
    }
    else
    {
      throw new APIVersionNotSupportedException($api_version);
    }
  }

  /**
   * @Given /^I have the POST parameters:$/
   */
  public function iHaveThePostParameters(TableNode $table): void
  {
    foreach ($table->getHash() as $parameter)
    {
      $this->request_parameters[$parameter['name']] = $parameter['value'];
    }
  }

  /**
   * @Then /^i should receive a project file$/
   */
  public function iShouldReceiveAProjectFile(): void
  {
    $content_type = $this->getKernelBrowser()->getResponse()->headers->get('Content-Type');
    Assert::assertEquals('application/zip', $content_type);
  }

  /**
   * @When /^I have a parameter "([^"]*)" with the returned projectId$/
   *
   * @param mixed $name
   */
  public function iHaveAParameterWithTheReturnedProjectid($name): void
  {
    $response = json_decode($this->getKernelBrowser()
      ->getResponse()
      ->getContent(), true);
    $this->request_parameters[$name] = $response['projectId'];
  }

  /**
   * @Then it should be updated, API version :api_version
   *
   * @param string $api_version The version of the API to be used
   *
   * @throws APIVersionNotSupportedException When the specified $api_version is not supported
   */
  public function itShouldBeUpdated(string $api_version): void
  {
    if ('1' == $api_version)
    {
      $last_json = json_decode($this->getKernelBrowser()->getResponse()->getContent(), true);
      $json = json_decode($this->getKernelBrowser()->getResponse()->getContent(), true);
      Assert::assertEquals($last_json['projectId'], $json['projectId'],
        $this->getKernelBrowser()->getResponse()->getContent()
      );
    }
    elseif ('2' == $api_version)
    {
      Assert::assertEquals($this->getKernelBrowser()->getResponse()->headers->get('Location'),
        $this->getKernelBrowser()->getResponse()->headers->get('Location'));
    }
    else
    {
      throw new APIVersionNotSupportedException($api_version);
    }
  }

  /**
   * @Given /^the upload problem "([^"]*)"$/
   *
   * @param mixed $problem
   */
  public function theUploadProblem($problem): void
  {
    switch ($problem)
    {
      case 'no authentication':
        $this->method = 'POST';
        $this->url = '/app/api/upload/upload.json';
        break;
      case 'missing parameters':
        $this->method = 'POST';
        $this->url = '/app/api/upload/upload.json';
        $this->request_parameters['username'] = 'Catrobat';
        $this->request_parameters['token'] = 'cccccccccc';
        break;
      case 'invalid program file':
        $this->method = 'POST';
        $this->url = '/app/api/upload/upload.json';
        $this->request_parameters['username'] = 'Catrobat';
        $this->request_parameters['token'] = 'cccccccccc';
        $filepath = $this->FIXTURES_DIR.'invalid_archive.catrobat';
        Assert::assertTrue(file_exists($filepath), 'File not found');
        $this->request_files[] = new UploadedFile($filepath, 'test.catrobat');
        $this->request_parameters['fileChecksum'] = md5_file($this->request_files[0]->getPathname());
        break;
      default:
        throw new PendingException('No implementation of case "'.$problem.'"');
    }
  }

  /**
   * @Given I try to upload a project with unnecessary files, API version :api_version
   *
   * @param string $api_version The version of the API to be used
   *
   * @throws APIVersionNotSupportedException when the specified API is not supported
   */
  public function iTryToUploadAProjectWithUnnecessaryFiles(string $api_version): void
  {
    $this->uploadProject($this->FIXTURES_DIR.'unnecessaryFiles.catrobat', null, $api_version);
  }

  /**
   * @Given I try to upload a project with scenes and unnecessary files, API version :api_version
   *
   * @param string $api_version The API version to be used
   *
   * @throws APIVersionNotSupportedException when the specified API is not supported
   */
  public function iTryToUploadAProjectWithScenesAndUnnecessaryFiles(string $api_version): void
  {
    $this->uploadProject($this->FIXTURES_DIR.'unnecessaryFilesInScenes.catrobat', null, $api_version);
  }

  /**
   * @When I upload this program with id :id, API version :api_version
   *
   * @param string $id          The desired ID of the newly uploaded project
   * @param string $api_version The version of the API to be used
   *
   * @throws APIVersionNotSupportedException when the specified API is not supported
   */
  public function iUploadThisProgramWithId(string $id, string $api_version): void
  {
    if ('1' == $api_version)
    {
      if (array_key_exists('deviceLanguage', $this->request_parameters))
      {
        $this->uploadProject(sys_get_temp_dir().'/program_generated.catrobat', null,
          $api_version, $id, 'pocketcode');
      }
      else
      {
        $this->uploadProject(sys_get_temp_dir().'/program_generated.catrobat', null, $api_version, $id);
      }

      $resp_array = (array) json_decode($this->getKernelBrowser()->getResponse()->getContent());
      $resp_array['projectId'] = $id;
      $this->getKernelBrowser()->getResponse()->setContent(json_encode($resp_array));
    }
    elseif ('2' == $api_version)
    {
      $this->uploadProject(sys_get_temp_dir().'/program_generated.catrobat', null, $api_version, $id);
    }
    else
    {
      throw new APIVersionNotSupportedException($api_version);
    }
  }

  /**
   * @When I upload this generated program, API version :api_version
   * @When I upload a generated program, API version :api_version
   *
   * @param string $api_version The version of the API to be used
   *
   * @throws APIVersionNotSupportedException when the specified API version is not supported
   */
  public function iUploadThisGeneratedProject(string $api_version): void
  {
    $this->uploadProject(sys_get_temp_dir().'/program_generated.catrobat', null, $api_version);
  }

  /**
   * @When user :username uploads this generated program, API version :api_version
   *
   * @param string $username    The name of the user uploading the project
   * @param string $api_version The version of the API to be used
   *
   * @throws APIVersionNotSupportedException when the specified API version is not supported
   */
  public function userUploadThisGeneratedProject(string $username, string $api_version): void
  {
    $this::userUploadThisGeneratedProjectWithID($username, $api_version, '');
  }

  /**
   * @When user :username uploads this generated program, API version :api_version, ID :id
   *
   * @param string $username    The name of the user uploading the project
   * @param string $api_version The version of the API to be used
   * @param string $id          Desired id of the project
   *
   * @throws APIVersionNotSupportedException when the specified API version is not supported
   */
  public function userUploadThisGeneratedProjectWithID(string $username, string $api_version, string $id): void
  {
    /** @var User|null $user */
    $user = $this->getUserManager()->findUserByUsername($username);
    Assert::assertNotNull($user);
    $this->uploadProject(sys_get_temp_dir().'/program_generated.catrobat', $user, $api_version, $id);
  }

  /**
   * @When I upload the program with the id ":id", API version :api_version
   *
   * @param mixed  $id
   * @param string $api_version The version of the API to be used
   *
   * @throws APIVersionNotSupportedException when the specified API version is not supported
   */
  public function iUploadAProgramWithId($id, string $api_version): void
  {
    $this->uploadProject(sys_get_temp_dir().'/program_generated.catrobat', null, $api_version, $id);
  }

  /**
   * @When I upload the generated program with the id :id and name :name, API version :api_version
   *
   * @param mixed  $id
   * @param mixed  $name
   * @param string $api_version The version of the API to be used
   *
   * @throws APIVersionNotSupportedException when the specified API version is not supported
   */
  public function iUploadTheGeneratedProgramWithIdAndName($id, $name, string $api_version): void
  {
    $this->uploadProject(sys_get_temp_dir().'/program_generated.catrobat', null, $api_version, $id);

    /** @var Program $project */
    $project = $this->getProgramManager()->find($id);
    $project->setName($name);
    $this->getProgramManager()->save($project);

    $this->new_uploaded_projects[] = $project;
  }

  /**
   * @When I upload this generated program again without extensions, API version :api_version
   *
   * @param string $api_version The version of the API to be used
   *
   * @throws APIVersionNotSupportedException when the specified API is not supported
   */
  public function iUploadTheGeneratedProgramAgainWithoutExtensions(string $api_version): void
  {
    $this->iHaveAProjectWithAs('name', 'extensions');
    $this->iUploadThisGeneratedProject($api_version);
  }

  /**
   * @When I upload another program with name set to ":name" and url set to ":url", API version :api_version
   *
   * @param mixed  $name
   * @param mixed  $url
   * @param string $api_version The version of the API to be used
   *
   * @throws APIVersionNotSupportedException when the specified API version is not supported
   */
  public function iUploadAnotherProgramWithNameSetToAndUrlSetTo($name, $url, $api_version): void
  {
    $this->iHaveAProjectWithAsTwoHeaderFields('name', $name, 'url', $url);
    $this->iUploadThisGeneratedProject($api_version);
  }

  /**
   * @When I upload another program with name set to :arg1, url set to :arg2 \
   *       and catrobatLanguageVersion set to :arg3, API version :api_version
   *
   * @param mixed  $name
   * @param mixed  $url
   * @param mixed  $catrobat_language_version
   * @param string $api_version               The version of the API to be used
   *
   * @throws APIVersionNotSupportedException when the specified API version is not supported
   */
  public function iUploadAnotherProgramWithNameSetToUrlSetToAndCatrobatLanguageVersionSetTo($name, $url, $catrobat_language_version, string $api_version): void
  {
    $this->iHaveAProjectWithAsMultipleHeaderFields('name', $name, 'url', $url,
      'catrobatLanguageVersion', $catrobat_language_version);
    $this->iUploadThisGeneratedProject($api_version);
  }

  /**
   * @When I upload this generated program again with the tags :arg1, API version :arg2
   *
   * @param mixed  $tags        The tags of the project
   * @param string $api_version The version of the API to be used
   *
   * @throws APIVersionNotSupportedException when the specified API version is not supported
   */
  public function iUploadThisProgramAgainWithTheTags($tags, $api_version): void
  {
    $this->generateProgramFileWith([
      'tags' => $tags,
    ]);
    $file = sys_get_temp_dir().'/program_generated.catrobat';
    $this->uploadProject($file, null, $api_version, '', 'pocketcode');
  }

  /**
   * @Given /^I have a parameter "([^"]*)" with value "([^"]*)"$/
   */
  public function iHaveAParameterWithValue(string $name, string $value): void
  {
    $this->request_parameters[$name] = $value;
  }

  /**
   * @Given /^a catrobat file is attached to the request$/
   */
  public function iAttachACatrobatFile(): void
  {
    $filepath = $this->FIXTURES_DIR.'test.catrobat';
    Assert::assertTrue(file_exists($filepath), 'File not found');
    $this->request_files[0] = new UploadedFile($filepath, 'test.catrobat');
  }

  /**
   * @Given /^the POST parameter "([^"]*)" contains the MD5 sum of the attached file$/
   *
   * @param mixed $arg1
   */
  public function thePostParameterContainsTheMdSumOfTheGivenFile($arg1): void
  {
    $this->request_parameters[$arg1] = md5_file($this->request_files[0]->getPathname());
  }

  /**
   * @Given /^the check token problem "([^"]*)"$/
   * @When /^there is a check token problem ([^"]*)$/
   *
   * @param mixed $problem
   */
  public function thereIsACheckTokenProblem($problem): void
  {
    switch ($problem)
    {
      case 'invalid token':
        $this->method = 'POST';
        $this->url = '/app/api/checkToken/check.json';
        $this->request_parameters['username'] = 'Catrobat';
        $this->request_parameters['token'] = 'INVALID';
        break;
      default:
        throw new PendingException('No implementation of case "'.$problem.'"');
    }
  }

  /**
   * @Given /^I have a parameter "([^"]*)" with the tag id "([^"]*)"$/
   *
   * @param mixed $name
   * @param mixed $value
   */
  public function iHaveAParameterWithTheTagId($name, $value): void
  {
    $this->request_parameters[$name] = $value;
  }

  /**
   * @Given I use the :arg1 app, API version :arg2
   *
   * @param string $language    The desired language
   * @param string $api_version The version of the API to be used
   *
   * @throws APIVersionNotSupportedException when the specified API is not supported
   */
  public function iUseTheApp(string $language, string $api_version): void
  {
    switch ($language)
    {
      case 'english':
        $deviceLanguage = 'en';
        break;
      case 'german':
        $deviceLanguage = 'de';
        break;
      default:
        $deviceLanguage = 'NotExisting';
    }

    if ('1' == $api_version)
    {
      $this->iHaveAParameterWithValue('deviceLanguage', $deviceLanguage);
    }
    elseif ('2' == $api_version)
    {
      $this->iHaveARequestHeaderWithValue('HTTP_ACCEPT_LANGUAGE', $deviceLanguage);
    }
    else
    {
      throw new APIVersionNotSupportedException($api_version);
    }
  }

  /**
   * @Given /^the HTTP Request:$/
   * @Given /^I have the HTTP Request:$/
   */
  public function iHaveTheHttpRequest(TableNode $table): void
  {
    $values = $table->getRowsHash();
    $this->method = $values['Method'];
    $this->url = $values['Url'];
  }

  /**
   * @Given /^the POST parameters:$/
   * @Given /^I use the POST parameters:$/
   */
  public function iUseThePostParameters(TableNode $table): void
  {
    $values = $table->getRowsHash();
    $this->request_parameters = $values;
  }

  /**
   * @Given /^the GET parameters:$/
   * @Given /^I use the GET parameters:$/
   */
  public function iUseTheGetParameters(TableNode $table): void
  {
    $values = $table->getRowsHash();
    $this->request_parameters = $values;
  }

  /**
   * @Given /^the server name is "([^"]*)"$/
   *
   * @param mixed $name
   */
  public function theServerNameIs($name): void
  {
    $this->request_headers['HTTP_HOST'] = $name;
  }

  /**
   * @Given /^I use a secure connection$/
   */
  public function iUseASecureConnection(): void
  {
    $this->request_headers['HTTPS'] = true;
  }

  /**
   * @Given /^I have a program with "([^"]*)" set to "([^"]*)" and "([^"]*)" set to "([^"]*)"$/
   *
   * @param mixed $key1
   * @param mixed $value1
   * @param mixed $key2
   * @param mixed $value2
   */
  public function iHaveAProjectWithAsTwoHeaderFields($key1, $value1, $key2, $value2): void
  {
    $this->generateProgramFileWith([
      $key1 => $value1,
      $key2 => $value2,
    ]);
  }

  /**
   * @Given I have a program with :key1 set to :value1, :key2 set to :value2 and :key3 set to :value3
   *
   * @param mixed $key1
   * @param mixed $value1
   * @param mixed $key2
   * @param mixed $value2
   * @param mixed $key3
   * @param mixed $value3
   */
  public function iHaveAProjectWithAsMultipleHeaderFields($key1, $value1, $key2, $value2, $key3, $value3): void
  {
    $this->generateProgramFileWith([
      $key1 => $value1,
      $key2 => $value2,
      $key3 => $value3,
    ]);
  }

  /**
   * @Given /^I have a file "([^"]*)"$/
   *
   * @param mixed $filename
   */
  public function iHaveAFile($filename): void
  {
    $filepath = './src/Catrobat/ApiBundle/Features/Fixtures/'.$filename;
    Assert::assertTrue(file_exists($filepath), 'File not found');
    $this->request_files[] = new UploadedFile($filepath, $filename);
  }

  /**
   * @Given I have a valid Catrobat file, API version :api_version
   *
   * @param string $api_version the version of the API which should be used
   *
   * @throws APIVersionNotSupportedException When a not supported version of the API is passed as parameter
   *                                         $api_version
   */
  public function iHaveAValidCatrobatFile(string $api_version): void
  {
    $filepath = $this->FIXTURES_DIR.'test.catrobat';
    Assert::assertTrue(file_exists($filepath), 'File not found');
    $this->request_files = [];

    if ('1' == $api_version)
    {
      $this->request_files[0] = new UploadedFile($filepath, 'test.catrobat');
    }
    elseif ('2' == $api_version)
    {
      $this->request_files['file'] = new UploadedFile($filepath, 'test.catrobat');
    }
    else
    {
      throw new APIVersionNotSupportedException($api_version);
    }
  }

  /**
   * @Given I have a broken Catrobat file, API version :api_version
   *
   * @param string $api_version the version of the API which should be used
   *
   * @throws APIVersionNotSupportedException When a not supported version of the API is passed as parameter
   *                                         $api_version
   */
  public function iHaveABrokenCatrobatFile(string $api_version): void
  {
    $filepath = $this->FIXTURES_DIR.'broken.catrobat';
    Assert::assertTrue(file_exists($filepath), 'File not found');
    $this->request_files = [];

    if ('2' == $api_version)
    {
      $this->request_files['file'] = new UploadedFile($filepath, 'broken.catrobat');
    }
    else
    {
      throw new APIVersionNotSupportedException($api_version);
    }
  }

  /**
   * @Given /^I have otherwise valid registration parameters$/
   */
  public function iHaveOtherwiseValidRegistrationParameters(): void
  {
    $this->prepareValidRegistrationParameters();
  }

  /**
   * @Given /^I have a project with "([^"]*)" set to "([^"]*)"$/
   * @Given /^I have a program with "([^"]*)" set to "([^"]*)"$/
   * @Given /^there is a project with "([^"]*)" set to "([^"]*)"$/
   *
   * @param mixed $key
   * @param mixed $value
   */
  public function iHaveAProjectWithAs($key, $value): void
  {
    $this->generateProgramFileWith([
      $key => $value,
    ]);
  }

  /**
   * @Then I should receive the following programs:
   */
  public function iShouldReceiveTheFollowingPrograms(TableNode $table): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    $responseArray = json_decode($response->getContent(), true);
    $returned_programs = $responseArray['CatrobatProjects'];
    $expected_programs = $table->getHash();
    for ($i = 0; $i < count($returned_programs); ++$i)
    {
      Assert::assertEquals($expected_programs[$i]['Name'], $returned_programs[$i]['ProjectName'], 'Wrong order of results');
    }
    Assert::assertEquals(count($expected_programs), count($returned_programs), 'Wrong number of returned programs');
  }

  /**
   * @Then The total number of found projects should be :arg1
   *
   * @param mixed $arg1
   */
  public function theTotalNumberOfFoundProjectsShouldBe($arg1): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    $responseArray = json_decode($response->getContent(), true);
    Assert::assertEquals($arg1, $responseArray['CatrobatInformation']['TotalProjects']);
  }

  /**
   * @Then I should receive my program
   */
  public function iShouldReceiveMyProgram(): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    $responseArray = json_decode($response->getContent(), true);
    $returned_programs = $responseArray['CatrobatProjects'];
    Assert::assertEquals('test', $returned_programs[0]['ProjectName'], 'Could not find the program');
  }

  /**
   * @Then I should get the url to the google form
   */
  public function iShouldGetTheUrlToTheGoogleForm(): void
  {
    $answer = json_decode($this->getKernelBrowser()
      ->getResponse()
      ->getContent(), true);
    Assert::assertArrayHasKey('form', $answer);
    Assert::assertEquals('https://catrob.at/url/to/form', $answer['form']);
  }

  /**
   * @When /^I login$/
   */
  public function iLogin(): void
  {
    /** @var Crawler $loginButton */
    $loginButton = $this->getKernelBrowser()->getResponse();
    $form = $loginButton->selectButton('Login')->form();
    $form['_username'] = 'Generated';
    $form['_password'] = 'generated';
    $this->getKernelBrowser()->submit($form);
  }

  /**
   * @Then /^I should be on the details page of my program$/
   */
  public function iShouldBeRedirectedToTheDetailsPageOfMyProgram(): void
  {
    Assert::assertEquals('/app/project/1', $this->getKernelBrowser()->getRequest()->getPathInfo());
  }

  /**
   * @When /^I visit the details page of a program from another user$/
   *
   * @throws Exception
   */
  public function iVisitTheDetailsPageOfAProgramFromAnotherUser(): void
  {
    $other = $this->insertUser([
      'name' => 'other',
    ]);
    $this->insertProject([
      'name' => 'other program',
      'owned by' => $other,
    ]);
    $this->iRequestWith('GET', '/app/project/1')
    ;
  }

  /**
   * @When /^I visit the details page of my program$/
   *
   * @throws Exception
   */
  public function iVisitTheDetailsPageOfMyProgram(): void
  {
    if (null == $this->my_program)
    {
      $this->insertProject([
        'name' => 'My Program',
        'owned by' => $this->getUserDataFixtures()->getCurrentUser(),
      ]);
    }
    $this->iRequestWith('GET', '/app/project/1')
    ;
  }

  /**
   * @Then /^I should be redirected to the google form$/
   */
  public function iShouldBeRedirectedToTheGoogleForm(): void
  {
    Assert::assertTrue($this->getKernelBrowser()->getResponse() instanceof RedirectResponse);
    Assert::assertEquals('https://localhost/url/to/form', $this->getKernelBrowser()->getResponse()->headers->get('location'));
  }

  /**
   * @Then The returned url with id :id should be
   *
   * @param mixed $id
   */
  public function theReturnedUrlShouldBe($id, PyStringNode $string): void
  {
    $answer = (array) json_decode($this->getKernelBrowser()->getResponse()->getContent());

    $form_url = $answer['form'];
    $form_url = preg_replace('/&id=.*?&mail=/', '&id='.$id.'&mail=', $form_url, -1);

    Assert::assertEquals($string->getRaw(), $form_url);
  }

  /**
   * @Then The submission should be rejected
   */
  public function theSubmissionShouldBeRejected(): void
  {
    $answer = json_decode($this->getKernelBrowser()
      ->getResponse()
      ->getContent(), true);
    Assert::assertNotEquals('200', $answer['statusCode']);
  }

  /**
   * @Then The message should be:
   */
  public function theMessageShouldBe(PyStringNode $string): void
  {
    $answer = json_decode($this->getKernelBrowser()
      ->getResponse()
      ->getContent(), true);
    Assert::assertEquals($string->getRaw(), $answer['answer']);
  }

  /**
   * @Then I should not get the url to the google form
   */
  public function iShouldNotGetTheUrlToTheGoogleForm(): void
  {
    $answer = json_decode($this->getKernelBrowser()
      ->getResponse()
      ->getContent(), true);
    Assert::assertArrayNotHasKey('form', $answer);
  }

  /**
   * @Then /^I should see the message "([^"]*)"$/
   *
   * @param mixed $arg1
   */
  public function iShouldSeeAMessage($arg1): void
  {
    Assert::assertStringContainsString($arg1, $this->getKernelBrowser()->getResponse()->getContent());
  }

  /**
   * @Then /^I should see the hashtag "([^"]*)" in the program description$/
   *
   * @param mixed $hashtag
   */
  public function iShouldSeeTheHashtagInTheProgramDescription($hashtag): void
  {
    Assert::assertStringContainsString($hashtag, $this->getKernelBrowser()
      ->getResponse()
      ->getContent());
  }

  // to df ->

  /**
   * @Then /^the program "([^"]*)" should be a remix root$/
   *
   * @param mixed $program_id
   */
  public function theProgramShouldBeARemixRoot($program_id): void
  {
    $program_manager = $this->getProgramManager();
    $uploaded_program = $program_manager->find($program_id);
    Assert::assertTrue($uploaded_program->isRemixRoot());
  }

  /**
   * @Then /^the program "([^"]*)" should not be a remix root$/
   *
   * @param mixed $program_id
   */
  public function theProgramShouldNotBeARemixRoot($program_id): void
  {
    $program_manager = $this->getProgramManager();
    /** @var Program $uploaded_program */
    $uploaded_program = $program_manager->find($program_id);
    Assert::assertFalse($uploaded_program->isRemixRoot());
  }

  /**
   * @Given /^the program "([^"]*)" should have a Scratch parent having id "([^"]*)"$/
   *
   * @param mixed $program_id
   * @param mixed $scratch_parent_id
   */
  public function theProgramShouldHaveAScratchParentHavingScratchID($program_id, $scratch_parent_id): void
  {
    $direct_edge_relation = $this->getScratchProgramRemixRepository()->findOneBy([
      'scratch_parent_id' => $scratch_parent_id,
      'catrobat_child_id' => $program_id,
    ]);

    Assert::assertNotNull($direct_edge_relation);
    $this->checked_catrobat_remix_forward_ancestor_relations[$direct_edge_relation->getUniqueKey()] =
      $direct_edge_relation;
  }

  /**
   * @Given /^the program "([^"]*)" should have no further Scratch parents$/
   *
   * @param mixed $program_id
   */
  public function theProgramShouldHaveNoFurtherScratchParents($program_id): void
  {
    $direct_edge_relations = $this->getScratchProgramRemixRepository()->findBy([
      'catrobat_child_id' => $program_id,
    ]);

    $further_scratch_parent_relations = array_filter($direct_edge_relations,
      function (ScratchProgramRemixRelation $relation): bool
      {
        return !array_key_exists(
          $relation->getUniqueKey(), $this->checked_catrobat_remix_forward_ancestor_relations
        );
      });

    Assert::assertCount(0, $further_scratch_parent_relations);
  }

  /**
   * @Then /^the program "([^"]*)" should have a Catrobat forward ancestor having id "([^"]*)" and depth "([^"]*)"$/
   *
   * @param mixed $program_id
   * @param mixed $ancestor_program_id
   * @param mixed $depth
   */
  public function theProgramShouldHaveACatrobatForwardAncestorHavingIdAndDepth($program_id, $ancestor_program_id, $depth): void
  {
    $forward_ancestor_relation = $this->getProgramRemixForwardRepository()->findOneBy([
      'ancestor_id' => $ancestor_program_id,
      'descendant_id' => $program_id,
      'depth' => $depth,
    ]);

    Assert::assertNotNull($forward_ancestor_relation);
    $this->checked_catrobat_remix_forward_ancestor_relations[$forward_ancestor_relation->getUniqueKey()] =
      $forward_ancestor_relation;

    if ($program_id == $ancestor_program_id && 0 == $depth)
    {
      $this->checked_catrobat_remix_forward_descendant_relations[$forward_ancestor_relation->getUniqueKey()] =
        $forward_ancestor_relation;
    }
  }

  /**
   * @Then /^the program "([^"]*)" should have a Catrobat backward parent having id "([^"]*)"$/
   *
   * @param mixed $program_id
   * @param mixed $backward_parent_program_id
   */
  public function theProgramShouldHaveACatrobatBackwardParentHavingId($program_id, $backward_parent_program_id): void
  {
    $backward_parent_relation = $this->getProgramRemixBackwardRepository()->findOneBy([
      'parent_id' => $backward_parent_program_id,
      'child_id' => $program_id,
    ]);

    Assert::assertNotNull($backward_parent_relation);
    $this->checked_catrobat_remix_backward_relations[$backward_parent_relation->getUniqueKey()] =
      $backward_parent_relation;
  }

  /**
   * @Then /^the program "([^"]*)" should have no Catrobat forward ancestors except self-relation$/
   *
   * @param mixed $program_id
   */
  public function theProgramShouldHaveNoCatrobatForwardAncestorsExceptSelfRelation($program_id): void
  {
    $forward_ancestors_including_self_referencing_relation = $this
      ->getProgramRemixForwardRepository()
      ->findBy(['descendant_id' => $program_id])
    ;

    Assert::assertCount(0, array_filter($forward_ancestors_including_self_referencing_relation,
      function (ProgramRemixRelation $relation): bool
      {
        return $relation->getDepth() >= 1;
      }));
  }

  /**
   * @Then /^the program "([^"]*)" should have no further Catrobat forward ancestors$/
   *
   * @param mixed $program_id
   */
  public function theProgramShouldHaveNoFurtherCatrobatForwardAncestors($program_id): void
  {
    $forward_ancestors_including_self_referencing_relation = $this
      ->getProgramRemixForwardRepository()
      ->findBy(['descendant_id' => $program_id])
    ;

    $further_forward_ancestor_relations = array_filter($forward_ancestors_including_self_referencing_relation,
      function (ProgramRemixRelation $relation): bool
      {
        return !array_key_exists(
          $relation->getUniqueKey(), $this->checked_catrobat_remix_forward_ancestor_relations
        );
      });

    Assert::assertCount(0, $further_forward_ancestor_relations);
  }

  /**
   * @Then /^the program "([^"]*)" should have no Catrobat backward parents$/
   *
   * @param mixed $program_id
   */
  public function theProgramShouldHaveNoCatrobatBackwardParents($program_id): void
  {
    $backward_parent_relations = $this->getProgramRemixBackwardRepository()->findBy(['child_id' => $program_id]);
    Assert::assertCount(0, $backward_parent_relations);
  }

  /**
   * @Then /^the program "([^"]*)" should have no further Catrobat backward parents$/
   *
   * @param mixed $program_id
   */
  public function theProgramShouldHaveNoFurtherCatrobatBackwardParents($program_id): void
  {
    $backward_parent_relations = $this->getProgramRemixBackwardRepository()->findBy(['child_id' => $program_id]);

    $further_backward_parent_relations = array_filter($backward_parent_relations,
      function (ProgramRemixBackwardRelation $relation): bool
      {
        return !array_key_exists(
          $relation->getUniqueKey(), $this->checked_catrobat_remix_backward_relations
        );
      });

    Assert::assertCount(0, $further_backward_parent_relations);
  }

  /**
   * @Then /^the program "([^"]*)" should have no Catrobat ancestors except self-relation$/
   *
   * @param mixed $program_id
   */
  public function theProgramShouldHaveNoCatrobatAncestors($program_id): void
  {
    $this->theProgramShouldHaveNoCatrobatForwardAncestorsExceptSelfRelation($program_id);
    $this->theProgramShouldHaveNoCatrobatBackwardParents($program_id);
  }

  /**
   * @Then /^the program "([^"]*)" should have no Scratch parents$/
   *
   * @param mixed $program_id
   */
  public function theProgramShouldHaveNoScratchParents($program_id): void
  {
    $scratch_parents = $this->getScratchProgramRemixRepository()->findBy(['catrobat_child_id' => $program_id]);
    Assert::assertCount(0, $scratch_parents);
  }

  /**
   * @Then /^the program "([^"]*)" should have a Catrobat forward descendant having id "([^"]*)" and depth "([^"]*)"$/
   *
   * @param mixed $program_id
   * @param mixed $descendant_program_id
   * @param mixed $depth
   */
  public function theProgramShouldHaveCatrobatForwardDescendantHavingIdAndDepth($program_id, $descendant_program_id, $depth): void
  {
    /** @var ProgramRemixRelation $forward_descendant_relation */
    $forward_descendant_relation = $this->getProgramRemixForwardRepository()->findOneBy([
      'ancestor_id' => $program_id,
      'descendant_id' => $descendant_program_id,
      'depth' => $depth,
    ]);

    Assert::assertNotNull($forward_descendant_relation);
    $this->checked_catrobat_remix_forward_descendant_relations[$forward_descendant_relation->getUniqueKey()] =
      $forward_descendant_relation;

    if ($program_id == $descendant_program_id && 0 == $depth)
    {
      $this->checked_catrobat_remix_forward_ancestor_relations[$forward_descendant_relation->getUniqueKey()] =
        $forward_descendant_relation;
    }
  }

  /**
   * @Then /^the program "([^"]*)" should have no Catrobat forward descendants except self-relation$/
   *
   * @param mixed $program_id
   */
  public function theProgramShouldHaveNoCatrobatForwardDescendantsExceptSelfRelation($program_id): void
  {
    $forward_ancestors_including_self_referencing_relation = $this
      ->getProgramRemixForwardRepository()
      ->findBy(['ancestor_id' => $program_id])
    ;

    Assert::assertCount(0, array_filter($forward_ancestors_including_self_referencing_relation,
      function (ProgramRemixRelation $relation): bool
      {
        return $relation->getDepth() >= 1;
      }));
  }

  /**
   * @Then the program :program_id should have no further Catrobat forward descendants
   *
   * @param mixed $program_id
   */
  public function theProgramShouldHaveNoFurtherCatrobatForwardDescendants($program_id): void
  {
    $forward_descendants_including_self_referencing_relation = $this
      ->getProgramRemixForwardRepository()
      ->findBy(['ancestor_id' => $program_id])
    ;

    $further_forward_descendant_relations = array_filter($forward_descendants_including_self_referencing_relation,
      function (ProgramRemixRelation $relation): bool
      {
        return !array_key_exists(
          $relation->getUniqueKey(), $this->checked_catrobat_remix_forward_descendant_relations
        );
      });

    Assert::assertCount(0, $further_forward_descendant_relations);
  }

  /**
   * @Then /^the program "([^"]*)" should have RemixOf "([^"]*)" in the xml$/
   *
   * @param mixed $program_id
   * @param mixed $value
   */
  public function theProgramShouldHaveRemixofInTheXml($program_id, $value): void
  {
    $program_manager = $this->getProgramManager();
    /** @var Program $uploaded_program */
    $uploaded_program = $program_manager->find($program_id);
    $efr = $this->getExtractedFileRepository();
    $extracted_catrobat_file = $efr->loadProgramExtractedFile($uploaded_program);
    $project_xml_prop = $extracted_catrobat_file->getProgramXmlProperties();
    Assert::assertEquals($value, $project_xml_prop->header->remixOf->__toString());
  }

  /**
   * @Given /^I request from a (debug|release) build of the Catroid app$/
   *
   * @param mixed $build_type
   */
  public function iRequestFromASpecificBuildTypeOfCatroidApp($build_type): void
  {
    $this->iUseTheUserAgentParameterized('0.998', 'PocketCode', '0.9.60', $build_type);
  }

  /**
   * @Given /^I request from an ios app$/
   */
  public function iRequestFromAnIOSApp(): void
  {
    // see org.catrobat.catroid.ui.WebViewActivity
    $platform = 'iPhone';
    $user_agent = ' Platform/'.$platform;
    $this->iUseTheUserAgent($user_agent);
  }

  /**
   * @Given /^I request from a specific "([^"]*)" themed app$/
   *
   * @param mixed $theme
   */
  public function iUseASpecificThemedApp($theme): void
  {
    $this->iUseTheUserAgentParameterized('0.998', 'PocketCode', '0.9.60',
      'release', $theme);
  }

  /**
   * @When /^I upload a catrobat program with the phiro app$/
   */
  public function iUploadACatrobatProgramWithThePhiroProApp(): void
  {
    $user = $this->insertUser();
    $program = $this->getStandardProgramFile();
    $this->uploadProject($program, $user, '1', '1', 'phirocode');
    Assert::assertEquals(200, $this->getKernelBrowser()->getResponse()->getStatusCode(),
      'Wrong response code. '.$this->getKernelBrowser()->getResponse()->getContent());
  }

  //--------------------------------------------------------------------------------------------------------------------
  //  Error Logging
  //--------------------------------------------------------------------------------------------------------------------

  /**
   * @AfterStep
   */
  public function saveResponseToFile(AfterStepScope $scope): void
  {
    if (null == $this->ERROR_DIR)
    {
      return;
    }

    try
    {
      if (!$scope->getTestResult()->isPassed() && null != $this->getKernelBrowser())
      {
        $response = $this->getKernelBrowser()->getResponse();
        if (null != $response && '' != $response->getContent())
        {
          file_put_contents($this->ERROR_DIR.'errors.json', $response->getContent());
        }
      }
    }
    catch (Exception $e)
    {
      file_put_contents($this->ERROR_DIR.'errors.json', '');
    }
  }

  /**
   * @When /^I upload a standard catrobat program$/
   */
  public function iUploadAStandardCatrobatProgram(): void
  {
    $user = $this->insertUser();
    $file = $this->getStandardProgramFile();
    $this->uploadProject($file, $user, '1', '1');
    Assert::assertEquals(200, $this->getKernelBrowser()->getResponse()->getStatusCode(),
      'Wrong response code. '.$this->getKernelBrowser()->getResponse()->getContent());
  }

  /**
   * @Then /^I should be redirected to a catrobat program$/
   */
  public function iShouldBeRedirectedToACatrobatProgram(): void
  {
    Assert::assertStringStartsWith('/app/project/', $this->getKernelBrowser()->getRequest()->getPathInfo());
  }

  private function findProgram(array $programs, string $wanted_program_name): array
  {
    foreach ($programs as $program)
    {
      if ($program['name'] === $wanted_program_name)
      {
        return $program;
      }
    }

    return [];
  }

  private function findUser(array $users, string $wanted_user_name): array
  {
    foreach ($users as $user)
    {
      if ($user['username'] === $wanted_user_name)
      {
        return $user;
      }
    }

    return [];
  }

  private function expectProgram(array $programs, string $value): bool
  {
    foreach ($programs as $program)
    {
      if ($program['Name'] === $value)
      {
        return true;
      }
    }

    return false;
  }

  private function getStoredPrograms(array $expected_programs): array
  {
    $programs = array_merge($this->dataFixturesContext->getPrograms(), $this->new_uploaded_projects);
    $projects = [];
    /** @var Program $program */
    foreach ($programs as $program_index => $program)
    {
      if (!$this->expectProgram($expected_programs, $program->getName()))
      {
        continue;
      }
      $result = [
        'id' => $program->getId(),
        'name' => $program->getName(),
        'author' => $program->getUser()->getUserName(),
        'description' => $program->getDescription(),
        'version' => $program->getCatrobatVersionName(),
        'views' => $program->getViews(),
        'download' => $program->getDownloads(),
        'private' => $program->getPrivate(),
        'flavor' => $program->getFlavor(),
        'project_url' => 'http://localhost/app/project/'.$program->getId(),
        'download_url' => 'http://localhost/app/download/'.$program->getId().'.catrobat',
        'filesize' => ($program->getFilesize() / 1_048_576),
      ];
      $projects[] = $result;
    }

    return $projects;
  }

  private function getStoredFeaturedPrograms(array $expected_programs): array
  {
    $programs = $this->dataFixturesContext->getFeaturedPrograms();
    $projects = [];
    /** @var FeaturedProgram $program */
    foreach ($programs as $program_index => $program)
    {
      if (!$this->expectProgram($expected_programs, $program->getProgram()->getName()))
      {
        continue;
      }
      $result = [
        'id' => $program->getId(),
        'name' => $program->getProgram()->getName(),
        'author' => $program->getProgram()->getUser()->getUserName(),
        'featured_image' => 'http://localhost/resources_test/featured/featured_'.$program->getId().'.jpg',
      ];
      $projects[] = $result;
    }

    return $projects;
  }

  private function getStoredMediaFiles(array $expected_programs): array
  {
    $programs = $this->dataFixturesContext->getMediaFiles();
    $projects = [];
    foreach ($programs as $program_index => $program)
    {
      if (!$this->expectProgram($expected_programs, $program['name']))
      {
        continue;
      }
      $projects[] = $program;
    }

    return $projects;
  }

  private function getStoredUsers(array $expected_users): array
  {
    $stored_users = $this->dataFixturesContext->getUsers();
    $users = [];
    /** @var User $user */
    foreach ($stored_users as $program_index => $user)
    {
      $result = [
        'id' => $user->getId(),
        'username' => $user->getUsername(),
        'email' => $user->getEmail(),
        'country' => $user->getCountry(),
        'projects' => $user->getPrograms()->count(),
        'followers' => $user->getFollowers()->count(),
        'following' => $user->getFollowing()->count(),
      ];
      $users[] = $result;
    }

    return $users;
  }

  private function checkProjectFieldsValue(array $program, string $key): bool
  {
    $fields = [
      'id' => function ($id)
      {
        Assert::assertIsString($id);
        Assert::assertMatchesRegularExpression('/^[a-zA-Z0-9-]+$/', $id, 'id');
      },
      'name' => function ($name)
      {
        Assert::assertIsString($name);
      },
      'author' => function ($author)
      {
        Assert::assertIsString($author);
      },
      'description' => function ($description)
      {
        Assert::assertIsString($description);
      },
      'version' => function ($version)
      {
        Assert::assertIsString($version);
        Assert::assertMatchesRegularExpression('/[0-9]\\.[0-9]\\.[0-9]/', $version);
      },
      'views' => function ($views)
      {
        Assert::assertIsInt($views);
      },
      'download' => function ($download)
      {
        Assert::assertIsInt($download);
      },
      'private' => function ($private)
      {
        Assert::assertIsBool($private);
      },
      'flavor' => function ($flavor)
      {
        Assert::assertIsString($flavor);
      },
      'uploaded' => function ($uploaded)
      {
        Assert::assertIsInt($uploaded);
      },
      'uploaded_string' => function ($uploaded_string)
      {
        Assert::assertIsString($uploaded_string);
      },
      'screenshot_large' => function ($screenshot_large)
      {
        Assert::assertIsString($screenshot_large);
        Assert::assertMatchesRegularExpression('/http:\\/\\/localhost\\/((resources_test\\/screenshots\/screen_[0-9]+)|(images\\/default\\/screenshot))\\.png/',
          $screenshot_large);
      },
      'screenshot_small' => function ($screenshot_small)
      {
        Assert::assertIsString($screenshot_small);
        Assert::assertMatchesRegularExpression('/http:\\/\\/localhost\\/((resources_test\\/thumbnails\/screen_[0-9]+)|(images\\/default\\/thumbnail))\\.png/',
          $screenshot_small);
      },
      'project_url' => function ($project_url)
      {
        Assert::assertIsString($project_url);
        Assert::assertMatchesRegularExpression('/http:\\/\\/localhost\\/app\\/project\\/[a-zA-Z0-9-]+/', $project_url);
      },
      'download_url' => function ($download_url)
      {
        Assert::assertIsString($download_url);
        Assert::assertMatchesRegularExpression('/http:\\/\\/localhost\\/app\\/download\\/([a-zA-Z0-9-]+)\\.catrobat/',
          $download_url);
      },
      'filesize' => function ($filesize)
      {
        Assert::assertEquals(is_float($filesize) || is_int($filesize), true);
      },
    ];

    Assert::assertArrayHasKey($key, $fields);
    call_user_func($fields[$key], $program[$key]);

    return true;
  }

  private function checkFeaturedProjectFieldsValue(array $program, string $key): bool
  {
    $fields = [
      'id' => function ($id)
      {
        Assert::assertIsString($id);
        Assert::assertMatchesRegularExpression('/^[a-zA-Z0-9-]+$/', $id, 'id');
      },
      'name' => function ($name)
      {
        Assert::assertIsString($name);
      },
      'author' => function ($author)
      {
        Assert::assertIsString($author);
      },
      'featured_image' => function ($featured_image)
      {
        Assert::assertIsString($featured_image);
        Assert::assertMatchesRegularExpression('/http:\/\/localhost\/resources_test\/featured\/featured_[0-9]+\.jpg/',
          $featured_image);
      },
    ];

    Assert::assertArrayHasKey($key, $fields);
    call_user_func($fields[$key], $program[$key]);

    return true;
  }

  private function checkMediaFileFieldsValue(array $program, string $key): bool
  {
    $fields = [
      'id' => function ($id)
      {
        Assert::assertIsInt($id);
      },
      'name' => function ($name)
      {
        Assert::assertIsString($name);
      },
      'flavor' => function ($flavor)
      {
        Assert::assertIsString($flavor);
      },
      'package' => function ($package)
      {
        Assert::assertIsString($package);
      },
      'category' => function ($category)
      {
        Assert::assertIsString($category);
      },
      'author' => function ($author)
      {
        Assert::assertIsString($author);
      },
      'extension' => function ($extension)
      {
        Assert::assertIsString($extension);
      },
      'download_url' => function ($download_url)
      {
        Assert::assertIsString($download_url);
        Assert::assertMatchesRegularExpression('/http:\/\/localhost\/app\/download-media\/[a-zA-Z0-9-]+/',
          $download_url, 'download_url');
      },
    ];

    Assert::assertArrayHasKey($key, $fields);
    call_user_func($fields[$key], $program[$key]);

    return true;
  }

  private function checkUserFieldsValue(array $user, string $key): bool
  {
    $fields = [
      'id' => function ($id)
      {
        Assert::assertIsString($id);
        Assert::assertMatchesRegularExpression('/^[a-zA-Z0-9-]+$/', $id, 'id');
      },
      'username' => function ($username)
      {
        Assert::assertIsString($username);
      },
      'email' => function ($email)
      {
        Assert::assertIsString($email);
      },
      'country' => function ($country)
      {
        Assert::assertIsString($country);
      },
      'projects' => function ($projects)
      {
        Assert::assertIsInt($projects);
      },
      'followers' => function ($followers)
      {
        Assert::assertIsInt($followers);
      },
      'following' => function ($following)
      {
        Assert::assertIsInt($following);
      },
    ];

    Assert::assertArrayHasKey($key, $fields);
    call_user_func($fields[$key], $user[$key]);

    return true;
  }

  //--------------------------------------------------------------------------------------------------------------------
  //  Upload Request process
  //--------------------------------------------------------------------------------------------------------------------

  /**
   * Uploads a Catrobat Project.
   *
   * @param string $file        The Catrobat file to be uploaded
   * @param User   $user        The uploader
   * @param string $api_version The version of the API to be used
   * @param string $desired_id  Specifiy, if the uploaded project should get a desired id
   * @param string $flavor      The flavor of the project
   *
   * @throws APIVersionNotSupportedException when the specified API version is not supported
   * @throws Exception                       when an error while uploading occurs
   */
  private function uploadProject(string $file, User $user = null, string $api_version, string $desired_id = '',
                                 string $flavor = 'pocketcode'): void
  {
    if (null == $user)
    {
      if (isset($this->username))
      {
        /** @var User $user */
        $user = $this->getUserManager()->findUserByUsername($this->username);
      }
      else
      {
        $user = $this->getUserDataFixtures()->getDefaultUser();
      }
    }

    // overwrite id if desired
    if ('' !== $desired_id)
    {
      MyUuidGenerator::setNextValue($desired_id);
    }

    if (is_string($file))
    {
      try
      {
        $file = new UploadedFile($file, basename($file));
      }
      catch (Exception $e)
      {
        throw new Exception('File to upload does not exist'.$e);
      }
    }

    if ('1' == $api_version)
    {
      $this->request_parameters['username'] = $user->getUsername();
      $this->request_parameters['token'] = $user->getUploadToken();
      $this->request_files[0] = $file;
      $this->iHaveAParameterWithTheMdChecksumOfTheUploadFile('fileChecksum', '1');
      $this->iRequestWith('POST', '/'.$flavor.'/api/upload/upload.json');
    }
    elseif ('2' == $api_version)
    {
      $this->request_headers['CONTENT_TYPE'] = 'multipart/form-data';
      $this->request_headers['HTTP_ACCEPT'] = 'application/json';
      $this->request_files['file'] = $file;
      $this->iHaveAParameterWithTheMdChecksumOfTheUploadFile('checksum', '2');
      $this->iUseAValidJwtBearerTokenFor($user->getUsername());
      $this->iRequestWith('POST', '/api/projects');
    }
    else
    {
      throw new APIVersionNotSupportedException($api_version);
    }
  }

  /**
   * Returns the ID of the last uploaded project. The ID is retrieved from the last received response.
   *
   * @param string $api_version The API version to be used
   *
   * @throws APIVersionNotSupportedException when the specified API version is not supported
   *
   * @return string the ID of the last uploaded project or null if not available
   */
  private function getIDOfLastUploadedProject(string $api_version)
  {
    $last_uploaded_project_id = null;

    if ('1' == $api_version)
    {
      $json = json_decode($this->getKernelBrowser()->getResponse()->getContent(), true);
      $last_uploaded_project_id = $json['projectId'];
    }
    elseif ('2' == $api_version)
    {
      $splitted_project_uri = explode('/', $this->getKernelBrowser()->getResponse()->headers->get('Location'));
      $last_uploaded_project_id = $splitted_project_uri[sizeof($splitted_project_uri) - 1];
    }
    else
    {
      throw new APIVersionNotSupportedException($api_version);
    }

    return $last_uploaded_project_id;
  }

  private function prepareValidRegistrationParameters(): void
  {
    $this->request_parameters['registrationUsername'] = 'newuser';
    $this->request_parameters['registrationPassword'] = 'topsecret';
    $this->request_parameters['registrationEmail'] = 'someuser@example.com';
    $this->request_parameters['registrationCountry'] = 'at';
  }

  private function iUseTheUserAgent(string $user_agent): void
  {
    $this->request_headers['HTTP_USER_AGENT'] = $user_agent;
  }

  private function iUseTheUserAgentParameterized(string $lang_version, string $flavor, string $app_version,
                                                 string $build_type, string $theme = 'pocketcode'): void
  {
    // see org.catrobat.catroid.ui.WebViewActivity
    $platform = 'Android';
    $user_agent = 'Catrobat/'.$lang_version.' '.$flavor.'/'.$app_version.' Platform/'.$platform.
      ' BuildType/'.$build_type.' Theme/'.$theme;
    $this->iUseTheUserAgent($user_agent);
  }

  private function pathWithoutParam(string $path): string
  {
    return strtok($path, '?');
  }

  private function assertProgramsEqual(array $stored_program, array $returned_program): void
  {
    Assert::assertNotEmpty($stored_program);
    Assert::assertNotEmpty($returned_program);
    foreach ($this->program_structure as $key)
    {
      if (array_key_exists($key, $stored_program))
      {
        Assert::assertEquals($stored_program[$key], $returned_program[$key]);
      }
      elseif ('screenshot_large' === $key)
      {
        Assert::assertContains($this->pathWithoutParam($returned_program[$key]),
          ['http://localhost/resources/screenshots/screen_'.$returned_program['id'].'.png',
            'http://localhost/resources_test/screenshots/screen_'.$returned_program['id'].'.png',
            'http://localhost/images/default/screenshot.png', ]);
      }
      elseif ('screenshot_small' === $key)
      {
        Assert::assertContains($this->pathWithoutParam($returned_program[$key]),
          ['http://localhost/resources/thumbnails/screen_'.$returned_program['id'].'.png',
            'http://localhost/resources_test/thumbnails/screen_'.$returned_program['id'].'.png',
            'http://localhost/images/default/thumbnail.png', ]);
      }
    }
  }

  private function assertUsersEqual(array $stored_user, array $returned_user): void
  {
    Assert::assertNotEmpty($stored_user);
    Assert::assertNotEmpty($returned_user);
    foreach ($this->user_structure as $key)
    {
      Assert::assertEquals($stored_user[$key], $returned_user[$key]);
    }
  }
}
