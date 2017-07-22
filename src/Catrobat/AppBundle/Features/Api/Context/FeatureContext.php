<?php
namespace Catrobat\AppBundle\Features\Api\Context;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Mink\Exception\Exception;
use Catrobat\AppBundle\Entity\MediaPackage;
use Catrobat\AppBundle\Entity\MediaPackageCategory;
use Catrobat\AppBundle\Entity\MediaPackageFile;
use Catrobat\AppBundle\Entity\ProgramDownloads;
use Catrobat\AppBundle\Entity\ProgramDownloadsRepository;
use Catrobat\AppBundle\Entity\RudeWord;
use Catrobat\AppBundle\Features\Helpers\BaseContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Catrobat\AppBundle\Entity\User;
use Catrobat\AppBundle\Entity\Program;
use Catrobat\AppBundle\Services\MediaPackageFileRepository;
use Catrobat\AppBundle\Services\TestEnv\LdapTestDriver;
use DateTime;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Catrobat\AppBundle\Entity\FeaturedProgram;

//
// Require 3rd-party libraries here:
//
// require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';
//

/**
 * Feature context.
 */
class FeatureContext extends BaseContext
{

    private $username;

    private $request_parameters;

    private $files;

    private $last_response;

    private $hostname;

    private $secure;

    /**
     * @var $program_downloads_repository ProgramDownloadsRepository
     */
    private $download_statistics_service;

    private $fb_post_program_id;

    private $fb_post_id;

    private $checked_catrobat_remix_forward_ancestor_relations;

    private $checked_catrobat_remix_forward_descendant_relations;

    private $checked_catrobat_remix_backward_relations;

    const MEDIAPACKAGE_DIR = './testdata/DataFixtures/MediaPackage/';

    /**
     * Initializes context with parameters from behat.yml.
     *
     * @param array $parameters
     */
    public function __construct($error_directory)
    {
        parent::__construct();
        $this->setErrorDirectory($error_directory);
        $this->username = null;
        $this->request_parameters = array();
        $this->files = array();
        $this->hostname = 'localhost';
        $this->secure = false;
        $this->checked_catrobat_remix_forward_ancestor_relations = [];
        $this->checked_catrobat_remix_forward_descendant_relations = [];
        $this->checked_catrobat_remix_backward_relations = [];
    }

    // //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // //////////////////////////////////////////// Support Functions

    /**
     * @BeforeScenario @RealFacebook
     */
    public function activateRealFacebookService()
    {
        $this->getClient()->disableReboot();
        $this->getSymfonyService('facebook_post_service')->useRealService(true);
        $this->theServerNameIs('share.catrob.at');
        $this->iUseASecureConnection();
    }

    /**
     * @AfterScenario @RealFacebook
     */
    public function deactivateRealFacebookService()
    {
        $this->getSymfonyService('facebook_post_service')->useRealService(false);
        $this->theServerNameIs('localhost');
        $this->getClient()->enableReboot();
    }

    /**
     * @BeforeScenario @RealGeocoder
     */
    public function activateRealGeocoderService() {
        $this->getSymfonyService('statistics')->useRealService(true);
    }

    /**
     * @AfterScenario @RealGeocoder
     */
    public function deactivateRealGeocoderService() {
        $this->getSymfonyService('statistics')->useRealService(false);
    }

    private function prepareValidRegistrationParameters()
    {
        $this->request_parameters['registrationUsername'] = 'newuser';
        $this->request_parameters['registrationPassword'] = 'topsecret';
        $this->request_parameters['registrationEmail'] = 'someuser@example.com';
        $this->request_parameters['registrationCountry'] = 'at';
    }

    private function sendPostRequest($url)
    {
        $this->getClient()->request('POST', $url, $this->request_parameters, $this->files);
    }

    // //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // //////////////////////////////////////////// Steps

    /**
     * @Given /^I have a program with "([^"]*)" as (name|description|tags)$/
     */
    public function iHaveAProgramWithAsDescription($value, $header)
    {
        $this->generateProgramFileWith(array(
            $header => $value
        ));
    }

    /**
     * @When /^I upload this program$/
     */
    public function iUploadThisProgram()
    {
        if(array_key_exists('deviceLanguage', $this->request_parameters)) {
            $this->upload(sys_get_temp_dir() . '/program_generated.catrobat', null, 'pocketcode', $this->request_parameters);
        } else {
            $this->upload(sys_get_temp_dir() . '/program_generated.catrobat', null);
        }
    }

    /**
     * @Then /^the program should get (.*)$/
     */
    public function theProgramShouldGet($result)
    {
        $response = $this->getClient()->getResponse();
        $response_array = json_decode($response->getContent(), true);
        $code = $response_array['statusCode'];
        switch ($result) {
            case 'accepted':
                assertEquals(200, $code, 'Program was rejected (Status code 200)');
                break;
            case 'rejected':
                assertNotEquals(200, $code, 'Program was NOT rejected');
                break;
            default:
                new PendingException();
        }
    }

    /**
     * @Given /^I define the following rude words:$/
     */
    public function iDefineTheFollowingRudeWords(TableNode $table)
    {
        $words = $table->getHash();

        $word = null;
        $em = $this->getManager();

        for ($i = 0; $i < count($words); ++ $i) {
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
        $this->insertUser(array(
            'name' => 'BehatGeneratedName',
            'token' => 'BehatGeneratedToken',
            'password' => 'BehatGeneratedPassword'
        ));
    }

    /**
     * @When /^I upload a program with (.*)$/
     */
    public function iUploadAProgramWith($programattribute)
    {
        switch ($programattribute) {
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
                throw new PendingException('No case defined for "' . $programattribute . '"');
        }
        $this->upload(self::FIXTUREDIR . '/GeneratedFixtures/' . $filename, null);
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
     */
    public function thereAreUsers(TableNode $table)
    {
        $users = $table->getHash();
        for ($i = 0; $i < count($users); ++ $i) {
            $this->insertUser(@array(
                'name' => $users[$i]['name'],
                'email' => $users[$i]['email'],
                'token' => isset($users[$i]['token']) ? $users[$i]['token'] : "",
                'password' => isset($users[$i]['password']) ? $users[$i]['password'] : ""
            ));
        }
    }

    /**
     * @Given /^there are LDAP-users:$/
     */
    public function thereAreLdapUsers(TableNode $table)
    {
        /**
         *
         * @var $ldap_test_driver LdapTestDriver
         * @var $user User
         */
        $ldap_test_driver = $this->getSymfonyService('fr3d_ldap.ldap_driver');
        $users = $table->getHash();
        $ldap_test_driver->resetFixtures();

        for ($i = 0; $i < count($users); ++ $i) {
            $username = $users[$i]['name'];
            $pwd = $users[$i]['password'];
            $groups = array_key_exists("groups", $users[$i]) ? explode(",", $users[$i]["groups"]) : array();
            assert($ldap_test_driver->addTestUser($username, $pwd, $groups, isset($users[$i]['email']) ? $users[$i]['email'] : null), "APC_store not working. Check your cli/php.ini settings, add \"apc.enabled = 1\" and \"apc.enable_cli = 1\" at the end");
        }
    }

    /**
     * @Given /^there are programs:$/
     */
    public function thereArePrograms(TableNode $table)
    {
        $programs = $table->getHash();
        $program_manager = $this->getProgramManger();
        for ($i = 0; $i < count($programs); ++ $i) {
            $user = $this->getUserManager()->findOneBy(array(
                'username' => isset($programs[$i]['owned by']) ? $programs[$i]['owned by'] : ""
            ));
            @$config = array(
                'name' => $programs[$i]['name'],
                'description' => $programs[$i]['description'],
                'views' => $programs[$i]['views'],
                'downloads' => $programs[$i]['downloads'],
                'uploadtime' => $programs[$i]['upload time'],
                'apk_status' => $programs[$i]['apk_status'],
                'catrobatversionname' => $programs[$i]['version'],
                'directory_hash' => $programs[$i]['directory_hash'],
                'filesize' => @$programs[$i]['FileSize'],
                'visible' => isset($programs[$i]['visible']) ? $programs[$i]['visible'] == 'true' : true,
                'approved' => (isset($programs[$i]['approved_by_user']) && $programs[$i]['approved_by_user'] == '') ? null : true,
                'tags' => isset($programs[$i]['tags_id']) ? $programs[$i]['tags_id'] : null,
                'extensions' => isset($programs[$i]['extensions']) ? $programs[$i]['extensions'] : null,
                'remix_root' => isset($programs[$i]['remix_root']) ? $programs[$i]['remix_root'] == 'true' : true
            );

            $this->insertProgram($user, $config);
        }
    }

    /**
     * @Given /^there are like similar users:$/
     */
    public function thereAreLikeSimilarUsers(TableNode $table)
    {
        $similarities = $table->getHash();

        for ($i = 0; $i < count($similarities); ++$i)
        {
            $this->insertUserLikeSimilarity(array(
                'first_user_id' => $similarities[$i]['first_user_id'],
                'second_user_id' => $similarities[$i]['second_user_id'],
                'similarity' => $similarities[$i]['similarity']
            ));
        }
    }

    /**
     * @Given /^there are remix similar users:$/
     */
    public function thereAreRemixSimilarUsers(TableNode $table)
    {
        $similarities = $table->getHash();

        for ($i = 0; $i < count($similarities); ++$i)
        {
            $this->insertUserRemixSimilarity(array(
                'first_user_id' => $similarities[$i]['first_user_id'],
                'second_user_id' => $similarities[$i]['second_user_id'],
                'similarity' => $similarities[$i]['similarity']
            ));
        }
    }

    /**
     * @Given /^there are likes:$/
     */
    public function thereAreLikes(TableNode $table)
    {
        $likes = $table->getHash();

        foreach($likes as $like) {
            $this->insertProgramLike(array(
                'username' => $like['username'],
                'program_id' => $like['program_id'],
                'type' => $like['type'],
                'created at' => $like['created at']
            ));
        }
    }

    /**
     * @Given /^there are tags:$/
     */
    public function thereAreTags(TableNode $table)
    {
        $tags = $table->getHash();

        foreach($tags as $tag)
        {
            @$config = array(
                'id' => $tag['id'],
                'en' => $tag['en'],
                'de' => $tag['de']
            );
            $this->insertTag($config);
        }
    }

    /**
     * @Given /^there are extensions:$/
     */
    public function thereAreExtensions(TableNode $table)
    {
        $extensions = $table->getHash();

        foreach($extensions as $extension)
        {
            @$config = array(
                'name' => $extension['name'],
                'prefix' => $extension['prefix']
            );
            $this->insertExtension($config);
        }
    }

    /**
     * @Given /^there are forward remix relations:$/
     */
    public function thereAreForwardRemixRelations(TableNode $table)
    {
        $relations = $table->getHash();

        foreach($relations as $relation)
        {
            @$config = array(
                'ancestor_id' => $relation['ancestor_id'],
                'descendant_id' => $relation['descendant_id'],
                'depth' => $relation['depth']
            );
            $this->insertForwardRemixRelation($config);
        }
    }

    /**
     * @Given /^there are backward remix relations:$/
     */
    public function thereAreBackwardRemixRelations(TableNode $table)
    {
        $backward_relations = $table->getHash();

        foreach($backward_relations as $backward_relation)
        {
            @$config = array(
                'parent_id' => $backward_relation['parent_id'],
                'child_id' => $backward_relation['child_id']
            );
            $this->insertBackwardRemixRelation($config);
        }
    }

    /**
     * @Given /^there are Scratch remix relations:$/
     */
    public function thereAreScratchRemixRelations(TableNode $table)
    {
        $scratch_relations = $table->getHash();

        foreach($scratch_relations as $scratch_relation)
        {
            @$config = array(
                'scratch_parent_id' => $scratch_relation['scratch_parent_id'],
                'catrobat_child_id' => $scratch_relation['catrobat_child_id']
            );
            $this->insertScratchRemixRelation($config);
        }
    }

    /**
     * @Given /^following programs are featured:$/
     */
    public function followingProgramsAreFeatured(TableNode $table)
    {
        $em = $this->getManager();
        $featured = $table->getHash();
        for ($i = 0; $i < count($featured); ++ $i) {
            $program = $this->getProgramManger()->findOneByName($featured[$i]['name']);
            $featured_entry = new FeaturedProgram();
            $featured_entry->setProgram($program);
            $featured_entry->setActive($featured[$i]['active'] == 'yes');
            $featured_entry->setImageType('jpg');
            $featured_entry->setPriority($featured[$i]['priority']);
            $em->persist($featured_entry);
        }
        $em->flush();
    }

    /**
     * @Given /^there are downloadable programs:$/
     */
    public function thereAreDownloadablePrograms(TableNode $table)
    {
        $em = $this->getManager();
        $file_repo = $this->getFileRepository();
        $programs = $table->getHash();
        for ($i = 0; $i < count($programs); ++ $i) {
            $user = $this->getUserManager()->findOneBy(array(
                'username' => $programs[$i]['owned by']
            ));
            $config = array(
                'name' => $programs[$i]['name'],
                'description' => $programs[$i]['description'],
                'views' => $programs[$i]['views'],
                'downloads' => $programs[$i]['downloads'],
                'uploadtime' => $programs[$i]['upload time'],
                'catrobatversionname' => $programs[$i]['version'],
                'filesize' => @$programs[$i]['FileSize'],
                'visible' => isset($programs[$i]['visible']) ? $programs[$i]['visible'] == 'true' : true
            );

            $program = $this->insertProgram($user, $config);

            $file_repo->saveProgramfile(new File(self::FIXTUREDIR . 'test.catrobat'), $program->getId());
        }
    }

    /**
     * @Given /^I have a parameter "([^"]*)" with value "([^"]*)"$/
     */
    public function iHaveAParameterWithValue($name, $value)
    {
        $this->request_parameters[$name] = $value;
    }

    /**
     * @Given /^I have a parameter "([^"]*)" with the tag id "([^"]*)"$/
     */
    public function iHaveAParameterWithTheTagId($name, $value)
    {
        $this->request_parameters[$name] = $value;
    }

    /**
     * @When /^I POST these parameters to "([^"]*)"$/
     */
    public function iPostTheseParametersTo($url)
    {
        $this->getClient()->request('POST', $url, $this->request_parameters, $this->files, array(
            'HTTP_HOST' => $this->hostname,
            'HTTPS' => $this->secure
        ));
    }

    /**
     * @When /^I GET "([^"]*)" with these parameters$/
     */
    public function iGetWithTheseParameters($url)
    {
        $this->getClient()->request('GET', 'http://' . $this->hostname . $url . '?' . http_build_query($this->request_parameters), array(), $this->files, array(
            'HTTP_HOST' => $this->hostname,
            'HTTPS' => $this->secure
        ));
    }

  /**
   * @When /^I GET from the api "([^"]*)"$/
   */
  public function iGetFromTheApi($url)
  {
    $this->getClient()->request('GET', 'http://' . $this->hostname . $url, array(), $this->files, array(
      'HTTP_HOST' => $this->hostname,
      'HTTPS' => $this->secure
    ));
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
     */
    public function iAm($username)
    {
        $this->username = $username;
    }

    /**
     * @Given /^I search for "([^"]*)"$/
     */
    public function iSearchFor($arg1)
    {
        $this->iHaveAParameterWithValue('q', $arg1);
        if (isset($this->request_parameters['limit'])) {
            $this->iHaveAParameterWithValue('limit', $this->request_parameters['limit']);
        } else {
            $this->iHaveAParameterWithValue('limit', '1');
        }
        if (isset($this->request_parameters['offset'])) {
            $this->iHaveAParameterWithValue('offset', $this->request_parameters['offset']);
        } else {
            $this->iHaveAParameterWithValue('offset', '0');
        }
        $this->iGetWithTheseParameters('/pocketcode/api/projects/search.json');
    }

    /**
     * @Then /^I should get a total of "([^"]*)" projects$/
     */
    public function iShouldGetATotalOfProjects($arg1)
    {
        $response = $this->getClient()->getResponse();
        $responseArray = json_decode($response->getContent(), true);
        assertEquals($arg1, $responseArray['CatrobatInformation']['TotalProjects'], 'Wrong number of total projects');
    }

    /**
     * @Then /^I should get user-specific recommended projects$/
     */
    public function iShouldGetUserSpecificRecommendedProjects()
    {
        $response = $this->getClient()->getResponse();
        $responseArray = json_decode($response->getContent(), true);
        assertTrue(isset($responseArray['isUserSpecificRecommendation']), 'No isUserSpecificRecommendation parameter found in response!');
        assertTrue($responseArray['isUserSpecificRecommendation'], 'isUserSpecificRecommendation parameter has wrong value. Is false, but should be true!');
    }

    /**
     * @Then /^I should get no user-specific recommended projects$/
     */
    public function iShouldGetNoUserSpecificRecommendedProjects()
    {
        $response = $this->getClient()->getResponse();
        $responseArray = json_decode($response->getContent(), true);
        assertFalse(isset($responseArray['isUserSpecificRecommendation']), 'Unexpected isUserSpecificRecommendation parameter found in response!');
    }

    /**
     * @Given /^I use the limit "([^"]*)"$/
     */
    public function iUseTheLimit($arg1)
    {
        $this->iHaveAParameterWithValue('limit', $arg1);
    }

    /**
     * @Given /^I use the offset "([^"]*)"$/
     */
    public function iUseTheOffset($arg1)
    {
        $this->iHaveAParameterWithValue('offset', $arg1);
    }

    /**
     * @When /^I call "([^"]*)" with token "([^"]*)"$/
     */
    public function iCallWithToken($url, $token)
    {
        $params = array(
            'token' => $token,
            'username' => $this->username
        );
        $this->getClient()->request('GET', $url . '?' . http_build_query($params));
    }

    /**
     * @Then /^I should get the json object:$/
     */
    public function iShouldGetTheJsonObject(PyStringNode $string)
    {
        $response = $this->getClient()->getResponse();
        assertJsonStringEqualsJsonString($string->getRaw(), $response->getContent(), '');
    }

    /**
     * @Then /^I should get the json object with random token:$/
     */
    public function iShouldGetTheJsonObjectWithRandomToken(PyStringNode $string)
    {
        $response = $this->getClient()->getResponse();
        $responseArray = json_decode($response->getContent(), true);
        $expectedArray = json_decode($string->getRaw(), true);
        $responseArray['token'] = '';
        $expectedArray['token'] = '';
        assertEquals($expectedArray, $responseArray);
    }

    /**
     * @Then /^I should get the json object with random "([^"]*)" and "([^"]*)":$/
     */
    public function iShouldGetTheJsonObjectWithRandomAndProgramid($arg1, $arg2, PyStringNode $string)
    {
        $response = $this->getClient()->getResponse();
        $responseArray = json_decode($response->getContent(), true);
        $expectedArray = json_decode($string->getRaw(), true);
        $responseArray[$arg1] = $expectedArray[$arg1] = '';
        $responseArray[$arg2] = $expectedArray[$arg2] = '';
        assertEquals($expectedArray, $responseArray, $response);
    }

    /**
     * @Then /^I should get programs in the following order:$/
     */
    public function iShouldGetProgramsInTheFollowingOrder(TableNode $table)
    {
        $response = $this->getClient()->getResponse();
        $responseArray = json_decode($response->getContent(), true);
        $returned_programs = $responseArray['CatrobatProjects'];
        $expected_programs = $table->getHash();
        /* // if you want to check which programs are missing use this
        if (count($expected_programs) != count($returned_programs))
        {
          print_r($returned_programs);
          assertEquals(count($expected_programs), count($returned_programs), 'Wrong number of returned programs');
        }
        */
        for ($i = 0; $i < count($returned_programs); ++ $i) {
            assertEquals($expected_programs[$i]['Name'], $returned_programs[$i]['ProjectName'], 'Wrong order of results');
        }
    }

    /**
     * @Then /^I should get (\d+) programs in random order:$/
     */
    public function iShouldGetProgramsInRandomOrder($program_count, TableNode $table)
    {
        $response = $this->getClient()->getResponse();
        $response_array = json_decode($response->getContent(), true);
        $random_programs = $response_array['CatrobatProjects'];
        $expected_programs = $table->getHash();
        assertEquals($program_count, count($random_programs), 'Wrong number of random programs');

        for ($i = 0; $i < count($random_programs); ++ $i) {
            $program_found = false;
            for ($j = 0; $j < count($expected_programs); ++ $j) {
                if (strcmp($random_programs[$i]['ProjectName'], $expected_programs[$j]['Name']) === 0)
                    $program_found = true;
            }
            assertEquals($program_found, true, 'Program does not exist in the database');
        }
    }

    /**
     * @Then /^I should get following programs:$/
     */
    public function iShouldGetFollowingPrograms(TableNode $table)
    {
        $this->iShouldGetProgramsInTheFollowingOrder($table);
    }

    /**
     * @Then /^I should get following like similarities:$/
     */
    public function iShouldGetFollowingLikePrograms(TableNode $table)
    {
        $all_like_similarities = $this->getAllLikeSimilaritiesBetweenUsers();
        $expected_like_similarities = $table->getHash();
        assertEquals(count($expected_like_similarities), count($all_like_similarities), 'Wrong number of returned similarity entries');
        for ($i = 0; $i < count($all_like_similarities); ++$i) {
            assertEquals($expected_like_similarities[$i]['first_user_id'], $all_like_similarities[$i]->getFirstUserId(),
                'Wrong value for first_user_id or wrong order of results');
            assertEquals($expected_like_similarities[$i]['second_user_id'], $all_like_similarities[$i]->getSecondUserId(),
                'Wrong value for second_user_id');
            assertEquals(round($expected_like_similarities[$i]['similarity'], 3), round($all_like_similarities[$i]->getSimilarity(), 3),
                'Wrong value for similarity');
        }
    }

    /**
     * @Then /^I should get following remix similarities:$/
     */
    public function iShouldGetFollowingRemixPrograms(TableNode $table)
    {
        $all_remix_similarities = $this->getAllRemixSimilaritiesBetweenUsers();
        $expected_remix_similarities = $table->getHash();
        assertEquals(count($expected_remix_similarities), count($all_remix_similarities), 'Wrong number of returned similarity entries');
        for ($i = 0; $i < count($all_remix_similarities); ++$i) {
            assertEquals($expected_remix_similarities[$i]['first_user_id'], $all_remix_similarities[$i]->getFirstUserId(),
                'Wrong value for first_user_id or wrong order of results');
            assertEquals($expected_remix_similarities[$i]['second_user_id'], $all_remix_similarities[$i]->getSecondUserId(),
                'Wrong value for second_user_id');
            assertEquals(round($expected_remix_similarities[$i]['similarity'], 3), round($all_remix_similarities[$i]->getSimilarity(), 3),
                'Wrong value for similarity');
        }
    }

    /**
     * @Given /^the response code should be "([^"]*)"$/
     */
    public function theResponseCodeShouldBe($code)
    {
        $response = $this->getClient()->getResponse();
        assertEquals($code, $response->getStatusCode(), 'Wrong response code. ' . $response->getContent());
    }

    /**
     * @Given /^the returned "([^"]*)" should be a number$/
     */
    public function theReturnedShouldBeANumber($arg1)
    {
        $response = json_decode($this->getClient()
            ->getResponse()
            ->getContent(), true);
        assertTrue(is_numeric($response[$arg1]));
    }

    /**
     * @Given /^I have a file "([^"]*)"$/
     */
    public function iHaveAFile($filename)
    {
        $filepath = './src/Catrobat/ApiBundle/Features/Fixtures/' . $filename;
        assertTrue(file_exists($filepath), 'File not found');
        $this->files[] = new UploadedFile($filepath, $filename);
    }

    /**
     * @Given /^I have a valid Catrobat file$/
     */
    public function iHaveAValidCatrobatFile()
    {
        $filepath = self::FIXTUREDIR . 'test.catrobat';
        assertTrue(file_exists($filepath), 'File not found');
        $this->files = array();
        $this->files[] = new UploadedFile($filepath, 'test.catrobat');
    }

    /**
     * @Given /^I have a Catrobat file with an bad word in the description$/
     */
    public function iHaveACatrobatFileWithAnRudeWordInTheDescription()
    {
        $filepath = self::FIXTUREDIR . 'GeneratedFixtures/program_with_rudeword_in_description.catrobat';
        assertTrue(file_exists($filepath), 'File not found');
        $this->files[] = new UploadedFile($filepath, 'program_with_rudeword_in_description.catrobat');
    }

    /**
     * @Given /^I have a parameter "([^"]*)" with the md5checksum my file$/
     */
    public function iHaveAParameterWithTheMdchecksumMyFile($parameter)
    {
        $this->request_parameters[$parameter] = md5_file($this->files[0]->getPathname());
    }

    /**
     * @Given /^I have a parameter "([^"]*)" with an invalid md5checksum of my file$/
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
     */
    public function iUploadAnotherProgramWithNameSetToAndUrlSetTo($name, $url)
    {
        $this->iHaveAProgramWithAsTwoHeaderFields('name', $name, 'url', $url);
        $this->iUploadAProgram();
    }

    /**
     * @When /^I upload another program with name set to "([^"]*)", url set to "([^"]*)" and catrobatLanguageVersion set to "([^"]*)"$/
     */
    public function iUploadAnotherProgramWithNameSetToUrlSetToAndCatrobatLanguageVersionSetTo($name, $url, $catrobat_language_version)
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
        assertEquals('200', $json['statusCode'], $this->getClient()
            ->getResponse()
            ->getContent());
    }

    /**
     * @When /^I upload a catrobat program with the same name$/
     */
    public function iUploadACatrobatProgramWithTheSameName()
    {
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
        assertEquals($last_json['projectId'], $json['projectId'], $this->getClient()
            ->getResponse()
            ->getContent());
    }

    /**
     * @Given /^I have a parameter "([^"]*)" with the md5checksum of "([^"]*)"$/
     */
    public function iHaveAParameterWithTheMdchecksumOf($parameter, $file)
    {
        $this->request_parameters[$parameter] = md5_file($this->files[0]->getPathname());
    }

    /**
     * @Given /^the next generated token will be "([^"]*)"$/
     */
    public function theNextGeneratedTokenWillBe($token)
    {
        $token_generator = $this->getSymfonyService('tokengenerator');
        $token_generator->setTokenGenerator(new FixedTokenGenerator($token));
    }

    /**
     * @Given /^the current time is "([^"]*)"$/
     */
    public function theCurrentTimeIs($time)
    {
        $date = new \DateTime($time, new \DateTimeZone('UTC'));
        $time_service = $this->getSymfonyService('time');
        $time_service->setTime(new FixedTime($date->getTimestamp()));
    }

    /**
     * @Given /^I have the POST parameters:$/
     */
    public function iHaveThePostParameters(TableNode $table)
    {
        $parameters = $table->getHash();
        foreach ($parameters as $parameter) {
            $this->request_parameters[$parameter['name']] = $parameter['value'];
        }
    }

    /**
     * @When /^I try to register without (.*)$/
     */
    public function iTryToRegisterWithout($missing_parameter)
    {
        $this->prepareValidRegistrationParameters();
        switch ($missing_parameter) {
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

    /**
     * @Then /^the program download statistic should have a download timestamp, street, postal code, locality, an anonimous user, latitude of approximately "([^"]*)", longitude of approximately "([^"]*)" and the following statistics:$/
     */
    public function theProgramShouldHaveADownloadTimestampStreetPostalCodeLocalityLatitudeOfApproximatelyLongitudeOfApproximatelyAndTheFollowingStatistics($expected_latitude, $expected_longitude, TableNode $table)
    {
        $statistics = $table->getHash();
        for ($i = 0; $i < count($statistics); ++$i ) {
            $ip = $statistics[$i]['ip'];
            $country_code = $statistics[$i]['country_code'];
            $country_name = $statistics[$i]['country_name'];
            $program_id = $statistics[$i]['program_id'];

            /**
             * @var $program_download_statistics ProgramDownloads
             */
            $repository = $this->getManager()->getRepository('AppBundle:ProgramDownloads');
            $program_download_statistics = $repository->find(1);

            assertEquals($ip, $program_download_statistics->getIp(), "Wrong IP in download statistics");
            assertEquals($country_code, $program_download_statistics->getCountryCode(), "Wrong country code in download statistics");
            assertEquals($country_name, strtoUpper($program_download_statistics->getCountryName()), "Wrong country name in download statistics");
            assertEquals($program_id, $program_download_statistics->getProgram()->getId(), "Wrong program ID in download statistics");
            assertNull($program_download_statistics->getUser(), "Wrong username in download statistics");

            assertNotEmpty($program_download_statistics->getLocality(), "No locality was written to download statistics");
            assertNotEmpty($program_download_statistics->getPostalCode(), "No postal code was written to download statistics");
            assertNotEmpty($program_download_statistics->getStreet(), "No street was written to download statistics");
            assertNotEmpty($program_download_statistics->getUserAgent(), "No user agent was written to download statistics");

            $limit = 5.0;

            $latitude = floatval($program_download_statistics->getLatitude());
            $longitude = floatval($program_download_statistics->getLongitude());
            $download_time = $program_download_statistics->getDownloadedAt();
            $current_time = new \DateTime();

            $time_delta = $current_time->getTimestamp() - $download_time->getTimestamp();

            assertTrue($latitude > (floatval($expected_latitude) - $limit) && $latitude < (floatval($expected_latitude) + $limit), "Latitude in download statistics not as expected");
            assertTrue($longitude > ($expected_longitude - $limit) && $longitude < ($expected_longitude + $limit), "Longitude in download statistics not as expected");
            assertTrue($time_delta < $limit, "Download time difference in download statistics too high");
        }
    }

    /**
     * @When /^i download "([^"]*)"$/
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
        assertEquals('application/zip', $content_type);
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
     */
    public function iAmUsingPocketcodeForWithVersion($platform, $version)
    {
        $this->generateProgramFileWith(array(
            'platform' => $platform,
            'applicationVersion' => $version
        ));
    }

    /**
     * @Given /^I have a program with "([^"]*)" set to "([^"]*)"$/
     */
    public function iHaveAProgramWithAs($key, $value)
    {
        $this->generateProgramFileWith(array(
            $key => $value
        ));
    }

    /**
     * @Given /^I have a program with "([^"]*)" set to "([^"]*)" and "([^"]*)" set to "([^"]*)"$/
     */
    public function iHaveAProgramWithAsTwoHeaderFields($key1, $value1, $key2, $value2)
    {
        $this->generateProgramFileWith(array(
            $key1 => $value1,
            $key2 => $value2
        ));
    }

    /**
     * @Given /^I have a program with "([^"]*)" set to "([^"]*)", "([^"]*)" set to "([^"]*)" and "([^"]*)" set to "([^"]*)"$/
     */
    public function iHaveAProgramWithAsMultipleHeaderFields($key1, $value1, $key2, $value2, $key3, $value3)
    {
        $this->generateProgramFileWith(array(
            $key1 => $value1,
            $key2 => $value2,
            $key3 => $value3
        ));
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
     */
    public function iAmUsingPocketcodeWithLanguageVersion($version)
    {
        $this->generateProgramFileWith(array(
            'catrobatLanguageVersion' => $version
        ));
    }

    /**
     * @Then /^The upload should be successful$/
     */
    public function theUploadShouldBeSuccessful()
    {
        $response = $this->getClient()->getResponse();
        $responseArray = json_decode($response->getContent(), true);
        assertEquals(200, $responseArray['statusCode']);
    }

    /**
     * @Given /^the server name is "([^"]*)"$/
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
     */
    public function programIsNotVisible($programname)
    {
        $em = $this->getManager();
        $program = $this->getProgramManger()->findOneByName($programname);
        if ($program == null) {
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
     */
    public function iGetTheMostRecentProgramsWithLimitAndOffset($limit, $offset)
    {
        $this->getClient()->request('GET', '/pocketcode/api/projects/recent.json', array(
            'limit' => $limit,
            'offset' => $offset
        ));
    }

    /**
     * @Given /^I upload the program with "([^"]*)" as name$/
     */
    public function iUploadTheProgramWithAsName($name)
    {
        $this->generateProgramFileWith(array(
            'name' => $name
        ));
        $this->upload(sys_get_temp_dir() . '/program_generated.catrobat', null);
    }

    /**
     * @When /^I upload the program with "([^"]*)" as name again$/
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
     */
    public function theProgramShouldBeARemixRoot($program_id)
    {
        $program_manager = $this->getProgramManger();
        $uploaded_program = $program_manager->find($program_id);
        assertTrue($uploaded_program->isRemixRoot());
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
        assertNotNull($uploaded_program->getRemixMigratedAt());
    }

    /**
     * @Then /^the program "([^"]*)" should not be a remix root$/
     */
    public function theProgramShouldNotBeARemixRoot($program_id)
    {
        $program_manager = $this->getProgramManger();
        $uploaded_program = $program_manager->find($program_id);
        assertFalse($uploaded_program->isRemixRoot());
    }

    /**
     * @Given /^the uploaded program should have a Scratch parent having id "([^"]*)"$/
     */
    public function theUploadedProgramShouldHaveAScratchParentHavingScratchID($scratch_parent_id)
    {
        $json = json_decode($this->getClient()->getResponse()->getContent(), true);
        $this->theProgramShouldHaveAScratchParentHavingScratchID($json["projectId"], $scratch_parent_id);
    }

    /**
     * @Given /^the program "([^"]*)" should have a Scratch parent having id "([^"]*)"$/
     */
    public function theProgramShouldHaveAScratchParentHavingScratchID($program_id, $scratch_parent_id)
    {
        $direct_edge_relation = $this->getScratchProgramRemixRepository()->findOneBy([
            'scratch_parent_id' => $scratch_parent_id,
            'catrobat_child_id' => $program_id
        ]);

        assertNotNull($direct_edge_relation);
        $this->checked_catrobat_remix_forward_ancestor_relations[$direct_edge_relation->getUniqueKey()] = $direct_edge_relation;
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
     */
    public function theProgramShouldHaveNoFurtherScratchParents($program_id)
    {
        $direct_edge_relations = $this->getScratchProgramRemixRepository()->findBy([
            'catrobat_child_id' => $program_id
        ]);

        $further_scratch_parent_relations = array_filter($direct_edge_relations,
            function ($r) { return !array_key_exists($r->getUniqueKey(), $this->checked_catrobat_remix_forward_ancestor_relations); });

        assertCount(0, $further_scratch_parent_relations);
    }

    /**
     * @Then /^the uploaded program should have a Catrobat forward ancestor having id "([^"]*)" and depth "([^"]*)"$/
     */
    public function theUploadedProgramShouldHaveACatrobatForwardAncestorHavingIdAndDepth($id, $depth)
    {
        $json = json_decode($this->getClient()->getResponse()->getContent(), true);
        $this->theProgramShouldHaveACatrobatForwardAncestorHavingIdAndDepth($json["projectId"], $id, $depth);
    }

    /**
     * @Then /^the program "([^"]*)" should have a Catrobat forward ancestor having id "([^"]*)" and depth "([^"]*)"$/
     */
    public function theProgramShouldHaveACatrobatForwardAncestorHavingIdAndDepth($program_id, $ancestor_program_id, $depth)
    {
        $forward_ancestor_relation = $this->getProgramRemixForwardRepository()->findOneBy([
            'ancestor_id' => $ancestor_program_id,
            'descendant_id' => $program_id,
            'depth' => $depth
        ]);

        assertNotNull($forward_ancestor_relation);
        $this->checked_catrobat_remix_forward_ancestor_relations[$forward_ancestor_relation->getUniqueKey()] = $forward_ancestor_relation;

        if ($program_id == $ancestor_program_id && $depth == 0) {
            $this->checked_catrobat_remix_forward_descendant_relations[$forward_ancestor_relation->getUniqueKey()] = $forward_ancestor_relation;
        }
    }

    /**
     * @Then /^the uploaded program should have a Catrobat backward parent having id "([^"]*)"$/
     */
    public function theUploadedProgramShouldHaveACatrobatBackwardParentHavingId($id)
    {
        $json = json_decode($this->getClient()->getResponse()->getContent(), true);
        $this->theProgramShouldHaveACatrobatBackwardParentHavingId($json["projectId"], $id);
    }

    /**
     * @Then /^the program "([^"]*)" should have a Catrobat backward parent having id "([^"]*)"$/
     */
    public function theProgramShouldHaveACatrobatBackwardParentHavingId($program_id, $backward_parent_program_id)
    {
        $backward_parent_relation = $this->getProgramRemixBackwardRepository()->findOneBy([
            'parent_id' => $backward_parent_program_id,
            'child_id' => $program_id
        ]);

        assertNotNull($backward_parent_relation);
        $this->checked_catrobat_remix_backward_relations[$backward_parent_relation->getUniqueKey()] = $backward_parent_relation;
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
     */
    public function theProgramShouldHaveNoCatrobatForwardAncestorsExceptSelfRelation($program_id)
    {
        $forward_ancestors_including_self_referencing_relation = $this
            ->getProgramRemixForwardRepository()
            ->findBy(['descendant_id' => $program_id]);

        assertCount(0, array_filter($forward_ancestors_including_self_referencing_relation,
            function ($r) { return $r->getDepth() >= 1; }));
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
     */
    public function theProgramShouldHaveNoFurtherCatrobatForwardAncestors($program_id)
    {
        $forward_ancestors_including_self_referencing_relation = $this
            ->getProgramRemixForwardRepository()
            ->findBy(['descendant_id' => $program_id]);

        $further_forward_ancestor_relations = array_filter($forward_ancestors_including_self_referencing_relation,
            function ($r) {
                return !array_key_exists($r->getUniqueKey(), $this->checked_catrobat_remix_forward_ancestor_relations);
            });

        assertCount(0, $further_forward_ancestor_relations);
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
     */
    public function theProgramShouldHaveNoCatrobatBackwardParents($program_id)
    {
        $backward_parent_relations = $this->getProgramRemixBackwardRepository()->findBy(['child_id' => $program_id]);
        assertCount(0, $backward_parent_relations);
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
     */
    public function theProgramShouldHaveNoFurtherCatrobatBackwardParents($program_id)
    {
        $backward_parent_relations = $this->getProgramRemixBackwardRepository()->findBy(['child_id' => $program_id]);

        $further_backward_parent_relations = array_filter($backward_parent_relations,
            function ($r) { return !array_key_exists($r->getUniqueKey(), $this->checked_catrobat_remix_backward_relations); });

        assertCount(0, $further_backward_parent_relations);
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
     */
    public function theProgramShouldHaveNoScratchParents($program_id)
    {
        $scratch_parents = $this->getScratchProgramRemixRepository()->findBy(['catrobat_child_id' => $program_id]);
        assertCount(0, $scratch_parents);
    }

    /**
     * @Then /^the uploaded program should have a Catrobat forward descendant having id "([^"]*)" and depth "([^"]*)"$/
     */
    public function theUploadedProgramShouldHaveCatrobatForwardDescendantHavingIdAndDepth($id, $depth)
    {
        $json = json_decode($this->getClient()->getResponse()->getContent(), true);
        $this->theProgramShouldHaveCatrobatForwardDescendantHavingIdAndDepth($json["projectId"], $id, $depth);
    }

    /**
     * @Then /^the program "([^"]*)" should have a Catrobat forward descendant having id "([^"]*)" and depth "([^"]*)"$/
     */
    public function theProgramShouldHaveCatrobatForwardDescendantHavingIdAndDepth($program_id, $descendant_program_id, $depth)
    {
        $forward_descendant_relation = $this->getProgramRemixForwardRepository()->findOneBy([
            'ancestor_id' => $program_id,
            'descendant_id' => $descendant_program_id,
            'depth' => $depth
        ]);

        assertNotNull($forward_descendant_relation);
        $this->checked_catrobat_remix_forward_descendant_relations[$forward_descendant_relation->getUniqueKey()] = $forward_descendant_relation;

        if ($program_id == $descendant_program_id && $depth == 0) {
            $this->checked_catrobat_remix_forward_ancestor_relations[$forward_descendant_relation->getUniqueKey()] = $forward_descendant_relation;
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
     */
    public function theProgramShouldHaveNoCatrobatForwardDescendantsExceptSelfRelation($program_id)
    {
        $forward_ancestors_including_self_referencing_relation = $this
            ->getProgramRemixForwardRepository()
            ->findBy(['ancestor_id' => $program_id]);

        assertCount(0, array_filter($forward_ancestors_including_self_referencing_relation,
            function ($r) { return $r->getDepth() >= 1; }));
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
     */
    public function theProgramShouldHaveNoFurtherCatrobatForwardDescendants($program_id)
    {
        $forward_descendants_including_self_referencing_relation = $this
            ->getProgramRemixForwardRepository()
            ->findBy(['ancestor_id' => $program_id]);

        $further_forward_descendant_relations = array_filter($forward_descendants_including_self_referencing_relation,
            function ($r) {
                return !array_key_exists($r->getUniqueKey(), $this->checked_catrobat_remix_forward_descendant_relations);
            });

        assertCount(0, $further_forward_descendant_relations);
    }

    /**
     * @Then /^the uploaded program should have RemixOf "([^"]*)" in the xml$/
     */
    public function theUploadedProgramShouldHaveRemixofInTheXml($value)
    {
        $json = json_decode($this->getClient()->getResponse()->getContent(), true);
        $this->theProgramShouldHaveRemixofInTheXml($json["projectId"], $value);
    }

    /**
     * @Then /^the program "([^"]*)" should have RemixOf "([^"]*)" in the xml$/
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
        assertEquals($value, $progXmlProp->header->remixOf->__toString());
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
     * @Then /^the project should be posted to Facebook with message "([^"]*)" and the correct project ID$/
     */
    public function theProjectShouldBePostedToFacebookWithMessageAndTheCorrectProjectId($fb_post_message)
    {
        $response = json_decode($this->getClient()
            ->getResponse()
            ->getContent(), true);
        $project_id = $response['projectId'];

        /**
         *
         * @var $program Program
         */
        $program_manager = $this->getSymfonySupport()->getProgramManager();
        $program = $program_manager->find($project_id);
        $user = $program->getUser();
        $fb_post_id = $program->getFbPostId();
        $fb_post_url = $program->getFbPostUrl();

        $profile_url = $this->getSymfonySupport()->getRouter()->generate('profile', array('id' => $user->getId()), true);
        assertTrue($fb_post_id != '', "No Facebook Post ID was persisted");
        assertTrue($fb_post_url != '', "No Facebook Post URL was persisted");
        $fb_response = $this->getSymfonyService('facebook_post_service')->checkFacebookPostAvailable($fb_post_id)->getGraphObject();

        $fb_post_message = $fb_post_message . chr(10) . 'by '  . $profile_url;

        $fb_id = $fb_response['id'];
        $fb_message = $fb_response['message'];

        $this->fb_post_id = $fb_id;
        assertTrue($fb_id != '', "No Facebook Post ID was returned");
        assertEquals($fb_id, $program_manager->find($project_id)->getFbPostId(), "Facebook Post ID's do not match");
        assertEquals($fb_post_message, $fb_message, "Facebook messages do not match");
    }

    /**
     * @When /^I have a parameter "([^"]*)" with the returned projectId$/
     */
    public function iHaveAParameterWithTheReturnedProjectid($name)
    {
        $response = json_decode($this->getClient()
            ->getResponse()
            ->getContent(), true);
        $this->fb_post_program_id = $response['projectId'];
        $this->request_parameters[$name] = $response['projectId'];
    }

    /**
     * @Then /^the Facebook Post should be deleted$/
     */
    public function theFacebookPostShouldBeDeleted()
    {
        //echo 'Delete post with Facebook ID ' . $this->fb_post_id;

        $program_manager = $this->getProgramManger();
        $program = $program_manager->find($this->fb_post_program_id);
        assertEmpty($program->getFbPostId(), 'FB Post was not resetted');
        $fb_response = $this->getSymfonyService('facebook_post_service')->checkFacebookPostAvailable($this->fb_post_id);

        $string = print_r($fb_response, true);
        assertNotContains('id', $string, 'Facebook ID was returned, but should not exist anymore as the post was deleted');
        assertNotContains('message', $string, 'Facebook message was returned, but should not exist anymore as the post was deleted');
    }

    /**
     * @Given /^I want to upload a program$/
     */
    public function iWantToUploadAProgram(){}

    /**
     * @Given /^I have no parameters$/
     */
    public function iHaveNoParameters(){}

    /**
     * @When /^I GET the tag list from "([^"]*)" with these parameters$/
     */
    public function iGetTheTagListFromWithTheseParameters($url)
    {
        $this->getClient()->request('GET', $url . '?' . http_build_query($this->request_parameters), array(), $this->files, array(
            'HTTP_HOST' => $this->hostname,
            'HTTPS' => $this->secure
        ));
    }

    /**
     * @Given /^I use the "([^"]*)" app$/
     */
    public function iUseTheApp($language)
    {

        switch ($language) {
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
     */
    public function theProgramShouldBeTaggedWithInTheDatabase($arg1)
    {
        $program_tags = $this->getProgramManger()->find(2)->getTags();
        $tags = explode(',',$arg1);
        assertEquals(count($program_tags), count($tags), 'Too much or too less tags found!');

        foreach ($program_tags as $program_tag) {
            if (!(in_array($program_tag->getDe(), $tags) || in_array($program_tag->getEn(), $tags))) {
                assertTrue(false, 'The tag is not found!');
            }
        }
    }

    /**
     * @Then /^the program should not be tagged$/
     */
    public function theProgramShouldNotBeTagged()
    {
        $program_tags = $this->getProgramManger()->find(2)->getTags();
        assertEquals(0, count($program_tags), 'The program is tagged but should not be tagged');
    }

    /**
     * @When /^I upload this program again with the tags "([^"]*)"$/
     */
    public function iUploadThisProgramAgainWithTheTags($tags)
    {
        $this->generateProgramFileWith(array(
            'tags' => $tags
        ));
        $this->upload(sys_get_temp_dir() . '/program_generated.catrobat', null, 'pocketcode', $this->request_parameters);
    }

    /**
     * @Given /^I have a program with Arduino, Lego and Phiro extensions$/
     */
    public function iHaveAProgramWithArduinoLegoAndPhiroExtensions()
    {
        $filesystem = new Filesystem();
        $filesystem->copy(self::FIXTUREDIR.'extensions.catrobat', sys_get_temp_dir()."/program_generated.catrobat", true);

    }

    /**
     * @Then /^the program should be marked with extensions in the database$/
     */
    public function theProgramShouldBeMarkedWithExtensionsInTheDatabase()
    {
        $program_extensions = $this->getProgramManger()->find(2)->getExtensions();

        assertEquals(count($program_extensions), 3, 'Too much or too less tags found!');

        $ext = array("Arduino", "Lego", "Phiro");
        foreach ($program_extensions as $program_extension) {
            if (!(in_array($program_extension->getName(), $ext))) {
                assertTrue(false, 'The Extension is not found!');
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

        assertEquals(count($program_extensions), 0, 'Too much or too less tags found!');

        $ext = array("Arduino", "Lego", "Phiro");
        foreach ($program_extensions as $program_extension) {
            if (!(in_array($program_extension->getName(), $ext))) {
                assertTrue(false, 'The Extension is not found!');
            }
        }
    }

    /**
     * @When /^I search similar programs for program id "([^"]*)"$/
     */
    public function iSearchSimilarProgramsForProgramId($id)
    {
        $this->iHaveAParameterWithValue('program_id', $id);
        if (isset($this->request_parameters['limit'])) {
            $this->iHaveAParameterWithValue('limit', $this->request_parameters['limit']);
        } else {
            $this->iHaveAParameterWithValue('limit', '1');
        }
        if (isset($this->request_parameters['offset'])) {
            $this->iHaveAParameterWithValue('offset', $this->request_parameters['offset']);
        } else {
            $this->iHaveAParameterWithValue('offset', '0');
        }
        $this->iGetWithTheseParameters('/pocketcode/api/projects/recsys.json');
    }

  /**
   * @Given /^there are mediapackages:$/
   */
  public function thereAreMediapackages(TableNode $table)
  {
    /**
     * @var $em EntityManager
     */
    $em = $this->getManager();
    $packages = $table->getHash();
    foreach($packages as $package) {
      $new_package = new MediaPackage();
      $new_package->setName($package['name']);
      $new_package->setNameUrl($package['name_url']);
      $em->persist($new_package);
    }
    $em->flush();
  }

  /**
   * @Given /^there are mediapackage categories:$/
   */
  public function thereAreMediapackageCategories(TableNode $table)
  {
    /**
     * @var $em EntityManager
     */
    $em = $this->getManager();
    $categories = $table->getHash();
    foreach($categories as $category) {
      $new_category = new MediaPackageCategory();
      $new_category->setName($category['name']);
      $package = $em->getRepository('AppBundle:MediaPackage')->findOneBy(array('name' => $category['package']));
      if ($package == null)
        assert(false, "Fatal error package not found");
      $new_category->setPackage(array($package));
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
   */
  public function thereAreMediapackageFiles(TableNode $table)
  {
    /**
     * @var $em EntityManager
     * @var $file_repo MediaPackageFileRepository
     */
    $em = $this->getManager();
    $file_repo = $this->getMediaPackageFileRepository();
    $files = $table->getHash();
    foreach($files as $file) {
      $new_file = new MediaPackageFile();
      $new_file->setName($file['name']);
      $new_file->setDownloads(0);
      $new_file->setExtension($file['extension']);
      $new_file->setActive($file['active']);
      $category = $em->getRepository('AppBundle:MediaPackageCategory')->findOneBy(array('name' => $file['category']));
      if ($category == null)
        assert(false, "Fatal error category not found");
      $new_file->setCategory($category);
      $old_files = $category->getFiles();
      $old_files = $old_files == null ? [] : $old_files;
      array_push($old_files, $new_file);
      $category->setFiles($old_files);

      $new_file->setAuthor($file['author']);

      $file_repo->saveMediaPackageFile(new File(self::MEDIAPACKAGE_DIR.$file['id'].'.'.$file['extension']), $file['id'], $file['extension']);

      $em->persist($new_file);
    }
    $em->flush();
  }

}
