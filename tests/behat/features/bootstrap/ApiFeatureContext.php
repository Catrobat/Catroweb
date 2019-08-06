<?php

use App\Catrobat\Services\MediaPackageFileRepository;
use App\Catrobat\Services\TestEnv\FixedTime;
use App\Catrobat\Services\TestEnv\FixedTokenGenerator;
use App\Catrobat\Services\TestEnv\LdapTestDriver;
use App\Entity\Extension;
use App\Entity\FeaturedProgram;
use App\Entity\MediaPackage;
use App\Entity\MediaPackageCategory;
use App\Entity\MediaPackageFile;
use App\Entity\Program;
use App\Entity\ProgramDownloads;
use App\Entity\ProgramRemixBackwardRelation;
use App\Entity\ProgramRemixRelation;
use App\Entity\RudeWord;
use App\Entity\ScratchProgramRemixRelation;
use App\Entity\Tag;
use App\Entity\User;
use App\Entity\UserLikeSimilarityRelation;
use App\Entity\UserRemixSimilarityRelation;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use PHPUnit\Framework\Assert;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;


/**
 * Api Feature context.
 */
class ApiFeatureContext extends BaseContext
{

  /**
   * @var null
   */
  private $username;

  /**
   * @var array
   */
  private $request_parameters;

  /**
   * @var
   */
  private $last_response;

  /**
   * @var string
   */
  private $hostname;

  /**
   * @var bool
   */
  private $secure;


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

  private const MEDIAPACKAGE_DIR = './tests/testdata/DataFixtures/MediaPackage/';

  /**
   * @var
   */
  private $method;

  /**
   * @var
   */
  private $url;

  /**
   * @var array
   */
  private $post_parameters = [];

  /**
   * @var array
   */
  private $get_parameters = [];

  /**
   * @var array
   */
  private $server_parameters = ['HTTP_HOST' => 'pocketcode.org', 'HTTPS' => true];

  /**
   * @var array
   */
  private $files = [];

  /**
   * @var array
   */
  private $stored_json = [];

  /**
   * FeatureContext constructor.
   *
   * @param $error_directory
   */
  public function __construct($error_directory)
  {
    parent::__construct();
    $this->setErrorDirectory($error_directory);
    $this->username = null;
    $this->request_parameters = [];
    $this->files = [];
    $this->hostname = 'localhost';
    $this->secure = false;
    $this->checked_catrobat_remix_forward_ancestor_relations = [];
    $this->checked_catrobat_remix_forward_descendant_relations = [];
    $this->checked_catrobat_remix_backward_relations = [];
  }

  /**
   * @Given /^the HTTP Request:$/
   * @Given /^I have the HTTP Request:$/
   * @param TableNode $table
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
   * @param TableNode $table
   */
  public function iUseThePostParameters(TableNode $table)
  {
    $values = $table->getRowsHash();
    $this->post_parameters = $values;
  }

  /**
   * @Given /^the GET parameters:$/
   * @Given /^I use the GET parameters:$/
   * @param TableNode $table
   */
  public function iUseTheGetParameters(TableNode $table)
  {
    $values = $table->getRowsHash();
    $this->get_parameters = $values;
  }

  /**
   * @When /^such a Request is invoked$/
   * @When /^a Request is invoked$/
   * @When /^the Request is invoked$/
   * @When /^I invoke the Request$/
   */
  public function iInvokeTheRequest()
  {
    $this->getClient()->request($this->method, 'https://' . $this->server_parameters['HTTP_HOST'] . $this->url .
      '?' . http_build_query($this->get_parameters), $this->post_parameters, $this->files, $this->server_parameters);
  }

  /**
   * @Then /^the returned json object will be:$/
   * @Then /^I will get the json object:$/
   * @param PyStringNode $string
   */
  public function iWillGetTheJsonObject(PyStringNode $string)
  {
    $response = $this->getClient()->getResponse();
    Assert::assertJsonStringEqualsJsonString($string->getRaw(), $response->getContent(), '');
  }

  /**
   * @Then /^the response code will be "([^"]*)"$/
   * @param $code
   */
  public function theResponseCodeWillBe($code)
  {
    $response = $this->getClient()->getResponse();
    Assert::assertEquals($code, $response->getStatusCode(), 'Wrong response code. ' . $response->getContent());
  }

  /**
   * @Given /^I use the user agent "([^"]*)"$/
   * @param string $user_agent
   */
  public function iUseTheUserAgent($user_agent)
  {
    $this->getClient()->setServerParameter("HTTP_USER_AGENT", $user_agent);
  }

  // @formatter:off
  /**
   * @Given /^I use the Catroid app with language version ([0-9]+(\.[0-9]+)?), flavor "([^"]+)", app version "([^"]+)*" and (debug|release) build type$/
   *
   * @param $lang_version
   * @param $flavor
   * @param $app_version
   * @param $build_type
   */
  // @formatter:on
  public function iUseTheUserAgentParameterized($lang_version, $flavor, $app_version, $build_type)
  {
    // see org.catrobat.catroid.ui.WebViewActivity
    $platform = "Android";
    $user_agent = "Catrobat/" . $lang_version . " " . $flavor . "/" . $app_version . " Platform/" . $platform .
      " BuildType/" . $build_type;
    $this->getClient()->setServerParameter("HTTP_USER_AGENT", $user_agent);
  }

  /**
   * @Given /^I use a (debug|release) build of the Catroid app$/
   * @param $build_type
   */
  public function iUseASpecificBuildTypeOfCatroidApp($build_type)
  {
    $this->iUseTheUserAgentParameterized("0.998", "PocketCode", "0.9.60", $build_type);
  }


  /**
   * @Given /^we assume the next generated token will be "([^"]*)"$/
   * @param $token
   */
  public function weAssumeTheNextGeneratedTokenWillBe($token)
  {
    $token_generator = $this->getSymfonyService('tokengenerator');
    $token_generator->setTokenGenerator(new FixedTokenGenerator($token));
  }

  /**
   * @Given /^a catrobat file is attached to the request$/
   */
  public function iAttachACatrobatFile()
  {
    $filepath = self::FIXTUREDIR . 'test.catrobat';
    Assert::assertTrue(file_exists($filepath), 'File not found');
    $this->files[] = new UploadedFile($filepath, 'test.catrobat');
  }

  /**
   * @Given /^the POST parameter "([^"]*)" contains the MD5 sum of the attached file$/
   * @param $arg1
   */
  public function thePostParameterContainsTheMdSumOfTheGivenFile($arg1)
  {
    $this->post_parameters[$arg1] = md5_file($this->files[0]->getPathname());
  }

  /**
   * @Given /^the registration problem "([^"]*)"$/
   * @Given /^there is a registration problem ([^"]*)$/
   * @param $problem
   */
  public function thereIsARegistrationProblem($problem)
  {
    switch ($problem)
    {
      case "no password given":
        $this->method = "POST";
        $this->url = "/pocketcode/api/loginOrRegister/loginOrRegister.json";
        $this->post_parameters['registrationUsername'] = "Someone";
        $this->post_parameters['registrationEmail'] = "someone@pocketcode.org";
        break;
      default:
        throw new PendingException("No implementation of case \"" . $problem . "\"");
    }
  }

  /**
   * @Given /^the check token problem "([^"]*)"$/
   * @When /^there is a check token problem ([^"]*)$/
   * @param $problem
   */
  public function thereIsACheckTokenProblem($problem)
  {
    switch ($problem)
    {
      case "invalid token":
        $this->method = "POST";
        $this->url = "/pocketcode/api/checkToken/check.json";
        $this->post_parameters['username'] = "Catrobat";
        $this->post_parameters['token'] = "INVALID";
        break;
      default:
        throw new PendingException("No implementation of case \"" . $problem . "\"");
    }
  }

  /**
   * @When /^searching for "([^"]*)"$/
   * @param $arg1
   */
  public function searchingFor($arg1)
  {
    $this->method = 'GET';
    $this->url = '/pocketcode/api/projects/search.json';
    $this->get_parameters = ['q' => $arg1, 'offset' => 0, 'limit' => 10];
    $this->iInvokeTheRequest();
  }


  /**
   * @Given /^the upload problem "([^"]*)"$/
   * @param $problem
   */
  public function theUploadProblem($problem)
  {
    switch ($problem)
    {
      case "no authentication":
        $this->method = "POST";
        $this->url = "/pocketcode/api/upload/upload.json";
        break;
      case "missing parameters":
        $this->method = "POST";
        $this->url = "/pocketcode/api/upload/upload.json";
        $this->post_parameters['username'] = "Catrobat";
        $this->post_parameters['token'] = "cccccccccc";
        break;
      case "invalid program file":
        $this->method = "POST";
        $this->url = "/pocketcode/api/upload/upload.json";
        $this->post_parameters['username'] = "Catrobat";
        $this->post_parameters['token'] = "cccccccccc";
        $filepath = self::FIXTUREDIR . 'invalid_archive.catrobat';
        Assert::assertTrue(file_exists($filepath), 'File not found');
        $this->files[] = new UploadedFile($filepath, 'test.catrobat');
        $this->post_parameters['fileChecksum'] = md5_file($this->files[0]->getPathname());
        break;
      default:
        throw new PendingException("No implementation of case \"" . $problem . "\"");
    }
  }


  // ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // //////////////////////////////////////////// Support Functions

  /**
   * @BeforeScenario @RealGeocoder
   */
  public function activateRealGeocoderService()
  {
    $this->getSymfonyService('statistics')->useRealService(true);
  }

  /**
   * @AfterScenario @RealGeocoder
   */
  public function deactivateRealGeocoderService()
  {
    $this->getSymfonyService('statistics')->useRealService(false);
  }

  /**
   *
   */
  private function prepareValidRegistrationParameters()
  {
    $this->request_parameters['registrationUsername'] = 'newuser';
    $this->request_parameters['registrationPassword'] = 'topsecret';
    $this->request_parameters['registrationEmail'] = 'someuser@example.com';
    $this->request_parameters['registrationCountry'] = 'at';
  }

  /**
   * @param $url
   */
  private function sendPostRequest($url)
  {
    $this->getClient()->request('POST', $url, $this->request_parameters, $this->files);
  }

  // ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // //////////////////////////////////////////// Steps

  /**
   * @Given /^I have a program with "([^"]*)" as (name|description|tags)$/
   * @param $value
   * @param $header
   */
  public function iHaveAProgramWithAsDescription($value, $header)
  {
    $this->generateProgramFileWith([
      $header => $value,
    ]);
  }

  /**
   * @Given I have an embroidery project
   */
  public function iHaveAnEmbroideryProject()
  {
    $this->generateProgramFileWith([], true);
  }

  /**
   * @When /^I upload this program$/
   */
  public function iUploadThisProgram()
  {
    if (array_key_exists('deviceLanguage', $this->request_parameters))
    {
      $this->upload(sys_get_temp_dir() . '/program_generated.catrobat', null,
        'pocketcode', $this->request_parameters);
    }
    else
    {
      $this->upload(sys_get_temp_dir() . '/program_generated.catrobat', null);
    }
  }

  /**
   * @Then /^the program should get (.*)$/
   * @param $result
   */
  public function theProgramShouldGet($result)
  {
    $response = $this->getClient()->getResponse();
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
   * @Given /^I define the following rude words:$/
   * @param TableNode $table
   *
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function iDefineTheFollowingRudeWords(TableNode $table)
  {
    $words = $table->getHash();

    $word = null;
    $em = $this->getManager();

    for ($i = 0; $i < count($words); ++$i)
    {
      $word = new RudeWord();
      $word->setWord($words[$i]['word']);
      $em->persist($word);
    }
    $em->flush();
  }

  /**
   * @Given /^I am a valid user$/
   */
  public function iAmAValidUser()
  {
    $this->insertUser([
      'name'     => 'BehatGeneratedName',
      'token'    => 'BehatGeneratedToken',
      'password' => 'BehatGeneratedPassword',
    ]);
  }

  /**
   * @When /^I upload a program with (.*)$/
   * @param string $program_attribute
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
        throw new PendingException('No case defined for "' . $program_attribute . '"');
    }
    $this->upload(self::FIXTUREDIR . 'GeneratedFixtures/' . $filename, null);
  }

  /**
   * @When /^I upload an invalid program file$/
   */
  public function iUploadAnInvalidProgramFile()
  {
    $this->upload(self::FIXTUREDIR . '/invalid_archive.catrobat', null);
  }

  /**
   * @Given /^there are users:$/
   * @param TableNode $table
   */
  public function thereAreUsers(TableNode $table)
  {
    $users = $table->getHash();
    for ($i = 0; $i < count($users); ++$i)
    {
      $this->insertUser(@[
        'name'     => $users[$i]['name'],
        'email'    => $users[$i]['email'],
        'token'    => isset($users[$i]['token']) ? $users[$i]['token'] : "",
        'password' => isset($users[$i]['password']) ? $users[$i]['password'] : "",
      ]);
    }
  }

  /**
   * @Given /^there are LDAP-users:$/
   * @param TableNode $table
   */
  public function thereAreLdapUsers(TableNode $table)
  {
    /**
     * @var $ldap_test_driver LdapTestDriver
     * @var $user             User
     */
    $ldap_test_driver = $this->getSymfonyService('fr3d_ldap.ldap_driver');
    $users = $table->getHash();
    $ldap_test_driver->resetFixtures();

    for ($i = 0; $i < count($users); ++$i)
    {
      $username = $users[$i]['name'];
      $pwd = $users[$i]['password'];
      $groups = array_key_exists("groups", $users[$i]) ? explode(",", $users[$i]["groups"]) : [];
      $ldap_test_driver->addTestUser($username, $pwd, $groups, isset($users[$i]['email']) ?
        $users[$i]['email'] : null);
    }
  }

  /**
   * @Given /^there are programs:$/
   * @param TableNode $table
   *
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function thereArePrograms(TableNode $table)
  {
    $programs = $table->getHash();

    for ($i = 0; $i < count($programs); ++$i)
    {
      $user = $this->getUserManager()->findOneBy([
        'username' => isset($programs[$i]['owned by']) ? $programs[$i]['owned by'] : "",
      ]);
      @$config = [
        'name'                => $programs[$i]['name'],
        'description'         => $programs[$i]['description'],
        'views'               => $programs[$i]['views'],
        'downloads'           => $programs[$i]['downloads'],
        'uploadtime'          => $programs[$i]['upload time'],
        'apk_status'          => $programs[$i]['apk_status'],
        'catrobatversionname' => $programs[$i]['version'],
        'directory_hash'      => $programs[$i]['directory_hash'],
        'filesize'            => @$programs[$i]['FileSize'],
        'visible'             => isset($programs[$i]['visible']) ? $programs[$i]['visible'] == 'true' : true,
        'approved'            => (isset($programs[$i]['approved_by_user']) && $programs[$i]['approved_by_user'] == '')
          ? null : true,
        'tags'                => isset($programs[$i]['tags_id']) ? $programs[$i]['tags_id'] : null,
        'extensions'          => isset($programs[$i]['extensions']) ? $programs[$i]['extensions'] : null,
        'remix_root'          => isset($programs[$i]['remix_root']) ? $programs[$i]['remix_root'] == 'true' : true,
        'debug'               => isset($programs[$i]['debug']) ? $programs[$i]['debug'] == 'true' : false,
      ];

      $this->insertProgram($user, $config);
    }
  }

  /**
   * @Given /^there are like similar users:$/
   * @param TableNode $table
   *
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function thereAreLikeSimilarUsers(TableNode $table)
  {
    $similarities = $table->getHash();

    for ($i = 0; $i < count($similarities); ++$i)
    {
      $this->insertUserLikeSimilarity([
        'first_user_id'  => $similarities[$i]['first_user_id'],
        'second_user_id' => $similarities[$i]['second_user_id'],
        'similarity'     => $similarities[$i]['similarity'],
      ]);
    }
  }

  /**
   * @Given /^there are remix similar users:$/
   * @param TableNode $table
   *
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function thereAreRemixSimilarUsers(TableNode $table)
  {
    $similarities = $table->getHash();

    for ($i = 0; $i < count($similarities); ++$i)
    {
      $this->insertUserRemixSimilarity([
        'first_user_id'  => $similarities[$i]['first_user_id'],
        'second_user_id' => $similarities[$i]['second_user_id'],
        'similarity'     => $similarities[$i]['similarity'],
      ]);
    }
  }

  /**
   * @Given /^there are likes:$/
   * @param TableNode $table
   *
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function thereAreLikes(TableNode $table)
  {
    $likes = $table->getHash();

    foreach ($likes as $like)
    {
      $this->insertProgramLike([
        'username'   => $like['username'],
        'program_id' => $like['program_id'],
        'type'       => $like['type'],
        'created at' => $like['created at'],
      ]);
    }
  }

  /**
   * @Given /^there are tags:$/
   * @param TableNode $table
   *
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function thereAreTags(TableNode $table)
  {
    $tags = $table->getHash();

    foreach ($tags as $tag)
    {
      @$config = [
        'id' => $tag['id'],
        'en' => $tag['en'],
        'de' => $tag['de'],
      ];
      $this->insertTag($config);
    }
  }

  /**
   * @Given /^there are extensions:$/
   * @param TableNode $table
   *
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function thereAreExtensions(TableNode $table)
  {
    $extensions = $table->getHash();

    foreach ($extensions as $extension)
    {
      @$config = [
        'name'   => $extension['name'],
        'prefix' => $extension['prefix'],
      ];
      $this->insertExtension($config);
    }
  }

  /**
   * @Given /^there are forward remix relations:$/
   * @param TableNode $table
   *
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function thereAreForwardRemixRelations(TableNode $table)
  {
    $relations = $table->getHash();

    foreach ($relations as $relation)
    {
      @$config = [
        'ancestor_id'   => $relation['ancestor_id'],
        'descendant_id' => $relation['descendant_id'],
        'depth'         => $relation['depth'],
      ];
      $this->insertForwardRemixRelation($config);
    }
  }

  /**
   * @Given /^there are backward remix relations:$/
   * @param TableNode $table
   *
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function thereAreBackwardRemixRelations(TableNode $table)
  {
    $backward_relations = $table->getHash();

    foreach ($backward_relations as $backward_relation)
    {
      @$config = [
        'parent_id' => $backward_relation['parent_id'],
        'child_id'  => $backward_relation['child_id'],
      ];
      $this->insertBackwardRemixRelation($config);
    }
  }

  /**
   * @Given /^there are Scratch remix relations:$/
   * @param TableNode $table
   *
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function thereAreScratchRemixRelations(TableNode $table)
  {
    $scratch_relations = $table->getHash();

    foreach ($scratch_relations as $scratch_relation)
    {
      @$config = [
        'scratch_parent_id' => $scratch_relation['scratch_parent_id'],
        'catrobat_child_id' => $scratch_relation['catrobat_child_id'],
      ];
      $this->insertScratchRemixRelation($config);
    }
  }

  /**
   * @Given /^following programs are featured:$/
   * @param TableNode $table
   *
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function followingProgramsAreFeatured(TableNode $table)
  {
    $em = $this->getManager();
    $featured = $table->getHash();
    for ($i = 0; $i < count($featured); ++$i)
    {
      $program = $this->getProgramManger()->findOneByName($featured[$i]['name']);
      $featured_entry = new FeaturedProgram();
      $featured_entry->setProgram($program);
      $featured_entry->setActive($featured[$i]['active'] == 'yes');
      $featured_entry->setImageType('jpg');
      $featured_entry->setPriority($featured[$i]['priority']);
      $featured_entry->setForIos(isset($featured[$i]['ios_only']) ? $featured[$i]['ios_only'] == 'yes' : false);
      $em->persist($featured_entry);
    }
    $em->flush();
  }

  /**
   * @Given /^there are downloadable programs:$/
   * @param TableNode $table
   *
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function thereAreDownloadablePrograms(TableNode $table)
  {
    $file_repo = $this->getFileRepository();
    $programs = $table->getHash();
    for ($i = 0; $i < count($programs); ++$i)
    {
      $user = $this->getUserManager()->findOneBy([
        'username' => $programs[$i]['owned by'],
      ]);
      $config = [
        'name'                => $programs[$i]['name'],
        'description'         => $programs[$i]['description'],
        'views'               => $programs[$i]['views'],
        'downloads'           => $programs[$i]['downloads'],
        'uploadtime'          => $programs[$i]['upload time'],
        'catrobatversionname' => $programs[$i]['version'],
        'filesize'            => @$programs[$i]['FileSize'],
        'visible'             => isset($programs[$i]['visible']) ? $programs[$i]['visible'] == 'true' : true,
      ];

      $program = $this->insertProgram($user, $config);

      $file_repo->saveProgramfile(new File(self::FIXTUREDIR . 'test.catrobat'), $program->getId());
    }
  }

  /**
   * @Given /^I have a parameter "([^"]*)" with value "([^"]*)"$/
   * @param $name
   * @param $value
   */
  public function iHaveAParameterWithValue($name, $value)
  {
    $this->request_parameters[$name] = $value;
  }

  /**
   * @Given /^I have a parameter "([^"]*)" with the tag id "([^"]*)"$/
   * @param $name
   * @param $value
   */
  public function iHaveAParameterWithTheTagId($name, $value)
  {
    $this->request_parameters[$name] = $value;
  }

  /**
   * @When /^I POST these parameters to "([^"]*)"$/
   * @param $url
   */
  public function iPostTheseParametersTo($url)
  {
    $this->getClient()->request('POST', $url, $this->request_parameters, $this->files, [
      'HTTP_HOST' => $this->hostname,
      'HTTPS'     => $this->secure,
    ]);
  }

  /**
   * @When /^I GET "([^"]*)" with these parameters$/
   * @param $url
   */
  public function iGetWithTheseParameters($url)
  {
    $uri = 'http://' . $this->hostname . $url . '?' . http_build_query($this->request_parameters);
    $this->getClient()->request('GET', $uri, [], $this->files, [
      'HTTP_HOST' => $this->hostname,
      'HTTPS'     => $this->secure,
    ]);
  }

  /**
   * @When /^I GET from the api "([^"]*)"$/
   * @param $url
   */
  public function iGetFromTheApi($url)
  {
    $this->getClient()->request('GET', 'http://' . $this->hostname . $url, [], $this->files, [
      'HTTP_HOST' => $this->hostname,
      'HTTPS'     => $this->secure,
    ]);
  }


  /**
   * @When /^I compute all like similarities between users$/
   */
  public function iComputeAllLikeSimilaritiesBetweenUsers()
  {
    $this->computeAllLikeSimilaritiesBetweenUsers();
  }

  /**
   * @When /^I compute all remix similarities between users$/
   */
  public function iComputeAllRemixSimilaritiesBetweenUsers()
  {
    $this->computeAllRemixSimilaritiesBetweenUsers();
  }

  /**
   * @Given /^I am "([^"]*)"$/
   * @param $username
   */
  public function iAm($username)
  {
    $this->username = $username;
  }

  /**
   * @Given /^I search for "([^"]*)"$/
   * @param $arg1
   */
  public function iSearchFor($arg1)
  {
    $this->iHaveAParameterWithValue('q', $arg1);
    if (isset($this->request_parameters['limit']))
    {
      $this->iHaveAParameterWithValue('limit', $this->request_parameters['limit']);
    }
    else
    {
      $this->iHaveAParameterWithValue('limit', '1');
    }
    if (isset($this->request_parameters['offset']))
    {
      $this->iHaveAParameterWithValue('offset', $this->request_parameters['offset']);
    }
    else
    {
      $this->iHaveAParameterWithValue('offset', '0');
    }
    $this->iGetWithTheseParameters('/pocketcode/api/projects/search.json');
  }

  /**
   * @Then /^I should get a total of (\d+) projects$/
   * @param $arg1
   */
  public function iShouldGetATotalOfProjects($arg1)
  {
    $response = $this->getClient()->getResponse();
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
    $response = $this->getClient()->getResponse();
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
    $response = $this->getClient()->getResponse();
    $responseArray = json_decode($response->getContent(), true);
    Assert::assertFalse(
      isset($responseArray['isUserSpecificRecommendation']),
      'Unexpected isUserSpecificRecommendation parameter found in response!'
    );
  }

  /**
   * @Given /^I use the limit "([^"]*)"$/
   * @param $arg1
   */
  public function iUseTheLimit($arg1)
  {
    $this->iHaveAParameterWithValue('limit', $arg1);
  }

  /**
   * @Given /^I use the offset "([^"]*)"$/
   * @param $arg1
   */
  public function iUseTheOffset($arg1)
  {
    $this->iHaveAParameterWithValue('offset', $arg1);
  }

  /**
   * @When /^I call "([^"]*)" with token "([^"]*)"$/
   * @param $url
   * @param $token
   */
  public function iCallWithToken($url, $token)
  {
    $params = [
      'token'    => $token,
      'username' => $this->username,
    ];
    $this->getClient()->request('GET', $url . '?' . http_build_query($params));
  }

  /**
   * @Then /^I should get the json object:$/
   * @param PyStringNode $string
   */
  public function iShouldGetTheJsonObject(PyStringNode $string)
  {
    $response = $this->getClient()->getResponse();
    Assert::assertJsonStringEqualsJsonString($string->getRaw(), $response->getContent(), '');
  }

  /**
   * @Given /^I store the following json object as "([^"]*)":$/
   * @param string       $name
   * @param PyStringNode $json
   */
  public function iStoreTheFollowingJsonObjectAs(string $name, PyStringNode $json)
  {
    $this->stored_json[$name] = $json->getRaw();
  }

  /**
   * @Then /^I should get the stored json object "([^"]*)"$/
   * @param string $name
   */
  public function iShouldGetTheStoredJsonObject(string $name)
  {
    $response = $this->getClient()->getResponse();
    Assert::assertJsonStringEqualsJsonString($this->stored_json[$name], $response->getContent(), '');
  }

  /**
   * @Then /^I should get the json object with random token:$/
   * @param PyStringNode $string
   */
  public function iShouldGetTheJsonObjectWithRandomToken(PyStringNode $string)
  {
    $response = $this->getClient()->getResponse();
    $responseArray = json_decode($response->getContent(), true);
    $expectedArray = json_decode($string->getRaw(), true);
    $responseArray['token'] = '';
    $expectedArray['token'] = '';
    Assert::assertEquals($expectedArray, $responseArray);
  }

  /**
   * @Then /^I should get the json object with random "([^"]*)" and "([^"]*)":$/
   * @param              $arg1
   * @param              $arg2
   * @param PyStringNode $string
   */
  public function iShouldGetTheJsonObjectWithRandomAndProgramid($arg1, $arg2, PyStringNode $string)
  {
    $response = $this->getClient()->getResponse();
    $responseArray = json_decode($response->getContent(), true);
    $expectedArray = json_decode($string->getRaw(), true);
    $responseArray[$arg1] = $expectedArray[$arg1] = '';
    $responseArray[$arg2] = $expectedArray[$arg2] = '';
    Assert::assertEquals($expectedArray, $responseArray, $response);
  }

  /**
   * @Then /^I should get programs in the following order:$/
   * @param TableNode $table
   */
  public function iShouldGetProgramsInTheFollowingOrder(TableNode $table)
  {
    $response = $this->getClient()->getResponse();
    $responseArray = json_decode($response->getContent(), true);
    $returned_programs = $responseArray['CatrobatProjects'];
    $expected_programs = $table->getHash();

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
   * @param           $program_count
   * @param TableNode $table
   */
  public function iShouldGetProgramsInRandomOrder($program_count, TableNode $table)
  {
    $response = $this->getClient()->getResponse();
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
        if (strcmp($random_programs[$i]['ProjectName'], $expected_programs[$j]['Name']) === 0)
        {
          $program_found = true;
        }
      }
      Assert::assertEquals($program_found, true, 'Program does not exist in the database');
    }
  }

  /**
   * @Then /^I should get the programs "([^"]*)" in random order$/
   * @param string $program_list
   */
  public function iShouldGetTheProgramsInRandomOrder($program_list)
  {
    $response = $this->getClient()->getResponse();
    $response_array = json_decode($response->getContent(), true);
    $random_programs = $response_array['CatrobatProjects'];
    $random_programs_count = count($random_programs);
    $expected_programs = explode(",", $program_list);
    $expected_programs_count = count($expected_programs);
    Assert::assertEquals($expected_programs_count, $random_programs_count, 'Wrong number of random programs');

    for ($i = 0; $i < $random_programs_count; ++$i)
    {
      $program_found = false;
      for ($j = 0; $j < $expected_programs_count; ++$j)
      {
        if (strcmp($random_programs[$i]['ProjectName'], $expected_programs[$j]) === 0)
        {
          $program_found = true;
        }
      }
      Assert::assertEquals($program_found, true, 'Program does not exist in the database');
    }
  }

  /**
   * @Then /^I should get following programs:$/
   * @param TableNode $table
   */
  public function iShouldGetFollowingPrograms(TableNode $table)
  {
    $this->iShouldGetProgramsInTheFollowingOrder($table);
  }

  /**
   * @Then /^I should get the programs "([^"]*)"$/
   * @param string $program_list
   */
  public function iShouldGetThePrograms($program_list)
  {
    $response = $this->getClient()->getResponse();
    $responseArray = json_decode($response->getContent(), true);
    $returned_programs = $responseArray['CatrobatProjects'];
    $expected_programs = explode(",", $program_list);

    for ($i = 0; $i < count($returned_programs); ++$i)
    {
      Assert::assertEquals(
        $expected_programs[$i], $returned_programs[$i]['ProjectName'],
        'Wrong order of results'
      );
    }
  }

  /**
   * @Then /^I should get following like similarities:$/
   * @param TableNode $table
   */
  public function iShouldGetFollowingLikePrograms(TableNode $table)
  {
    $all_like_similarities = $this->getAllLikeSimilaritiesBetweenUsers();
    $all_like_similarities_count = count($all_like_similarities);
    $expected_like_similarities = $table->getHash();
    Assert::assertEquals(count($expected_like_similarities), $all_like_similarities_count,
      'Wrong number of returned similarity entries');
    for ($i = 0; $i < $all_like_similarities_count; ++$i)
    {
      /**
       * @var $like_similarity UserLikeSimilarityRelation
       */
      $like_similarity = $all_like_similarities[$i];
      Assert::assertEquals($expected_like_similarities[$i]['first_user_id'],
        $like_similarity->getFirstUserId(),
        'Wrong value for first_user_id or wrong order of results');
      Assert::assertEquals($expected_like_similarities[$i]['second_user_id'],
        $like_similarity->getSecondUserId(),
        'Wrong value for second_user_id');
      Assert::assertEquals(round($expected_like_similarities[$i]['similarity'], 3),
        round($like_similarity->getSimilarity(), 3),
        'Wrong value for similarity');
    }
  }

  /**
   * @Then /^I should get following remix similarities:$/
   * @param TableNode $table
   */
  public function iShouldGetFollowingRemixPrograms(TableNode $table)
  {
    $all_remix_similarities = $this->getAllRemixSimilaritiesBetweenUsers();
    $all_remix_similarities_count = count($all_remix_similarities);
    $expected_remix_similarities = $table->getHash();
    Assert::assertEquals(count($expected_remix_similarities), $all_remix_similarities_count,
      'Wrong number of returned similarity entries');
    for ($i = 0; $i < $all_remix_similarities_count; ++$i)
    {
      /**
       * @var $remix_similarity UserRemixSimilarityRelation
       */
      $remix_similarity = $all_remix_similarities[$i];
      Assert::assertEquals(
        $expected_remix_similarities[$i]['first_user_id'], $remix_similarity->getFirstUserId(),
        'Wrong value for first_user_id or wrong order of results'
      );
      Assert::assertEquals(
        $expected_remix_similarities[$i]['second_user_id'], $remix_similarity->getSecondUserId(),
        'Wrong value for second_user_id'
      );
      Assert::assertEquals(round($expected_remix_similarities[$i]['similarity'], 3),
        round($remix_similarity->getSimilarity(), 3),
        'Wrong value for similarity');
    }
  }

  /**
   * @Given /^the response code should be "([^"]*)"$/
   * @param $code
   */
  public function theResponseCodeShouldBe($code)
  {
    $response = $this->getClient()->getResponse();
    Assert::assertEquals($code, $response->getStatusCode(), 'Wrong response code. ' . $response->getContent());
  }

  /**
   * @Given /^the returned "([^"]*)" should be a number$/
   * @param $arg1
   */
  public function theReturnedShouldBeANumber($arg1)
  {
    $response = json_decode($this->getClient()
      ->getResponse()
      ->getContent(), true);
    Assert::assertTrue(is_numeric($response[$arg1]));
  }

  /**
   * @Given /^I have a file "([^"]*)"$/
   * @param $filename
   */
  public function iHaveAFile($filename)
  {
    $filepath = './src/Catrobat/ApiBundle/Features/Fixtures/' . $filename;
    Assert::assertTrue(file_exists($filepath), 'File not found');
    $this->files[] = new UploadedFile($filepath, $filename);
  }

  /**
   * @Given /^I have a valid Catrobat file$/
   */
  public function iHaveAValidCatrobatFile()
  {
    $filepath = self::FIXTUREDIR . 'test.catrobat';
    Assert::assertTrue(file_exists($filepath), 'File not found');
    $this->files = [];
    $this->files[] = new UploadedFile($filepath, 'test.catrobat');
  }

  /**
   * @Given /^I have a Catrobat file with an bad word in the description$/
   */
  public function iHaveACatrobatFileWithAnRudeWordInTheDescription()
  {
    $filepath = self::FIXTUREDIR . 'GeneratedFixtures/program_with_rudeword_in_description.catrobat';
    Assert::assertTrue(file_exists($filepath), 'File not found');
    $this->files[] = new UploadedFile($filepath, 'program_with_rudeword_in_description.catrobat');
  }

  /**
   * @Given /^I have a parameter "([^"]*)" with the md5checksum my file$/
   * @param $parameter
   */
  public function iHaveAParameterWithTheMdchecksumMyFile($parameter)
  {
    $this->request_parameters[$parameter] = md5_file($this->files[0]->getPathname());
  }

  /**
   * @Given /^I have a parameter "([^"]*)" with an invalid md5checksum of my file$/
   * @param $parameter
   */
  public function iHaveAParameterWithAnInvalidMdchecksumOfMyFile($parameter)
  {
    $this->request_parameters[$parameter] = 'INVALIDCHECKSUM';
  }

  /**
   * @When /^I upload a catrobat program$/
   */
  public function iUploadACatrobatProgram()
  {
    $this->iHaveAValidCatrobatFile();
    $this->iHaveAParameterWithTheMdchecksumMyFile('fileChecksum');
    $this->request_parameters['username'] = $this->username;
    $this->request_parameters['token'] = 'cccccccccc';
    $this->iPostTheseParametersTo('/pocketcode/api/upload/upload.json');
  }

  /**
   * @When /^I upload another program using token "([^"]*)"$/
   * @param $arg1
   */
  public function iUploadAnotherProgramUsingToken($arg1)
  {
    $this->iHaveAValidCatrobatFile();
    $this->iHaveAParameterWithTheMdchecksumMyFile('fileChecksum');
    $this->request_parameters['username'] = $this->username;
    $this->request_parameters['token'] = $arg1;
    $this->iPostTheseParametersTo('/pocketcode/api/upload/upload.json');
  }

  /**
   * @When /^I upload another program with name set to "([^"]*)" and url set to "([^"]*)"$/
   * @param $name
   * @param $url
   */
  public function iUploadAnotherProgramWithNameSetToAndUrlSetTo($name, $url)
  {
    $this->iHaveAProgramWithAsTwoHeaderFields('name', $name, 'url', $url);
    $this->iUploadAProgram();
  }

  // @formatter:off
  /**
   * @When /^I upload another program with name set to "([^"]*)", url set to "([^"]*)" and catrobatLanguageVersion set to "([^"]*)"$/
   * @param $name
   * @param $url
   * @param $catrobat_language_version
   */
  // @formatter:on
  public function iUploadAnotherProgramWithNameSetToUrlSetToAndCatrobatLanguageVersionSetTo(
    $name, $url, $catrobat_language_version
  )
  {
    $this->iHaveAProgramWithAsMultipleHeaderFields('name', $name, 'url', $url,
      'catrobatLanguageVersion', $catrobat_language_version);
    $this->iUploadAProgram();
  }

  /**
   * @Then /^It should be uploaded$/
   */
  public function itShouldBeUploaded()
  {
    $json = json_decode($this->getClient()
      ->getResponse()
      ->getContent(), true);
    Assert::assertEquals('200', $json['statusCode'], $this->getClient()
      ->getResponse()
      ->getContent());
  }

  /**
   * @When /^I upload a catrobat program with the same name$/
   */
  public function iUploadACatrobatProgramWithTheSameName()
  {
    /**
     * @var $user User
     */
    $user = $this->getUserManager()->findUserByUsername($this->username);
    $this->request_parameters['token'] = $user->getUploadToken();
    $this->last_response = $this->getClient()
      ->getResponse()
      ->getContent();
    $this->iPostTheseParametersTo('/pocketcode/api/upload/upload.json');
  }

  /**
   * @Then /^it should be updated$/
   */
  public function itShouldBeUpdated()
  {
    $last_json = json_decode($this->last_response, true);
    $json = json_decode($this->getClient()
      ->getResponse()
      ->getContent(), true);
    Assert::assertEquals($last_json['projectId'], $json['projectId'], $this->getClient()
      ->getResponse()
      ->getContent());
  }

  /**
   * @Given /^I have a parameter "([^"]*)" with the md5checksum of "([^"]*)"$/
   * @param $parameter
   * @param $file
   */
  public function iHaveAParameterWithTheMdchecksumOf($parameter, $file)
  {
    $this->request_parameters[$parameter] = md5_file($this->files[0]->getPathname());
  }

  /**
   * @Given /^the next generated token will be "([^"]*)"$/
   * @param $token
   */
  public function theNextGeneratedTokenWillBe($token)
  {
    $token_generator = $this->getSymfonyService('tokengenerator');
    $token_generator->setTokenGenerator(new FixedTokenGenerator($token));
  }

  /**
   * @Given /^the current time is "([^"]*)"$/
   * @param $time
   *
   * @throws Exception
   */
  public function theCurrentTimeIs($time)
  {
    $date = new DateTime($time, new DateTimeZone('UTC'));
    $time_service = $this->getSymfonyService('time');
    $time_service->setTime(new FixedTime($date->getTimestamp()));
  }

  /**
   * @Given /^I have the POST parameters:$/
   * @param TableNode $table
   */
  public function iHaveThePostParameters(TableNode $table)
  {
    $parameters = $table->getHash();
    foreach ($parameters as $parameter)
    {
      $this->request_parameters[$parameter['name']] = $parameter['value'];
    }
  }

  /**
   * @When /^I try to register without (.*)$/
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
    $this->sendPostRequest('/pocketcode/api/loginOrRegister/loginOrRegister.json');
  }

  /**
   * @Given /^I have otherwise valid registration parameters$/
   */
  public function iHaveOtherwiseValidRegistrationParameters()
  {
    $this->prepareValidRegistrationParameters();
  }

  /**
   * @When /^I try to register$/
   */
  public function iTryToRegister()
  {
    $this->sendPostRequest('/pocketcode/api/loginOrRegister/loginOrRegister.json');
  }

  /**
   * @Given /^there are uploaded programs:$/
   * @param TableNode $table
   */
  public function thereAreUploadedPrograms(TableNode $table)
  {
    throw new PendingException();
  }

  /**
   * @When /^I have downloaded a valid program$/
   */
  public function iHaveDownloadedAValidProgram()
  {
    $this->iDownload('/pocketcode/download/1.catrobat');
    $this->iShouldReceiveAFile();
    $this->theResponseCodeShouldBe(200);
  }

  // @formatter:off
  /**
   * @Then /^the program download statistic should have a download timestamp, an anonymous user and the following statistics:$/
   * @param TableNode $table
   *
   * @throws Exception
   */
  // @formatter:on
  public function theProgramShouldHaveADownloadTimestampAndTheFollowingStatistics(TableNode $table)
  {
    $statistics = $table->getHash();
    for ($i = 0; $i < count($statistics); ++$i)
    {
      $ip = $statistics[$i]['ip'];
      $country_code = $statistics[$i]['country_code'];
      if ($country_code === "NULL")
      {
        $country_code = null;
      }
      $country_name = $statistics[$i]['country_name'];
      if ($country_name === "NULL")
      {
        $country_name = null;
      }
      $program_id = $statistics[$i]['program_id'];

      /**
       * @var $program_download_statistics ProgramDownloads
       */
      $repository = $this->getManager()->getRepository('App\Entity\ProgramDownloads');
      $program_download_statistics = $repository->find(1);

      Assert::assertEquals($ip, $program_download_statistics->getIp(), "Wrong IP in download statistics");
      Assert::assertEquals(
        $country_code, $program_download_statistics->getCountryCode(),
        "Wrong country code in download statistics"
      );
      Assert::assertEquals(
        $country_name, strtoUpper($program_download_statistics->getCountryName()),
        "Wrong country name in download statistics"
      );
      Assert::assertEquals(
        $program_id, $program_download_statistics->getProgram()->getId(),
        "Wrong program ID in download statistics"
      );
      Assert::assertNull($program_download_statistics->getUser(), "Wrong username in download statistics");
      Assert::assertNotEmpty(
        $program_download_statistics->getUserAgent(),
        "No user agent was written to download statistics"
      );

      $limit = 5.0;

      $download_time = $program_download_statistics->getDownloadedAt();
      $current_time = new DateTime();

      $time_delta = $current_time->getTimestamp() - $download_time->getTimestamp();

      Assert::assertTrue(
        $time_delta < $limit,
        "Download time difference in download statistics too high"
      );
    }
  }

  /**
   * @When /^i download "([^"]*)"$/
   * @param $arg1
   */
  public function iDownload($arg1)
  {
    $this->getClient()->request('GET', $arg1);
  }

  /**
   * @Then /^i should receive a file$/
   */
  public function iShouldReceiveAFile()
  {
    $content_type = $this->getClient()->getResponse()->headers->get('Content-Type');
    Assert::assertEquals('application/zip', $content_type);
  }

  /**
   * @When /^I register a new user$/
   */
  public function iRegisterANewUser()
  {
    $this->prepareValidRegistrationParameters();
    $this->sendPostRequest('/pocketcode/api/loginOrRegister/loginOrRegister.json');
  }

  /**
   * @When /^I try to register another user with the same email adress$/
   */
  public function iTryToRegisterAnotherUserWithTheSameEmailAdress()
  {
    $this->prepareValidRegistrationParameters();
    $this->request_parameters['registrationUsername'] = 'AnotherUser';
    $this->sendPostRequest('/pocketcode/api/loginOrRegister/loginOrRegister.json');
  }

  /**
   * @Given /^I am using pocketcode for "([^"]*)" with version "([^"]*)"$/
   * @param $platform
   * @param $version
   */
  public function iAmUsingPocketcodeForWithVersion($platform, $version)
  {
    $this->generateProgramFileWith([
      'platform'           => $platform,
      'applicationVersion' => $version,
    ]);
  }

  /**
   * @Given /^I have a program with "([^"]*)" set to "([^"]*)"$/
   * @param $key
   * @param $value
   */
  public function iHaveAProgramWithAs($key, $value)
  {
    $this->generateProgramFileWith([
      $key => $value,
    ]);
  }

  /**
   * @Given /^I have a program with "([^"]*)" set to "([^"]*)" and "([^"]*)" set to "([^"]*)"$/
   * @param $key1
   * @param $value1
   * @param $key2
   * @param $value2
   */
  public function iHaveAProgramWithAsTwoHeaderFields($key1, $value1, $key2, $value2)
  {
    $this->generateProgramFileWith([
      $key1 => $value1,
      $key2 => $value2,
    ]);
  }

  // @formatter:off
  /**
   * @Given /^I have a program with "([^"]*)" set to "([^"]*)", "([^"]*)" set to "([^"]*)" and "([^"]*)" set to "([^"]*)"$/
   * @param $key1
   * @param $value1
   * @param $key2
   * @param $value2
   * @param $key3
   * @param $value3
   */
  // @formatter:on
  public function iHaveAProgramWithAsMultipleHeaderFields($key1, $value1, $key2, $value2, $key3, $value3)
  {
    $this->generateProgramFileWith([
      $key1 => $value1,
      $key2 => $value2,
      $key3 => $value3,
    ]);
  }

  /**
   * @When /^I upload a program$/
   */
  public function iUploadAProgram()
  {
    $user = $this->username ? $this->getUserManager()->findUserByUsername($this->username) : null;
    $this->upload(sys_get_temp_dir() . '/program_generated.catrobat', $user);
  }

  /**
   * @Given /^I am using pocketcode with language version "([^"]*)"$/
   * @param $version
   */
  public function iAmUsingPocketcodeWithLanguageVersion($version)
  {
    $this->generateProgramFileWith([
      'catrobatLanguageVersion' => $version,
    ]);
  }

  /**
   * @Then /^The upload should be successful$/
   */
  public function theUploadShouldBeSuccessful()
  {
    $response = $this->getClient()->getResponse();
    $responseArray = json_decode($response->getContent(), true);
    Assert::assertEquals(200, $responseArray['statusCode']);
  }

  /**
   * @Given /^the server name is "([^"]*)"$/
   * @param $name
   */
  public function theServerNameIs($name)
  {
    $this->hostname = $name;
  }

  /**
   * @Given /^I use a secure connection$/
   */
  public function iUseASecureConnection()
  {
    $this->secure = true;
  }

  /**
   * @Given /^program "([^"]*)" is not visible$/
   * @param $programname
   *
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function programIsNotVisible($programname)
  {
    $em = $this->getManager();
    $program = $this->getProgramManger()->findOneByName($programname);
    if ($program == null)
    {
      throw new PendingException('There is no program named ' . $programname);
    }
    $program->setVisible(false);
    $em->persist($program);
    $em->flush();
  }

  /**
   * @When /^I get the most recent programs$/
   */
  public function iGetTheMostRecentPrograms()
  {
    $this->getClient()->request('GET', '/pocketcode/api/projects/recent.json');
  }

  /**
   * @When /^I get the most recent programs with limit "([^"]*)" and offset "([^"]*)"$/
   * @param $limit
   * @param $offset
   */
  public function iGetTheMostRecentProgramsWithLimitAndOffset($limit, $offset)
  {
    $this->getClient()->request('GET', '/pocketcode/api/projects/recent.json', [
      'limit'  => $limit,
      'offset' => $offset,
    ]);
  }

  /**
   * @Given /^I upload the program with "([^"]*)" as name$/
   * @param $name
   */
  public function iUploadTheProgramWithAsName($name)
  {
    $this->generateProgramFileWith([
      'name' => $name,
    ]);
    $this->upload(sys_get_temp_dir() . '/program_generated.catrobat', null);
  }

  /**
   * @When /^I upload the program with "([^"]*)" as name again$/
   * @param $name
   */
  public function iUploadTheProgramWithAsNameAgain($name)
  {
    $this->iUploadTheProgramWithAsName($name);
  }

  /**
   * @Then /^the uploaded program should be a remix root$/
   */
  public function theUploadedProgramShouldBeARemixRoot()
  {
    $json = json_decode($this->getClient()->getResponse()->getContent(), true);
    $this->theProgramShouldBeARemixRoot($json["projectId"]);
  }

  /**
   * @Then /^the program "([^"]*)" should be a remix root$/
   * @param $program_id
   */
  public function theProgramShouldBeARemixRoot($program_id)
  {
    $program_manager = $this->getProgramManger();
    $uploaded_program = $program_manager->find($program_id);
    Assert::assertTrue($uploaded_program->isRemixRoot());
  }

  /**
   * @Then /^the uploaded program should not be a remix root$/
   */
  public function theUploadedProgramShouldNotBeARemixRoot()
  {
    $json = json_decode($this->getClient()->getResponse()->getContent(), true);
    $this->theProgramShouldNotBeARemixRoot($json["projectId"]);
  }

  /**
   * @Then /^the uploaded program should have remix migration date NOT NULL$/
   */
  public function theUploadedProgramShouldHaveMigrationDateNotNull()
  {
    $json = json_decode($this->getClient()->getResponse()->getContent(), true);
    $program_manager = $this->getProgramManger();
    $uploaded_program = $program_manager->find($json["projectId"]);
    Assert::assertNotNull($uploaded_program->getRemixMigratedAt());
  }

  /**
   * @Then /^the program "([^"]*)" should not be a remix root$/
   * @param $program_id
   */
  public function theProgramShouldNotBeARemixRoot($program_id)
  {
    $program_manager = $this->getProgramManger();
    $uploaded_program = $program_manager->find($program_id);
    Assert::assertFalse($uploaded_program->isRemixRoot());
  }

  /**
   * @Given /^the uploaded program should have a Scratch parent having id "([^"]*)"$/
   * @param $scratch_parent_id
   */
  public function theUploadedProgramShouldHaveAScratchParentHavingScratchID($scratch_parent_id)
  {
    $json = json_decode($this->getClient()->getResponse()->getContent(), true);
    $this->theProgramShouldHaveAScratchParentHavingScratchID($json["projectId"], $scratch_parent_id);
  }

  /**
   * @Given /^the program "([^"]*)" should have a Scratch parent having id "([^"]*)"$/
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
   * @Given /^the uploaded program should have no further Scratch parents$/
   */
  public function theUploadedProgramShouldHaveNoFurtherScratchParents()
  {
    $json = json_decode($this->getClient()->getResponse()->getContent(), true);
    $this->theProgramShouldHaveNoFurtherScratchParents($json["projectId"]);
  }

  /**
   * @Given /^the program "([^"]*)" should have no further Scratch parents$/
   * @param $program_id
   */
  public function theProgramShouldHaveNoFurtherScratchParents($program_id)
  {
    $direct_edge_relations = $this->getScratchProgramRemixRepository()->findBy([
      'catrobat_child_id' => $program_id,
    ]);

    $further_scratch_parent_relations = array_filter($direct_edge_relations,
      function (ScratchProgramRemixRelation $relation) {
        return !array_key_exists(
          $relation->getUniqueKey(), $this->checked_catrobat_remix_forward_ancestor_relations
        );
      });

    Assert::assertCount(0, $further_scratch_parent_relations);
  }

  /**
   * @Then /^the uploaded program should have a Catrobat forward ancestor having id "([^"]*)" and depth "([^"]*)"$/
   * @param $id
   * @param $depth
   */
  public function theUploadedProgramShouldHaveACatrobatForwardAncestorHavingIdAndDepth($id, $depth)
  {
    $json = json_decode($this->getClient()->getResponse()->getContent(), true);
    $this->theProgramShouldHaveACatrobatForwardAncestorHavingIdAndDepth($json["projectId"], $id, $depth);
  }

  /**
   * @Then /^the program "([^"]*)" should have a Catrobat forward ancestor having id "([^"]*)" and depth "([^"]*)"$/
   * @param $program_id
   * @param $ancestor_program_id
   * @param $depth
   */
  public function theProgramShouldHaveACatrobatForwardAncestorHavingIdAndDepth($program_id, $ancestor_program_id, $depth)
  {
    $forward_ancestor_relation = $this->getProgramRemixForwardRepository()->findOneBy([
      'ancestor_id'   => $ancestor_program_id,
      'descendant_id' => $program_id,
      'depth'         => $depth,
    ]);

    Assert::assertNotNull($forward_ancestor_relation);
    $this->checked_catrobat_remix_forward_ancestor_relations[$forward_ancestor_relation->getUniqueKey()] =
      $forward_ancestor_relation;

    if ($program_id == $ancestor_program_id && $depth == 0)
    {
      $this->checked_catrobat_remix_forward_descendant_relations[$forward_ancestor_relation->getUniqueKey()] =
        $forward_ancestor_relation;
    }
  }

  /**
   * @Then /^the uploaded program should have a Catrobat backward parent having id "([^"]*)"$/
   * @param $id
   */
  public function theUploadedProgramShouldHaveACatrobatBackwardParentHavingId($id)
  {
    $json = json_decode($this->getClient()->getResponse()->getContent(), true);
    $this->theProgramShouldHaveACatrobatBackwardParentHavingId($json["projectId"], $id);
  }

  /**
   * @Then /^the program "([^"]*)" should have a Catrobat backward parent having id "([^"]*)"$/
   * @param $program_id
   * @param $backward_parent_program_id
   */
  public function theProgramShouldHaveACatrobatBackwardParentHavingId($program_id, $backward_parent_program_id)
  {
    $backward_parent_relation = $this->getProgramRemixBackwardRepository()->findOneBy([
      'parent_id' => $backward_parent_program_id,
      'child_id'  => $program_id,
    ]);

    Assert::assertNotNull($backward_parent_relation);
    $this->checked_catrobat_remix_backward_relations[$backward_parent_relation->getUniqueKey()] =
      $backward_parent_relation;
  }

  /**
   * @Then /^the uploaded program should have no Catrobat forward ancestors except self-relation$/
   */
  public function theUploadedProgramShouldHaveNoCatrobatForwardAncestorsExceptSelfRelation()
  {
    $json = json_decode($this->getClient()->getResponse()->getContent(), true);
    $this->theProgramShouldHaveNoCatrobatForwardAncestorsExceptSelfRelation($json["projectId"]);
  }

  /**
   * @Then /^the program "([^"]*)" should have no Catrobat forward ancestors except self-relation$/
   * @param $program_id
   */
  public function theProgramShouldHaveNoCatrobatForwardAncestorsExceptSelfRelation($program_id)
  {
    $forward_ancestors_including_self_referencing_relation = $this
      ->getProgramRemixForwardRepository()
      ->findBy(['descendant_id' => $program_id]);

    Assert::assertCount(0, array_filter($forward_ancestors_including_self_referencing_relation,
      function (ProgramRemixRelation $relation) {
        return $relation->getDepth() >= 1;
      }));
  }

  /**
   * @Then /^the uploaded program should have no further Catrobat forward ancestors$/
   */
  public function theUploadedProgramShouldHaveNoFurtherCatrobatForwardAncestors()
  {
    $json = json_decode($this->getClient()->getResponse()->getContent(), true);
    $this->theProgramShouldHaveNoFurtherCatrobatForwardAncestors($json["projectId"]);
  }

  /**
   * @Then /^the program "([^"]*)" should have no further Catrobat forward ancestors$/
   * @param $program_id
   */
  public function theProgramShouldHaveNoFurtherCatrobatForwardAncestors($program_id)
  {
    $forward_ancestors_including_self_referencing_relation = $this
      ->getProgramRemixForwardRepository()
      ->findBy(['descendant_id' => $program_id]);

    $further_forward_ancestor_relations = array_filter($forward_ancestors_including_self_referencing_relation,
      function (ProgramRemixRelation $relation) {
        return !array_key_exists(
          $relation->getUniqueKey(), $this->checked_catrobat_remix_forward_ancestor_relations
        );
      });

    Assert::assertCount(0, $further_forward_ancestor_relations);
  }

  /**
   * @Then /^the uploaded program should have no Catrobat backward parents$/
   */
  public function theUploadedProgramShouldHaveNoCatrobatBackwardParents()
  {
    $json = json_decode($this->getClient()->getResponse()->getContent(), true);
    $this->theProgramShouldHaveNoCatrobatBackwardParents($json["projectId"]);
  }

  /**
   * @Then /^the program "([^"]*)" should have no Catrobat backward parents$/
   * @param $program_id
   */
  public function theProgramShouldHaveNoCatrobatBackwardParents($program_id)
  {
    $backward_parent_relations = $this->getProgramRemixBackwardRepository()->findBy(['child_id' => $program_id]);
    Assert::assertCount(0, $backward_parent_relations);
  }

  /**
   * @Then /^the uploaded program should have no further Catrobat backward parents$/
   */
  public function theUploadedProgramShouldHaveNoFurtherCatrobatBackwardParents()
  {
    $json = json_decode($this->getClient()->getResponse()->getContent(), true);
    $this->theProgramShouldHaveNoFurtherCatrobatBackwardParents($json["projectId"]);
  }

  /**
   * @Then /^the program "([^"]*)" should have no further Catrobat backward parents$/
   * @param $program_id
   */
  public function theProgramShouldHaveNoFurtherCatrobatBackwardParents($program_id)
  {
    $backward_parent_relations = $this->getProgramRemixBackwardRepository()->findBy(['child_id' => $program_id]);

    $further_backward_parent_relations = array_filter($backward_parent_relations,
      function (ProgramRemixBackwardRelation $relation) {
        return !array_key_exists(
          $relation->getUniqueKey(), $this->checked_catrobat_remix_backward_relations
        );
      });

    Assert::assertCount(0, $further_backward_parent_relations);
  }

  /**
   * @Then /^the uploaded program should have no Catrobat ancestors except self-relation$/
   */
  public function theUploadedProgramShouldHaveNoCatrobatAncestors()
  {
    $json = json_decode($this->getClient()->getResponse()->getContent(), true);
    $this->theProgramShouldHaveNoCatrobatAncestors($json["projectId"]);
  }

  /**
   * @Then /^the program "([^"]*)" should have no Catrobat ancestors except self-relation$/
   * @param $program_id
   */
  public function theProgramShouldHaveNoCatrobatAncestors($program_id)
  {
    $this->theProgramShouldHaveNoCatrobatForwardAncestorsExceptSelfRelation($program_id);
    $this->theProgramShouldHaveNoCatrobatBackwardParents($program_id);
  }

  /**
   * @Then /^the uploaded program should have no Scratch parents$/
   */
  public function theUploadedProgramShouldHaveNoScratchParents()
  {
    $json = json_decode($this->getClient()->getResponse()->getContent(), true);
    $this->theProgramShouldHaveNoScratchParents($json["projectId"]);
  }

  /**
   * @Then /^the program "([^"]*)" should have no Scratch parents$/
   * @param $program_id
   */
  public function theProgramShouldHaveNoScratchParents($program_id)
  {
    $scratch_parents = $this->getScratchProgramRemixRepository()->findBy(['catrobat_child_id' => $program_id]);
    Assert::assertCount(0, $scratch_parents);
  }

  /**
   * @Then /^the uploaded program should have a Catrobat forward descendant having id "([^"]*)" and depth "([^"]*)"$/
   * @param $id
   * @param $depth
   */
  public function theUploadedProgramShouldHaveCatrobatForwardDescendantHavingIdAndDepth($id, $depth)
  {
    $json = json_decode($this->getClient()->getResponse()->getContent(), true);
    $this->theProgramShouldHaveCatrobatForwardDescendantHavingIdAndDepth($json["projectId"], $id, $depth);
  }

  /**
   * @Then /^the program "([^"]*)" should have a Catrobat forward descendant having id "([^"]*)" and depth "([^"]*)"$/
   * @param $program_id
   * @param $descendant_program_id
   * @param $depth
   */
  public function theProgramShouldHaveCatrobatForwardDescendantHavingIdAndDepth($program_id, $descendant_program_id, $depth)
  {
    /** @var ProgramRemixRelation $forward_descendant_relation */
    $forward_descendant_relation = $this->getProgramRemixForwardRepository()->findOneBy([
      'ancestor_id'   => $program_id,
      'descendant_id' => $descendant_program_id,
      'depth'         => $depth,
    ]);

    Assert::assertNotNull($forward_descendant_relation);
    $this->checked_catrobat_remix_forward_descendant_relations[$forward_descendant_relation->getUniqueKey()] =
      $forward_descendant_relation;

    if ($program_id == $descendant_program_id && $depth == 0)
    {
      $this->checked_catrobat_remix_forward_ancestor_relations[$forward_descendant_relation->getUniqueKey()] =
        $forward_descendant_relation;
    }
  }

  /**
   * @Then /^the uploaded program should have no Catrobat forward descendants except self-relation$/
   */
  public function theUploadedProgramShouldHaveNoCatrobatForwardDescendantsExceptSelfRelation()
  {
    $json = json_decode($this->getClient()->getResponse()->getContent(), true);
    $this->theProgramShouldHaveNoCatrobatForwardDescendantsExceptSelfRelation($json["projectId"]);
  }

  /**
   * @Then /^the program "([^"]*)" should have no Catrobat forward descendants except self-relation$/
   * @param $program_id
   */
  public function theProgramShouldHaveNoCatrobatForwardDescendantsExceptSelfRelation($program_id)
  {
    $forward_ancestors_including_self_referencing_relation = $this
      ->getProgramRemixForwardRepository()
      ->findBy(['ancestor_id' => $program_id]);

    Assert::assertCount(0, array_filter($forward_ancestors_including_self_referencing_relation,
      function (ProgramRemixRelation $relation) {
        return $relation->getDepth() >= 1;
      }));
  }

  /**
   * @Then /^the uploaded program should have no further Catrobat forward descendants$/
   */
  public function theUploadedProgramShouldHaveNoFurtherCatrobatForwardDescendants()
  {
    $json = json_decode($this->getClient()->getResponse()->getContent(), true);
    $this->theProgramShouldHaveNoFurtherCatrobatForwardDescendants($json["projectId"]);
  }

  /**
   * @Then /^the program "([^"]*)" should have no further Catrobat forward descendants$/
   * @param $program_id
   */
  public function theProgramShouldHaveNoFurtherCatrobatForwardDescendants($program_id)
  {
    $forward_descendants_including_self_referencing_relation = $this
      ->getProgramRemixForwardRepository()
      ->findBy(['ancestor_id' => $program_id]);

    $further_forward_descendant_relations = array_filter($forward_descendants_including_self_referencing_relation,
      function (ProgramRemixRelation $relation) {
        return !array_key_exists(
          $relation->getUniqueKey(), $this->checked_catrobat_remix_forward_descendant_relations
        );
      });

    Assert::assertCount(0, $further_forward_descendant_relations);
  }

  /**
   * @Then /^the uploaded program should have RemixOf "([^"]*)" in the xml$/
   * @param $value
   *
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function theUploadedProgramShouldHaveRemixofInTheXml($value)
  {
    $json = json_decode($this->getClient()->getResponse()->getContent(), true);
    $this->theProgramShouldHaveRemixofInTheXml($json["projectId"], $value);
  }

  /**
   * @Then /^the program "([^"]*)" should have RemixOf "([^"]*)" in the xml$/
   * @param $program_id
   * @param $value
   *
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function theProgramShouldHaveRemixofInTheXml($program_id, $value)
  {
    $program_manager = $this->getProgramManger();
    $uploaded_program = $program_manager->find($program_id);
    $efr = $this->getExtractedFileRepository();
    /**
     * @var Program $uploaded_program *
     */
    $extractedCatrobatFile = $efr->loadProgramExtractedFile($uploaded_program);
    $progXmlProp = $extractedCatrobatFile->getProgramXmlProperties();
    Assert::assertEquals($value, $progXmlProp->header->remixOf->__toString());
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
    $this->iPostTheseParametersTo('/pocketcode/api/upload/upload.json');
    $this->iHaveAParameterWithTheReturnedProjectid('program');
  }

  /**
   * @When /^I report the program$/
   */
  public function iReportTheProgram()
  {
    $this->iHaveAParameterWithValue('note', 'Bad Project');
    $this->iPostTheseParametersTo('/pocketcode/api/reportProgram/reportProgram.json');
  }


  /**
   * @When /^I have a parameter "([^"]*)" with the returned projectId$/
   * @param $name
   */
  public function iHaveAParameterWithTheReturnedProjectid($name)
  {
    $response = json_decode($this->getClient()
      ->getResponse()
      ->getContent(), true);
    $this->request_parameters[$name] = $response['projectId'];
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
   * @When /^I GET the tag list from "([^"]*)" with these parameters$/
   * @param $url
   */
  public function iGetTheTagListFromWithTheseParameters($url)
  {
    $uri = $url . '?' . http_build_query($this->request_parameters);
    $this->getClient()->request('GET', $uri, [], $this->files, [
      'HTTP_HOST' => $this->hostname,
      'HTTPS'     => $this->secure,
    ]);
  }

  /**
   * @Given /^I use the "([^"]*)" app$/
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
   * @Then /^the program should be tagged with "([^"]*)" in the database$/
   * @param $arg1
   */
  public function theProgramShouldBeTaggedWithInTheDatabase($arg1)
  {
    $program_tags = $this->getProgramManger()->find(2)->getTags();
    $tags = explode(',', $arg1);
    Assert::assertEquals(count($program_tags), count($tags), 'Too much or too less tags found!');

    foreach ($program_tags as $program_tag)
    {
      /**
       * @var $program_tag Tag
       */
      if (!(in_array($program_tag->getDe(), $tags) || in_array($program_tag->getEn(), $tags)))
      {
        Assert::assertTrue(false, 'The tag is not found!');
      }
    }
  }

  /**
   * @Then /^the program should not be tagged$/
   */
  public function theProgramShouldNotBeTagged()
  {
    $program_tags = $this->getProgramManger()->find(2)->getTags();
    Assert::assertEquals(0, count($program_tags), 'The program is tagged but should not be tagged');
  }

  /**
   * @When /^I upload this program again with the tags "([^"]*)"$/
   * @param $tags
   */
  public function iUploadThisProgramAgainWithTheTags($tags)
  {
    $this->generateProgramFileWith([
      'tags' => $tags,
    ]);
    $file = sys_get_temp_dir() . '/program_generated.catrobat';
    $this->upload($file, null, 'pocketcode', $this->request_parameters);
  }

  /**
   * @Given /^I have a program with Arduino, Lego and Phiro extensions$/
   */
  public function iHaveAProgramWithArduinoLegoAndPhiroExtensions()
  {
    $filesystem = new Filesystem();
    $original_file = self::FIXTUREDIR . 'extensions.catrobat';
    $target_file = sys_get_temp_dir() . "/program_generated.catrobat";
    $filesystem->copy($original_file, $target_file, true);

  }

  /**
   * @Then /^the program should be marked with extensions in the database$/
   */
  public function theProgramShouldBeMarkedWithExtensionsInTheDatabase()
  {
    $program_extensions = $this->getProgramManger()->find(2)->getExtensions();

    Assert::assertEquals(count($program_extensions), 3, 'Too much or too less tags found!');

    $ext = ["Arduino", "Lego", "Phiro"];
    foreach ($program_extensions as $program_extension)
    {
      /**
       * @var $program_extension Extension
       */
      if (!(in_array($program_extension->getName(), $ext)))
      {
        Assert::assertTrue(false, 'The Extension is not found!');
      }
    }
  }

  /**
   * @When /^I upload the program again without extensions$/
   */
  public function iUploadTheProgramAgainWithoutExtensions()
  {
    $this->iHaveAProgramWithAs("name", "extensions");
    $this->iUploadAProgram();
  }

  /**
   * @Then /^the program should be marked with no extensions in the database$/
   */
  public function theProgramShouldBeMarkedWithNoExtensionsInTheDatabase()
  {
    $program_extensions = $this->getProgramManger()->find(2)->getExtensions();

    Assert::assertEquals(count($program_extensions), 0, 'Too much or too less tags found!');

    $ext = ["Arduino", "Lego", "Phiro"];
    foreach ($program_extensions as $program_extension)
    {
      /**
       * @var $program_extension Extension
       */
      if (!(in_array($program_extension->getName(), $ext)))
      {
        Assert::assertTrue(false, 'The Extension is not found!');
      }
    }
  }

  /**
   * @When /^I search similar programs for program id "([^"]*)"$/
   * @param $id
   */
  public function iSearchSimilarProgramsForProgramId($id)
  {
    $this->iHaveAParameterWithValue('program_id', $id);
    if (isset($this->request_parameters['limit']))
    {
      $this->iHaveAParameterWithValue('limit', $this->request_parameters['limit']);
    }
    else
    {
      $this->iHaveAParameterWithValue('limit', '1');
    }
    if (isset($this->request_parameters['offset']))
    {
      $this->iHaveAParameterWithValue('offset', $this->request_parameters['offset']);
    }
    else
    {
      $this->iHaveAParameterWithValue('offset', '0');
    }
    $this->iGetWithTheseParameters('/pocketcode/api/projects/recsys.json');
  }

  /**
   * @Given /^there are mediapackages:$/
   * @param TableNode $table
   *
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function thereAreMediapackages(TableNode $table)
  {
    /**
     * @var $em EntityManager
     */
    $em = $this->getManager();
    $packages = $table->getHash();
    foreach ($packages as $package)
    {
      $new_package = new MediaPackage();
      $new_package->setName($package['name']);
      $new_package->setNameUrl($package['name_url']);
      $em->persist($new_package);
    }
    $em->flush();
  }

  /**
   * @Given /^there are mediapackage categories:$/
   * @param TableNode $table
   *
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function thereAreMediapackageCategories(TableNode $table)
  {
    /**
     * @var $em EntityManager
     */
    $em = $this->getManager();
    $categories = $table->getHash();
    foreach ($categories as $category)
    {
      $new_category = new MediaPackageCategory();
      $new_category->setName($category['name']);
      $package = $em->getRepository('App\Entity\MediaPackage')->findOneBy(['name' => $category['package']]);
      if ($package == null)
      {
        Assert::assertTrue(false, "Fatal error package not found");
      }
      $new_category->setPackage([$package]);
      $current_categories = $package->getCategories();
      $current_categories = $current_categories == null ? [] : $current_categories;
      array_push($current_categories, $new_category);
      $package->setCategories($current_categories);
      $em->persist($new_category);
    }
    $em->flush();
  }

  /**
   * @Given /^there are mediapackage files:$/
   * @param TableNode $table
   *
   * @throws ORMException
   * @throws OptimisticLockException
   * @throws ImagickException
   */
  public function thereAreMediapackageFiles(TableNode $table)
  {
    /**
     * @var $em        EntityManager
     * @var $file_repo MediaPackageFileRepository
     */
    $em = $this->getManager();
    $file_repo = $this->getMediaPackageFileRepository();
    $files = $table->getHash();
    foreach ($files as $file)
    {
      $new_file = new MediaPackageFile();
      $new_file->setName($file['name']);
      $new_file->setDownloads(0);
      $new_file->setExtension($file['extension']);
      $new_file->setActive($file['active']);
      $category = $em->getRepository('App\Entity\MediaPackageCategory')->findOneBy(['name' => $file['category']]);
      if ($category == null)
      {
        Assert::assertTrue(false, "Fatal error category not found");
      }
      $new_file->setCategory($category);
      $old_files = $category->getFiles();
      $old_files = $old_files == null ? [] : $old_files;
      array_push($old_files, $new_file);
      $category->setFiles($old_files);
      if (!empty($file['flavor']))
      {
        $new_file->setFlavor($file['flavor']);
      }
      $new_file->setAuthor($file['author']);

      $file_repo->saveMediaPackageFile(new File(self::MEDIAPACKAGE_DIR . $file['id'] . '.' .
        $file['extension']), $file['id'], $file['extension']);

      $em->persist($new_file);
    }
    $em->flush();
  }

  /**
   * @Then /^We can\'t test anything here$/
   *
   * @throws Exception
   */
  public function weCantTestAnythingHere()
  {
    throw new Exception(":(");
  }

}

