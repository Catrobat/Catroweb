<?php
namespace Catrobat\AppBundle\Features\Ci\Context;

use Behat\Behat\Tester\Exception\PendingException;
use Catrobat\AppBundle\Entity\RudeWord;
use Symfony\Component\HttpKernel\KernelInterface;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Gherkin\Node\PyStringNode, Behat\Gherkin\Node\TableNode;
use Catrobat\AppBundle\Entity\User;
use Catrobat\AppBundle\Entity\Program;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Behat\Behat\Context\CustomSnippetAcceptingContext;
use Catrobat\AppBundle\Services\TokenGenerator;
use Symfony\Bundle\FrameworkBundle\Client;
use Catrobat\AppBundle\Services\CatrobatFileCompressor;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Catrobat\AppBundle\Entity\FeaturedProgram;
use Catrobat\AppBundle\Model\ProgramManager;
require_once 'PHPUnit/Framework/Assert/Functions.php';

/**
 * Feature context.
 */
class FeatureContext implements KernelAwareContext, CustomSnippetAcceptingContext
{

    const FIXTUREDIR = "./testdata/DataFixtures/";

    private $kernel;

    private $hostname;

    private $secure;

    public static function getAcceptedSnippetType()
    {
        return 'regex';
    }

    /**
     * Initializes context with parameters from behat.yml.
     *
     * @param array $parameters            
     */
    public function __construct($error_directory)
    {
        $this->error_directory = $error_directory;
        $this->request_parameters = array();
        $this->files = array();
        $this->hostname = "localhost";
        $this->secure = false;
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
    
    // //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // //////////////////////////////////////////// Support Functions

    private function generateUser($name = "Generated")
    {
        $user_manager = $this->kernel->getContainer()->get('usermanager');
        $user = $user_manager->createUser();
        $user->setUsername($name);
        $user->setEmail("dev@pocketcode.org");
        $user->setPlainPassword("GeneratedPassword");
        $user->setEnabled(true);
        $user->setUploadToken("GeneratedToken");
        $user->setCountry("at");
        $user_manager->updateUser($user, true);
        return $user;
    }

    private function generateFakeProgramFor($user, $name)
    {
        $em = $this->kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        $program = new Program();
        $program->setUser($user);
        $program->setName($name);
        $program->setDescription("Generated");
        $program->setFilename("file.catrobat");
        $program->setThumbnail("thumb.png");
        $program->setScreenshot("screenshot.png");
        $program->setViews(1);
        $program->setDownloads(1);
        $program->setUploadedAt(new \DateTime());
        $program->setCatrobatVersion(1);
        $program->setCatrobatVersionName("0.9.1");
        $program->setLanguageVersion(1);
        $program->setUploadIp("127.0.0.1");
        $program->setRemixCount(0);
        $program->setFilesize(0);
        $program->setVisible(true);
        $program->setUploadLanguage("en");
        $program->setApproved(true);
        $em->persist($program);
        $em->flush();
    }

    private function getStandardProgramFile()
    {
        $filepath = self::FIXTUREDIR . "test.catrobat";
        assertTrue(file_exists($filepath), "File not found");
        return new UploadedFile($filepath, "test.catrobat");
    }

    /**
     * @Given /^the server name is "([^"]*)"$/
     */
    public function theServerNameIs($arg1)
    {
        $this->hostname = $arg1;
    }

    /**
     * @Given /^I use a secure connection$/
     */
    public function iUseASecureConnection()
    {
        $this->secure = true;
    }

    /**
     * @Given /^the token to upload an apk file is "([^"]*)"$/
     */
    public function theTokenToUploadAnApkFileIs($arg1)
    {
        // Defined in config_test.yml
    }

    /**
     * @Given /^I have a program "([^"]*)" with id "([^"]*)"$/
     */
    public function iHaveAProgramWithId($arg1, $arg2)
    {
        $user = $this->generateUser();
        $this->generateFakeProgramFor($user, $arg1);
    }

    /**
     * @Given /^the jenkins job id is "([^"]*)"$/
     */
    public function theJenkinsJobIdIs($arg1)
    {
        // Defined in config_test.yml
    }

    /**
     * @Given /^the jenkins token is "([^"]*)"$/
     */
    public function theJenkinsTokenIs($arg1)
    {
        // Defined in config_test.yml
    }

    /**
     * @When /^I start an apk generation of my program$/
     */
    public function iStartAnApkGenerationOfMyProgram()
    {
        $client = $this->kernel->getContainer()->get('test.client');
        $client->request('GET', "/ci/build/1", array(), array(), array('HTTP_HOST' => $this->hostname, 'HTTPS' => $this->secure));
        $response = $client->getResponse();
        assertEquals(200, $response->getStatusCode(), "Wrong response code. " . $response->getContent());
    }

    /**
     * @Then /^following parameters are sent to jenkins:$/
     */
    public function followingParametersAreSentToJenkins(TableNode $table)
    {
        $parameter_defs = $table->getHash();
        $expected_parameters = array();
        for($i = 0; $i < count($parameter_defs); $i ++)
        {
            $expected_parameters[$parameter_defs[$i]['parameter']] = $parameter_defs[$i]['value'];
        }
        $dispatcher = $this->kernel->getContainer()->get('ci.jenkins.dispatcher');
        $parameters = $dispatcher->getLastParameters();
        assertEquals($expected_parameters, $parameters);
    }
}
