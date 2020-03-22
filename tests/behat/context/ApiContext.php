<?php

use App\Catrobat\Services\TestEnv\SymfonySupport;
use App\Entity\Program;
use App\Entity\ProgramRemixBackwardRelation;
use App\Entity\ProgramRemixRelation;
use App\Entity\ScratchProgramRemixRelation;
use App\Entity\User;
use App\Utils\MyUuidGenerator;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Profiler\Profile;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Class ApiContext.
 *
 * Basic request/response handling.
 */
class ApiContext implements KernelAwareContext
{
  use SymfonySupport;

  /**
   * Name of the user which is used for the next requests.
   *
   * @var string
   */
  private $username;

  /**
   * @var string
   */
  private $method;

  /**
   * @var string
   */
  private $url;

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
   * @var string
   */
  private $last_response;

  /**
   * @var array
   */
  private $stored_json;

  /**
   * @var KernelBrowser
   */
  private $kernel_browser;

  // to df ->function
  /**
   * @var array
   */
  private $checked_catrobat_remix_forward_ancestor_relations;

  /**
   * @var array
   */
  private $checked_catrobat_remix_forward_descendant_relations;

  /**
   * @var array
   */
  private $checked_catrobat_remix_backward_relations;

  /**
   * @var Program
   */
  private $my_program;

  public function getKernelBrowser(): KernelBrowser
  {
    if (null === $this->kernel_browser)
    {
      $this->kernel_browser = $this->kernel->getContainer()->get('test.client');
    }

    return $this->kernel_browser;
  }

  // -------------------------------------------------------------------------------------------------------------------
  //  Hooks
  // -------------------------------------------------------------------------------------------------------------------

  /**
   * @BeforeScenario
   */
  public function followRedirects()
  {
    $this->getKernelBrowser()->followRedirects(true);
  }

  /**
   * @BeforeScenario
   */
  public function generateSessionCookie()
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
  public function clearRequest()
  {
    $this->method = 'GET';
    $this->url = '/';
    $this->request_parameters = [];
    $this->request_files = [];
    $this->request_server = [];
    $this->request_content = null;
  }

  /**
   * @Given /^I activate the Profiler$/
   */
  public function iActivateTheProfiler()
  {
    $this->getKernelBrowser()->enableProfiler();
  }

  /**
   * @When /^I request "([^"]*)" "([^"]*)"$/
   * @When /^I :method :url with these parameters$/
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
   * @When /^such a Request is invoked$/
   * @When /^a Request is invoked$/
   * @When /^the Request is invoked$/
   * @When /^I invoke the Request$/
   */
  public function iInvokeTheRequest()
  {
    $this->iRequest($this->method, $this->url);
  }

  /**
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
  public function iGetFrom($url)
  {
    $this->iRequest('GET', $url);
  }

  /**
   * @When /^I POST these parameters to "([^"]*)"$/
   *
   * @param mixed $url
   */
  public function iPostTo($url)
  {
    $this->iRequest('POST', $url);
  }

  /**
   * @Given /^I search for "([^"]*)"$/
   *
   * @param $arg1
   */
  public function iSearchFor($arg1)
  {
    $this->iHaveAParameterWithValue('q', $arg1);
    $this->iGetFrom('/app/api/projects/search.json');
  }

  /**
   * @When /^I search similar programs for program id "([^"]*)"$/
   *
   * @param $id
   */
  public function iSearchSimilarProgramsForProgramId($id)
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
   * @param $arg1
   *
   * @throws Exception
   */
  public function iWantToDownloadTheApkFileOf($arg1)
  {
    $program_manager = $this->getProgramManager();
    /** @var Program $program */
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
   * @param $url
   */
  public function iGetTheUserSProgramsWith($url)
  {
    /** @var User $user */
    $user = $this->getUserManager()->findAll()[0];

    $this->iHaveAParameterWithValue('user_id', $user->getId());
    $this->iGetFrom($url);
  }

  /**
   * @When /^I upload a valid program$/
   */
  public function iUploadAValidProgram()
  {
    $this->iHaveAParameterWithValue('username', 'Catrobat');
    $this->iHaveAParameterWithValue('token', 'cccccccccc');
    $this->iHaveAValidCatrobatFile();
    $this->iHaveAParameterWithTheMdchecksumOf('fileChecksum', 'test.catrobat');
    $this->iPostTo('/app/api/upload/upload.json');
    $this->iHaveAParameterWithTheReturnedProjectid('program');
  }

  /**
   * @Given /^I am "([^"]*)"$/
   *
   * @param $username
   */
  public function iAm($username)
  {
    $this->username = $username;
  }

  /**
   * @When /^I upload a catrobat program with the same name$/
   */
  public function iUploadACatrobatProgramWithTheSameName()
  {
    /** @var User $user */
    $user = $this->getUserManager()->findUserByUsername($this->username);
    $this->request_parameters['token'] = $user->getUploadToken();
    $this->last_response = $this->getKernelBrowser()->getResponse()->getContent()
    ;
    $this->iPostTo('/app/api/upload/upload.json');
  }

  /**
   * @When /^I upload a catrobat program$/
   */
  public function iUploadACatrobatProgram()
  {
    $this->iHaveAValidCatrobatFile();
    $this->iHaveAParameterWithTheMdChecksumOf('fileChecksum');
    $this->request_parameters['username'] = $this->username;
    $this->request_parameters['token'] = 'cccccccccc';
    $this->iPostTo('/app/api/upload/upload.json');
  }

  /**
   * @When /^I upload another program using token "([^"]*)"$/
   *
   * @param $arg1
   */
  public function iUploadAnotherProgramUsingToken($arg1)
  {
    $this->iHaveAValidCatrobatFile();
    $this->iHaveAParameterWithTheMdChecksumOf('fileChecksum');
    $this->request_parameters['username'] = $this->username;
    $this->request_parameters['token'] = $arg1;
    $this->iPostTo('/app/api/upload/upload.json');
  }

  /**
   * @When /^jenkins uploads the apk file to the given upload url$/
   */
  public function jenkinsUploadsTheApkFileToTheGivenUploadUrl()
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
   * @param $program_id
   * @param $category
   * @param $note
   */
  public function iReportProgramWithNote($program_id, $category, $note)
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
   * @param $uname
   * @param $pwd
   */
  public function iPostLoginUserWithPassword($uname, $pwd)
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
   * @param $missing_parameter
   */
  public function iTryToRegisterWithout($missing_parameter)
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
  public function iTryToRegister()
  {
    $this->iPostTo('/app/api/loginOrRegister/loginOrRegister.json');
  }

  /**
   * @When /^I register a new user$/
   */
  public function iRegisterANewUser()
  {
    $this->prepareValidRegistrationParameters();
    $this->iPostTo('/app/api/loginOrRegister/loginOrRegister.json');
  }

  /**
   * @When /^I try to register another user with the same email adress$/
   */
  public function iTryToRegisterAnotherUserWithTheSameEmailAdress()
  {
    $this->prepareValidRegistrationParameters();
    $this->request_parameters['registrationUsername'] = 'AnotherUser';
    $this->iPostTo('/app/api/loginOrRegister/loginOrRegister.json');
  }

  /**
   * @When /^I report the program$/
   */
  public function iReportTheProgram()
  {
    $this->iHaveAParameterWithValue('note', 'Bad Project');
    $this->iPostTo('/app/api/reportProject/reportProject.json');
  }

  /**
   * @Then /^I should receive a "([^"]*)" file$/
   *
   * @param $extension
   */
  public function iShouldReceiveAFile($extension)
  {
    $content_type = $this->getKernelBrowser()->getResponse()->headers->get('Content-Type');
    Assert::assertEquals('image/'.$extension, $content_type);
  }

  /**
   * @Then /^I should receive a file named "([^"]*)"$/
   *
   * @param $name
   */
  public function iShouldReceiveAFileNamed($name)
  {
    $content_disposition = $this->getKernelBrowser()->getResponse()->headers->get('Content-Disposition');
    Assert::assertEquals('attachment; filename="'.$name.'"', $content_disposition);
  }

  /**
   * @Then /^I should receive the apk file$/
   */
  public function iShouldReceiveTheApkFile()
  {
    $content_type = $this->getKernelBrowser()->getResponse()->headers->get('Content-Type');
    $code = $this->getKernelBrowser()->getResponse()->getStatusCode();
    Assert::assertEquals(200, $code);
    Assert::assertEquals('application/vnd.android.package-archive', $content_type);
  }

  /**
   * @Then /^I should receive an application file$/
   */
  public function iShouldReceiveAnApplicationFile()
  {
    $content_type = $this->getKernelBrowser()->getResponse()->headers->get('Content-Type');
    $code = $this->getKernelBrowser()->getResponse()->getStatusCode();
    Assert::assertEquals(200, $code);
    Assert::assertEquals('application/zip', $content_type);
  }

  /**
   * @Then /^The upload should be successful$/
   */
  public function theUploadShouldBeSuccessful()
  {
    $response = $this->getKernelBrowser()->getResponse();
    $responseArray = json_decode($response->getContent(), true);
    Assert::assertEquals(200, $responseArray['statusCode']);
  }

  /**
   * @Then /^the uploaded program should be a remix root$/
   */
  public function theUploadedProgramShouldBeARemixRoot()
  {
    $json = json_decode($this->getKernelBrowser()->getResponse()->getContent(), true);
    $this->theProgramShouldBeARemixRoot($json['projectId']);
  }

  /**
   * @Then /^It should be uploaded$/
   */
  public function itShouldBeUploaded()
  {
    $json = json_decode($this->getKernelBrowser()
      ->getResponse()
      ->getContent(), true);
    Assert::assertEquals('200', $json['statusCode'], $this->getKernelBrowser()
      ->getResponse()
      ->getContent());
  }

  /**
   * @Then /^the response status code should be "([^"]*)"$/
   *
   * @param $status_code
   */
  public function theResponseStatusCodeShouldBe($status_code)
  {
    $response = $this->getKernelBrowser()->getResponse();
    Assert::assertEquals(
      $status_code, $response->getStatusCode(),
      'Response contains invalid status code "'.$response->getStatusCode().'"'
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
   *
   * @param $name
   * @param $value
   */
  public function iHaveARequestHeaderWithValue($name, $value)
  {
    $this->request_server[$name] = $value;
  }

  /**
   * @Given I have the following JSON request body:
   */
  public function iHaveTheFollowingJsonRequestBody(PyStringNode $content)
  {
    $this->request_content = $content;
  }

  /**
   * @Then /^I should get the json object:$/
   */
  public function iShouldGetTheJsonObject(PyStringNode $string)
  {
    $response = $this->getKernelBrowser()->getResponse();
    $this->assertJsonRegex($string, $response->getContent());
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
    $this->request_server['HTTP_authorization'] = 'Bearer '.$token;
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
    $token = $this->getJwtEncoder()->encode(['username' => $username, 'exp' => 1]);
    sleep(1);
    $this->request_server['HTTP_authorization'] = 'Bearer '.$token;
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
   * @param $file
   * @param $user
   *
   * @return Response
   */
  public function submit($file, $user, string $desired_id = '')
  {
    if (null == $user)
    {
      $user = $this->getUserDataFixtures()->getDefaultUser();
    }

    if ('' !== $desired_id)
    {
      MyUuidGenerator::setNextValue($desired_id);
    }

    if (is_string($file))
    {
      $file = new UploadedFile($file, 'uploadedFile');
    }

    $parameters = [];
    $parameters['username'] = $user->getUsername();
    $parameters['token'] = $user->getUploadToken();
    $parameters['fileChecksum'] = md5_file($file->getPathname());
    $client = $this->getKernelBrowser();
    $client->request('POST', '/app/api/gamejam/submit.json', $parameters, [$file]);

    return $client->getResponse();
  }

  //--------------------------------------------------------------------------------------------------------------------
  //  Upload Request process
  //--------------------------------------------------------------------------------------------------------------------

  /**
   * @When /^I upload a program with (.*)$/
   *
   * @param $program_attribute
   */
  public function iUploadAProgramWith($program_attribute)
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
    $this->upload($this->FIXTURES_DIR.'GeneratedFixtures/'.$filename, null);
  }

  /**
   * @When /^I upload an invalid program file$/
   */
  public function iUploadAnInvalidProgramFile()
  {
    $this->upload($this->FIXTURES_DIR.'/invalid_archive.catrobat', null);
  }

  /**
   * @When I upload this project with id :id
   *
   * @param mixed $id
   */
  public function iUploadThisProject($id)
  {
    $this->upload(sys_get_temp_dir().'/program_generated.catrobat', null, $id);
  }

  /**
   * @When /^User "([^"]*)" uploads the program$/
   * @When /^User "([^"]*)" uploads the project$/
   *
   * @param $username
   */
  public function iUploadAProject($username)
  {
    $user = $this->getUserManager()->findUserByUsername($username);
    $this->upload(sys_get_temp_dir().'/program_generated.catrobat', $user);
  }

  /**
   * @Given /^I upload the program with "([^"]*)" as name$/
   * @When /^I upload the program with "([^"]*)" as name again$/
   *
   * @param $name
   */
  public function iUploadTheProgramWithAsName($name)
  {
    $this->generateProgramFileWith([
      'name' => $name,
    ]);
    $this->upload(sys_get_temp_dir().'/program_generated.catrobat', null);
  }

  /**
   * @Then /^the uploaded program should not be a remix root$/
   */
  public function theUploadedProgramShouldNotBeARemixRoot()
  {
    $json = json_decode($this->getKernelBrowser()->getResponse()->getContent(), true);
    $this->theProgramShouldNotBeARemixRoot($json['projectId']);
  }

  /**
   * @Then /^the uploaded program should have remix migration date NOT NULL$/
   */
  public function theUploadedProgramShouldHaveMigrationDateNotNull()
  {
    $json = json_decode($this->getKernelBrowser()->getResponse()->getContent(), true);
    $program_manager = $this->getProgramManager();
    $uploaded_program = $program_manager->find($json['projectId']);
    Assert::assertNotNull($uploaded_program->getRemixMigratedAt());
  }

  /**
   * @Given /^the uploaded program should have a Scratch parent having id "([^"]*)"$/
   *
   * @param $scratch_parent_id
   */
  public function theUploadedProgramShouldHaveAScratchParentHavingScratchID($scratch_parent_id)
  {
    $json = json_decode($this->getKernelBrowser()->getResponse()->getContent(), true);
    $this->theProgramShouldHaveAScratchParentHavingScratchID($json['projectId'], $scratch_parent_id);
  }

  /**
   * @Given /^the uploaded program should have no further Scratch parents$/
   */
  public function theUploadedProgramShouldHaveNoFurtherScratchParents()
  {
    $json = json_decode($this->getKernelBrowser()->getResponse()->getContent(), true);
    $this->theProgramShouldHaveNoFurtherScratchParents($json['projectId']);
  }

  /**
   * @Then /^the uploaded program should have a Catrobat forward ancestor having id "([^"]*)" and depth "([^"]*)"$/
   *
   * @param $id
   * @param $depth
   */
  public function theUploadedProgramShouldHaveACatrobatForwardAncestorHavingIdAndDepth($id, $depth)
  {
    $json = json_decode($this->getKernelBrowser()->getResponse()->getContent(), true);

    $this->theProgramShouldHaveACatrobatForwardAncestorHavingIdAndDepth($json['projectId'], $id, $depth);
  }

  /**
   * @Then the uploaded program should have a Catrobat forward ancestor having its own id and depth :depth
   *
   * @param $depth
   */
  public function theUploadedProgramShouldHaveACatrobatForwardAncestorHavingItsOwnIdAndDepth($depth)
  {
    $json = json_decode($this->getKernelBrowser()->getResponse()->getContent(), true);

    $this->theProgramShouldHaveACatrobatForwardAncestorHavingIdAndDepth($json['projectId'], $json['projectId'], $depth);
  }

  /**
   * @Then /^the uploaded program should have a Catrobat backward parent having id "([^"]*)"$/
   *
   * @param $id
   */
  public function theUploadedProgramShouldHaveACatrobatBackwardParentHavingId($id)
  {
    $json = json_decode($this->getKernelBrowser()->getResponse()->getContent(), true);
    $this->theProgramShouldHaveACatrobatBackwardParentHavingId($json['projectId'], $id);
  }

  /**
   * @Then /^the uploaded program should have no Catrobat forward ancestors except self-relation$/
   */
  public function theUploadedProgramShouldHaveNoCatrobatForwardAncestorsExceptSelfRelation()
  {
    $json = json_decode($this->getKernelBrowser()->getResponse()->getContent(), true);
    $this->theProgramShouldHaveNoCatrobatForwardAncestorsExceptSelfRelation($json['projectId']);
  }

  /**
   * @Then /^the uploaded program should have no Catrobat backward parents$/
   */
  public function theUploadedProgramShouldHaveNoCatrobatBackwardParents()
  {
    $json = json_decode($this->getKernelBrowser()->getResponse()->getContent(), true);
    $this->theProgramShouldHaveNoCatrobatBackwardParents($json['projectId']);
  }

  /**
   * @Then /^the uploaded program should have no further Catrobat backward parents$/
   */
  public function theUploadedProgramShouldHaveNoFurtherCatrobatBackwardParents()
  {
    $json = json_decode($this->getKernelBrowser()->getResponse()->getContent(), true);
    $this->theProgramShouldHaveNoFurtherCatrobatBackwardParents($json['projectId']);
  }

  /**
   * @Then /^the uploaded program should have no Catrobat ancestors except self-relation$/
   */
  public function theUploadedProgramShouldHaveNoCatrobatAncestors()
  {
    $json = json_decode($this->getKernelBrowser()->getResponse()->getContent(), true);
    $this->theProgramShouldHaveNoCatrobatAncestors($json['projectId']);
  }

  /**
   * @Then /^the uploaded program should have no Scratch parents$/
   */
  public function theUploadedProgramShouldHaveNoScratchParents()
  {
    $json = json_decode($this->getKernelBrowser()->getResponse()->getContent(), true);
    $this->theProgramShouldHaveNoScratchParents($json['projectId']);
  }

  /**
   * @Then /^the uploaded program should have a Catrobat forward descendant having id "([^"]*)" and depth "([^"]*)"$/
   *
   * @param $id
   * @param $depth
   */
  public function theUploadedProgramShouldHaveCatrobatForwardDescendantHavingIdAndDepth($id, $depth)
  {
    $json = json_decode($this->getKernelBrowser()->getResponse()->getContent(), true);
    $this->theProgramShouldHaveCatrobatForwardDescendantHavingIdAndDepth($json['projectId'], $id, $depth);
  }

  /**
   * @Then /^the uploaded program should have no Catrobat forward descendants except self-relation$/
   */
  public function theUploadedProgramShouldHaveNoCatrobatForwardDescendantsExceptSelfRelation()
  {
    $json = json_decode($this->getKernelBrowser()->getResponse()->getContent(), true);
    $this->theProgramShouldHaveNoCatrobatForwardDescendantsExceptSelfRelation($json['projectId']);
  }

  /**
   * @Then /^the uploaded program should have no further Catrobat forward descendants$/
   */
  public function theUploadedProgramShouldHaveNoFurtherCatrobatForwardDescendants()
  {
    $json = json_decode($this->getKernelBrowser()->getResponse()->getContent(), true);
    $this->theProgramShouldHaveNoFurtherCatrobatForwardDescendants($json['projectId']);
  }

  /**
   * @Then /^the uploaded program should have RemixOf "([^"]*)" in the xml$/
   *
   * @param $value
   *
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function theUploadedProgramShouldHaveRemixOfInTheXml($value)
  {
    $json = json_decode($this->getKernelBrowser()->getResponse()->getContent(), true);

    $this->theProgramShouldHaveRemixofInTheXml($json['projectId'], $value);
  }

  /**
   * @Given /^I want to upload a program$/
   */
  public function iWantToUploadAProgram()
  {
  }

  /**
   * @Given /^I have no parameters$/
   */
  public function iHaveNoParameters()
  {
  }

  /**
   * @When /^I upload a standard catrobat program$/
   */
  public function iUploadAStandardCatrobatProgram()
  {
    $user = $this->insertUser();
    $file = $this->getStandardProgramFile();
    $response = $this->upload($file, $user, '1');
    Assert::assertEquals(200, $response->getStatusCode(), 'Wrong response code. '.$response->getContent());
  }

  /**
   * @Then /^I should get no programs$/
   */
  public function iShouldGetNoPrograms()
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
  public function iShouldGetFollowingPrograms(TableNode $table)
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
   * @Then /^the uploaded program should have no further Catrobat forward ancestors$/
   */
  public function theUploadedProgramShouldHaveNoFurtherCatrobatForwardAncestors()
  {
    $json = json_decode($this->getKernelBrowser()->getResponse()->getContent(), true);
    $this->theProgramShouldHaveNoFurtherCatrobatForwardAncestors($json['projectId']);
  }

  /**
   * @When /^I start an apk generation of my program$/
   */
  public function iStartAnApkGenerationOfMyProgram()
  {
    $id = 1;
    $this->iGetFrom('/app/ci/build/'.$id);
    $response = $this->getKernelBrowser()->getResponse();
    Assert::assertEquals(200, $response->getStatusCode(), 'Wrong response code. '.$response->getContent());
  }

  /**
   * @Then /^the apk file will not be found$/
   */
  public function theApkFileWillNotBeFound()
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
  public function iReportABuildError()
  {
    $id = 1;
    $url = '/app/ci/failed/'.$id.'?token=UPLOADTOKEN';
    $this->iGetFrom($url);
    Assert::assertEquals(200, $this->getKernelBrowser()
      ->getResponse()
      ->getStatusCode());
  }

  /**
   * @When /^I GET "([^"]*)" with program id "([^"]*)"$/
   *
   * @param $uri
   * @param mixed $id
   */
  public function iGetWithProgramID($uri, $id)
  {
    $uri = str_replace('@id@', $id, $uri);

    $this->iGetFrom($uri);
  }

  /**
   * @When /^I get the most recent programs$/
   */
  public function iGetTheMostRecentPrograms()
  {
    $this->iGetFrom('/app/api/projects/recent.json');
  }

  /**
   * @When /^I get the most recent programs with limit "([^"]*)" and offset "([^"]*)"$/
   *
   * @param $limit
   * @param $offset
   */
  public function iGetTheMostRecentProgramsWithLimitAndOffset($limit, $offset)
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
  public function iHaveDownloadedAValidProgram()
  {
    $id = 1;
    $this->iGetFrom('/app/download/'.$id.'.catrobat');
    $this->iShouldReceiveAProjectFile();
    $this->theResponseCodeShouldBe(200);
  }

  /**
   * @Then /^will get the following JSON:$/
   */
  public function willGetTheFollowingJson(PyStringNode $string)
  {
    $response = $this->getKernelBrowser()->getResponse();
    Assert::assertEquals(200, $response->getStatusCode());

    $pattern = json_encode(json_decode($string));
    $pattern = str_replace('\\', '\\\\', $pattern);
    Assert::assertRegExp($pattern, $response->getContent());
  }

  /**
   * @Then /^I should see (\d+) outgoing emails$/
   *
   * @param $email_amount
   */
  public function iShouldSeeOutgoingEmailsInTheProfiler($email_amount)
  {
    $profile = $this->getSymfonyProfile();
    $collector = $profile->getCollector('swiftmailer');
    Assert::assertEquals($email_amount, $collector->getMessageCount());
  }

  /**
   * @Then /^I should see a email with recipient "([^"]*)"$/
   *
   * @param $recipient
   */
  public function iShouldSeeAEmailWithRecipient($recipient)
  {
    $profile = $this->getSymfonyProfile();
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
   * @param $role
   */
  public function iAmAUserWithRole($role)
  {
    $this->insertUser([
      'role' => $role,
      'name' => 'generatedBehatUser',
    ]);

    $client = $this->getKernelBrowser();
    $client->getCookieJar()->set(new Cookie(session_name(), true));

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
   * @param $needle
   */
  public function theResponseShouldContain($needle)
  {
    if (false === strpos($this->getKernelBrowser()
      ->getResponse()
      ->getContent(), $needle)
    ) {
      Assert::assertTrue(false, $needle.' not found in the response ');
    }
  }

  /**
   * @Then /^the client response should contain the elements:$/
   */
  public function theResponseShouldContainTheElements(TableNode $table)
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
   * @param $needle
   */
  public function theResponseShouldNotContain($needle)
  {
    Assert::assertNotContains($needle, $this->getKernelBrowser()->getResponse()->getContent());
  }

  /**
   * @When /^I update this program$/
   */
  public function iUpdateThisProgram()
  {
    $pm = $this->getProgramManager();
    $program = $pm->find(1);
    if (null === $program)
    {
      throw new PendingException('last program not found');
    }
    $file = $this->generateProgramFileWith([
      'name' => $program->getName(),
    ]);
    $this->upload($file, null);
  }

  /**
   * @Given /^I am a logged in as super admin$/
   */
  public function iAmALoggedInAsSuperAdmin()
  {
    $this->iAmAUserWithRole('ROLE_SUPER_ADMIN');
  }

  /**
   * @Given /^I am logged in as normal user$/
   */
  public function iAmLoggedInAsNormalUser()
  {
    $this->iAmAUserWithRole('ROLE_USER');
  }

  /**
   * @Given /^I am a logged in as admin$/
   */
  public function iAmALoggedInAsAdmin()
  {
    $this->iAmAUserWithRole('ROLE_ADMIN');
  }

  /**
   * @Then /^URI from "([^"]*)" should be "([^"]*)"$/
   *
   * @param $arg1
   * @param $arg2
   */
  public function uriFromShouldBe($arg1, $arg2)
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
   * @param $headerKey
   * @param $headerValue
   */
  public function theResponseHeadershouldContainTheKeyWithTheValue($headerKey, $headerValue)
  {
    $headers = $this->getKernelBrowser()->getResponse()->headers;
    Assert::assertEquals($headerValue, $headers->get($headerKey),
      'expected: '.$headerKey.': '.$headerValue.
      "\nget: ".$headerKey.': '.$headers->get($headerKey));
  }

  /**
   * @Then the returned json object with id :id will be:
   *
   * @param $id
   */
  public function theReturnedJsonObjectWithIdWillBe($id, PyStringNode $string)
  {
    $response = $this->getKernelBrowser()->getResponse();

    $res_array = (array) json_decode($response->getContent());

    $res_array['projectId'] = $id;

    Assert::assertJsonStringEqualsJsonString($string->getRaw(), json_encode($res_array), '');
  }

  /**
   * @Then /^the response code will be "([^"]*)"$/
   *
   * @param $code
   */
  public function theResponseCodeWillBe($code)
  {
    $response = $this->getKernelBrowser()->getResponse();
    Assert::assertEquals($code, $response->getStatusCode(), 'Wrong response code. '.$response->getContent());
  }

  /**
   * @When /^searching for "([^"]*)"$/
   *
   * @param $arg1
   */
  public function searchingFor($arg1)
  {
    $this->method = 'GET';
    $this->url = '/app/api/projects/search.json';
    $this->request_parameters = ['q' => $arg1, 'offset' => 0, 'limit' => 10];
    $this->iInvokeTheRequest();
  }

  /**
   * @Then /^the program should get (.*)$/
   *
   * @param $result
   */
  public function theProgramShouldGet($result)
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
   * @param $arg1
   */
  public function iShouldGetATotalOfProjects($arg1)
  {
    $response = $this->getKernelBrowser()->getResponse();
    $responseArray = json_decode($response->getContent(), true);
    Assert::assertEquals(
      $arg1, $responseArray['CatrobatInformation']['TotalProjects'],
      'Wrong number of total projects'
    );
  }

  /**
   * @Then /^I should get user-specific recommended projects$/
   */
  public function iShouldGetUserSpecificRecommendedProjects()
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
  public function iShouldGetNoUserSpecificRecommendedProjects()
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
   * @param $arg1
   */
  public function iUseTheLimit($arg1)
  {
    $this->iHaveAParameterWithValue('limit', $arg1);
  }

  /**
   * @Given /^I use the offset "([^"]*)"$/
   *
   * @param $arg1
   */
  public function iUseTheOffset($arg1)
  {
    $this->iHaveAParameterWithValue('offset', $arg1);
  }

  /**
   * @Then /^I should get programs in the following order:$/
   */
  public function iShouldGetProgramsInTheFollowingOrder(TableNode $table)
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
   * @param $program_count
   */
  public function iShouldGetProgramsInRandomOrder($program_count, TableNode $table)
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
  public function iShouldGetTheProgramsInRandomOrder($program_list)
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
  public function iShouldGetThePrograms($program_list)
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
   * @param $code
   */
  public function theResponseCodeShouldBe($code)
  {
    $response = $this->getKernelBrowser()->getResponse();
    Assert::assertEquals($code, $response->getStatusCode(), 'Wrong response code. '.$response->getContent());
  }

  /**
   * @Given /^I store the following json object as "([^"]*)":$/
   */
  public function iStoreTheFollowingJsonObjectAs(string $name, PyStringNode $json)
  {
    $this->stored_json[$name] = $json->getRaw();
  }

  /**
   * @Then /^I should get the stored json object "([^"]*)"$/
   */
  public function iShouldGetTheStoredJsonObject(string $name)
  {
    $response = $this->getKernelBrowser()->getResponse();
    $this->assertJsonRegex($this->stored_json[$name], $response->getContent());
  }

  /**
   * @Then /^I should get (\d+) programs in the following order:$/
   *
   * @param $program_count
   */
  public function iShouldGetScratchProgramsInTheFollowingOrder($program_count, TableNode $table)
  {
    $response = $this->getKernelBrowser()->getResponse();

    $responseArray = json_decode($response->getContent(), true);
    $programs = $table->getHash();

    $returned_programs = $responseArray['CatrobatProjects'];
    $scratch_programs_count = count($programs);
    Assert::assertEquals($program_count, $scratch_programs_count, 'Wrong number of Scratch programs');

    $expected_programs = $table->getHash();
    Assert::assertEquals(count($returned_programs), count($expected_programs), 'Number of returned programs should be '.count($returned_programs));

    for ($i = 0; $i < count($returned_programs); ++$i)
    {
      Assert::assertEquals(
        $expected_programs[$i]['Name'], $returned_programs[$i]['ProjectName'],
        'Wrong order of results'
      );
    }
  }

  /**
   * @Given /^I have a parameter "([^"]*)" with an invalid md5checksum of my file$/
   *
   * @param $parameter
   */
  public function iHaveAParameterWithAnInvalidMdchecksumOfMyFile($parameter)
  {
    $this->request_parameters[$parameter] = 'INVALIDCHECKSUM';
  }

  /**
   * @Given /^I have a parameter "([^"]*)" with the md5checksum of "([^"]*)"$/
   *
   * @param $parameter
   * @param $file
   */
  public function iHaveAParameterWithTheMdChecksumOf($parameter, $file = null)
  {
    $this->request_parameters[$parameter] = md5_file($this->request_files[0]->getPathname());
  }

  /**
   * @Given /^I have the POST parameters:$/
   */
  public function iHaveThePostParameters(TableNode $table)
  {
    foreach ($table->getHash() as $parameter)
    {
      $this->request_parameters[$parameter['name']] = $parameter['value'];
    }
  }

  /**
   * @Then /^i should receive a project file$/
   */
  public function iShouldReceiveAProjectFile()
  {
    $content_type = $this->getKernelBrowser()->getResponse()->headers->get('Content-Type');
    Assert::assertEquals('application/zip', $content_type);
  }

  /**
   * @When /^I have a parameter "([^"]*)" with the returned projectId$/
   *
   * @param $name
   */
  public function iHaveAParameterWithTheReturnedProjectid($name)
  {
    $response = json_decode($this->getKernelBrowser()
      ->getResponse()
      ->getContent(), true);
    $this->request_parameters[$name] = $response['projectId'];
  }

  /**
   * @Then it should be updated
   */
  public function itShouldBeUpdated()
  {
    $last_json = json_decode($this->last_response, true);
    $json = json_decode($this->getKernelBrowser()->getResponse()->getContent(), true);
    Assert::assertEquals($last_json['projectId'], $json['projectId'],
      $this->getKernelBrowser()->getResponse()->getContent()
    );
  }

  /**
   * @Given /^the upload problem "([^"]*)"$/
   *
   * @param $problem
   */
  public function theUploadProblem($problem)
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
   * @Given I try to upload a program with unnecessary files
   */
  public function iTryToUploadAProgramWithUnnecessaryFiles()
  {
    $this->sendUploadRequest($this->FIXTURES_DIR.'unnecessaryFiles.catrobat');
  }

  /**
   * @Given I try to upload a program with scenes and unnecessary files
   */
  public function iTryToUploadAProgramWithScenesAndUnnecessaryFiles()
  {
    $this->sendUploadRequest($this->FIXTURES_DIR.'unnecessaryFilesInScenes.catrobat');
  }

  /**
   * @When I upload this program with id :id
   *
   * @param mixed $id
   */
  public function iUploadThisProgramWithId($id)
  {
    if (array_key_exists('deviceLanguage', $this->request_parameters))
    {
      $response = $this->upload(sys_get_temp_dir().'/program_generated.catrobat', null,
        $id, 'pocketcode', $this->request_parameters);
    }
    else
    {
      $response = $this->upload(sys_get_temp_dir().'/program_generated.catrobat', null, $id);
    }

    $resp_array = (array) json_decode($response->getContent());
    $resp_array['projectId'] = $id;
    $this->getKernelBrowser()->getResponse()->setContent(json_encode($resp_array));
  }

  /**
   * @When /^I upload this program$/
   */
  public function iUploadThisProgram()
  {
    if (array_key_exists('deviceLanguage', $this->request_parameters))
    {
      $this->upload(sys_get_temp_dir().'/program_generated.catrobat', null,
        '', 'pocketcode', $this->request_parameters);
    }
    else
    {
      $this->upload(sys_get_temp_dir().'/program_generated.catrobat', null);
    }
  }

  /**
   * @When /^I upload a program$/
   */
  public function iUploadAProgram()
  {
    $user = $this->username ? $this->getUserManager()->findUserByUsername($this->username) : null;
    $this->upload(sys_get_temp_dir().'/program_generated.catrobat', $user);
  }

  /**
   * @When /^I upload the program with the id "([^"]*)"$/
   *
   * @param mixed $id
   */
  public function iUploadAProgramWithId($id)
  {
    $user = $this->username ? $this->getUserManager()->findUserByUsername($this->username) : null;
    $this->upload(sys_get_temp_dir().'/program_generated.catrobat', $user, $id);
  }

  /**
   * @When /^I upload the program with the id "([^"]*)" and name "([^"]*)"$/
   *
   * @param mixed $id
   * @param mixed $name
   */
  public function iUploadAProgramWithIdAndName($id, $name)
  {
    $user = $this->username ? $this->getUserManager()->findUserByUsername($this->username) : null;
    $this->upload(sys_get_temp_dir().'/program_generated.catrobat', $user, $id);

    /** @var Program $project */
    $project = $this->getProgramManager()->find(['id' => $id]);
    $project->setName($name);
  }

  /**
   * @When /^I upload the program again without extensions$/
   */
  public function iUploadTheProgramAgainWithoutExtensions()
  {
    $this->iHaveAProjectWithAs('name', 'extensions');
    $this->iUploadAProgram();
  }

  /**
   * @When /^I upload another program with name set to "([^"]*)" and url set to "([^"]*)"$/
   *
   * @param $name
   * @param $url
   */
  public function iUploadAnotherProgramWithNameSetToAndUrlSetTo($name, $url)
  {
    $this->iHaveAProjectWithAsTwoHeaderFields('name', $name, 'url', $url);
    $this->iUploadAProgram();
  }

  /**
   * @When I upload another program with name set to :arg1, url set to :arg2 \
   *       and catrobatLanguageVersion set to :arg3
   *
   * @param $name
   * @param $url
   * @param $catrobat_language_version
   */
  public function iUploadAnotherProgramWithNameSetToUrlSetToAndCatrobatLanguageVersionSetTo(
    $name, $url, $catrobat_language_version
  ) {
    $this->iHaveAProjectWithAsMultipleHeaderFields('name', $name, 'url', $url,
      'catrobatLanguageVersion', $catrobat_language_version);
    $this->iUploadAProgram();
  }

  /**
   * @When /^I upload this program again with the tags "([^"]*)"$/
   *
   * @param $tags
   */
  public function iUploadThisProgramAgainWithTheTags($tags)
  {
    $this->generateProgramFileWith([
      'tags' => $tags,
    ]);
    $file = sys_get_temp_dir().'/program_generated.catrobat';
    $this->upload($file, null, '', 'pocketcode', $this->request_parameters);
  }

  /**
   * @Given /^I have a parameter "([^"]*)" with value "([^"]*)"$/
   *
   * @param $name
   * @param $value
   */
  public function iHaveAParameterWithValue($name, $value)
  {
    $this->request_parameters[$name] = $value;
  }

  /**
   * @Given /^a catrobat file is attached to the request$/
   */
  public function iAttachACatrobatFile()
  {
    $filepath = $this->FIXTURES_DIR.'test.catrobat';
    Assert::assertTrue(file_exists($filepath), 'File not found');
    $this->request_files[0] = new UploadedFile($filepath, 'test.catrobat');
  }

  /**
   * @Given /^the POST parameter "([^"]*)" contains the MD5 sum of the attached file$/
   *
   * @param $arg1
   */
  public function thePostParameterContainsTheMdSumOfTheGivenFile($arg1)
  {
    $this->request_parameters[$arg1] = md5_file($this->request_files[0]->getPathname());
  }

  /**
   * @Given /^the registration problem "([^"]*)"$/
   * @Given /^there is a registration problem ([^"]*)$/
   *
   * @param $problem
   */
  public function thereIsARegistrationProblem($problem)
  {
    switch ($problem)
    {
      case 'no password given':
        $this->method = 'POST';
        $this->url = '/app/api/loginOrRegister/loginOrRegister.json';
        $this->request_parameters['registrationUsername'] = 'Someone';
        $this->request_parameters['registrationEmail'] = 'someone@pocketcode.org';
        break;
      default:
        throw new PendingException('No implementation of case "'.$problem.'"');
    }
  }

  /**
   * @Given /^the check token problem "([^"]*)"$/
   * @When /^there is a check token problem ([^"]*)$/
   *
   * @param $problem
   */
  public function thereIsACheckTokenProblem($problem)
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
   * @param $name
   * @param $value
   */
  public function iHaveAParameterWithTheTagId($name, $value)
  {
    $this->request_parameters[$name] = $value;
  }

  /**
   * @Given /^I use the "([^"]*)" app$/
   *
   * @param $language
   */
  public function iUseTheApp($language)
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

    $this->iHaveAParameterWithValue('deviceLanguage', $deviceLanguage);
  }

  /**
   * @Given /^the HTTP Request:$/
   * @Given /^I have the HTTP Request:$/
   */
  public function iHaveTheHttpRequest(TableNode $table)
  {
    $values = $table->getRowsHash();
    $this->method = $values['Method'];
    $this->url = $values['Url'];
  }

  /**
   * @Given /^the POST parameters:$/
   * @Given /^I use the POST parameters:$/
   */
  public function iUseThePostParameters(TableNode $table)
  {
    $values = $table->getRowsHash();
    $this->request_parameters = $values;
  }

  /**
   * @Given /^the GET parameters:$/
   * @Given /^I use the GET parameters:$/
   */
  public function iUseTheGetParameters(TableNode $table)
  {
    $values = $table->getRowsHash();
    $this->request_parameters = $values;
  }

  /**
   * @Given /^the server name is "([^"]*)"$/
   *
   * @param $name
   */
  public function theServerNameIs($name)
  {
    $this->request_server['HTTP_HOST'] = $name;
  }

  /**
   * @Given /^I use a secure connection$/
   */
  public function iUseASecureConnection()
  {
    $this->request_server['HTTPS'] = true;
  }

  /**
   * @Given /^I have a program with "([^"]*)" set to "([^"]*)" and "([^"]*)" set to "([^"]*)"$/
   *
   * @param $key1
   * @param $value1
   * @param $key2
   * @param $value2
   */
  public function iHaveAProjectWithAsTwoHeaderFields($key1, $value1, $key2, $value2)
  {
    $this->generateProgramFileWith([
      $key1 => $value1,
      $key2 => $value2,
    ]);
  }

  /**
   * @Given I have a program with :key1 set to :value1, :key2 set to :value2 and :key3 set to :value3
   *
   * @param $key1
   * @param $value1
   * @param $key2
   * @param $value2
   * @param $key3
   * @param $value3
   */
  public function iHaveAProjectWithAsMultipleHeaderFields($key1, $value1, $key2, $value2, $key3, $value3)
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
   * @param $filename
   */
  public function iHaveAFile($filename)
  {
    $filepath = './src/Catrobat/ApiBundle/Features/Fixtures/'.$filename;
    Assert::assertTrue(file_exists($filepath), 'File not found');
    $this->request_files[] = new UploadedFile($filepath, $filename);
  }

  /**
   * @Given /^I have a valid Catrobat file$/
   */
  public function iHaveAValidCatrobatFile()
  {
    $filepath = $this->FIXTURES_DIR.'test.catrobat';
    Assert::assertTrue(file_exists($filepath), 'File not found');
    $this->request_files = [];
    $this->request_files[] = new UploadedFile($filepath, 'test.catrobat');
  }

  /**
   * @Given /^I have otherwise valid registration parameters$/
   */
  public function iHaveOtherwiseValidRegistrationParameters()
  {
    $this->prepareValidRegistrationParameters();
  }

  /**
   * @Given /^I have a project with "([^"]*)" set to "([^"]*)"$/
   * @Given /^I have a program with "([^"]*)" set to "([^"]*)"$/
   *
   * @param $key
   * @param $value
   */
  public function iHaveAProjectWithAs($key, $value)
  {
    $this->generateProgramFileWith([
      $key => $value,
    ]);
  }

  /**
   * @Then I should receive the following programs:
   */
  public function iShouldReceiveTheFollowingPrograms(TableNode $table)
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
   * @param $arg1
   */
  public function theTotalNumberOfFoundProjectsShouldBe($arg1)
  {
    $response = $this->getKernelBrowser()->getResponse();
    $responseArray = json_decode($response->getContent(), true);
    Assert::assertEquals($arg1, $responseArray['CatrobatInformation']['TotalProjects']);
  }

  /**
   * @Then I should receive my program
   */
  public function iShouldReceiveMyProgram()
  {
    $response = $this->getKernelBrowser()->getResponse();
    $responseArray = json_decode($response->getContent(), true);
    $returned_programs = $responseArray['CatrobatProjects'];
    Assert::assertEquals('test', $returned_programs[0]['ProjectName'], 'Could not find the program');
  }

  /**
   * @When I update my program
   */
  public function iUpdateMyProgram()
  {
    $file = $this->getDefaultProgramFile();
    $this->upload($file, $this->getUserDataFixtures()->getCurrentUser());
  }

  /**
   * @When I submit a game with id :id
   * @Given I submitted a game with id :arg1
   *
   * @param $id
   */
  public function iSubmitAGame($id)
  {
    $file = $this->getDefaultProgramFile();
    $this->submit($file, $this->getUserDataFixtures()->getCurrentUser(), $id);
  }

  /**
   * @Then I should get the url to the google form
   */
  public function iShouldGetTheUrlToTheGoogleForm()
  {
    $answer = json_decode($this->getKernelBrowser()
      ->getResponse()
      ->getContent(), true);
    Assert::assertArrayHasKey('form', $answer);
    Assert::assertEquals('https://catrob.at/url/to/form', $answer['form']);
  }

  /**
   * @When /^I submit the program$/
   */
  public function iSubmitTheProgram()
  {
    $link = $this->last_response->filter('#gamejam-submission')
      ->parents()
      ->link()
    ;
    $this->last_response = $this->getKernelBrowser()->click($link);
  }

  /**
   * @When /^I login$/
   */
  public function iLogin()
  {
    $loginButton = $this->last_response;
    $form = $loginButton->selectButton('Login')->form();
    $form['_username'] = 'Generated';
    $form['_password'] = 'generated';
    $this->last_response = $this->getKernelBrowser()->submit($form);
  }

  /**
   * @Then /^I should be on the details page of my program$/
   */
  public function iShouldBeRedirectedToTheDetailsPageOfMyProgram()
  {
    Assert::assertEquals('/app/project/1', $this->getKernelBrowser()->getRequest()->getPathInfo());
  }

  /**
   * @When /^I visit the details page of a program from another user$/
   *
   * @throws Exception
   */
  public function iVisitTheDetailsPageOfAProgramFromAnotherUser()
  {
    $other = $this->insertUser([
      'name' => 'other',
    ]);
    $this->insertProject([
      'name' => 'other program',
      'owned by' => $other,
    ]);
    $this->last_response = $this
      ->getKernelBrowser()
      ->request('GET', '/app/project/1')
    ;
  }

  /**
   * @Given /^I submit a program to this gamejam$/
   *
   * @throws Exception
   */
  public function iSubmitAProgramToThisGamejam()
  {
    if (null == $this->my_program)
    {
      $this->my_program = $this->insertProject([
        'name' => 'My Program',
        'owned by' => $this->getUserDataFixtures()->getCurrentUser(),
      ]);
    }
    $this->last_response = $this
      ->getKernelBrowser()
      ->request('GET', '/app/project/1')
    ;
    $link = $this->last_response->filter('#gamejam-submission')
      ->parents()
      ->link()
    ;
    $this->last_response = $this->getKernelBrowser()->click($link);
  }

  /**
   * @When /^I visit the details page of my program$/
   *
   * @throws Exception
   */
  public function iVisitTheDetailsPageOfMyProgram()
  {
    if (null == $this->my_program)
    {
      $this->insertProject([
        'name' => 'My Program',
        'owned by' => $this->getUserDataFixtures()->getCurrentUser(),
      ]);
    }
    $this->last_response = $this->getKernelBrowser()->request('GET', '/app/project/1')
    ;
  }

  /**
   * @Then /^There should be a button to submit it to the jam$/
   */
  public function thereShouldBeAButtonToSubmitItToTheJam()
  {
    Assert::assertEquals(200, $this->getKernelBrowser()
      ->getResponse()
      ->getStatusCode());
    Assert::assertEquals(1, $this->last_response->filter('#gamejam-submission')->count());
  }

  /**
   * @Then /^There should not be a button to submit it to the jam$/
   */
  public function thereShouldNotBeAButtonToSubmitItToTheJam()
  {
    Assert::assertEquals(200, $this->getKernelBrowser()
      ->getResponse()
      ->getStatusCode());
    Assert::assertEquals(0, $this->last_response->filter('#gamejam-submission')->count());
  }

  /**
   * @Then /^There should be a div with whats the gamejam$/
   */
  public function thereShouldBeADivWithWhatsTheGamejam()
  {
    Assert::assertEquals(200, $this->getKernelBrowser()
      ->getResponse()
      ->getStatusCode());
    Assert::assertEquals(1, $this->last_response->filter('#gamejam-whats')->count());
  }

  /**
   * @Then /^There should not be a div with whats the gamejam$/
   */
  public function thereShouldNotBeADivWithWhatsTheGamejam()
  {
    Assert::assertEquals(200, $this->getKernelBrowser()
      ->getResponse()
      ->getStatusCode());
    Assert::assertEquals(0, $this->last_response->filter('#gamejam-whats')->count());
  }

  /**
   * @When /^I submit my program to a gamejam$/
   *
   * @throws Exception
   */
  public function iSubmitMyProgramToAGamejam()
  {
    $this->iAmLoggedIn();

    if (null == $this->game_jam)
    {
      $this->game_jam = $this->insertDefaultGameJam([
        'formurl' => 'https://localhost/url/to/form',
      ]);
    }
    if (null == $this->my_program)
    {
      $this->my_program = $this->insertProject([
        'name' => 'My Program',
        'owned by' => $this->getUserDataFixtures()->getCurrentUser(),
      ]);
    }

    $this->getKernelBrowser()->followRedirects(false);
    $this->last_response = $this
      ->getKernelBrowser()
      ->request('GET', '/app/project/1')
    ;
    $link = $this->last_response->filter('#gamejam-submission')
      ->parents()
      ->link()
    ;
    $this->last_response = $this->getKernelBrowser()->click($link);
  }

  /**
   * @Then /^I should be redirected to the google form$/
   */
  public function iShouldBeRedirectedToTheGoogleForm()
  {
    Assert::assertTrue($this->getKernelBrowser()->getResponse() instanceof RedirectResponse);
    Assert::assertEquals('https://localhost/url/to/form', $this->getKernelBrowser()->getResponse()->headers->get('location'));
  }

  /**
   * @When I submit a game which gets the id :arg1
   *
   * @param $arg1
   */
  public function iSubmitAGameWhichGetsTheId($arg1)
  {
    $file = $this->getDefaultProgramFile();
    $this->submit($file, $this->getUserDataFixtures()->getCurrentUser(), $arg1);
  }

  /**
   * @Then The returned url with id :id should be
   *
   * @param mixed $id
   */
  public function theReturnedUrlShouldBe($id, PyStringNode $string)
  {
    $answer = (array) json_decode($this->getKernelBrowser()->getResponse()->getContent());

    $form_url = $answer['form'];
    $form_url = preg_replace('/&id=.*?&mail=/', '&id='.$id.'&mail=', $form_url, -1);

    Assert::assertEquals($string->getRaw(), $form_url);
  }

  /**
   * @Then The submission should be rejected
   */
  public function theSubmissionShouldBeRejected()
  {
    $answer = json_decode($this->getKernelBrowser()
      ->getResponse()
      ->getContent(), true);
    Assert::assertNotEquals('200', $answer['statusCode']);
  }

  /**
   * @Then The message should be:
   */
  public function theMessageShouldBe(PyStringNode $string)
  {
    $answer = json_decode($this->getKernelBrowser()
      ->getResponse()
      ->getContent(), true);
    Assert::assertEquals($string->getRaw(), $answer['answer']);
  }

  /**
   * @When I upload my game
   */
  public function iUploadMyGame()
  {
    $file = $this->getDefaultProgramFile();
    $this->upload($file, $this->getUserDataFixtures()->getCurrentUser());
  }

  /**
   * @Then I should not get the url to the google form
   */
  public function iShouldNotGetTheUrlToTheGoogleForm()
  {
    $answer = json_decode($this->getKernelBrowser()
      ->getResponse()
      ->getContent(), true);
    Assert::assertArrayNotHasKey('form', $answer);
  }

  /**
   * @Given I already submitted my game with id :id
   *
   * @param $id
   */
  public function iAlreadySubmittedMyGame($id)
  {
    $file = $this->getDefaultProgramFile();
    $this->last_response = $this->submit($file, $this->getUserDataFixtures()->getCurrentUser(), $id)->getContent();
  }

  /**
   * @Given I already filled the google form with id :id
   *
   * @param $id
   */
  public function iAlreadyFilledTheGoogleForm($id)
  {
    $this->getKernelBrowser()->request('GET', '/app/api/gamejam/finalize/'.$id);
    Assert::assertEquals('200', $this->getKernelBrowser()
      ->getResponse()
      ->getStatusCode());
  }

  /**
   * @When I resubmit my game
   */
  public function iResubmitMyGame()
  {
    $file = $this->getDefaultProgramFile();
    $this->submit($file, $this->getUserDataFixtures()->getCurrentUser());
  }

  /**
   * @When I fill out the google form
   */
  public function iFillOutTheGoogleForm()
  {
    $this->getKernelBrowser()->request('GET', '/app/api/gamejam/finalize/1');
    Assert::assertEquals('200', $this->getKernelBrowser()
      ->getResponse()
      ->getStatusCode());
  }

  /**
   * @Then /^I should see the message "([^"]*)"$/
   *
   * @param $arg1
   */
  public function iShouldSeeAMessage($arg1)
  {
    Assert::assertContains($arg1, $this->getKernelBrowser()->getResponse()->getContent());
  }

  /**
   * @Then /^I should see the hashtag "([^"]*)" in the program description$/
   *
   * @param $hashtag
   */
  public function iShouldSeeTheHashtagInTheProgramDescription($hashtag)
  {
    Assert::assertContains($hashtag, $this->getKernelBrowser()
      ->getResponse()
      ->getContent());
  }

  // to df ->

  /**
   * @Then /^the program "([^"]*)" should be a remix root$/
   *
   * @param $program_id
   */
  public function theProgramShouldBeARemixRoot($program_id)
  {
    $program_manager = $this->getProgramManager();
    $uploaded_program = $program_manager->find($program_id);
    Assert::assertTrue($uploaded_program->isRemixRoot());
  }

  /**
   * @Then /^the program "([^"]*)" should not be a remix root$/
   *
   * @param $program_id
   */
  public function theProgramShouldNotBeARemixRoot($program_id)
  {
    /**
     * @var Program
     */
    $program_manager = $this->getProgramManager();
    $uploaded_program = $program_manager->find($program_id);
    Assert::assertFalse($uploaded_program->isRemixRoot());
  }

  /**
   * @Given /^the program "([^"]*)" should have a Scratch parent having id "([^"]*)"$/
   *
   * @param $program_id
   * @param $scratch_parent_id
   */
  public function theProgramShouldHaveAScratchParentHavingScratchID($program_id, $scratch_parent_id)
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
   * @param $program_id
   */
  public function theProgramShouldHaveNoFurtherScratchParents($program_id)
  {
    $direct_edge_relations = $this->getScratchProgramRemixRepository()->findBy([
      'catrobat_child_id' => $program_id,
    ]);

    $further_scratch_parent_relations = array_filter($direct_edge_relations,
      function (ScratchProgramRemixRelation $relation)
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
   * @param $program_id
   * @param $ancestor_program_id
   * @param $depth
   */
  public function theProgramShouldHaveACatrobatForwardAncestorHavingIdAndDepth($program_id, $ancestor_program_id, $depth)
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
   * @param $program_id
   * @param $backward_parent_program_id
   */
  public function theProgramShouldHaveACatrobatBackwardParentHavingId($program_id, $backward_parent_program_id)
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
   * @param $program_id
   */
  public function theProgramShouldHaveNoCatrobatForwardAncestorsExceptSelfRelation($program_id)
  {
    $forward_ancestors_including_self_referencing_relation = $this
      ->getProgramRemixForwardRepository()
      ->findBy(['descendant_id' => $program_id])
    ;

    Assert::assertCount(0, array_filter($forward_ancestors_including_self_referencing_relation,
      function (ProgramRemixRelation $relation)
      {
        return $relation->getDepth() >= 1;
      }));
  }

  /**
   * @Then /^the program "([^"]*)" should have no further Catrobat forward ancestors$/
   *
   * @param $program_id
   */
  public function theProgramShouldHaveNoFurtherCatrobatForwardAncestors($program_id)
  {
    $forward_ancestors_including_self_referencing_relation = $this
      ->getProgramRemixForwardRepository()
      ->findBy(['descendant_id' => $program_id])
    ;

    $further_forward_ancestor_relations = array_filter($forward_ancestors_including_self_referencing_relation,
      function (ProgramRemixRelation $relation)
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
   * @param $program_id
   */
  public function theProgramShouldHaveNoCatrobatBackwardParents($program_id)
  {
    $backward_parent_relations = $this->getProgramRemixBackwardRepository()->findBy(['child_id' => $program_id]);
    Assert::assertCount(0, $backward_parent_relations);
  }

  /**
   * @Then /^the program "([^"]*)" should have no further Catrobat backward parents$/
   *
   * @param $program_id
   */
  public function theProgramShouldHaveNoFurtherCatrobatBackwardParents($program_id)
  {
    $backward_parent_relations = $this->getProgramRemixBackwardRepository()->findBy(['child_id' => $program_id]);

    $further_backward_parent_relations = array_filter($backward_parent_relations,
      function (ProgramRemixBackwardRelation $relation)
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
   * @param $program_id
   */
  public function theProgramShouldHaveNoCatrobatAncestors($program_id)
  {
    $this->theProgramShouldHaveNoCatrobatForwardAncestorsExceptSelfRelation($program_id);
    $this->theProgramShouldHaveNoCatrobatBackwardParents($program_id);
  }

  /**
   * @Then /^the program "([^"]*)" should have no Scratch parents$/
   *
   * @param $program_id
   */
  public function theProgramShouldHaveNoScratchParents($program_id)
  {
    $scratch_parents = $this->getScratchProgramRemixRepository()->findBy(['catrobat_child_id' => $program_id]);
    Assert::assertCount(0, $scratch_parents);
  }

  /**
   * @Then /^the program "([^"]*)" should have a Catrobat forward descendant having id "([^"]*)" and depth "([^"]*)"$/
   *
   * @param $program_id
   * @param $descendant_program_id
   * @param $depth
   */
  public function theProgramShouldHaveCatrobatForwardDescendantHavingIdAndDepth($program_id, $descendant_program_id, $depth)
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
   * @param $program_id
   */
  public function theProgramShouldHaveNoCatrobatForwardDescendantsExceptSelfRelation($program_id)
  {
    $forward_ancestors_including_self_referencing_relation = $this
      ->getProgramRemixForwardRepository()
      ->findBy(['ancestor_id' => $program_id])
    ;

    Assert::assertCount(0, array_filter($forward_ancestors_including_self_referencing_relation,
      function (ProgramRemixRelation $relation)
      {
        return $relation->getDepth() >= 1;
      }));
  }

  /**
   * @Then /^the program "([^"]*)" should have no further Catrobat forward descendants$/
   *
   * @param $program_id
   */
  public function theProgramShouldHaveNoFurtherCatrobatForwardDescendants($program_id)
  {
    $forward_descendants_including_self_referencing_relation = $this
      ->getProgramRemixForwardRepository()
      ->findBy(['ancestor_id' => $program_id])
    ;

    $further_forward_descendant_relations = array_filter($forward_descendants_including_self_referencing_relation,
      function (ProgramRemixRelation $relation)
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
   * @param $program_id
   * @param $value
   *
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function theProgramShouldHaveRemixofInTheXml($program_id, $value)
  {
    $program_manager = $this->getProgramManager();
    /** @var Program $uploaded_program */
    $uploaded_program = $program_manager->find($program_id);
    $efr = $this->getExtractedFileRepository();
    $extracted_catrobat_file = $efr->loadProgramExtractedFile($uploaded_program);
    $project_xml_prop = $extracted_catrobat_file->getProgramXmlProperties();
    Assert::assertEquals($value, $project_xml_prop->header->remixOf->__toString());
  }

  //--------------------------------------------------------------------------------------------------------------------
  //  Error Logging
  //--------------------------------------------------------------------------------------------------------------------

  /**
   * @AfterStep
   */
  public function saveResponseToFile(AfterStepScope $scope)
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
   * @Given /^I request from a (debug|release) build of the Catroid app$/
   *
   * @param $build_type
   */
  public function iRequestFromASpecificBuildTypeOfCatroidApp($build_type)
  {
    $this->iUseTheUserAgentParameterized('0.998', 'PocketCode', '0.9.60', $build_type);
  }

  /**
   * @Given /^I request from an ios app$/
   */
  public function iRequestFromAnIOSApp()
  {
    // see org.catrobat.catroid.ui.WebViewActivity
    $platform = 'iPhone';
    $user_agent = ' Platform/'.$platform;
    $this->iUseTheUserAgent($user_agent);
  }

  /**
   * @Given /^I request from a specific "([^"]*)" themed app$/
   *
   * @param $theme
   */
  public function iUseASpecificThemedApp($theme)
  {
    $this->iUseTheUserAgentParameterized('0.998', 'PocketCode', '0.9.60',
      'release', $theme);
  }

  /**
   * @When /^I upload a catrobat program with the phiro app$/
   */
  public function iUploadACatrobatProgramWithThePhiroProApp()
  {
    $user = $this->insertUser();
    $program = $this->getStandardProgramFile();
    $response = $this->upload($program, $user, 1, 'phirocode');
    Assert::assertEquals(200, $response->getStatusCode(), 'Wrong response code. '.$response->getContent());
  }

  /**
   * @param $filepath
   */
  private function sendUploadRequest($filepath)
  {
    Assert::assertTrue(file_exists($filepath), 'File not found');

    $this->request_files = [];
    $this->request_files[] = new UploadedFile($filepath, 'unnecessaryFiles.catrobat');

    $this->iHaveAParameterWithTheMdChecksumOf('fileChecksum');
    $this->request_parameters['username'] = $this->username;
    $this->request_parameters['token'] = 'cccccccccc';
    $this->iPostTo('/app/api/upload/upload.json');
  }

  /**
   * @param      $file
   * @param      $user
   * @param null $request_param
   *
   * @return Response
   */
  private function upload($file, $user, string $desired_id = '', string $flavor = 'pocketcode', $request_param = null)
  {
    if (null == $user)
    {
      $user = $this->getUserDataFixtures()->getDefaultUser();
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
        $file = new UploadedFile($file, 'uploadedFile');
      }
      catch (Exception $e)
      {
        throw new PendingException('No case defined for '.$e);
      }
    }

    $parameters = [];
    $parameters['username'] = $user->getUsername();
    $parameters['token'] = $user->getUploadToken();
    $parameters['fileChecksum'] = md5_file($file->getPathname());

    if (isset($request_param['deviceLanguage']))
    {
      $parameters['deviceLanguage'] = $request_param['deviceLanguage'];
    }

    $client = $this->getKernelBrowser();
    $client->request('POST', '/'.$flavor.'/api/upload/upload.json', $parameters, [$file]);

    return $client->getResponse();
  }

  private function prepareValidRegistrationParameters()
  {
    $this->request_parameters['registrationUsername'] = 'newuser';
    $this->request_parameters['registrationPassword'] = 'topsecret';
    $this->request_parameters['registrationEmail'] = 'someuser@example.com';
    $this->request_parameters['registrationCountry'] = 'at';
  }

  private function iUseTheUserAgent($user_agent)
  {
    $this->request_server['HTTP_USER_AGENT'] = $user_agent;
  }

  private function iUseTheUserAgentParameterized($lang_version, $flavor, $app_version, $build_type, $theme = 'pocketcode')
  {
    // see org.catrobat.catroid.ui.WebViewActivity
    $platform = 'Android';
    $user_agent = 'Catrobat/'.$lang_version.' '.$flavor.'/'.$app_version.' Platform/'.$platform.
      ' BuildType/'.$build_type.' Theme/'.$theme;
    $this->iUseTheUserAgent($user_agent);
  }
}
