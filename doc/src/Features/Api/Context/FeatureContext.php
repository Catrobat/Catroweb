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
use Catrobat\AppBundle\Services\DownloadStatisticsService;
use Catrobat\AppBundle\Services\TestEnv\LdapTestDriver;
use DateTime;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Catrobat\AppBundle\Services\TokenGenerator;
use Catrobat\AppBundle\Entity\FeaturedProgram;
use Catrobat\AppBundle\Features\Api\Context\FixedTokenGenerator;

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
    private $files = array();
    
    public function __construct()
    {
        parent::__construct();
    }
    
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
     * @Given /^I have the HTTP Request:$/
     */
    public function iHaveTheHttpRequest(TableNode $table)
    {
        $values = $table->getRowsHash();
        $this->method = $values['Method'];
        $this->url = $values['Url'];
    }

    /**
     * @Given /^I use the POST parameters:$/
     */
    public function iUseThePostParameters(TableNode $table)
    {
        $values = $table->getRowsHash();
        $this->post_parameters = $values;
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
     * @Given /^We assume the next generated token will be "([^"]*)"$/
     */
    public function weAssumeTheNextGeneratedTokenWillBe($token)
    {
        $token_generator = $this->getSymfonyService('tokengenerator');
        $token_generator->setTokenGenerator(new FixedTokenGenerator($token));
    }

    /**
     * @When /^I invoke the Request$/
     */
    public function iInvokeTheRequest()
    {
        if ($this->method == "GET") {
            $this->getClient()->request('GET', $this->url . '?' . http_build_query($this->get_parameters), array(), array(), array());
        }
        else if ($this->method == "POST") {
            $this->getClient()->request('POST', $this->url, $this->post_parameters, $this->files, array());
        }
        else {
           throw new PendingException();
        }
    }

    /**
     * @Then /^I will get the json object:$/
     */
    public function iWillGetTheJsonObject(PyStringNode $string)
    {
        $response = $this->getClient()->getResponse();
        assertJsonStringEqualsJsonString($string->getRaw(), $response->getContent(), '');
    }

    /**
     * @Then /^The response code will be "([^"]*)"$/
     */
    public function theResponseCodeWillBe($code)
    {
        $response = $this->getClient()->getResponse();
        assertEquals($code, $response->getStatusCode(), 'Wrong response code. ' . $response->getContent());
    }
    
    /**
     * @When /^There is a registration problem ([^"]*)$/
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
     * @When /^There is a check token problem ([^"]*)$/
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
     * @Given /^I attach a catrobat file$/
     */
    public function iAttachACatrobatFile()
    {
        $filepath = self::FIXTUREDIR . 'test.catrobat';
        assertTrue(file_exists($filepath), 'File not found');
        $this->files[] = new UploadedFile($filepath, 'test.catrobat');
    }

    /**
     * @Given /^the POST parameter "([^"]*)" contains the MD5 sum of the given file$/
     */
    public function thePostParameterContainsTheMdSumOfTheGivenFile($arg1)
    {
        $this->post_parameters[$arg1] = md5_file($this->files[0]->getPathname());
    }
}
