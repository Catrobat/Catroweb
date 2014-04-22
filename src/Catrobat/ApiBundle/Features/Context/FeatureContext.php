<?php

namespace Catrobat\ApiBundle\Features\Context;

use Symfony\Component\HttpKernel\KernelInterface;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\MinkExtension\Context\MinkContext;

use Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;
use Catrobat\CoreBundle\Entity\User;
use Catrobat\CoreBundle\Entity\Project;
use Behat\Behat\Event\SuiteEvent;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

//
// Require 3rd-party libraries here:
//
//require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';
//

/**
 * Feature context.
 */
class FeatureContext implements KernelAwareContext
{
    const FIXTUREDIR = "./src/Catrobat/TestBundle/DataFixtures/";
    private $kernel;

    private $user;
    private $request_parameters;
    
    private $files;
    
    /**
     * Initializes context with parameters from behat.yml.
     *
     * @param array $parameters
     */
    public function __construct()
    {
        $this->request_parameters = array();
        $this->files = array();
    }

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
     * @Given /^the upload folder is empty$/
     */
    public function theUploadFolderIsEmpty()
    {
      $extract_dir = $this->kernel->getContainer()->getParameter("catrobat.file.storage.dir");
      $this->emptyDirectory($extract_dir);
    }
    
    /**
     * @Given /^the extract folder is empty$/
     */
    public function theExtractFolderIsEmpty()
    {
      $extract_dir = $this->kernel->getContainer()->getParameter("catrobat.file.extract.dir");
      $this->emptyDirectory($extract_dir);
    }
    
    /**
     * @Given /^there are users:$/
     */
    public function thereAreUsers(TableNode $table)
    {
      $user_manager =  $this->kernel->getContainer()->get('catrobat.core.model.usermanager');
      $users = $table->getHash();
      $user = null;
      for ($i = 0; $i < count($users); $i++)
      {
      	$user = $user_manager->createUser();
      	$user->setUsername($users[$i]["name"]);
      	$user->setEmail("dev".$i."@pocketcode.org");
      	$user->setPlainPassword($users[$i]["password"]);
      	$user->setEnabled(true);
      	$user->setUploadToken($users[$i]["token"]);
      	$user_manager->updateUser($user,false);
      }
      $user_manager->updateUser($user,true);
    }
    
    /**
     * @Given /^there are projects:$/
     */
    public function thereAreProjects(TableNode $table)
    {
    	$em = $this->kernel->getContainer()->get('doctrine')->getManager();
    	$projects = $table->getHash();
    	for ($i = 0; $i < count($projects); $i++)
    	{
    		$user = $em->getRepository('CatrobatCoreBundle:User')->findOneBy(array('username' => 'Catrobat'));
				$project = new Project();
				$project->setUser($user);
				$project->setName($projects[$i]['name']);
				$project->setDescription($projects[$i]['description']);
				$project->setFilename("file".$i.".catrobat");
				$project->setThumbnail("thumb.png");
				$project->setScreenshot("screenshot.png");
				$project->setViews($projects[$i]['views']);
				$project->setDownloads($projects[$i]['downloads']);
				$project->setUploadedAt(new \DateTime($projects[$i]['upload time'],new \DateTimeZone('UTC')));
				$project->setCatrobatVersion(1);
				$project->setCatrobatVersionName($projects[$i]['version']);
				$project->setLanguageVersion(1);
				$project->setUploadIp("127.0.0.1");
				$project->setRemixCount(0);
				$project->setFilesize(0);
				$project->setVisible(true);
				$project->setUploadLanguage("en");
				$project->setApproved(false);
				$em->persist($project);
    	}
    	$em->flush();
    }
    
    /**
     * @Given /^I have a parameter "([^"]*)" with value "([^"]*)"$/
     */
    public function iHaveAParameterWithValue($name, $value)
    {
      $this->request_parameters[$name] = $value;
    }
    
    /**
     * @When /^I POST these parameters to "([^"]*)"$/
     */
    public function iPostTheseParametersTo($url)
    {
      $this->client = $this->kernel->getContainer()->get('test.client');
    	$crawler = $this->client->request('POST', $url, $this->request_parameters, $this->files);
    }
    
    /**
     * @Given /^I am "([^"]*)"$/
     */
    public function iAm($username)
    {
    	$this->user = $username;
    }
    
    /**
     * @When /^I call "([^"]*)" with token "([^"]*)"$/
     */
    public function iCallWithToken($url, $token)
    {
      $this->client = $this->kernel->getContainer()->get('test.client');
      $params = array("token" => $token, "username" => $this->user); 
      $crawler = $this->client->request('POST', $url, $params);
    }
    
    /**
     * @Then /^I should see:$/
     */
    public function iShouldSee(PyStringNode $string)
    {
    	$response = $this->client->getResponse();
    	assertEquals($string->getRaw(), $response->getContent());
    }
    
    /**
     * @Then /^I should get the json object:$/
     */
    public function iShouldGetTheJsonObject(PyStringNode $string)
    {
      $response = $this->client->getResponse();
      assertJsonStringEqualsJsonString($string->getRaw(), $response->getContent(), $response->getContent());
    }
    
    /**
     * @Then /^I should get the json object with random token:$/
     */
    public function iShouldGetTheJsonObjectWithRandomToken(PyStringNode $string)
    {
      $response = $this->client->getResponse();
      $responseArray = json_decode($response->getContent(),true);
      $expectedArray = json_decode($string->getRaw(),true);
      $responseArray['token'] = "";
      $expectedArray['token'] = "";
      assertEquals($expectedArray, $responseArray);
    }
    
    /**
     * @Then /^I should get the json object with random "([^"]*)" and "([^"]*)":$/
     */
    public function iShouldGetTheJsonObjectWithRandomAndProjectid($arg1, $arg2, PyStringNode $string)
    {
      $response = $this->client->getResponse();
      $responseArray = json_decode($response->getContent(),true);
      $expectedArray = json_decode($string->getRaw(),true);
      $responseArray[$arg1] = $expectedArray[$arg1] = "";
      $responseArray[$arg2] = $expectedArray[$arg2] = "";
      assertEquals($expectedArray, $responseArray, $response);
    }
    
    /**
     * @Then /^I should get projects in the following order:$/
     */
    public function iShouldGetProjectsInTheFollowingOrder(TableNode $table)
    {
      $response = $this->client->getResponse();
      $responseArray = json_decode($response->getContent(),true);
      $returned_projects = $responseArray['CatrobatProjects'];
      $expected_projects = $table->getHash();
      assertEquals(count($expected_projects), count($returned_projects), "Wrong number of returned projects");
      for ($i = 0; $i < count($returned_projects); $i++)
      {
        assertEquals($expected_projects[$i]["Name"], $returned_projects[$i]["ProjectName"], "Wrong order of results");
      }
    }
    
    /**
     * @Given /^the response code should be "([^"]*)"$/
     */
    public function theResponseCodeShouldBe($code)
    {
      $response = $this->client->getResponse();
      assertEquals($code, $response->getStatusCode(), "Wrong response code");
    }
    
    /**
     * @Given /^the returned "([^"]*)" should be a number$/
     */
    public function theReturnedShouldBeANumber($arg1)
    {
      $response = json_decode($this->client->getResponse()->getContent(),true);
      assertTrue(is_numeric($response[$arg1]));
    }
    
    /**
     * @Given /^I am not registered$/
     */
    public function iAmNotRegistered()
    {
    	throw new PendingException();
    }
    
    /**
     * @Given /^I have a username "([^"]*)"$/
     */
    public function iHaveAUsername($arg1)
    {
      //just a test input
    	throw new PendingException();
    }
    
    /**
     * @Given /^I have a password "([^"]*)"$/
     */
    public function iHaveAPassword($arg1)
    {
    	throw new PendingException();
    }
    
    /**
     * @Given /^I have a language "([^"]*)"$/
     */
    public function iHaveALanguage($arg1)
    {
    	throw new PendingException();
    }
    
    /**
     * @Given /^I have an email address "([^"]*)"$/
     */
    public function iHaveAnEmailAddress($arg1)
    {
    	throw new PendingException();
    }
    
    /**
     * @When /^I call "([^"]*)" the given data$/
     */
    public function iCallTheGivenData($arg1)
    {
    	throw new PendingException();
    }
    
    /**
     * @When /^I call "([^"]*)" with username "([^"]*)" and password "([^"]*)"$/
     */
    public function iCallWithUsernameAndPassword($arg1, $arg2, $arg3)
    {
    	throw new PendingException();
    }
    
    /**
     * @Given /^I have the username "([^"]*)"$/
     */
    public function iHaveTheUsername($arg1)
    {
    	throw new PendingException();
    }
    
    /**
     * @Given /^I have a token "([^"]*)"$/
     */
    public function iHaveAToken($arg1)
    {
    	throw new PendingException();
    }
    
    /**
     * @Given /^I have a file "([^"]*)"$/
     */
    public function iHaveAFile($filename)
    {
      $filepath = "./src/Catrobat/ApiBundle/Features/Fixtures/".$filename;
      assertTrue(file_exists($filepath),"File not found");
      $this->files[] = new UploadedFile($filepath,$filename);
    }

    /**
     * @Given /^I have a valid Catrobat file$/
     */
    public function iHaveAValidCatrobatFile()
    {
        $filepath = self::FIXTUREDIR . "compass.catrobat";
        assertTrue(file_exists($filepath),"File not found");
        $this->files[] = new UploadedFile($filepath,"compass.catrobat");
    }

    /**
     * @Given /^I have a Catrobat file with an invalid code\.xml$/
     */
    public function iHaveACatrobatFileWithAnInvalidCodeXml()
    {
        $filepath = self::FIXTUREDIR . "GeneratedFixtures/project_with_invalid_code_xml.catrobat";
        assertTrue(file_exists($filepath),"File not found");
        $this->files[] = new UploadedFile($filepath,"project_with_invalid_code_xml.catrobat");
    }
    
    /**
     * @Given /^I have a Catrobat file with an missing code\.xml$/
     */
    public function iHaveACatrobatFileWithAnMissingCodeXml()
    {
        $filepath = self::FIXTUREDIR . "GeneratedFixtures/project_with_missing_code_xml.catrobat";
        assertTrue(file_exists($filepath),"File not found");
        $this->files[] = new UploadedFile($filepath,"project_with_missing_code_xml.catrobat");
    }
    
    /**
     * @Given /^I have a Catrobat file with a missing image$/
     */
    public function iHaveACatrobatFileWithAMissingImage()
    {
        $filepath = self::FIXTUREDIR . "GeneratedFixtures/project_with_missing_image.catrobat";
        assertTrue(file_exists($filepath),"File not found");
        $this->files[] = new UploadedFile($filepath,"project_with_missing_image.catrobat");
    }
    
    /**
     * @Given /^I have a Catrobat file with an additional image$/
     */
    public function iHaveACatrobatFileWithAnAdditionalImage()
    {
        $filepath = self::FIXTUREDIR . "GeneratedFixtures/project_with_extra_image.catrobat";
        assertTrue(file_exists($filepath),"File not found");
        $this->files[] = new UploadedFile($filepath,"project_with_extra_image.catrobat");
    }
    
    /**
     * @Given /^I have an invalid Catrobat file$/
     */
    public function iHaveAnInvalidCatrobatFile()
    {
        $filepath = self::FIXTUREDIR . "invalid_archive.catrobat";
        assertTrue(file_exists($filepath),"File not found");
        $this->files[] = new UploadedFile($filepath,"invalid_archive.catrobat");
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
      $this->request_parameters[$parameter] = "INVALIDCHECKSUM"; 
    }
    
    
    /**
     * @Given /^I have a parameter "([^"]*)" with the md5checksum of "([^"]*)"$/
     */
    public function iHaveAParameterWithTheMdchecksumOf($parameter, $file)
    {
      $this->request_parameters[$parameter] = md5_file($this->files[0]->getPathname()); 
    }
    
    /**
     * @When /^I call "([^"]*)" with the given data$/
     */
    public function iCallWithTheGivenData($arg1)
    {
    	throw new PendingException();
    }
    
    /**
     * @Given /^I want to search for the term "([^"]*)"$/
     */
    public function iWantToSearchForTheTerm($arg1)
    {
    	throw new PendingException();
    }
    
    /**
     * @Given /^I have the limit "([^"]*)"$/
     */
    public function iHaveTheLimit($arg1)
    {
    	throw new PendingException();
    }
    
    /**
     * @Given /^I have the offset "([^"]*)"$/
     */
    public function iHaveTheOffset($arg1)
    {
    	throw new PendingException();
    }
    
   private function emptyDirectory($directory)
   {
     $filesystem = new Filesystem();
     
     $finder = new Finder();
     $finder->in($directory)->depth(0);
     foreach ($finder as $file)
     {
       $filesystem->remove($file);
     }
   }
}
