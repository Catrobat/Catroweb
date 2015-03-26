<?php

namespace Catrobat\AppBundle\Features\Flavor\Context;

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
  private $error_directory;
  
  private $kernel;
  private $user;
  private $request_parameters;
  private $files;
  private $last_response;
  private $hostname;
  private $secure;
  
  /*
   * @var \Symfony\Component\HttpKernel\Client
   */
  private $client;
  
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
    $this->request_parameters = array ();
    $this->files = array ();
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


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////// Support Functions

  private function emptyDirectory($directory)
  {
    if(!is_dir($directory))
    {
      return;
    }
    $filesystem = new Filesystem();

    $finder = new Finder();
    $finder->in($directory)->depth(0);
    foreach($finder as $file)
    {
      $filesystem->remove($file);
    }
  }

  private function generateProgramFileWith($parameters)
  {
    $filesystem = new Filesystem();
    $this->emptyDirectory(sys_get_temp_dir()."/program_generated/");
    $new_program_dir = sys_get_temp_dir()."/program_generated/";
    $filesystem->mirror(self::FIXTUREDIR."/GeneratedFixtures/base", $new_program_dir);
    $properties = simplexml_load_file($new_program_dir."/code.xml");

    foreach($parameters as $name => $value) {

      switch ($name)
      {
        case "description":
          $properties->header->description = $value;
          break;
        case "name":
          $properties->header->programName = $value;
          break;
        case "platform":
          $properties->header->platform = $value;
          break;
        case "catrobatLanguageVersion":
          $properties->header->catrobatLanguageVersion = $value;
          break;
        case "applicationVersion":
          $properties->header->applicationVersion = $value;
          break;
        case "applicationName":
          $properties->header->applicationName = $value;
          break;

        default:
          throw new PendingException("unknown xml field " . $name);
      }

    }

    $properties->asXML($new_program_dir."/code.xml");
    $compressor = new CatrobatFileCompressor();
    return $compressor->compress($new_program_dir, sys_get_temp_dir()."/", "program_generated");
  }

    /**
     * @When /^I upload a catrobat program with the kodey app$/
     */
    public function iUploadACatrobatProgramWithTheKodeyApp()
    {
        $user = $this->generateUser();
        $program = $this->getKodeyProgramFile();
        $response = $this->upload($program, $user);
        $this->last_response = $response;
    }

    /**
     * @Then /^the program should be flagged as kodey$/
     */
    public function theProgramShouldBeFlaggedAsKodey()
    {
        $program_manager = $this->kernel->getContainer()->get('programmanager');
        $program = $program_manager->find(1);
        assertNotNull($program, "No program added");
        assertEquals("pocketkodey", $program->getFlavor(), "Program is NOT flagged a kodey");
    }

    /**
     * @When /^I upload a standard catrobat program$/
     */
    public function iUploadAStandardCatrobatProgram()
    {
        $user = $this->generateUser();
        $program = $this->getStandardProgramFile();
        $response = $this->upload($program, $user);
        $this->last_response = $response;
    }

    /**
     * @Then /^the program should not be flagged as kodey$/
     */
    public function theProgramShouldNotBeFlaggedAsKodey()
    {
        $program_manager = $this->kernel->getContainer()->get('programmanager');
        $program = $program_manager->find(1);
        assertNotNull($program, "No program added");
        assertNotEquals("pocketkodey", $program->getFlavor(), "Program is flagged a kodey");
    }
    
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
    
    private function getStandardProgramFile()
    {
        $filepath = self::FIXTUREDIR . "test.catrobat";
        assertTrue(file_exists($filepath), "File not found");
        return new UploadedFile($filepath, "test.catrobat");
    }
    
    private function getKodeyProgramFile()
    {
        $filepath = $this->generateProgramFileWith(array('applicationName' => 'Pocket Kodey'));
        assertTrue(file_exists($filepath), "File not found");
        return new UploadedFile($filepath, "program_generated.catrobat");
    }
    
    private function upload($file, $user)
    {
        $parameters =  array();
        $parameters["username"] = $user->getUsername();
        $parameters["token"] = $user->getUploadToken();
        $parameters["fileChecksum"] = md5_file($file->getPathname());
        $client = $this->kernel->getContainer()->get('test.client');
        $client->request('POST', "/api/upload/upload.json", $parameters, array($file));
        $response = $client->getResponse(); 
        assertEquals(200, $response->getStatusCode(), "Wrong response code. " . $response->getContent());
        return $response;
    }
    
}
