<?php

namespace Tests\behat\context;

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
   */
  private ?string $username = null;

  private string $method;

  private string $url;

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
  private array $request_server;

  private ?string $request_content;

  /**
   * @var Crawler|string|null and maybe even more types... (Should be refactored!)
   */
  private $last_response;

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
    $this->request_server = [];
    $this->request_content = null;
  }

  /**
   * @Given /^I activate the Profiler$/
   */
  public function iActivateTheProfiler(): void
  {
    $this->getKernelBrowser()->enableProfiler();
  }

  /**
   * @When /^I request "([^"]*)" "([^"]*)"$/
   * @When /^I :method :url with these parameters$/
   *
   * @param mixed $method
   * @param mixed $uri
   */
  public function iRequest($method, $uri): void
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
  public function iInvokeTheRequest(): void
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
  public function iGetFrom($url): void
  {
    $this->iRequest('GET', $url);
  }

  /**
   * @When /^I POST these parameters to "([^"]*)"$/
   *
   * @param mixed $url
   */
  public function iPostTo($url): void
  {
    $this->iRequest('POST', $url);
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
   * @When /^I upload a valid program$/
   */
  public function iUploadAValidProgram(): void
  {
    $this->iHaveAParameterWithValue('username', 'Catrobat');
    $this->iHaveAParameterWithValue('token', 'cccccccccc');
    $this->iHaveAValidCatrobatFile();
    $this->iHaveAParameterWithTheMdchecksumOf('fileChecksum');
    $this->iPostTo('/app/api/upload/upload.json');
    $this->iHaveAParameterWithTheReturnedProjectid('program');
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
   * @When /^I upload a catrobat program with the same name$/
   */
  public function iUploadACatrobatProgramWithTheSameName(): void
  {
    /** @var User|null $user */
    $user = $this->getUserManager()->findUserByUsername($this->username);
    $this->request_parameters['token'] = $user->getUploadToken();
    $this->last_response = $this->getKernelBrowser()->getResponse()->getContent()
    ;
    $this->iPostTo('/app/api/upload/upload.json');
  }

  /**
   * @When /^I upload a catrobat program$/
   */
  public function iUploadACatrobatProgram(): void
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
   * @param mixed $arg1
   */
  public function iUploadAnotherProgramUsingToken($arg1): void
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
   * @Then /^The upload should be successful$/
   */
  public function theUploadShouldBeSuccessful(): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    $responseArray = json_decode($response->getContent(), true);
    Assert::assertEquals(200, $responseArray['statusCode']);
  }

  /**
   * @Then /^the uploaded program should be a remix root$/
   */
  public function theUploadedProgramShouldBeARemixRoot(): void
  {
    $json = json_decode($this->getKernelBrowser()->getResponse()->getContent(), true);
    $this->theProgramShouldBeARemixRoot($json['projectId']);
  }

  /**
   * @Then /^It should be uploaded$/
   */
  public function itShouldBeUploaded(): void
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
   *
   * @param mixed $name
   * @param mixed $value
   */
  public function iHaveARequestHeaderWithValue($name, $value): void
  {
    $this->request_server[$name] = $value;
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
    $this->request_server['HTTP_authorization'] = 'Bearer '.$token;
  }

  /**
   * @Given I use an empty JWT Bearer token
   */
  public function iUseAnEmptyJwtBearerToken(): void
  {
    $this->request_server['HTTP_authorization'] = '';
  }

  /**
   * @Given I use an invalid JWT Bearer token
   */
  public function iUseAnInvalidJwtBearerToken(): void
  {
    $this->request_server['HTTP_authorization'] = 'Bearer invalid-token';
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
   * @param mixed $file
   * @param mixed $user
   */
  public function submit($file, $user, string $desired_id = ''): Response
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
   * @param mixed $program_attribute
   */
  public function iUploadAProgramWith($program_attribute): void
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
  public function iUploadAnInvalidProgramFile(): void
  {
    $this->upload($this->FIXTURES_DIR.'/invalid_archive.catrobat', null);
  }

  /**
   * @When I upload this project with id :id
   *
   * @param mixed $id
   */
  public function iUploadThisProject($id): void
  {
    $this->upload(sys_get_temp_dir().'/program_generated.catrobat', null, $id);
  }

  /**
   * @When /^User "([^"]*)" uploads the program$/
   * @When /^User "([^"]*)" uploads the project$/
   *
   * @param mixed $username
   */
  public function iUploadAProject($username): void
  {
    /** @var User|null $user */
    $user = $this->getUserManager()->findUserByUsername($username);
    $this->upload(sys_get_temp_dir().'/program_generated.catrobat', $user);
  }

  /**
   * @Given /^I upload the program with "([^"]*)" as name$/
   * @When /^I upload the program with "([^"]*)" as name again$/
   *
   * @param mixed $name
   */
  public function iUploadTheProgramWithAsName($name): void
  {
    $this->generateProgramFileWith([
      'name' => $name,
    ]);
    $this->upload(sys_get_temp_dir().'/program_generated.catrobat', null);
  }

  /**
   * @Then /^the uploaded program should not be a remix root$/
   */
  public function theUploadedProgramShouldNotBeARemixRoot(): void
  {
    $json = json_decode($this->getKernelBrowser()->getResponse()->getContent(), true);
    $this->theProgramShouldNotBeARemixRoot($json['projectId']);
  }

  /**
   * @Then /^the uploaded program should have remix migration date NOT NULL$/
   */
  public function theUploadedProgramShouldHaveMigrationDateNotNull(): void
  {
    $json = json_decode($this->getKernelBrowser()->getResponse()->getContent(), true);
    $program_manager = $this->getProgramManager();
    $uploaded_program = $program_manager->find($json['projectId']);
    Assert::assertNotNull($uploaded_program->getRemixMigratedAt());
  }

  /**
   * @Given /^the uploaded program should have a Scratch parent having id "([^"]*)"$/
   *
   * @param mixed $scratch_parent_id
   */
  public function theUploadedProgramShouldHaveAScratchParentHavingScratchID($scratch_parent_id): void
  {
    $json = json_decode($this->getKernelBrowser()->getResponse()->getContent(), true);
    $this->theProgramShouldHaveAScratchParentHavingScratchID($json['projectId'], $scratch_parent_id);
  }

  /**
   * @Given /^the uploaded program should have no further Scratch parents$/
   */
  public function theUploadedProgramShouldHaveNoFurtherScratchParents(): void
  {
    $json = json_decode($this->getKernelBrowser()->getResponse()->getContent(), true);
    $this->theProgramShouldHaveNoFurtherScratchParents($json['projectId']);
  }

  /**
   * @Then /^the uploaded program should have a Catrobat forward ancestor having id "([^"]*)" and depth "([^"]*)"$/
   *
   * @param mixed $id
   * @param mixed $depth
   */
  public function theUploadedProgramShouldHaveACatrobatForwardAncestorHavingIdAndDepth($id, $depth): void
  {
    $json = json_decode($this->getKernelBrowser()->getResponse()->getContent(), true);

    $this->theProgramShouldHaveACatrobatForwardAncestorHavingIdAndDepth($json['projectId'], $id, $depth);
  }

  /**
   * @Then the uploaded program should have a Catrobat forward ancestor having its own id and depth :depth
   *
   * @param mixed $depth
   */
  public function theUploadedProgramShouldHaveACatrobatForwardAncestorHavingItsOwnIdAndDepth($depth): void
  {
    $json = json_decode($this->getKernelBrowser()->getResponse()->getContent(), true);

    $this->theProgramShouldHaveACatrobatForwardAncestorHavingIdAndDepth($json['projectId'], $json['projectId'], $depth);
  }

  /**
   * @Then /^the uploaded program should have a Catrobat backward parent having id "([^"]*)"$/
   *
   * @param mixed $id
   */
  public function theUploadedProgramShouldHaveACatrobatBackwardParentHavingId($id): void
  {
    $json = json_decode($this->getKernelBrowser()->getResponse()->getContent(), true);
    $this->theProgramShouldHaveACatrobatBackwardParentHavingId($json['projectId'], $id);
  }

  /**
   * @Then /^the uploaded program should have no Catrobat forward ancestors except self-relation$/
   */
  public function theUploadedProgramShouldHaveNoCatrobatForwardAncestorsExceptSelfRelation(): void
  {
    $json = json_decode($this->getKernelBrowser()->getResponse()->getContent(), true);
    $this->theProgramShouldHaveNoCatrobatForwardAncestorsExceptSelfRelation($json['projectId']);
  }

  /**
   * @Then /^the uploaded program should have no Catrobat backward parents$/
   */
  public function theUploadedProgramShouldHaveNoCatrobatBackwardParents(): void
  {
    $json = json_decode($this->getKernelBrowser()->getResponse()->getContent(), true);
    $this->theProgramShouldHaveNoCatrobatBackwardParents($json['projectId']);
  }

  /**
   * @Then /^the uploaded program should have no further Catrobat backward parents$/
   */
  public function theUploadedProgramShouldHaveNoFurtherCatrobatBackwardParents(): void
  {
    $json = json_decode($this->getKernelBrowser()->getResponse()->getContent(), true);
    $this->theProgramShouldHaveNoFurtherCatrobatBackwardParents($json['projectId']);
  }

  /**
   * @Then /^the uploaded program should have no Catrobat ancestors except self-relation$/
   */
  public function theUploadedProgramShouldHaveNoCatrobatAncestors(): void
  {
    $json = json_decode($this->getKernelBrowser()->getResponse()->getContent(), true);
    $this->theProgramShouldHaveNoCatrobatAncestors($json['projectId']);
  }

  /**
   * @Then /^the uploaded program should have no Scratch parents$/
   */
  public function theUploadedProgramShouldHaveNoScratchParents(): void
  {
    $json = json_decode($this->getKernelBrowser()->getResponse()->getContent(), true);
    $this->theProgramShouldHaveNoScratchParents($json['projectId']);
  }

  /**
   * @Then /^the uploaded program should have a Catrobat forward descendant having id "([^"]*)" and depth "([^"]*)"$/
   *
   * @param mixed $id
   * @param mixed $depth
   */
  public function theUploadedProgramShouldHaveCatrobatForwardDescendantHavingIdAndDepth($id, $depth): void
  {
    $json = json_decode($this->getKernelBrowser()->getResponse()->getContent(), true);
    $this->theProgramShouldHaveCatrobatForwardDescendantHavingIdAndDepth($json['projectId'], $id, $depth);
  }

  /**
   * @Then /^the uploaded program should have no Catrobat forward descendants except self-relation$/
   */
  public function theUploadedProgramShouldHaveNoCatrobatForwardDescendantsExceptSelfRelation(): void
  {
    $json = json_decode($this->getKernelBrowser()->getResponse()->getContent(), true);
    $this->theProgramShouldHaveNoCatrobatForwardDescendantsExceptSelfRelation($json['projectId']);
  }

  /**
   * @Then /^the uploaded program should have no further Catrobat forward descendants$/
   */
  public function theUploadedProgramShouldHaveNoFurtherCatrobatForwardDescendants(): void
  {
    $json = json_decode($this->getKernelBrowser()->getResponse()->getContent(), true);
    $this->theProgramShouldHaveNoFurtherCatrobatForwardDescendants($json['projectId']);
  }

  /**
   * @Then /^the uploaded program should have RemixOf "([^"]*)" in the xml$/
   *
   * @param mixed $value
   *
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function theUploadedProgramShouldHaveRemixOfInTheXml($value): void
  {
    $json = json_decode($this->getKernelBrowser()->getResponse()->getContent(), true);

    $this->theProgramShouldHaveRemixofInTheXml($json['projectId'], $value);
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
   * @When /^I upload a standard catrobat program$/
   */
  public function iUploadAStandardCatrobatProgram(): void
  {
    $user = $this->insertUser();
    $file = $this->getStandardProgramFile();
    $response = $this->upload($file, $user, '1');
    Assert::assertEquals(200, $response->getStatusCode(), 'Wrong response code. '.$response->getContent());
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
   * @Then /^the uploaded program should have no further Catrobat forward ancestors$/
   */
  public function theUploadedProgramShouldHaveNoFurtherCatrobatForwardAncestors(): void
  {
    $json = json_decode($this->getKernelBrowser()->getResponse()->getContent(), true);
    $this->theProgramShouldHaveNoFurtherCatrobatForwardAncestors($json['projectId']);
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
   * @When /^I GET "([^"]*)" with program id "([^"]*)"$/
   *
   * @param mixed $id
   * @param mixed $uri
   */
  public function iGetWithProgramID($uri, $id): void
  {
    $uri = str_replace('@id@', $id, $uri);

    $this->iGetFrom($uri);
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
    Assert::assertRegExp($pattern, $response->getContent());
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
    Assert::assertNotContains($needle, $this->getKernelBrowser()->getResponse()->getContent());
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
    $this->iInvokeTheRequest();
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
    Assert::assertEquals(count($returned_programs), count($expected_programs), 'Number of returned programs should be '.count($returned_programs));

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
   * @Given /^I have a parameter "([^"]*)" with the md5checksum of "([^"]*)"$/
   *
   * @param mixed $parameter
   */
  public function iHaveAParameterWithTheMdChecksumOf($parameter): void
  {
    $this->request_parameters[$parameter] = md5_file($this->request_files[0]->getPathname());
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
   * @Then it should be updated
   */
  public function itShouldBeUpdated(): void
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
   * @Given I try to upload a program with unnecessary files
   */
  public function iTryToUploadAProgramWithUnnecessaryFiles(): void
  {
    $this->sendUploadRequest($this->FIXTURES_DIR.'unnecessaryFiles.catrobat');
  }

  /**
   * @Given I try to upload a program with scenes and unnecessary files
   */
  public function iTryToUploadAProgramWithScenesAndUnnecessaryFiles(): void
  {
    $this->sendUploadRequest($this->FIXTURES_DIR.'unnecessaryFilesInScenes.catrobat');
  }

  /**
   * @When I upload this program with id :id
   *
   * @param mixed $id
   */
  public function iUploadThisProgramWithId($id): void
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
  public function iUploadThisProgram(): void
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
  public function iUploadAProgram(): void
  {
    /** @var User|null $user */
    $user = $this->username ? $this->getUserManager()->findUserByUsername($this->username) : null;
    $this->upload(sys_get_temp_dir().'/program_generated.catrobat', $user);
  }

  /**
   * @When /^I upload the program with the id "([^"]*)"$/
   *
   * @param mixed $id
   */
  public function iUploadAProgramWithId($id): void
  {
    /** @var User|null $user */
    $user = $this->username ? $this->getUserManager()->findUserByUsername($this->username) : null;
    $this->upload(sys_get_temp_dir().'/program_generated.catrobat', $user, $id);
  }

  /**
   * @When /^I upload the program with the id "([^"]*)" and name "([^"]*)"$/
   *
   * @param mixed $id
   * @param mixed $name
   */
  public function iUploadAProgramWithIdAndName($id, $name): void
  {
    /** @var User|null $user */
    $user = $this->username ? $this->getUserManager()->findUserByUsername($this->username) : null;
    $this->upload(sys_get_temp_dir().'/program_generated.catrobat', $user, $id);

    /** @var Program $project */
    $project = $this->getProgramManager()->find($id);
    $project->setName($name);
  }

  /**
   * @When /^I upload the program again without extensions$/
   */
  public function iUploadTheProgramAgainWithoutExtensions(): void
  {
    $this->iHaveAProjectWithAs('name', 'extensions');
    $this->iUploadAProgram();
  }

  /**
   * @When /^I upload another program with name set to "([^"]*)" and url set to "([^"]*)"$/
   *
   * @param mixed $name
   * @param mixed $url
   */
  public function iUploadAnotherProgramWithNameSetToAndUrlSetTo($name, $url): void
  {
    $this->iHaveAProjectWithAsTwoHeaderFields('name', $name, 'url', $url);
    $this->iUploadAProgram();
  }

  /**
   * @When I upload another program with name set to :arg1, url set to :arg2 \
   *       and catrobatLanguageVersion set to :arg3
   *
   * @param mixed $name
   * @param mixed $url
   * @param mixed $catrobat_language_version
   */
  public function iUploadAnotherProgramWithNameSetToUrlSetToAndCatrobatLanguageVersionSetTo(
    $name, $url, $catrobat_language_version
  ): void {
    $this->iHaveAProjectWithAsMultipleHeaderFields('name', $name, 'url', $url,
      'catrobatLanguageVersion', $catrobat_language_version);
    $this->iUploadAProgram();
  }

  /**
   * @When /^I upload this program again with the tags "([^"]*)"$/
   *
   * @param mixed $tags
   */
  public function iUploadThisProgramAgainWithTheTags($tags): void
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
   * @param mixed $name
   * @param mixed $value
   */
  public function iHaveAParameterWithValue($name, $value): void
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
   * @Given /^the registration problem "([^"]*)"$/
   * @Given /^there is a registration problem ([^"]*)$/
   *
   * @param mixed $problem
   */
  public function thereIsARegistrationProblem($problem): void
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
   * @Given /^I use the "([^"]*)" app$/
   *
   * @param mixed $language
   */
  public function iUseTheApp($language): void
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
    $this->request_server['HTTP_HOST'] = $name;
  }

  /**
   * @Given /^I use a secure connection$/
   */
  public function iUseASecureConnection(): void
  {
    $this->request_server['HTTPS'] = true;
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
   * @Given /^I have a valid Catrobat file$/
   */
  public function iHaveAValidCatrobatFile(): void
  {
    $filepath = $this->FIXTURES_DIR.'test.catrobat';
    Assert::assertTrue(file_exists($filepath), 'File not found');
    $this->request_files = [];
    $this->request_files[] = new UploadedFile($filepath, 'test.catrobat');
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
   * @When I update my program
   */
  public function iUpdateMyProgram(): void
  {
    $file = $this->getDefaultProgramFile();
    $this->upload($file, $this->getUserDataFixtures()->getCurrentUser());
  }

  /**
   * @When I submit a game with id :id
   * @Given I submitted a game with id :arg1
   *
   * @param mixed $id
   */
  public function iSubmitAGame($id): void
  {
    $file = $this->getDefaultProgramFile();
    $this->submit($file, $this->getUserDataFixtures()->getCurrentUser(), $id);
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
   * @When /^I submit the program$/
   */
  public function iSubmitTheProgram(): void
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
  public function iLogin(): void
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
  public function iSubmitAProgramToThisGamejam(): void
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
  public function iVisitTheDetailsPageOfMyProgram(): void
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
  public function thereShouldBeAButtonToSubmitItToTheJam(): void
  {
    Assert::assertEquals(200, $this->getKernelBrowser()
      ->getResponse()
      ->getStatusCode());
    Assert::assertEquals(1, $this->last_response->filter('#gamejam-submission')->count());
  }

  /**
   * @Then /^There should not be a button to submit it to the jam$/
   */
  public function thereShouldNotBeAButtonToSubmitItToTheJam(): void
  {
    Assert::assertEquals(200, $this->getKernelBrowser()
      ->getResponse()
      ->getStatusCode());
    Assert::assertEquals(0, $this->last_response->filter('#gamejam-submission')->count());
  }

  /**
   * @Then /^There should be a div with whats the gamejam$/
   */
  public function thereShouldBeADivWithWhatsTheGamejam(): void
  {
    Assert::assertEquals(200, $this->getKernelBrowser()
      ->getResponse()
      ->getStatusCode());
    Assert::assertEquals(1, $this->last_response->filter('#gamejam-whats')->count());
  }

  /**
   * @Then /^There should not be a div with whats the gamejam$/
   */
  public function thereShouldNotBeADivWithWhatsTheGamejam(): void
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
  public function iSubmitMyProgramToAGamejam(): void
  {
    $this->insertDefaultGameJam([
      'formurl' => 'https://localhost/url/to/form',
    ]);

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
  public function iShouldBeRedirectedToTheGoogleForm(): void
  {
    Assert::assertTrue($this->getKernelBrowser()->getResponse() instanceof RedirectResponse);
    Assert::assertEquals('https://localhost/url/to/form', $this->getKernelBrowser()->getResponse()->headers->get('location'));
  }

  /**
   * @When I submit a game which gets the id :arg1
   *
   * @param mixed $arg1
   */
  public function iSubmitAGameWhichGetsTheId($arg1): void
  {
    $file = $this->getDefaultProgramFile();
    $this->submit($file, $this->getUserDataFixtures()->getCurrentUser(), $arg1);
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
   * @When I upload my game
   */
  public function iUploadMyGame(): void
  {
    $file = $this->getDefaultProgramFile();
    $this->upload($file, $this->getUserDataFixtures()->getCurrentUser());
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
   * @Given I already submitted my game with id :id
   *
   * @param mixed $id
   */
  public function iAlreadySubmittedMyGame($id): void
  {
    $file = $this->getDefaultProgramFile();
    $this->last_response = $this->submit($file, $this->getUserDataFixtures()->getCurrentUser(), $id)->getContent();
  }

  /**
   * @Given I already filled the google form with id :id
   *
   * @param mixed $id
   */
  public function iAlreadyFilledTheGoogleForm($id): void
  {
    $this->getKernelBrowser()->request('GET', '/app/api/gamejam/finalize/'.$id);
    Assert::assertEquals('200', $this->getKernelBrowser()
      ->getResponse()
      ->getStatusCode());
  }

  /**
   * @When I resubmit my game
   */
  public function iResubmitMyGame(): void
  {
    $file = $this->getDefaultProgramFile();
    $this->submit($file, $this->getUserDataFixtures()->getCurrentUser());
  }

  /**
   * @When I fill out the google form
   */
  public function iFillOutTheGoogleForm(): void
  {
    $this->getKernelBrowser()->request('GET', '/app/api/gamejam/finalize/1');
    Assert::assertEquals('200', $this->getKernelBrowser()
      ->getResponse()
      ->getStatusCode());
  }

  /**
   * @Then /^I should see the message "([^"]*)"$/
   *
   * @param mixed $arg1
   */
  public function iShouldSeeAMessage($arg1): void
  {
    Assert::assertContains($arg1, $this->getKernelBrowser()->getResponse()->getContent());
  }

  /**
   * @Then /^I should see the hashtag "([^"]*)" in the program description$/
   *
   * @param mixed $hashtag
   */
  public function iShouldSeeTheHashtagInTheProgramDescription($hashtag): void
  {
    Assert::assertContains($hashtag, $this->getKernelBrowser()
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
   * @Then /^the program "([^"]*)" should have no further Catrobat forward descendants$/
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
    $response = $this->upload($program, $user, '1', 'phirocode');
    Assert::assertEquals(200, $response->getStatusCode(), 'Wrong response code. '.$response->getContent());
  }

  private function sendUploadRequest(?string $filepath): void
  {
    Assert::assertTrue(file_exists($filepath), 'File not found');

    $this->request_files = [];
    $this->request_files[] = new UploadedFile($filepath, 'unnecessaryFiles.catrobat');

    $this->iHaveAParameterWithTheMdChecksumOf('fileChecksum');
    $this->request_parameters['username'] = $this->username;
    $this->request_parameters['token'] = 'cccccccccc';
    $this->iPostTo('/app/api/upload/upload.json');
  }

  private function upload(string $file, ?User $user, string $desired_id = '', string $flavor = 'pocketcode', ?array $request_param = null): Response
  {
    if (null === $user)
    {
      $user = $this->getUserDataFixtures()->getDefaultUser();
    }

    // overwrite id if desired
    if ('' !== $desired_id)
    {
      MyUuidGenerator::setNextValue($desired_id);
    }

    try
    {
      $file = new UploadedFile($file, 'uploadedFile');
    }
    catch (Exception $e)
    {
      throw new PendingException('No case defined for '.$e);
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

  private function prepareValidRegistrationParameters(): void
  {
    $this->request_parameters['registrationUsername'] = 'newuser';
    $this->request_parameters['registrationPassword'] = 'topsecret';
    $this->request_parameters['registrationEmail'] = 'someuser@example.com';
    $this->request_parameters['registrationCountry'] = 'at';
  }

  private function iUseTheUserAgent(string $user_agent): void
  {
    $this->request_server['HTTP_USER_AGENT'] = $user_agent;
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
}
