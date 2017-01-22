<?php
namespace Features\Api\Context;

use Behat\Behat\Tester\Exception\PendingException;
use Catrobat\AppBundle\Entity\ProgramDownloads;
use Catrobat\AppBundle\Entity\ProgramDownloadsRepository;
use Catrobat\AppBundle\Entity\RudeWord;
use Catrobat\AppBundle\Features\Helpers\BaseContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Catrobat\AppBundle\Entity\User;
use Catrobat\AppBundle\Entity\Program;
use Catrobat\AppBundle\Services\StatisticsService;
use Catrobat\AppBundle\Services\TestEnv\LdapTestDriver;
use DateTime;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Catrobat\AppBundle\Services\TokenGenerator;
use Catrobat\AppBundle\Entity\FeaturedProgram;
use Catrobat\AppBundle\Features\Api\Context\FixedTokenGenerator;
use Catrobat\AppBundle\Features\Api\Context\FixedTime;

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
    private $method;
    private $url;
    private $post_parameters = array();
    private $get_parameters = array();
    private $server_parameters = array('HTTP_HOST' => 'pocketcode.org', 'HTTPS' => true);
    private $files = array();
    
    public function __construct()
    {
        parent::__construct();
    }
    
    // ----------------------------------------------------------------
    
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
        $this->post_parameters = $values;
    }

    /**
     * @Given /^the GET parameters:$/
     * @Given /^I use the GET parameters:$/
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
        $this->getClient()->request($this->method, 'https://' . $this->server_parameters['HTTP_HOST'] . $this->url . '?' . http_build_query($this->get_parameters), $this->post_parameters, $this->files, $this->server_parameters);
    }

    /**
     * @Then /^the returned json object will be:$/
     * @Then /^I will get the json object:$/
     */
    public function iWillGetTheJsonObject(PyStringNode $string)
    {
        $response = $this->getClient()->getResponse();
        assertJsonStringEqualsJsonString($string->getRaw(), $response->getContent(), '');
    }

    /**
     * @Then /^the response code will be "([^"]*)"$/
     */
    public function theResponseCodeWillBe($code)
    {
        $response = $this->getClient()->getResponse();
        assertEquals($code, $response->getStatusCode(), 'Wrong response code. ' . $response->getContent());
    }
    
    /**
     * @Given /^the server name is "([^"]*)"$/
     */
    public function theServerNameIs($arg1)
    {
        $this->server_parameters = array('HTTP_HOST' => 'pocketcode.org', 'HTTPS' => true, 'SERVER_NAME' => 'asdsd.org');
    }
    
    // ----------------------------------------------------------------
    
    /**
     * @Given /^there are users:$/
     */
    public function thereAreUsers(TableNode $table)
    {
        $users = $table->getHash();
    
        for ($i = 0; $i < count($users); ++ $i)
        {
            $this->insertUser(array('name' => $users[$i]['name'], 'token' => $users[$i]['token'], 'password' => $users[$i]['password']));
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
            if ($user == null) {
                if (isset($programs[$i]['owned by'])) {
                    $user = $this->insertUser(array('name' => $programs[$i]['owned by']));
                }
            }
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
                'remix_root' => isset($programs[$i]['remix_root']) ? $programs[$i]['remix_root'] == 'true' : true
            );

            $this->insertProgram($user, $config);
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
            $featured_entry->setActive(isset($featured[$i]['active']) ?  $featured[$i]['active'] == 'yes' : true);
            $featured_entry->setImageType('jpg');
            $em->persist($featured_entry);
        }
        $em->flush();
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
     * @Given /^we assume the next generated token will be "([^"]*)"$/
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
        assertTrue(file_exists($filepath), 'File not found');
        $this->files[] = new UploadedFile($filepath, 'test.catrobat');
    }

    /**
     * @Given /^the POST parameter "([^"]*)" contains the MD5 sum of the attached file$/
     */
    public function thePostParameterContainsTheMdSumOfTheGivenFile($arg1)
    {
        $this->post_parameters[$arg1] = md5_file($this->files[0]->getPathname());
    }
    
    /**
     * @Given /^the registration problem "([^"]*)"$/
     * @Given /^there is a registration problem ([^"]*)$/
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
     */
    public function searchingFor($arg1)
    {
        $this->method = 'GET';
        $this->url = '/pocketcode/api/projects/search.json';
        $this->get_parameters = array('q' => $arg1, 'offset' => 0, 'limit' => 10);
        $this->iInvokeTheRequest();
    }
    

    /**
     * @Given /^the upload problem "([^"]*)"$/
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
                assertTrue(file_exists($filepath), 'File not found');
                $this->files[] = new UploadedFile($filepath, 'test.catrobat');
                $this->post_parameters['fileChecksum'] = md5_file($this->files[0]->getPathname());
                break;
            default:
                throw new PendingException("No implementation of case \"" . $problem . "\"");
        }
    }

}
