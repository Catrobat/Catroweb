<?php

declare(strict_types=1);

namespace App\System\Testing\Behat\Context;

use App\DB\Entity\Flavor;
use App\DB\Entity\Project\Program;
use App\DB\Entity\Project\Remix\ProgramRemixBackwardRelation;
use App\DB\Entity\Project\Remix\ProgramRemixRelation;
use App\DB\Entity\Project\Scratch\ScratchProgramRemixRelation;
use App\DB\Entity\Project\Special\FeaturedProgram;
use App\DB\Entity\User\User;
use App\DB\Generator\MyUuidGenerator;
use App\System\Testing\Behat\ContextTrait;
use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use FriendsOfBehat\SymfonyExtension\Context\Environment\InitializedSymfonyExtensionEnvironment;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Profiler\Profile;
use Symfony\Component\Intl\Locales;
use Symfony\Component\Security\Core\User\UserInterface;

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
 *
 * 3) Retrieve the response by using $this->getKernelBrowser()->getResponse().
 */
class ApiContext implements Context
{
  use ContextTrait;

  /**
   * Name of the user which is used for the next requests.
   */
  private ?string $username = null;

  private string $method;

  private string $url;

  private DataFixturesContext $dataFixturesContext;

  private array $request_parameters;

  private array $request_files;

  private array $request_headers;

  private ?string $request_content = null;

  private ?KernelBrowser $kernel_browser = null;

  // to df ->function
  private array $checked_catrobat_remix_forward_ancestor_relations;

  private array $checked_catrobat_remix_forward_descendant_relations;

  private array $checked_catrobat_remix_backward_relations;

  /**
   * @var string[]
   */
  private array $default_project_structure = [
    'id',
    'name',
    'author',
    'views',
    'downloads',
    'flavor',
    'uploaded_string',
    'screenshot_large',
    'screenshot_small',
    'project_url',
    'tags',
    'download',
    'description',
    'version',
    'uploaded',
    'download_url',
    'filesize',
    'not_for_kids',
  ];

  /**
   * @var string[]
   */
  private array $full_project_structure = ['id', 'name', 'author', 'description', 'credits', 'version', 'views',
    'downloads', 'reactions', 'comments', 'private', 'flavor', 'tags', 'uploaded', 'uploaded_string',
    'screenshot_large', 'screenshot_small', 'project_url', 'download_url', 'filesize', 'download', 'not_for_kids', ];

  /**
   * @var string[]
   */
  private array $default_user_structure = ['id', 'username'];

  /**
   * @var string[]
   */
  private array $full_user_structure = ['id', 'username', 'picture', 'about', 'currently_working_on', 'projects', 'followers', 'following'];

  /**
   * @var string[]
   */
  private array $default_user_structure_extended = ['id', 'username', 'email'];

  /**
   * @var string[]
   */
  private array $default_featured_project_structure = ['id', 'project_id', 'project_url', 'url', 'name', 'author', 'featured_image'];

  /**
   * @var string[]
   */
  private array $default_media_file_structure = ['id', 'name'];

  /**
   * @var string[]
   */
  private array $survey_structure = ['url'];

  private array $new_uploaded_projects = [];

  /**
   * @throws \Exception
   */
  public function getKernelBrowser(): KernelBrowser
  {
    if (!$this->kernel_browser instanceof KernelBrowser) {
      $this->kernel_browser = $this->getSymfonyService('test.client');
    }

    if (null === $this->kernel_browser) {
      throw new \Exception("Can't get KernelBrowser");
    }

    return $this->kernel_browser;
  }

  // -------------------------------------------------------------------------------------------------------------------
  //  Hook
  // -------------------------------------------------------------------------------------------------------------------

  /**
   * @BeforeScenario
   *
   * @throws \Exception
   */
  public function followRedirects(): void
  {
    $this->getKernelBrowser()->followRedirects();
  }

  /**
   * @BeforeScenario
   *
   * @throws \Exception
   */
  public function generateSessionCookie(): void
  {
    $client = $this->getKernelBrowser();

    $session = $this->getKernelBrowser()
      ->getContainer()
      ->get('session.factory')
      ->createSession()
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

  /**
   * @BeforeScenario
   *
   * @throws \Exception
   */
  public function gatherContexts(BeforeScenarioScope $scope): void
  {
    /** @var InitializedSymfonyExtensionEnvironment $environment */
    $environment = $scope->getEnvironment();
    $this->dataFixturesContext = $this->getDataFixturesContext($environment);
  }

  /**
   * @throws \Exception
   */
  protected function getDataFixturesContext(InitializedSymfonyExtensionEnvironment $environment): DataFixturesContext
  {
    return $environment->getContext(DataFixturesContext::class);
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
   *
   * @throws \Exception
   */
  public function iRequestWith(string $method, string $uri): void
  {
    $this->getKernelBrowser()->request(
      $method,
      $uri,
      $this->request_parameters,
      $this->request_files,
      $this->request_headers,
      $this->request_content ?? ''
    );
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
   * @When /^the Request is invoked$/
   *
   * @throws \Exception
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
   * @When /^I get the recent projects with "([^"]*)"$/
   * @When /^I get the most downloaded projects with "([^"]*)"$/
   * @When /^I get the most viewed projects with "([^"]*)"$/
   * @When /^I GET the tag list from "([^"]*)" with these parameters$/
   *
   * @throws \Exception
   */
  public function iGetFrom(string $url): void
  {
    $this->iRequestWith('GET', $url);
  }

  /**
   * @When /^I want to download the apk file of "([^"]*)"$/
   *
   * @throws \Exception
   */
  public function iWantToDownloadTheApkFileOf(string $arg1): void
  {
    $project_manager = $this->getProjectManager();

    $project = $project_manager->findOneByName($arg1);
    if (null === $project) {
      throw new \Exception('Project not found: '.$arg1);
    }

    $router = $this->getRouter();
    $url = $router->generate('ci_download', ['id' => $project->getId(), 'theme' => Flavor::POCKETCODE]);
    $this->iGetFrom($url);
  }

  /**
   * @Given /^I am "([^"]*)"$/
   */
  public function iAm(string $username): void
  {
    $this->username = $username;
  }

  /**
   * @When I upload a valid Catrobat project
   * @When I upload a valid Catrobat project with the same name
   */
  public function iUploadAValidCatrobatProject(): void
  {
    $this->uploadProject($this->FIXTURES_DIR.'test.catrobat');
  }

  /**
   * @When user :username uploads a valid Catrobat project
   *
   * @param string $username The name of the user who initiates the upload
   */
  public function userUploadsAValidCatrobatProject(string $username): void
  {
    /** @var User|null $user */
    $user = $this->getUserManager()->findUserByUsername($username);
    $this->uploadProject($this->FIXTURES_DIR.'test.catrobat', $user);
  }

  /**
   * @When /^jenkins uploads the apk file to the given upload url$/
   */
  public function jenkinsUploadsTheApkFileToTheGivenUploadUrl(): void
  {
    $filepath = $this->FIXTURES_DIR.'test.catrobat';
    Assert::assertFileExists($filepath, 'File not found');
    $temp_path = strval($this->getTempCopy($filepath));
    $this->request_files = [
      new UploadedFile($temp_path, 'test.apk'),
    ];
    $id = 1;
    $url = '/app/ci/upload/'.$id.'?token=UPLOADTOKEN';
    $this->iRequestWith('POST', $url);
  }

  /**
   * @When /^I report project (\d+) with category "([^"]*)" and note "([^"]*)"$/
   */
  public function iReportProjectWithNote(string $project_id, string $category, string $note): void
  {
    $url = '/app/api/reportProject/reportProject.json';
    $this->request_parameters = [
      'program' => $project_id,
      'category' => $category,
      'note' => $note,
    ];
    $this->iRequestWith('POST', $url);
  }

  /**
   * @When /^I POST login with user "([^"]*)" and password "([^"]*)"$/
   *
   * @throws \Exception
   * @throws \Exception
   * @throws \JsonException
   * @throws \JsonException
   */
  public function iPostLoginUserWithPassword(string $uname, string $pwd): void
  {
    $this->request_content = $this->getAuthenticationRequestBody($uname, $pwd);
    $this->iRequestWith('POST', '/api/authentication');
    $response = json_decode($this->getKernelBrowser()->getResponse()->getContent(), null, 512, JSON_THROW_ON_ERROR);
    $bearer_cookie = new Cookie('BEARER', $response->{'token'});
    $refresh_cookie = new Cookie('REFRESH_TOKEN', $response->{'refresh_token'});
    $cookieJar = $this->getKernelBrowser()->getCookieJar();
    $cookieJar->set($bearer_cookie);
    $cookieJar->set($refresh_cookie);
  }

  /**
   * @Then /^I should receive a "([^"]*)" file$/
   *
   * @throws \Exception
   */
  public function iShouldReceiveAFile(string $extension): void
  {
    $content_type = $this->getKernelBrowser()->getResponse()->headers->get('Content-Type');
    Assert::assertEquals('image/'.$extension, $content_type);
  }

  /**
   * @Then /^I should receive a file named "([^"]*)"$/
   *
   * @throws \Exception
   */
  public function iShouldReceiveAFileNamed(string $name): void
  {
    $content_disposition = $this->getKernelBrowser()->getResponse()->headers->get('Content-Disposition');
    Assert::assertEquals('attachment; filename="'.$name.'"', $content_disposition);
  }

  /**
   * @Then /^I should receive the apk file$/
   *
   * @throws \Exception
   * @throws \Exception
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
   *
   * @throws \Exception
   * @throws \Exception
   */
  public function iShouldReceiveAnApplicationFile(): void
  {
    $content_type = $this->getKernelBrowser()->getResponse()->headers->get('Content-Type');
    $code = $this->getKernelBrowser()->getResponse()->getStatusCode();
    Assert::assertEquals(200, $code);
    Assert::assertEquals('application/zip', $content_type);
  }

  /**
   * @Then the uploaded project should be a remix root
   *
   * @throws \JsonException
   */
  public function theUploadedProjectShouldBeARemixRoot(): void
  {
    $this->theProjectShouldBeARemixRoot($this->getIDOfLastUploadedProject());
  }

  /**
   * @Then the uploaded project should exist in the database
   *
   * @throws \JsonException
   */
  public function theUploadedProjectShouldExistInTheDatabase(): void
  {
    // Trying to find the id of the last uploaded project in the database
    $uploaded_project = $this->getManager()->getRepository(Program::class)->findOneBy([
      'id' => $this->getIDOfLastUploadedProject(),
    ]);

    Assert::assertNotNull($uploaded_project);
  }

  /**
   * @Then /^the response status code should be "([^"]*)"$/
   *
   * @throws \Exception
   */
  public function theResponseStatusCodeShouldBe(string $status_code): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    Assert::assertEquals(
      $status_code, $response->getStatusCode(),
      'Response contains invalid status code "'.$response->getStatusCode().'"'
    );
  }

  /**
   * @Then /^the response should be in json format$/
   *
   * @throws \Exception
   */
  public function theResponseShouldBeInJsonFormat(): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    Assert::assertJson($response->getContent());
  }

  /**
   * @Given /^I have a request parameter "([^"]*)" with value "([^"]*)"$/
   */
  public function iHaveARequestParameterWithValue(string $name, string $value): void
  {
    $this->request_parameters[$name] = $value;
  }

  /**
   * @Given /^I have a request header "([^"]*)" with value "([^"]*)"$/
   * @Given /^I have a request header "([^"]*)" with value '([^']*)'$/
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
   *
   * @throws \JsonException
   * @throws \Exception
   */
  public function iShouldGetTheJsonObject(PyStringNode $string): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    $this->assertJsonRegex($string->getRaw(), $response->getContent());
  }

  /**
   * @Then the response content must be empty
   *
   * @throws \Exception
   */
  public function theResponseContentMustBeEmpty(): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    Assert::assertEmpty($response->getContent());
  }

  /**
   * @Given I use a valid JWT Bearer token for :username
   *
   * @throws JWTEncodeFailureException
   */
  public function iUseAValidJwtBearerTokenFor(string $username): void
  {
    /** @var JWTManager $jwt_manager */
    $jwt_manager = $this->getJwtManager();
    $user_manager = $this->getUserManager();
    $user = $user_manager->findUserByUsername($username);
    if ($user instanceof UserInterface) {
      $token = $jwt_manager->create($user);
    } else {
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
   * @throws JWTEncodeFailureException
   */
  public function iUseAnExpiredJwtBearerTokenFor(string $username): void
  {
    $token = $this->getJwtEncoder()->encode(['username' => $username, 'exp' => 1]);
    sleep(1);
    $this->request_headers['HTTP_authorization'] = 'Bearer '.$token;
  }

  /**
   * @throws \Exception
   */
  public function getSymfonyProfile(): Profile
  {
    $profile = $this->getKernelBrowser()->getProfile();
    if (!$profile) {
      throw new \RuntimeException('The profiler is disabled. Activate it by setting framework.profiler.only_exceptions to false in your config');
    }

    return $profile;
  }

  /**
   * @When I upload a project with :project_attribute
   */
  public function iUploadAProjectWith(string $project_attribute): void
  {
    $filename = match ($project_attribute) {
      'a missing code.xml' => 'program_with_missing_code_xml.catrobat',
      'an invalid code.xml' => 'program_with_invalid_code_xml.catrobat',
      'a missing image' => 'program_with_missing_image.catrobat',
      'an additional image' => 'program_with_extra_image.catrobat',
      'an extra file' => 'program_with_too_many_files.catrobat',
      'valid parameters' => 'base.catrobat',
      'tags' => 'program_with_tags.catrobat',
      default => throw new PendingException('No case defined for "'.$project_attribute.'"'),
    };
    $this->uploadProject($this->FIXTURES_DIR.'GeneratedFixtures/'.$filename);
  }

  /**
   * @When I upload an invalid project file
   */
  public function iUploadAnInvalidProjectFile(): void
  {
    $this->uploadProject($this->FIXTURES_DIR.'/invalid_archive.catrobat');
  }

  /**
   * @When I upload this generated project with id :id
   *
   * @param string $id The desired id of the uploaded project
   */
  public function iUploadThisGeneratedProjectWithId(string $id): void
  {
    $this->uploadProject(sys_get_temp_dir().'/project_generated.catrobat', null, $id);
  }

  /**
   * @Given I upload the project with ":name" as name
   * @Given I upload the project with ":name" as name again
   *
   * @throws \Exception
   */
  public function iUploadTheProjectWithAsName(string $name): void
  {
    $this->generateProjectFileWith([
      'name' => $name,
    ]);
    $this->uploadProject(sys_get_temp_dir().'/project_generated.catrobat');
  }

  /**
   * @Then the uploaded project should not be a remix root
   *
   * @throws \JsonException
   */
  public function theUploadedProjectShouldNotBeARemixRoot(): void
  {
    $this->theProjectShouldNotBeARemixRoot($this->getIDOfLastUploadedProject());
  }

  /**
   * @Then the uploaded project should have remix migration date NOT NULL
   *
   * @throws \JsonException
   */
  public function theUploadedProjectShouldHaveMigrationDateNotNull(): void
  {
    $project_manager = $this->getProjectManager();
    $uploaded_project = $project_manager->find($this->getIDOfLastUploadedProject());
    Assert::assertNotNull($uploaded_project->getRemixMigratedAt());
  }

  /**
   * @Given the uploaded project should have a Scratch parent having id :id
   *
   * @throws \JsonException
   */
  public function theUploadedProjectShouldHaveAScratchParentHavingScratchID(string $scratch_parent_id): void
  {
    $this->theProjectShouldHaveAScratchParentHavingScratchID($this->getIDOfLastUploadedProject(), $scratch_parent_id);
  }

  /**
   * @Given the uploaded project should have no further Scratch parents
   *
   * @throws \JsonException
   */
  public function theUploadedProjectShouldHaveNoFurtherScratchParents(): void
  {
    $this->theProjectShouldHaveNoFurtherScratchParents($this->getIDOfLastUploadedProject());
  }

  /**
   * @Then the uploaded project should have a Catrobat forward ancestor having id :id and depth :depth
   *
   * @throws \JsonException
   */
  public function theUploadedProjectShouldHaveACatrobatForwardAncestorHavingIdAndDepth(string $id, string $depth): void
  {
    $this->theProjectShouldHaveACatrobatForwardAncestorHavingIdAndDepth($this->getIDOfLastUploadedProject(), $id, $depth);
  }

  /**
   * @Then the uploaded project should have a Catrobat forward ancestor having its own id and depth :depth
   *
   * @throws \JsonException
   */
  public function theUploadedProjectShouldHaveACatrobatForwardAncestorHavingItsOwnIdAndDepth(string $depth): void
  {
    $this->theProjectShouldHaveACatrobatForwardAncestorHavingIdAndDepth($this->getIDOfLastUploadedProject(), $this->getIDOfLastUploadedProject(), $depth);
  }

  /**
   * @Then the uploaded project should have a Catrobat backward parent having id :id
   *
   * @throws \JsonException
   */
  public function theUploadedProjectShouldHaveACatrobatBackwardParentHavingId(string $id): void
  {
    $this->theProjectShouldHaveACatrobatBackwardParentHavingId($this->getIDOfLastUploadedProject(), $id);
  }

  /**
   * @Then the uploaded project should have no Catrobat forward ancestors except self-relation
   *
   * @throws \JsonException
   */
  public function theUploadedProjectShouldHaveNoCatrobatForwardAncestorsExceptSelfRelation(): void
  {
    $this->theProjectShouldHaveNoCatrobatForwardAncestorsExceptSelfRelation($this->getIDOfLastUploadedProject());
  }

  /**
   * @Then the uploaded project should have no Catrobat backward parents
   *
   * @throws \JsonException
   */
  public function theUploadedProjectShouldHaveNoCatrobatBackwardParents(): void
  {
    $this->theProjectShouldHaveNoCatrobatBackwardParents($this->getIDOfLastUploadedProject());
  }

  /**
   * @Then the uploaded project should have no further Catrobat backward parents
   *
   * @throws \JsonException
   */
  public function theUploadedProjectShouldHaveNoFurtherCatrobatBackwardParents(): void
  {
    $this->theProjectShouldHaveNoFurtherCatrobatBackwardParents($this->getIDOfLastUploadedProject());
  }

  /**
   * @Then the uploaded project should have no Catrobat ancestors except self-relation
   *
   * @throws \JsonException
   */
  public function theUploadedProjectShouldHaveNoCatrobatAncestors(): void
  {
    $this->theProjectShouldHaveNoCatrobatAncestors($this->getIDOfLastUploadedProject());
  }

  /**
   * @Then the uploaded project should have no Scratch parents
   *
   * @throws \JsonException
   */
  public function theUploadedProjectShouldHaveNoScratchParents(): void
  {
    $this->theProjectShouldHaveNoScratchParents($this->getIDOfLastUploadedProject());
  }

  /**
   * @Then the uploaded project should have a Catrobat forward descendant having id :id and depth :depth
   *
   * @throws \JsonException
   */
  public function theUploadedProjectShouldHaveCatrobatForwardDescendantHavingIdAndDepth(string $id, string $depth): void
  {
    $this->theProjectShouldHaveCatrobatForwardDescendantHavingIdAndDepth($this->getIDOfLastUploadedProject(), $id, $depth);
  }

  /**
   * @Then the uploaded project should have no Catrobat forward descendants except self-relation
   *
   * @throws \JsonException
   */
  public function theUploadedProjectShouldHaveNoCatrobatForwardDescendantsExceptSelfRelation(): void
  {
    $this->theProjectShouldHaveNoCatrobatForwardDescendantsExceptSelfRelation($this->getIDOfLastUploadedProject());
  }

  /**
   * @Then the uploaded project should have no further Catrobat forward descendants
   *
   * @throws \JsonException
   */
  public function theUploadedProjectShouldHaveNoFurtherCatrobatForwardDescendants(): void
  {
    $this->theProjectShouldHaveNoFurtherCatrobatForwardDescendants($this->getIDOfLastUploadedProject());
  }

  /**
   * @Then the uploaded project should have RemixOf :value in the xml
   *
   * @throws \JsonException
   */
  public function theUploadedProjectShouldHaveRemixOfInTheXml(string $value): void
  {
    $this->theProjectShouldHaveRemixofInTheXml($this->getIDOfLastUploadedProject(), $value);
  }

  /**
   * @Given /^I want to upload a project/
   */
  public function iWantToUploadAProject(): void
  {
  }

  /**
   * @Given /^I have no parameters$/
   */
  public function iHaveNoParameters(): void
  {
  }

  /**
   * @Then /^I should get no projects/
   *
   * @throws \JsonException
   * @throws \Exception
   */
  public function iShouldGetNoProjects(): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    Assert::assertEquals(200, $response->getStatusCode());
    $responseArray = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    $returned_projects = $responseArray['CatrobatProjects'];
    Assert::assertEmpty($returned_projects, 'Projects were returned');
  }

  /**
   * @Then /^I should get following projects:$/
   *
   * @throws \JsonException
   * @throws \Exception
   */
  public function iShouldGetFollowingProjects(TableNode $table): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    Assert::assertEquals(200, $response->getStatusCode());
    $responseArray = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    $returned_projects = $responseArray['CatrobatProjects'];
    $expected_projects = $table->getHash();
    Assert::assertEquals(count($expected_projects), is_countable($returned_projects) ? count($returned_projects) : 0, 'Wrong number of returned projects');
    $counter = count($expected_projects);
    for ($i = 0; $i < $counter; ++$i) {
      $found = false;
      for ($j = 0; $j < (is_countable($returned_projects) ? count($returned_projects) : 0); ++$j) {
        if ($expected_projects[$i]['name'] === $returned_projects[$j]['ProjectName']) {
          $found = true;
        }
      }

      Assert::assertTrue($found, $expected_projects[$i]['name'].' was not found in the returned projects');
    }
  }

  /**
   * @Then the uploaded project should have no further Catrobat forward ancestors
   *
   * @throws \JsonException
   */
  public function theUploadedProjectShouldHaveNoFurtherCatrobatForwardAncestors(): void
  {
    $this->theProjectShouldHaveNoFurtherCatrobatForwardAncestors($this->getIDOfLastUploadedProject());
  }

  /**
   * @When /^I start an apk generation of my project$/
   *
   * @throws \Exception
   */
  public function iStartAnApkGenerationOfMyProject(): void
  {
    $id = 1;
    $this->iGetFrom('/app/ci/build/'.$id);
    $response = $this->getKernelBrowser()->getResponse();
    Assert::assertEquals(200, $response->getStatusCode(), 'Wrong response code. '.$response->getContent());
  }

  /**
   * @Then /^the apk file will not be found$/
   *
   * @throws \Exception
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
   *
   * @throws \Exception
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
   * @When /^I have downloaded a valid project$/
   */
  public function iHaveDownloadedAValidProject(): void
  {
    $id = 1;
    $this->iGetFrom('/api/project/'.$id.'/catrobat');
    $this->iShouldReceiveAProjectFile();
    $this->theResponseCodeShouldBe('200');
  }

  /**
   * @Then /^will get the following JSON:$/
   *
   * @throws \JsonException
   * @throws \JsonException
   * @throws \Exception
   */
  public function willGetTheFollowingJson(PyStringNode $string): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    Assert::assertEquals(200, $response->getStatusCode());

    $pattern = json_encode(json_decode($string->getRaw(), null, 512, JSON_THROW_ON_ERROR), JSON_THROW_ON_ERROR);
    $pattern = str_replace('\\', '\\\\', $pattern);
    Assert::assertMatchesRegularExpression($pattern, $response->getContent());
  }

  /**
   * @Given /^I am a user with role "([^"]*)"$/
   *
   * @throws \JsonException
   */
  public function iAmAUserWithRole(string $role): void
  {
    $this->insertUser([
      'role' => $role,
      'name' => 'generatedBehatUser',
    ]);

    $this->iPostLoginUserWithPassword('generatedBehatUser', '123456');
  }

  /**
   * @Then /^the client response should contain "([^"]*)"$/
   *
   * @throws \Exception
   */
  public function theResponseShouldContain(string $needle): void
  {
    $content = $this->getKernelBrowser()->getResponse()->getContent();
    Assert::assertStringContainsString($needle, $content, $needle.' not found in the response');
  }

  /**
   * @Then /^the response should contain the URL of the uploaded project$/
   *
   * @throws \JsonException
   * @throws \Exception
   * @throws \Exception
   */
  public function theResponseShouldContainALocationHeaderWithURLOfTheUploadedProject(): void
  {
    $uploaded_project = $this->getManager()->getRepository(Program::class)->findOneBy([
      'name' => 'test',
    ]);

    Assert::assertNotNull($uploaded_project);

    $location_header = $this->getKernelBrowser()->getResponse()->headers->get('Location');
    Assert::assertEquals('http://localhost/app/project/'.$uploaded_project->getId(), $location_header);

    $response = $this->getKernelBrowser()->getResponse();
    $responseArray = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

    Assert::assertEquals('http://localhost/app/project/'.$uploaded_project->getId(), $responseArray['project_url']);
  }

  /**
   * @Then /^the client response should not contain "([^"]*)"$/
   *
   * @throws \Exception
   */
  public function theResponseShouldNotContain(string $needle): void
  {
    Assert::assertStringNotContainsString($needle, $this->getKernelBrowser()->getResponse()->getContent());
  }

  /**
   * @When /^I update this project$/
   *
   * @throws \Exception
   * @throws \Exception
   */
  public function iUpdateThisProject(): void
  {
    $pm = $this->getProjectManager();
    $project = $pm->find('1');
    if (null === $project) {
      throw new \Exception('last project not found');
    }

    $file = $this->generateProjectFileWith([
      'name' => $project->getName(),
    ]);
    $this->uploadProject($file, $project->getUser());
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
   * @throws \Exception
   */
  public function uriFromShouldBe(string $arg1, string $arg2): void
  {
    $link = $this->getKernelBrowser()->getCrawler()->selectLink($arg1)->link();
    $absoluteUrl = $link->getUri();
    $parsedUrl = parse_url($absoluteUrl);
    $relativePath = $parsedUrl['path'];
    Assert::assertEquals($arg2, $relativePath, 'expected: '.$arg2.'  get: '.$relativePath);
  }

  /**
   * @Then /^the response Header should contain the key "([^"]*)" with the value '([^']*)'$/
   *
   * @throws \Exception
   */
  public function theResponseHeadershouldContainTheKeyWithTheValue(string $headerKey, string $headerValue): void
  {
    $headers = $this->getKernelBrowser()->getResponse()->headers;
    Assert::assertEquals($headerValue, $headers->get($headerKey),
      'expected: '.$headerKey.': '.$headerValue.
      "\nget: ".$headerKey.': '.$headers->get($headerKey));
  }

  /**
   * @Then the returned json object with id :id will be:
   *
   * @throws \JsonException
   * @throws \JsonException
   * @throws \Exception
   */
  public function theReturnedJsonObjectWithIdWillBe(string $id, PyStringNode $string): void
  {
    $response = $this->getKernelBrowser()->getResponse();

    $res_array = (array) json_decode($response->getContent(), null, 512, JSON_THROW_ON_ERROR);

    $res_array['projectId'] = $id;

    Assert::assertJsonStringEqualsJsonString($string->getRaw(), json_encode($res_array, JSON_THROW_ON_ERROR));
  }

  /**
   * @Then /^the response code will be "([^"]*)"$/
   *
   * @throws \Exception
   */
  public function theResponseCodeWillBe(string $code): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    Assert::assertEquals($code, $response->getStatusCode(), 'Wrong response code. '.$response->getContent());
  }

  /**
   * @When /^searching for "([^"]*)"$/
   */
  public function searchingFor(string $arg1): void
  {
    $this->method = 'GET';
    $this->url = '/api/search';
    $this->request_parameters = ['q' => $arg1, 'offset' => 0, 'limit' => 10];
    $this->iRequest();
  }

  /**
   * @Then /^the project should get (.*)$/
   *
   * @throws \JsonException
   * @throws \Exception
   */
  public function theProjectShouldGet(string $result): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    $response_array = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    $code = $response_array['statusCode'];
    match ($result) {
      'accepted' => Assert::assertEquals(200, $code, 'Project was rejected (Status code 200)'),
      'rejected' => Assert::assertNotEquals(200, $code, 'Project was NOT rejected'),
      default => new PendingException(),
    };
  }

  /**
   * @Then /^I should get a total of (\d+) projects$/
   *
   * @throws \JsonException
   * @throws \Exception
   */
  public function iShouldGetATotalOfProjects(string $arg1): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    $responseArray = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    Assert::assertEquals(
      $arg1, $responseArray['CatrobatInformation']['TotalProjects'],
      'Wrong number of total projects'
    );
  }

  /**
   * @Then /^I should get (\d+) projects$/
   *
   * @throws \JsonException
   * @throws \Exception
   */
  public function iShouldGetProjects(string $arg1): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    $responseArray = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    Assert::assertEquals(
      $arg1, is_countable($responseArray['CatrobatProjects']) ? count($responseArray['CatrobatProjects']) : 0,
      'Wrong number of total projects'
    );
  }

  /**
   * @Given /^I use the limit "([^"]*)"$/
   */
  public function iUseTheLimit(string $arg1): void
  {
    $this->iHaveAParameterWithValue('limit', $arg1);
  }

  /**
   * @Given /^I use the offset "([^"]*)"$/
   */
  public function iUseTheOffset(string $arg1): void
  {
    $this->iHaveAParameterWithValue('offset', $arg1);
  }

  /**
   * @Then /^I should get projects in the following order:$/
   *
   * @throws \JsonException
   * @throws \Exception
   */
  public function iShouldGetProjectsInTheFollowingOrder(TableNode $table): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    $responseArray = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    $returned_projects = $responseArray['CatrobatProjects'];
    $expected_projects = $table->getHash();

    Assert::assertEquals(count($expected_projects), is_countable($returned_projects) ? count($returned_projects) : 0);

    for ($i = 0; $i < (is_countable($returned_projects) ? count($returned_projects) : 0); ++$i) {
      Assert::assertEquals(
        $expected_projects[$i]['Name'], $returned_projects[$i]['ProjectName'],
        'Wrong order of results'
      );
    }
  }

  /**
   * @Then /^I should get (\d+) projects in random order:$/
   *
   * @throws \JsonException
   * @throws \Exception
   */
  public function iShouldGetProjectsInRandomOrder(string $project_count, TableNode $table): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    $response_array = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    $random_projects = $response_array['CatrobatProjects'];
    $random_projects_count = is_countable($random_projects) ? count($random_projects) : 0;
    $expected_projects = $table->getHash();
    $expected_projects_count = count($expected_projects);
    Assert::assertEquals($project_count, $random_projects_count, 'Wrong number of random projects');

    for ($i = 0; $i < $random_projects_count; ++$i) {
      $project_found = false;
      for ($j = 0; $j < $expected_projects_count; ++$j) {
        if (0 === strcmp((string) $random_projects[$i]['ProjectName'], (string) $expected_projects[$j]['Name'])) {
          $project_found = true;
        }
      }

      Assert::assertEquals($project_found, true, 'Project does not exist in the database');
    }
  }

  /**
   * @Then /^I should get the projects "([^"]*)" in random order$/
   *
   * @throws \JsonException
   */
  public function iShouldGetTheProjectsInRandomOrder(string $project_list): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    $response_array = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    $random_projects = $response_array['CatrobatProjects'];
    $random_projects_count = is_countable($random_projects) ? count($random_projects) : 0;
    $expected_projects = explode(',', $project_list);
    $expected_projects_count = count($expected_projects);
    Assert::assertEquals($expected_projects_count, $random_projects_count, 'Wrong number of random projects');

    for ($i = 0; $i < $random_projects_count; ++$i) {
      $project_found = false;
      for ($j = 0; $j < $expected_projects_count; ++$j) {
        if (0 === strcmp((string) $random_projects[$i]['ProjectName'], $expected_projects[$j])) {
          $project_found = true;
        }
      }

      Assert::assertEquals($project_found, true, 'Project does not exist in the database');
    }
  }

  /**
   * @Then /^I should get the projects "([^"]*)"$/
   *
   * @throws \JsonException
   */
  public function iShouldGetTheProjects(string $project_list): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    $responseArray = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    $returned_projects = $responseArray['CatrobatProjects'];
    $expected_projects = explode(',', $project_list);

    for ($i = 0; $i < (is_countable($returned_projects) ? count($returned_projects) : 0); ++$i) {
      $found = false;
      $counter = count($expected_projects);
      for ($j = 0; $j < $counter; ++$j) {
        if ($expected_projects[$j] === $returned_projects[$i]['ProjectName']) {
          $found = true;
        }
      }

      Assert::assertTrue($found);
    }
  }

  /**
   * @Given /^the response code should be "([^"]*)"$/
   *
   * @throws \Exception
   */
  public function theResponseCodeShouldBe(string $code): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    Assert::assertEquals($code, $response->getStatusCode(), 'Wrong response code. '.$response->getContent());
  }

  /**
   * @Then /^the response should contain the following project:$/
   *
   * @throws \JsonException
   * @throws \Exception
   */
  public function responseShouldContainTheFollowingProject(TableNode $table): void
  {
    $response = $this->getKernelBrowser()->getResponse();

    $returned_project = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

    $expected_project = $table->getHash();
    $stored_projects = $this->getStoredProjects($expected_project);
    $stored_project = $this->findProject($stored_projects, $returned_project['name']);

    $this->assertProjectsEqual($stored_project, $returned_project);
  }

  /**
   * @Then /^the response should contain the following projects:$/
   * @Then /^the response should contain projects in the following order:$/
   *
   * @throws \JsonException
   * @throws \Exception
   */
  public function responseShouldContainProjectsInTheFollowingOrder(TableNode $table): void
  {
    $response = $this->getKernelBrowser()->getResponse();

    $returned_projects = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    $expected_projects = $table->getHash();
    $stored_projects = $this->getStoredProjects($expected_projects);
    Assert::assertEquals(count($expected_projects), is_countable($returned_projects) ? count($returned_projects) : 0, 'Number of returned projects should be '.count($expected_projects));

    foreach ($returned_projects as $returned_project) {
      $stored_project = $this->findProject($stored_projects, $returned_project['name']);
      $this->assertProjectsEqual($stored_project, $returned_project);
    }
  }

  /**
   * @Then /^the response should contain featured projects in the following order:$/
   *
   * @throws \JsonException
   * @throws \Exception
   */
  public function responseShouldContainFeaturedProjectsInTheFollowingOrder(TableNode $table): void
  {
    $response = $this->getKernelBrowser()->getResponse();

    $returned_projects = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    $expected_projects = $table->getHash();
    $stored_projects = $this->getStoredFeaturedProjects($expected_projects);
    Assert::assertEquals(count($expected_projects), is_countable($returned_projects) ? count($returned_projects) : 0,
      'Number of returned projects should be '.count($expected_projects));

    foreach ($returned_projects as $returned_project) {
      $stored_project = $this->findProject($stored_projects, $returned_project['name']);
      foreach ($this->default_featured_project_structure as $key) {
        Assert::assertNotEmpty($stored_project);
        Assert::assertEquals($returned_project[$key], $stored_project[$key]);
      }
    }
  }

  /**
   * @Then /^the response should have the default projects model structure$/
   *
   * @throws \JsonException
   * @throws \Exception
   */
  public function responseShouldHaveDefaultProjectsModelStructure(): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    $responseArray = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

    /** @var array $project */
    foreach ($responseArray as $project) {
      Assert::assertCount(
        count($this->default_project_structure),
        $project,
        'Number of project fields should be '.count($this->default_project_structure));
      foreach ($this->default_project_structure as $key) {
        Assert::assertArrayHasKey($key, $project, 'Project should contain '.$key);
        Assert::assertTrue($this->checkProjectFieldsValue($project, $key), 'Project field '.$key.' has wrong value');
      }
    }
  }

  /**
   * @Then /^the response should contain the following users:$/
   * @Then /^the response should contain users in the following order:$/
   *
   * @throws \JsonException
   * @throws \Exception
   */
  public function responseShouldContainUsersInTheFollowingOrder(TableNode $table): void
  {
    $response = $this->getKernelBrowser()->getResponse();

    $returned_users = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    $expected_users = $table->getHash();
    $stored_users = $this->getStoredUsers();

    Assert::assertEquals(is_countable($returned_users) ? count($returned_users) : 0, count($expected_users), 'Number of returned users should be '.count($expected_users));

    foreach ($returned_users as $returned_user) {
      $stored_user = $this->findUser($stored_users, $returned_user['username']);
      $this->assertUsersEqual($stored_user, $returned_user);
    }
  }

  /**
   * @Then /^the response should contain the following user:$/
   *
   * @throws \JsonException
   * @throws \Exception
   */
  public function responseShouldContainTheFollowingUser(): void
  {
    $response = $this->getKernelBrowser()->getResponse();

    $returned_user = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    $stored_users = $this->getStoredUsers();

    $stored_user = $this->findUser($stored_users, $returned_user['username']);
    $this->assertUsersEqual($stored_user, $returned_user);
  }

  /**
   * @Then /^the response should have the default users model structure$/
   *
   * @throws \JsonException
   * @throws \Exception
   */
  public function responseShouldHaveDefaultUsersModelStructure(): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    $returned_users = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

    foreach ($returned_users as $user) {
      Assert::assertEquals(count($this->default_user_structure), is_countable($user) ? count($user) : 0,
        'Number of user fields should be '.count($this->default_user_structure));
      foreach ($this->default_user_structure as $key) {
        Assert::assertArrayHasKey($key, $user, 'User should contain '.$key);
        Assert::assertEquals($this->checkUserFieldsValue($user, $key), true);
      }
    }
  }

  /**
   * @Then /^the response should have the user model structure(?: excluding "(?P<excluded>([^"]+))")?$/
   *
   * @throws \JsonException
   * @throws \Exception
   */
  public function responseShouldHaveUserModelStructure(string $excluded = ''): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    $user = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

    $structure = array_diff($this->full_user_structure, '' === $excluded || '0' === $excluded ? [] : explode(',', $excluded));

    Assert::assertEquals(
      count($structure),
      is_countable($user) ? count($user) : 0,
      'Number of user fields should be '.count($structure)
    );
    foreach ($structure as $key) {
      Assert::assertArrayHasKey($key, $user, 'User should contain '.$key);
      Assert::assertEquals($this->checkUserFieldsValue($user, $key), true);
    }
  }

  /**
   * @Then /^the response should have the survey model structure$/
   *
   * @throws \JsonException
   * @throws \Exception
   */
  public function responseShouldHaveSurveyModelStructure(): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    $survey = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

    Assert::assertEquals(count($this->survey_structure), is_countable($survey) ? count($survey) : 0,
      'Number of survey fields should be '.count($this->survey_structure));
    foreach ($this->survey_structure as $key) {
      Assert::assertArrayHasKey($key, $survey, 'Survey should contain '.$key);
      Assert::assertEquals($this->checkSurveyFieldsValue($survey, $key), true);
    }
  }

  /**
   * @Then /^the response should have the project model structure$/
   *
   * @throws \JsonException
   * @throws \Exception
   */
  public function responseShouldHaveProjectModelStructure(): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    $project = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

    Assert::assertEquals(is_countable($project) ? count($project) : 0, count($this->full_project_structure),
      'Number of project fields should be '.count($this->full_project_structure));
    foreach ($this->full_project_structure as $key) {
      Assert::assertArrayHasKey($key, $project, 'Project should contain '.$key);
      Assert::assertEquals($this->checkProjectFieldsValue($project, $key), true);
    }
  }

  /**
   * @Then /^the response should have the default featured projects model structure$/
   *
   * @throws \JsonException
   * @throws \Exception
   */
  public function responseShouldHaveDefaultFeaturedProjectsModelStructure(): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    $returned_projects = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

    foreach ($returned_projects as $project) {
      Assert::assertEquals(count($this->default_featured_project_structure), is_countable($project) ? count($project) : 0,
        'Number of project fields should be '.count($this->default_featured_project_structure));
      foreach ($this->default_featured_project_structure as $key) {
        Assert::assertArrayHasKey($key, $project, 'Project should contain '.$key);
        Assert::assertEquals($this->checkFeaturedProjectFieldsValue($project, $key), true);
      }
    }
  }

  /**
   * @Then /^the response should have the default media files model structure$/
   *
   * @throws \JsonException
   * @throws \Exception
   */
  public function responseShouldHaveDefaultMediaFilesModelStructure(): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    $returned_media_files = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

    foreach ($returned_media_files as $project) {
      Assert::assertEquals(is_countable($project) ? count($project) : 0, count($this->default_media_file_structure),
        'Number of project fields should be '.count($this->default_media_file_structure));
      foreach ($this->default_media_file_structure as $key) {
        Assert::assertArrayHasKey($key, $project, 'Project should contain '.$key);
        Assert::assertEquals($this->checkMediaFileFieldsValue($project, $key), true);
      }
    }
  }

  /**
   * @Then /^the response should have language list structure$/
   *
   * @throws \JsonException
   * @throws \Exception
   */
  public function responseShouldHaveLanguageListStructure(): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    $returned_languages = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

    $all_locales = array_filter(Locales::getNames(), static fn ($key): bool => 2 == strlen((string) $key) || 5 == strlen((string) $key), ARRAY_FILTER_USE_KEY);
    $all_locales_count = count($all_locales);

    Assert::assertEquals($all_locales_count, is_countable($returned_languages) ? count($returned_languages) : 0,
      'Number of languages should be '.$all_locales_count);

    foreach ($returned_languages as $language_code => $display_text) {
      Assert::assertEquals('string', gettype($language_code));
      Assert::assertTrue(2 == strlen((string) $language_code) || 5 == strlen((string) $language_code));
      Assert::assertEquals('string', gettype($display_text));
    }
  }

  /**
   * @Then /^the response should contain the following languages:$/
   *
   * @throws \JsonException
   * @throws \Exception
   */
  public function responseShouldContainTheFollowingLanguages(TableNode $table): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    $returned_languages = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

    foreach ($table as $row) {
      Assert::assertArrayHasKey($row['Language Code'], $returned_languages);
      Assert::assertEquals($row['Display Name'], $returned_languages[$row['Language Code']]);
    }
  }

  /**
   * @Then /^I set request language to "([^"]*)"$/
   */
  public function iSetRequestLanguageTo(string $language): void
  {
    match ($language) {
      'English' => $this->iSetCookie('hl', 'en'),
      'Deutsch' => $this->iSetCookie('hl', 'de_DE'),
      'French' => $this->iSetCookie('hl', 'fr_FR'),
      default => throw new \InvalidArgumentException('Invalid language: '.$language),
    };
  }

  /**
   * @Then /^I set cookie "([^"]*)" to "([^"]*)"$/
   *
   * @throws \Exception
   */
  public function iSetCookie(string $key, string $value): void
  {
    $this->getKernelBrowser()->getCookieJar()->set(new Cookie($key, $value));
  }

  /**
   * @Then /^the response should contain media files in the following order:$/
   *
   * @throws \JsonException
   * @throws \Exception
   */
  public function responseShouldContainMediaFilesInTheFollowingOrder(TableNode $table): void
  {
    $response = $this->getKernelBrowser()->getResponse();

    $returned_files = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    $expected_files = $table->getHash();
    $stored_files = $this->getStoredMediaFiles($expected_files);

    Assert::assertEquals(is_countable($returned_files) ? count($returned_files) : 0, count($expected_files),
      'Number of returned projects should be '.count($expected_files));
    foreach ($returned_files as $returned_file) {
      $stored_file = $this->findProject($stored_files, $returned_file['name']);
      foreach ($this->default_media_file_structure as $key) {
        Assert::assertNotEmpty($stored_file);
        Assert::assertEquals($returned_file[$key], $stored_file[$key]);
      }
    }
  }

  /**
   * @Then /^the response should contain (\d+) projects$/
   *
   * @throws \JsonException
   * @throws \Exception
   */
  public function responseShouldContainNumberProjects(int $projects): void
  {
    $response = $this->getKernelBrowser()->getResponse();

    $returned_projects = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

    Assert::assertEquals(is_countable($returned_projects) ? count($returned_projects) : 0, $projects,
      'Number of returned projects should be '.(is_countable($returned_projects) ? count($returned_projects) : 0));
  }

  /**
   * @Then /^I should get (\d+) programs in the following order:$/
   *
   * @throws \JsonException
   * @throws \Exception
   */
  public function iShouldGetScratchProgramsInTheFollowingOrder(string $project_count, TableNode $table): void
  {
    $response = $this->getKernelBrowser()->getResponse();

    $responseArray = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    $projects = $table->getHash();

    $returned_projects = $responseArray['CatrobatProjects'];
    $scratch_projects_count = count($projects);
    Assert::assertEquals($project_count, $scratch_projects_count, 'Wrong number of Scratch projects');

    $expected_projects = $table->getHash();
    Assert::assertEquals(count($expected_projects), is_countable($returned_projects) ? count($returned_projects) : 0,
      'Number of returned projects should be '.(is_countable($returned_projects) ? count($returned_projects) : 0));

    for ($i = 0; $i < (is_countable($returned_projects) ? count($returned_projects) : 0); ++$i) {
      Assert::assertEquals(
        $expected_projects[$i]['Name'], $returned_projects[$i]['ProjectName'],
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
   *
   * @throws \JsonException
   * @throws \Exception
   */
  public function iShouldGetScratchProjectsInTheFollowingOrder(int $project_count, TableNode $table): void
  {
    $response = $this->getKernelBrowser()->getResponse();

    $returned_projects = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    $projects = $table->getHash();

    $scratch_projects_count = count($projects);
    Assert::assertEquals($project_count, $scratch_projects_count, 'Wrong number of Scratch projects');

    $expected_projects = $table->getHash();
    Assert::assertEquals(count($expected_projects), is_countable($returned_projects) ? count($returned_projects) : 0,
      'Number of returned projects should be '.(is_countable($returned_projects) ? count($returned_projects) : 0));

    for ($i = 0; $i < (is_countable($returned_projects) ? count($returned_projects) : 0); ++$i) {
      Assert::assertEquals(
        $expected_projects[$i]['Name'], $returned_projects[$i]['name'],
        'Wrong order of results'
      );
    }
  }

  /**
   * @Then the search response should contain :count projects
   */
  public function theSearchResponseShouldContainProjects(int $count): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    $data = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

    Assert::assertArrayHasKey('projects', $data);
    Assert::assertCount($count, $data['projects'], "Expected {$count} projects in response.");
  }

  /**
   * @Then the search response should contain :count users
   */
  public function theSearchResponseShouldContainUsers(int $count): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    $data = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

    Assert::assertArrayHasKey('users', $data);
    Assert::assertCount($count, $data['users'], "Expected {$count} users in response.");
  }

  /**
   * @Then the search response should contain the following projects:
   */
  public function theSearchResponseShouldContainTheFollowingProjects(TableNode $table): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    $data = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    $expectedProjects = $table->getHash();

    Assert::assertArrayHasKey('projects', $data);
    $returnedProjects = $data['projects'];

    foreach ($expectedProjects as $expected) {
      $found = false;
      foreach ($returnedProjects as $project) {
        if ($project['name'] === $expected['Name']) {
          $found = true;
          break;
        }
      }
      Assert::assertTrue($found, 'Project "'.$expected['Name'].'" not found in search response.');
    }
  }

  /**
   * @Then the search response should not contain any :key
   */
  public function theSearchResponseShouldNotContainAny(string $key): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    $data = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

    Assert::assertTrue(
      !isset($data[$key]),
      'Expected no '.$key.' in response.'
    );
  }

  /**
   * @Then the search response should contain the following users:
   */
  public function theSearchResponseShouldContainTheFollowingUsers(TableNode $table): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    $data = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    $expectedUsers = $table->getHash();

    Assert::assertArrayHasKey('users', $data);
    $returnedUsers = $data['users'];

    foreach ($expectedUsers as $expected) {
      $found = false;
      foreach ($returnedUsers as $user) {
        if ($user['username'] === $expected['Name']) {
          $found = true;
          break;
        }
      }
      Assert::assertTrue($found, 'User "'.$expected['Name'].'" not found in search response.');
    }
  }

  /**
   * @Given /^I have a parameter "([^"]*)" with an invalid md5checksum of my file$/
   */
  public function iHaveAParameterWithAnInvalidMdchecksumOfMyFile(string $parameter): void
  {
    $this->request_parameters[$parameter] = 'INVALIDCHECKSUM';
  }

  /**
   * @Given /^I have a parameter ":parameter" with the md5checksum of the file to be uploaded$/
   *
   * @param string $parameter The HTTP request parameter holding the checksum
   */
  public function iHaveAParameterWithTheMdChecksumOfTheUploadFile(string $parameter): void
  {
    $this->request_parameters[$parameter] = md5_file($this->request_files['file']->getPathname());
  }

  /**
   * @Given /^I have the POST parameters:$/
   */
  public function iHaveThePostParameters(TableNode $table): void
  {
    foreach ($table->getHash() as $parameter) {
      $this->request_parameters[$parameter['name']] = $parameter['value'];
    }
  }

  /**
   * @Then /^i should receive a project file$/
   *
   * @throws \Exception
   */
  public function iShouldReceiveAProjectFile(): void
  {
    $content_type = $this->getKernelBrowser()->getResponse()->headers->get('Content-Type');
    Assert::assertEquals('application/zip', $content_type);
  }

  /**
   * @When /^I have a parameter "([^"]*)" with the returned projectId$/
   *
   * @throws \Exception
   * @throws \JsonException
   */
  public function iHaveAParameterWithTheReturnedProjectid(string $name): void
  {
    $response = json_decode($this->getKernelBrowser()
      ->getResponse()
      ->getContent(), true, 512, JSON_THROW_ON_ERROR);
    $this->request_parameters[$name] = $response['projectId'];
  }

  /**
   * @Then it should be updated
   *
   * @throws \JsonException
   */
  public function itShouldBeUpdated(): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    $responseArray = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    $location = $responseArray['project_url'];

    Assert::assertNotNull($location);
  }

  /**
   * @Given /^the upload problem "([^"]*)"$/
   */
  public function theUploadProblem(string $problem): void
  {
    switch ($problem) {
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
   * @Given I try to upload a project with unnecessary files
   */
  public function iTryToUploadAProjectWithUnnecessaryFiles(): void
  {
    $this->uploadProject($this->FIXTURES_DIR.'unnecessaryFiles.catrobat');
  }

  /**
   * @Given I try to upload a project with scenes and unnecessary files
   */
  public function iTryToUploadAProjectWithScenesAndUnnecessaryFiles(): void
  {
    $this->uploadProject($this->FIXTURES_DIR.'unnecessaryFilesInScenes.catrobat');
  }

  /**
   * @When I upload this project with id :id
   *
   * @param string $id The desired ID of the newly uploaded project
   *
   * @throws \JsonException
   */
  public function iUploadThisProjectWithId(string $id): void
  {
    $this->uploadProject(sys_get_temp_dir().'/project_generated.catrobat', null, $id);
  }

  /**
   * @When I upload this generated project
   * @When I upload a generated project
   */
  public function iUploadThisGeneratedProject(): void
  {
    $this->uploadProject(sys_get_temp_dir().'/project_generated.catrobat');
  }

  /**
   * @When user :username uploads this generated project
   *
   * @param string $username The name of the user uploading the project
   */
  public function userUploadThisGeneratedProject(string $username): void
  {
    $this::userUploadThisGeneratedProjectWithID($username, '');
  }

  /**
   * @When user :username uploads this generated project, ID :id
   *
   * @param string $username The name of the user uploading the project
   * @param string $id       Desired id of the project
   */
  public function userUploadThisGeneratedProjectWithID(string $username, string $id): void
  {
    /** @var User|null $user */
    $user = $this->getUserManager()->findUserByUsername($username);
    Assert::assertNotNull($user);
    $this->uploadProject(sys_get_temp_dir().'/project_generated.catrobat', $user, $id);
  }

  /**
   * @When I upload the project with the id ":id"
   */
  public function iUploadAProjectWithId(string $id): void
  {
    $this->uploadProject(sys_get_temp_dir().'/project_generated.catrobat', null, $id);
  }

  /**
   * @When I upload the generated project with the id :id and name :name
   */
  public function iUploadTheGeneratedProjectWithIdAndName(string $id, string $name): void
  {
    $this->uploadProject(sys_get_temp_dir().'/project_generated.catrobat', null, $id);

    /** @var Program $project */
    $project = $this->getProjectManager()->find($id);
    $project->setName($name);
    $this->getProjectManager()->save($project);

    $this->new_uploaded_projects[] = $project;
  }

  /**
   * @When I upload this generated project again without extensions
   */
  public function iUploadTheGeneratedProjectAgainWithoutExtensions(): void
  {
    $this->iHaveAProjectWithAs('name', 'extensions');
    $this->iUploadThisGeneratedProject();
  }

  /**
   * @When I upload another project with name set to :name and url set to :url
   */
  public function iUploadAnotherProjectWithNameSetToAndUrlSetTo(string $name, string $url): void
  {
    $this->iHaveAProjectWithAsTwoHeaderFields('name', $name, 'url', $url);
    $this->iUploadThisGeneratedProject();
  }

  /**
   * @When I upload another project with name set to :arg1, url set to :arg2 \
   *       and catrobatLanguageVersion set to :arg3
   */
  public function iUploadAnotherProjectWithNameSetToUrlSetToAndCatrobatLanguageVersionSetTo(string $name, string $url, string $catrobat_language_version): void
  {
    $this->iHaveAProjectWithAsMultipleHeaderFields('name', $name, 'url', $url,
      'catrobatLanguageVersion', $catrobat_language_version);
    $this->iUploadThisGeneratedProject();
  }

  /**
   * @When I upload this generated project again with the tags :arg1
   *
   * @param string $tags The tags of the project
   *
   * @throws \Exception
   */
  public function iUploadThisProjectAgainWithTheTags(string $tags): void
  {
    $this->generateProjectFileWith([
      'tags' => $tags,
    ]);
    $file = sys_get_temp_dir().'/project_generated.catrobat';
    $this->uploadProject($file);
  }

  /**
   * @Given /^I have a parameter "([^"]*)" with value "([^"]*)"$/
   */
  public function iHaveAParameterWithValue(string $name, string $value): void
  {
    $this->request_parameters[$name] = $value;
  }

  /**
   * @Given /^the POST parameter "([^"]*)" contains the MD5 sum of the attached file$/
   */
  public function thePostParameterContainsTheMdSumOfTheGivenFile(string $arg1): void
  {
    $this->request_parameters[$arg1] = md5_file($this->request_files[0]->getPathname());
  }

  /**
   * @Given /^the check token problem "([^"]*)"$/
   *
   * @When /^there is a check token problem ([^"]*)$/
   */
  public function thereIsACheckTokenProblem(string $problem): void
  {
    switch ($problem) {
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
   * @Given /^I have a parameter "([^"]*)" with the tag "([^"]*)"$/
   */
  public function iHaveAParameterWithTheTag(string $name, string $value): void
  {
    $this->request_parameters[$name] = $value;
  }

  /**
   * @Given I use the :language app
   *
   * @param string $language The desired language
   */
  public function iUseTheApp(string $language): void
  {
    $deviceLanguage = match ($language) {
      'english' => 'en',
      'german' => 'de',
      default => 'NotExisting',
    };

    $this->iHaveARequestHeaderWithValue('HTTP_ACCEPT_LANGUAGE', $deviceLanguage);
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
    $this->request_parameters = $table->getRowsHash();
  }

  /**
   * @Given /^the GET parameters:$/
   * @Given /^I use the GET parameters:$/
   */
  public function iUseTheGetParameters(TableNode $table): void
  {
    $this->request_parameters = $table->getRowsHash();
  }

  /**
   * @Given /^the server name is "([^"]*)"$/
   */
  public function theServerNameIs(string $name): void
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
   * @Given /^I have a project with "([^"]*)" set to "([^"]*)" and "([^"]*)" set to "([^"]*)"$/
   *
   * @throws \Exception
   */
  public function iHaveAProjectWithAsTwoHeaderFields(string $key1, string $value1, string $key2, string $value2): void
  {
    $this->generateProjectFileWith([
      $key1 => $value1,
      $key2 => $value2,
    ]);
  }

  /**
   * @Given I have a project with :key1 set to :value1, :key2 set to :value2 and :key3 set to :value3
   *
   * @throws \Exception
   */
  public function iHaveAProjectWithAsMultipleHeaderFields(string $key1, string $value1, string $key2, string $value2, string $key3, string $value3): void
  {
    $this->generateProjectFileWith([
      $key1 => $value1,
      $key2 => $value2,
      $key3 => $value3,
    ]);
  }

  /**
   * @Given I have a valid Catrobat file
   */
  public function iHaveAValidCatrobatFile(): void
  {
    $filepath = $this->FIXTURES_DIR.'test.catrobat';
    Assert::assertTrue(file_exists($filepath), 'File not found');
    $this->request_files = [];
    $this->request_files['file'] = new UploadedFile($filepath, 'test.catrobat');
  }

  /**
   * @Given I add the file :fileName from path :fixturePath as :key
   */
  public function iSendAValidFileWithKey(string $fileName, string $fixturePath, string $key): void
  {
    $filepath = $this->FIXTURES_DIR.$fixturePath.'/'.$fileName;
    Assert::assertTrue(file_exists($filepath), 'File not found: '.$filepath);
    $tempFilePath = sys_get_temp_dir().'/'.uniqid('upload_', true).'_'.$fileName;
    copy($filepath, $tempFilePath);
    $this->request_files[$key] = new UploadedFile($tempFilePath, $fileName);
  }

  /**
   * @Given I have a broken Catrobat file
   */
  public function iHaveABrokenCatrobatFile(): void
  {
    $filepath = $this->FIXTURES_DIR.'broken.catrobat';
    Assert::assertTrue(file_exists($filepath), 'File not found');
    $this->request_files = [];
    $this->request_files['file'] = new UploadedFile($filepath, 'broken.catrobat');
  }

  /**
   * @Given /^I have a project with "([^"]*)" set to "([^"]*)"$/
   * @Given /^there is a project with "([^"]*)" set to "([^"]*)"$/
   *
   * @throws \Exception
   */
  public function iHaveAProjectWithAs(string $key, string $value): void
  {
    $this->generateProjectFileWith([
      $key => $value,
    ]);
  }

  /**
   * @Then The returned url with id :id should be
   *
   * @throws \Exception
   * @throws \JsonException
   */
  public function theReturnedUrlShouldBe(string $id, PyStringNode $string): void
  {
    $answer = (array) json_decode($this->getKernelBrowser()->getResponse()->getContent(), null, 512, JSON_THROW_ON_ERROR);

    $form_url = $answer['form'];
    $form_url = preg_replace('/&id=.*?&mail=/', '&id='.$id.'&mail=', (string) $form_url, -1);

    Assert::assertEquals($string->getRaw(), $form_url);
  }

  /**
   * @Then The submission should be rejected
   *
   * @throws \Exception
   * @throws \JsonException
   */
  public function theSubmissionShouldBeRejected(): void
  {
    $answer = json_decode($this->getKernelBrowser()
      ->getResponse()
      ->getContent(), true, 512, JSON_THROW_ON_ERROR);
    Assert::assertNotEquals('200', $answer['statusCode']);
  }

  /**
   * @Then /^I should see the message "([^"]*)"$/
   *
   * @throws \Exception
   */
  public function iShouldSeeAMessage(string $arg1): void
  {
    Assert::assertStringContainsString($arg1, $this->getKernelBrowser()->getResponse()->getContent());
  }

  // to df ->
  /**
   * @Then /^the project "([^"]*)" should be a remix root$/
   */
  public function theProjectShouldBeARemixRoot(string $project_id): void
  {
    $project_manager = $this->getProjectManager();
    $uploaded_project = $project_manager->find($project_id);
    Assert::assertTrue($uploaded_project->isRemixRoot());
  }

  /**
   * @Then /^the project "([^"]*)" should not be a remix root$/
   */
  public function theProjectShouldNotBeARemixRoot(string $project_id): void
  {
    $project_manager = $this->getProjectManager();
    /** @var Program $uploaded_project */
    $uploaded_project = $project_manager->find($project_id);
    Assert::assertFalse($uploaded_project->isRemixRoot());
  }

  /**
   * @Given /^the project "([^"]*)" should have a Scratch parent having id "([^"]*)"$/
   */
  public function theProjectShouldHaveAScratchParentHavingScratchID(string $project_id, string $scratch_parent_id): void
  {
    $direct_edge_relation = $this->getScratchProjectRemixRepository()->findOneBy([
      'scratch_parent_id' => $scratch_parent_id,
      'catrobat_child_id' => $project_id,
    ]);

    Assert::assertNotNull($direct_edge_relation);
    $this->checked_catrobat_remix_forward_ancestor_relations[$direct_edge_relation->getUniqueKey()] =
      $direct_edge_relation;
  }

  /**
   * @Given /^the project "([^"]*)" should have no further Scratch parents$/
   */
  public function theProjectShouldHaveNoFurtherScratchParents(string $project_id): void
  {
    $direct_edge_relations = $this->getScratchProjectRemixRepository()->findBy([
      'catrobat_child_id' => $project_id,
    ]);

    $further_scratch_parent_relations = array_filter($direct_edge_relations,
      fn (ScratchProgramRemixRelation $relation): bool => !array_key_exists(
        $relation->getUniqueKey(), $this->checked_catrobat_remix_forward_ancestor_relations
      ));

    Assert::assertCount(0, $further_scratch_parent_relations);
  }

  /**
   * @Then /^the project "([^"]*)" should have a Catrobat forward ancestor having id "([^"]*)" and depth "([^"]*)"$/
   */
  public function theProjectShouldHaveACatrobatForwardAncestorHavingIdAndDepth(string $project_id, string $ancestor_project_id, string $depth): void
  {
    $forward_ancestor_relation = $this->getProjectRemixForwardRepository()->findOneBy([
      'ancestor_id' => $ancestor_project_id,
      'descendant_id' => $project_id,
      'depth' => $depth,
    ]);

    Assert::assertNotNull($forward_ancestor_relation);
    $this->checked_catrobat_remix_forward_ancestor_relations[$forward_ancestor_relation->getUniqueKey()] =
      $forward_ancestor_relation;
    if ($project_id !== $ancestor_project_id) {
      return;
    }
    if (0 != $depth) {
      return;
    }
    $this->checked_catrobat_remix_forward_descendant_relations[$forward_ancestor_relation->getUniqueKey()] =
      $forward_ancestor_relation;
  }

  /**
   * @Then /^the project "([^"]*)" should have a Catrobat backward parent having id "([^"]*)"$/
   */
  public function theProjectShouldHaveACatrobatBackwardParentHavingId(string $project_id, string $backward_parent_project_id): void
  {
    $backward_parent_relation = $this->getProjectRemixBackwardRepository()->findOneBy([
      'parent_id' => $backward_parent_project_id,
      'child_id' => $project_id,
    ]);

    Assert::assertNotNull($backward_parent_relation);
    $this->checked_catrobat_remix_backward_relations[$backward_parent_relation->getUniqueKey()] =
      $backward_parent_relation;
  }

  /**
   * @Then /^the project "([^"]*)" should have no Catrobat forward ancestors except self-relation$/
   */
  public function theProjectShouldHaveNoCatrobatForwardAncestorsExceptSelfRelation(string $project_id): void
  {
    $forward_ancestors_including_self_referencing_relation = $this
      ->getProjectRemixForwardRepository()
      ->findBy(['descendant_id' => $project_id])
    ;

    Assert::assertCount(0, array_filter($forward_ancestors_including_self_referencing_relation,
      static fn (ProgramRemixRelation $relation): bool => $relation->getDepth() >= 1));
  }

  /**
   * @Then /^the project "([^"]*)" should have no further Catrobat forward ancestors$/
   */
  public function theProjectShouldHaveNoFurtherCatrobatForwardAncestors(string $project_id): void
  {
    $forward_ancestors_including_self_referencing_relation = $this
      ->getProjectRemixForwardRepository()
      ->findBy(['descendant_id' => $project_id])
    ;

    $further_forward_ancestor_relations = array_filter($forward_ancestors_including_self_referencing_relation,
      fn (ProgramRemixRelation $relation): bool => !array_key_exists(
        $relation->getUniqueKey(), $this->checked_catrobat_remix_forward_ancestor_relations
      ));

    Assert::assertCount(0, $further_forward_ancestor_relations);
  }

  /**
   * @Then /^the project "([^"]*)" should have no Catrobat backward parents$/
   */
  public function theProjectShouldHaveNoCatrobatBackwardParents(string $project_id): void
  {
    $backward_parent_relations = $this->getProjectRemixBackwardRepository()->findBy(['child_id' => $project_id]);
    Assert::assertCount(0, $backward_parent_relations);
  }

  /**
   * @Then /^the project "([^"]*)" should have no further Catrobat backward parents$/
   */
  public function theProjectShouldHaveNoFurtherCatrobatBackwardParents(string $project_id): void
  {
    $backward_parent_relations = $this->getProjectRemixBackwardRepository()->findBy(['child_id' => $project_id]);

    $further_backward_parent_relations = array_filter($backward_parent_relations,
      fn (ProgramRemixBackwardRelation $relation): bool => !array_key_exists(
        $relation->getUniqueKey(), $this->checked_catrobat_remix_backward_relations
      ));

    Assert::assertCount(0, $further_backward_parent_relations);
  }

  /**
   * @Then /^the project "([^"]*)" should have no Catrobat ancestors except self-relation$/
   */
  public function theProjectShouldHaveNoCatrobatAncestors(string $project_id): void
  {
    $this->theProjectShouldHaveNoCatrobatForwardAncestorsExceptSelfRelation($project_id);
    $this->theProjectShouldHaveNoCatrobatBackwardParents($project_id);
  }

  /**
   * @Then /^the project "([^"]*)" should have no Scratch parents$/
   */
  public function theProjectShouldHaveNoScratchParents(string $project_id): void
  {
    $scratch_parents = $this->getScratchProjectRemixRepository()->findBy(['catrobat_child_id' => $project_id]);
    Assert::assertCount(0, $scratch_parents);
  }

  /**
   * @Then /^the project "([^"]*)" should have a Catrobat forward descendant having id "([^"]*)" and depth "([^"]*)"$/
   */
  public function theProjectShouldHaveCatrobatForwardDescendantHavingIdAndDepth(string $project_id, string $descendant_project_id, string $depth): void
  {
    /** @var ProgramRemixRelation|null $forward_descendant_relation */
    $forward_descendant_relation = $this->getProjectRemixForwardRepository()->findOneBy([
      'ancestor_id' => $project_id,
      'descendant_id' => $descendant_project_id,
      'depth' => $depth,
    ]);

    Assert::assertNotNull($forward_descendant_relation);
    $this->checked_catrobat_remix_forward_descendant_relations[$forward_descendant_relation->getUniqueKey()] =
      $forward_descendant_relation;
    if ($project_id !== $descendant_project_id) {
      return;
    }
    if (0 != $depth) {
      return;
    }
    $this->checked_catrobat_remix_forward_ancestor_relations[$forward_descendant_relation->getUniqueKey()] =
      $forward_descendant_relation;
  }

  /**
   * @Then /^the project "([^"]*)" should have no Catrobat forward descendants except self-relation$/
   */
  public function theProjectShouldHaveNoCatrobatForwardDescendantsExceptSelfRelation(string $project_id): void
  {
    $forward_ancestors_including_self_referencing_relation = $this
      ->getProjectRemixForwardRepository()
      ->findBy(['ancestor_id' => $project_id])
    ;

    Assert::assertCount(0, array_filter($forward_ancestors_including_self_referencing_relation,
      static fn (ProgramRemixRelation $relation): bool => $relation->getDepth() >= 1));
  }

  /**
   * @Then the project :project_id should have no further Catrobat forward descendants
   */
  public function theProjectShouldHaveNoFurtherCatrobatForwardDescendants(string $project_id): void
  {
    $forward_descendants_including_self_referencing_relation = $this
      ->getProjectRemixForwardRepository()
      ->findBy(['ancestor_id' => $project_id])
    ;

    $further_forward_descendant_relations = array_filter($forward_descendants_including_self_referencing_relation,
      fn (ProgramRemixRelation $relation): bool => !array_key_exists(
        $relation->getUniqueKey(), $this->checked_catrobat_remix_forward_descendant_relations
      ));

    Assert::assertCount(0, $further_forward_descendant_relations);
  }

  /**
   * @Then /^the project "([^"]*)" should have RemixOf "([^"]*)" in the xml$/
   */
  public function theProjectShouldHaveRemixofInTheXml(string $project_id, string $value): void
  {
    $project_manager = $this->getProjectManager();
    /** @var Program $uploaded_project */
    $uploaded_project = $project_manager->find($project_id);
    $efr = $this->getExtractedFileRepository();
    $extracted_catrobat_file = $efr->loadProjectExtractedFile($uploaded_project);
    $project_xml_prop = $extracted_catrobat_file->getProjectXmlProperties();
    Assert::assertEquals($value, $project_xml_prop->header->remixOf->__toString());
  }

  /**
   * @Given /^I use a (debug|release) catroid build useragent$/
   */
  public function iHaveSpecificBuild(string $build_type): void
  {
    $this->iUseTheUserAgentParameterized('0.998', Flavor::POCKETCODE, '0.9.60', $build_type);
  }

  /**
   * @When /^I upload a catrobat project with the phiro app$/
   *
   * @throws \Exception
   * @throws \Exception
   */
  public function iUploadACatrobatProjectWithThePhiroProApp(): void
  {
    $user = $this->insertUser();
    $uploadedFile = $this->getStandardProjectFile();
    $this->uploadProject(strval($uploadedFile), $user, '1', Flavor::PHIROCODE);
    Assert::assertEquals(200, $this->getKernelBrowser()->getResponse()->getStatusCode(),
      'Wrong response code. '.$this->getKernelBrowser()->getResponse()->getContent());
  }

  // --------------------------------------------------------------------------------------------------------------------
  //  Error Logging
  // --------------------------------------------------------------------------------------------------------------------

  /**
   * @AfterStep
   */
  public function saveResponseToFile(AfterStepScope $scope): void
  {
    if (null == $this->ERROR_DIR) {
      return;
    }

    try {
      if (!$scope->getTestResult()->isPassed() && null != $this->getKernelBrowser()) {
        $response = $this->getKernelBrowser()->getResponse();
        if (null != $response && '' != $response->getContent()) {
          file_put_contents($this->ERROR_DIR.'errors.json', $response->getContent());
        }
      }
    } catch (\Exception) {
      file_put_contents($this->ERROR_DIR.'errors.json', '');
    }
  }

  /**
   * @Then /^I should be redirected to a catrobat project$/
   *
   * @throws \Exception
   */
  public function iShouldBeRedirectedToACatrobatProject(): void
  {
    Assert::assertStringStartsWith('/app/project/', $this->getKernelBrowser()->getRequest()->getPathInfo());
  }

  /**
   * @Then /^the response should contain all categories$/
   *
   * @throws \JsonException
   * @throws \Exception
   */
  public function theResponseShouldContainAllCategories(): void
  {
    $response = $this->getKernelBrowser()->getResponse();

    $expected_categories = ['recent', 'random', 'most_downloaded', 'example', 'scratch', 'trending'];
    $categories = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    Assert::assertEquals(count($expected_categories), is_countable($categories) ? count($categories) : 0, 'Number of returned projects should be '.count($expected_categories));

    foreach ($categories as $category) {
      Assert::assertIsString($category['type']);
      Assert::assertIsString($category['name']);
      Assert::assertIsArray($category['projects_list']);
      foreach ($category['projects_list'] as $project) {
        Assert::assertEquals(count($this->default_project_structure), is_countable($project) ? count($project) : 0,
          'Number of project fields should be '.count($this->default_project_structure));
        foreach ($this->default_project_structure as $key) {
          Assert::assertArrayHasKey($key, $project, 'Project should contain '.$key);
          Assert::assertEquals($this->checkProjectFieldsValue($project, $key), true);
        }
      }
    }
  }

  /**
   * @Then /^the response should contain example projects in the following order:$/
   *
   * @throws \JsonException
   * @throws \Exception
   */
  public function theResponseShouldContainExampleProjectsInTheFollowingOrder(TableNode $table): void
  {
    $response = $this->getKernelBrowser()->getResponse();

    $returned_projects = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    $expected_projects = $table->getHash();
    $stored_projects = $this->getStoredProjects($expected_projects);
    Assert::assertEquals(count($expected_projects), is_countable($returned_projects) ? count($returned_projects) : 0, 'Number of returned projects should be '.count($expected_projects));

    foreach ($returned_projects as $returned_project) {
      $stored_project = $this->findProject($stored_projects, $returned_project['name']);
      Assert::assertNotEmpty($stored_project);
      $this->testExampleProjectStructure($stored_project, $returned_project);
    }
  }

  /**
   * @Then /^the response should have the default extended user model structure$/
   *
   * @throws \JsonException
   * @throws \Exception
   */
  public function theResponseShouldHaveTheExtendedUserModelStructure(): void
  {
    $response = $this->getKernelBrowser()->getResponse();
    $user = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

    Assert::assertEquals(count($this->default_user_structure_extended), is_countable($user) ? count($user) : 0,
      'Number of user fields should be '.count($this->default_user_structure_extended));
    foreach ($this->default_user_structure_extended as $key) {
      Assert::assertArrayHasKey($key, $user, 'User should contain '.$key);
      Assert::assertEquals($this->checkUserFieldsValue($user, $key), true);
    }
  }

  /**
   * @throws \JsonException
   */
  private function getAuthenticationRequestBody(string $username, string $password): string
  {
    $credentials = [
      'username' => $username,
      'password' => $password,
    ];

    return json_encode($credentials, JSON_THROW_ON_ERROR);
  }

  private function findProject(array $projects, string $wanted_project_name): array
  {
    foreach ($projects as $project) {
      if ($project['name'] === $wanted_project_name) {
        return $project;
      }
    }

    return [];
  }

  private function findUser(array $users, string $wanted_user_name): array
  {
    foreach ($users as $user) {
      if ($user['username'] === $wanted_user_name) {
        return $user;
      }
    }

    return [];
  }

  private function expectProject(array $projects, string $value): bool
  {
    foreach ($projects as $project) {
      if ($project['Name'] === $value) {
        return true;
      }
    }

    return false;
  }

  private function getStoredProjects(array $expected_projects): array
  {
    $projects = array_merge($this->dataFixturesContext->getProjects(), $this->new_uploaded_projects);
    $stored_projects = [];
    /** @var Program $project */
    foreach ($projects as $project) {
      if (!$this->expectProject($expected_projects, $project->getName())) {
        continue;
      }

      $result = [
        'id' => $project->getId(),
        'name' => $project->getName(),
        'author' => $project->getUser()->getUserIdentifier(),
        'description' => $project->getDescription(),
        'version' => $project->getCatrobatVersionName(),
        'views' => $project->getViews(),
        'download' => $project->getDownloads(),
        'private' => $project->getPrivate(),
        'flavor' => $project->getFlavor(),
        'project_url' => 'http://localhost/app/project/'.$project->getId(),
        'download_url' => 'http://localhost/api/project/'.$project->getId().'/catrobat',
        'filesize' => ($project->getFilesize() / 1_048_576),
      ];
      $stored_projects[] = $result;
    }

    return $stored_projects;
  }

  private function getStoredFeaturedProjects(array $expected_projects): array
  {
    $featured_projects = $this->dataFixturesContext->getFeaturedProjects();
    $projects = [];
    /** @var FeaturedProgram $featured_project */
    foreach ($featured_projects as $featured_project) {
      if (!$this->expectProject($expected_projects, $featured_project->getProgram()->getName())) {
        continue;
      }

      $url = $featured_project->getUrl();
      $project_url = 'http://localhost/app/project/'.$featured_project->getProgram()->getId();
      if (empty($url)) {
        $url = $project_url;
      } else {
        $project_url = null;
      }

      $result = [
        'id' => $featured_project->getId(),
        'name' => $featured_project->getProgram()->getName(),
        'author' => $featured_project->getProgram()->getUser()->getUserIdentifier(),
        'project_id' => $featured_project->getProgram()->getId(),
        'project_url' => $project_url,
        'url' => $url,
        'featured_image' => 'http://localhost/resources_test/featured/featured_'.$featured_project->getId().'.jpg',
      ];
      $projects[] = $result;
    }

    return $projects;
  }

  private function getStoredMediaFiles(array $expected_projects): array
  {
    $media_projects = $this->dataFixturesContext->getMediaFiles();
    $projects = [];
    foreach ($media_projects as $project) {
      if (!$this->expectProject($expected_projects, $project['name'])) {
        continue;
      }

      $projects[] = $project;
    }

    return $projects;
  }

  private function getStoredUsers(): array
  {
    $stored_users = $this->dataFixturesContext->getUsers();
    $users = [];
    /** @var User $user */
    foreach ($stored_users as $user) {
      $result = [
        'id' => $user->getId(),
        'username' => $user->getUsername(),
        'email' => $user->getEmail(),
        'projects' => $user->getPrograms()->count(),
        'followers' => $user->getFollowers()->count(),
        'following' => $user->getFollowing()->count(),
      ];
      $users[] = $result;
    }

    return $users;
  }

  private function checkProjectFieldsValue(array $project, string $key): bool
  {
    $fields = [
      'id' => static function ($id): void {
        Assert::assertIsString($id, 'id is not a string!');
        Assert::assertMatchesRegularExpression('/^[a-zA-Z0-9-]+$/', $id, 'id is not in the correct format!');
      },
      'name' => static function ($name): void {
        Assert::assertIsString($name, 'Name is not a string!');
      },
      'author' => static function ($author): void {
        Assert::assertIsString($author, 'Author is not a string!');
      },
      'description' => static function ($description): void {
        Assert::assertIsString($description, 'Description is not a string!');
      },
      'credits' => static function ($description): void {
        Assert::assertIsString($description, 'Credits is not a string!');
      },
      'version' => static function ($version): void {
        Assert::assertIsString($version, 'Version is not a string!');
        Assert::assertMatchesRegularExpression('/\d\.\d\.\d/', $version, 'Version is not in the correct format!');
      },
      'views' => static function ($views): void {
        Assert::assertIsInt($views, 'Views is not an integer!');
      },
      'download' => static function ($downloads): void {
        Assert::assertIsInt($downloads, 'Download is not an integer!');
        // deprecated
      },
      'downloads' => static function ($downloads): void {
        Assert::assertIsInt($downloads, 'Downloads is not an integer!');
      },
      'reactions' => static function ($reactions): void {
        Assert::assertIsInt($reactions, 'Reactions is not an integer!');
      },
      'comments' => static function ($comments): void {
        Assert::assertIsInt($comments, 'Comments is not an integer!');
      },
      'private' => static function ($private): void {
        Assert::assertIsBool($private, 'Private is not a boolean!');
      },
      'flavor' => static function ($flavor): void {
        Assert::assertIsString($flavor, 'Flavor is not a string!');
      },
      'tags' => static function ($tags): void {
        Assert::assertIsArray($tags, 'Tags is not an array!');
      },
      'uploaded' => static function ($uploaded): void {
        Assert::assertIsInt($uploaded, 'uploaded is not an integer!');
      },
      'uploaded_string' => static function ($uploaded_string): void {
        Assert::assertIsString($uploaded_string, 'uploaded_string is not a string!');
      },
      'screenshot_large' => static function ($screenshot_large): void {
        Assert::assertIsString($screenshot_large);
        Assert::assertMatchesRegularExpression(
          '/http:\/\/localhost\/((resources_test\/screenshots\/screen_\d+)|(images\/default\/screenshot))\.png/',
          $screenshot_large,
          'screenshot_large is not a valid URL!'
        );
      },
      'screenshot_small' => static function ($screenshot_small): void {
        Assert::assertIsString($screenshot_small);
        Assert::assertMatchesRegularExpression(
          '/http:\/\/localhost\/((resources_test\/thumbnails\/screen_\d+)|(images\/default\/thumbnail))\.png/',
          $screenshot_small,
          'screenshot_small is not a valid URL!'
        );
      },
      'project_url' => static function ($project_url): void {
        Assert::assertIsString($project_url);
        Assert::assertMatchesRegularExpression(
          '/http:\/\/localhost\/app\/project\/[a-zA-Z0-9-]+/',
          $project_url,
          'project_url is not a valid URL!'
        );
      },
      'download_url' => static function ($download_url): void {
        Assert::assertIsString($download_url);
        Assert::assertMatchesRegularExpression(
          '/http:\/\/localhost\/api\/project\/([a-zA-Z0-9-]+)\/catrobat/',
          $download_url,
          'download_url is not a valid URL!'
        );
      },
      'filesize' => static function ($filesize): void {
        Assert::assertEquals(is_float($filesize) || is_int($filesize), true, 'Filesize is not a number!');
      },
      'not_for_kids' => static function ($not_for_kids): void {
        Assert::assertIsInt($not_for_kids, 'not_for_kids is not an integer! '.$not_for_kids);
        Assert::assertContains($not_for_kids, [0, 1, 2], 'not_for_kids is not 0 or 1 or 2! '.$not_for_kids);
      },
    ];

    Assert::assertArrayHasKey($key, $fields, sprintf('Key \'%s\' not found in fields array of checkProjectFieldsValue', $key));
    call_user_func($fields[$key], $project[$key]);

    return true;
  }

  private function checkFeaturedProjectFieldsValue(array $project, string $key): bool
  {
    $fields = [
      'id' => static function ($id): void {
        Assert::assertIsString($id);
        Assert::assertMatchesRegularExpression('/^[a-zA-Z0-9-]+$/', $id, 'id');
      },
      'name' => static function ($name): void {
        Assert::assertIsString($name);
      },
      'author' => static function ($author): void {
        Assert::assertIsString($author);
      },
      'project_id' => static function ($project_id): void {
        Assert::assertIsString($project_id);
        Assert::assertMatchesRegularExpression('/^[a-zA-Z0-9-]+$/', $project_id, 'project_id');
      },
      'project_url' => static function ($project_url): void {
        Assert::assertIsString($project_url);
        Assert::assertMatchesRegularExpression('/http:\/\/localhost\/app\/project\/[a-zA-Z0-9-]+$/', $project_url);
      },
      'url' => static function ($url): void {
        Assert::assertIsString($url);
        Assert::assertNotFalse(filter_var($url, FILTER_VALIDATE_URL));
      },
      'featured_image' => static function ($featured_image): void {
        Assert::assertIsString($featured_image);
        Assert::assertMatchesRegularExpression('/http:\/\/localhost\/resources_test\/featured\/featured_\d+\.(jpg|png)/',
          $featured_image);
      },
    ];

    Assert::assertArrayHasKey($key, $fields);
    call_user_func($fields[$key], $project[$key]);

    return true;
  }

  private function checkMediaFileFieldsValue(array $project, string $key): bool
  {
    $fields = [
      'id' => static function ($id): void {
        Assert::assertIsInt($id);
      },
      'name' => static function ($name): void {
        Assert::assertIsString($name);
      },
      'flavor' => static function ($flavor): void {
        Assert::assertIsString($flavor);
      },
      'packages' => static function ($package): void {
        Assert::assertIsArray($package);
      },
      'category' => static function ($category): void {
        Assert::assertIsString($category);
      },
      'author' => static function ($author): void {
        Assert::assertIsString($author);
      },
      'extension' => static function ($extension): void {
        Assert::assertIsString($extension);
      },
      'download_url' => static function ($download_url): void {
        Assert::assertIsString($download_url);
        Assert::assertMatchesRegularExpression('/http:\/\/localhost\/app\/download-media\/[a-zA-Z0-9-]+/',
          $download_url, 'download_url');
      },
      'size' => static function ($size): void {
        Assert::assertIsInt($size);
      },
      'file_type' => static function ($file_type): void {
        Assert::assertIsString($file_type);
        Assert::assertTrue(in_array($file_type, ['project', 'image', 'sound', 'other'], true));
      },
    ];

    Assert::assertArrayHasKey($key, $fields);
    call_user_func($fields[$key], $project[$key]);

    return true;
  }

  private function checkUserFieldsValue(array $user, string $key): bool
  {
    $fields = [
      'id' => static function ($id): void {
        Assert::assertIsString($id);
        Assert::assertMatchesRegularExpression('/^[a-zA-Z0-9-]+$/', $id, 'id');
      },
      'username' => static function ($username): void {
        Assert::assertIsString($username);
      },
      'email' => static function ($email): void {
        Assert::assertIsString($email);
      },
      'picture' => static function ($picture): void {
        Assert::assertIsString($picture);
        Assert::assertMatchesRegularExpression('/^data:image\/([^;]+);base64,([A-Za-z0-9\/+=]+)$/', $picture, 'Invalid user picture data-URL');
      },
      'about' => static function ($about): void {
        Assert::assertIsString($about);
      },
      'currently_working_on' => static function ($currentlyWorkingOn): void {
        Assert::assertIsString($currentlyWorkingOn);
      },
      'projects' => static function ($projects): void {
        Assert::assertIsInt($projects);
      },
      'followers' => static function ($followers): void {
        Assert::assertIsInt($followers);
      },
      'following' => static function ($following): void {
        Assert::assertIsInt($following);
      },
    ];

    Assert::assertArrayHasKey($key, $fields);
    call_user_func($fields[$key], $user[$key]);

    return true;
  }

  private function checkSurveyFieldsValue(array $user, string $key): bool
  {
    $fields = [
      'url' => static function ($username): void {
        Assert::assertIsString($username);
      },
    ];

    Assert::assertArrayHasKey($key, $fields);
    call_user_func($fields[$key], $user[$key]);

    return true;
  }

  // --------------------------------------------------------------------------------------------------------------------
  //  Upload Request process
  // --------------------------------------------------------------------------------------------------------------------

  /**
   * Uploads a Catrobat Project.
   *
   * @param string    $file       The Catrobat file to be uploaded
   * @param User|null $user       The uploader
   * @param string    $desired_id Specify, if the uploaded project should get a desired id
   * @param string    $flavor     The flavor of the project
   *
   * @throws \Exception when an error while uploading occurs
   */
  private function uploadProject(string $file, ?User $user = null, string $desired_id = '', string $flavor = Flavor::POCKETCODE): void
  {
    if (null == $user) {
      if (null !== $this->username) {
        /** @var User $user */
        $user = $this->getUserManager()->findUserByUsername($this->username);
      } else {
        $user = $this->getUserDataFixtures()->getDefaultUser();
      }
    }

    // overwrite id if desired
    if ('' !== $desired_id) {
      MyUuidGenerator::setNextValue($desired_id);
    }

    try {
      $file = new UploadedFile($file, basename($file));
    } catch (\Exception $exception) {
      throw new \Exception('File to upload does not exist: '.$exception->getMessage(), $exception->getCode(), $exception);
    }

    $this->request_headers['CONTENT_TYPE'] = 'multipart/form-data';
    $this->request_headers['HTTP_ACCEPT'] = 'application/json';
    $this->request_files['file'] = $file;
    $this->iHaveAParameterWithTheMdChecksumOfTheUploadFile('checksum');
    $this->iUseAValidJwtBearerTokenFor($user->getUsername());
    $this->iRequestWith('POST', '/api/projects');
  }

  /**
   * Returns the ID of the last uploaded project. The ID is retrieved from the last received response.
   *
   * @return string the ID of the last uploaded project or null if not available
   *
   * @throws \JsonException
   */
  private function getIDOfLastUploadedProject(): string
  {
    $json = json_decode($this->getKernelBrowser()->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

    return $json['id'];
  }

  private function iUseTheUserAgent(string $user_agent): void
  {
    $this->request_headers['HTTP_USER_AGENT'] = $user_agent;
  }

  private function iUseTheUserAgentParameterized(string $lang_version, string $flavor, string $app_version,
    string $build_type, string $theme = Flavor::POCKETCODE): void
  {
    // see org.catrobat.catroid.ui.WebViewActivity
    $platform = 'Android';
    $user_agent = 'Catrobat/'.$lang_version.' '.$flavor.'/'.$app_version.' Platform/'.$platform.
      ' BuildType/'.$build_type.' Theme/'.$theme;
    $this->iUseTheUserAgent($user_agent);
  }

  private function pathWithoutParam(string $path): string
  {
    return strtok($path, '?') ?: '';
  }

  private function assertProjectsEqual(array $stored_project, array $returned_project): void
  {
    Assert::assertNotEmpty($stored_project);
    Assert::assertNotEmpty($returned_project);
    foreach ($this->default_project_structure as $key) {
      if (array_key_exists($key, $stored_project)) {
        Assert::assertEquals($stored_project[$key], $returned_project[$key]);
      } elseif ('screenshot_large' === $key) {
        Assert::assertContains($this->pathWithoutParam($returned_project[$key]),
          ['http://localhost/resources/screenshots/screen_'.$returned_project['id'].'.png',
            'http://localhost/resources_test/screenshots/screen_'.$returned_project['id'].'.png',
            'http://localhost/images/default/screenshot.png', ]);
      } elseif ('screenshot_small' === $key) {
        Assert::assertContains($this->pathWithoutParam($returned_project[$key]),
          ['http://localhost/resources/thumbnails/screen_'.$returned_project['id'].'.png',
            'http://localhost/resources_test/thumbnails/screen_'.$returned_project['id'].'.png',
            'http://localhost/images/default/thumbnail.png', ]);
      }
    }
  }

  private function assertUsersEqual(array $stored_user, array $returned_user): void
  {
    Assert::assertNotEmpty($stored_user);
    Assert::assertNotEmpty($returned_user);
    foreach ($this->default_user_structure as $key) {
      Assert::assertEquals($stored_user[$key], $returned_user[$key]);
    }
  }

  private function testExampleProjectStructure(array $stored_project, array $example_project): void
  {
    Assert::assertNotEmpty($stored_project);
    Assert::assertNotEmpty($example_project);
    foreach ($this->default_project_structure as $key) {
      if (array_key_exists($key, $stored_project)) {
        Assert::assertEquals($stored_project[$key], $example_project[$key]);
      } elseif ('screenshot_large' === $key) {
        Assert::assertContains($this->pathWithoutParam($example_project[$key]),
          ['http://localhost/resources/example/example_'.$example_project['id'].'.jpg']);
      } elseif ('screenshot_small' === $key) {
        Assert::assertContains($this->pathWithoutParam($example_project[$key]),
          ['http://localhost/resources/example/example_'.$example_project['id'].'.jpg']);
      }
    }
  }

  /**
   * @When /^I wait for the search index to be updated$/
   */
  public function waitForSearchIndex(): void
  {
    sleep(1); // wait for search index to be updated
  }
}
