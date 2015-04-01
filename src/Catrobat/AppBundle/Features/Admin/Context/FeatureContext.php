<?php

namespace Catrobat\AppBundle\Features\Admin\Context;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Symfony2Extension\Driver\KernelDriver;
use Catrobat\AppBundle\Entity\Notification;
use Catrobat\AppBundle\Entity\RudeWord;
use Catrobat\AppBundle\Model\UserManager;
use Symfony\Bundle\SwiftmailerBundle\DataCollector\MessageDataCollector;
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

//
// Require 3rd-party libraries here:
//
// require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';
//

/**
 * Feature context.
 */
class FeatureContext extends MinkContext implements KernelAwareContext, CustomSnippetAcceptingContext
{
  const FIXTUREDIR = "./src/Catrobat/TestBundle/DataFixtures/";
  private $error_directory;
  
  private $kernel;
  private $user;
  private $request_parameters;
  private $files;
  private $last_response;

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

    public function getSymfonyProfile()
    {
        $profile = $this->client->getProfile();
        if (false === $profile) {
            throw new \RuntimeException(
                'The profiler is disabled. Activate it by setting '.
                'framework.profiler.only_exceptions to false in '.
                'your config'
            );
        }

        return $profile;
    }

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
  
  private function uploadProgramFileAsDefaultUser($directory, $filename)
  {
    $filepath = $directory . "/" . $filename;
    assertTrue(file_exists($filepath), "File not found");
    $files = array(new UploadedFile($filepath, $filename));
    $url = "/api/upload/upload.json";
    $parameters = array(
        "username" => "BehatGeneratedName",
        "token" => "BehatGeneratedToken",
        "fileChecksum" => md5_file($files[0]->getPathname())
    );
    
    $this->client = $this->kernel->getContainer()->get('test.client');
    $this->client->request('POST', $url, $parameters, $files);
  }



////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////// Hooks

  /** @AfterSuite */
  protected function emptyDirectories()
  {
    $extract_dir = $this->kernel->getContainer()->getParameter("catrobat.file.storage.dir");
    $this->emptyDirectory($extract_dir);
    $extract_dir = $this->kernel->getContainer()->getParameter("catrobat.file.extract.dir");
    $this->emptyDirectory($extract_dir);
  }

  /** @BeforeScenario */
  public function emptyUploadFolder()
  {
    $extract_dir = $this->kernel->getContainer()->getParameter("catrobat.file.storage.dir");
    $this->emptyDirectory($extract_dir);
  }

  /** @BeforeScenario */
  public function emptyExtraxtFolder()
  {
    $extract_dir = $this->kernel->getContainer()->getParameter("catrobat.file.extract.dir");
    $this->emptyDirectory($extract_dir);
  }

  /** @AfterScenario */
  public function disableProfiler()
  {
    $this->kernel->getContainer()->get('profiler')->disable();
  }

  /** @AfterStep */
  public function saveResponseToFile(AfterStepScope $scope)
  {
    if (!$scope->getTestResult()->isPassed() && $this->client != null)
    {
      $response = $this->client->getResponse(); 
      if ($response->getContent() != "")
      {
        file_put_contents($this->error_directory . "error.json", $response->getContent());
      }
    }
  }

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////// Steps

    /**
     * @Given /^there are users:$/
     */
    public function thereAreUsers(TableNode $table)
    {
        /* @var $user User*/
        $user_manager = $this->kernel->getContainer()->get('usermanager');
        $users = $table->getHash();
        $user = null;
        for($i = 0; $i < count($users); $i ++)
        {
            $user = $user_manager->createUser();
            $user->setUsername($users[$i]["name"]);
            //$user->setEmail("dev" . $i . "@pocketcode.org");
            $user->setPlainPassword($users[$i]["password"]);
            $user->setEnabled(true);
            $user->setEmail($users[$i]["email"]);
            $user->setUploadToken($users[$i]["token"]);
            $user->setSuperAdmin($users[$i]["SuperAdmin"]);
            $user_manager->updateUser($user, false);
        }
        $user_manager->updateUser($user, true);
    }

  /**
   * @Given /^there are programs:$/
   */
  public function thereArePrograms(TableNode $table)
  {
    $em = $this->kernel->getContainer()->get('doctrine')->getManager();
    $programs = $table->getHash();
    for($i = 0; $i < count($programs); $i ++)
    {
      $user = $em->getRepository('AppBundle:User')->findOneBy(array (
          'username' => $programs[$i]['owned by']
      ));
      $program = new Program();
      $program->setUser($user);
      $program->setName($programs[$i]['name']);
      $program->setDescription($programs[$i]['description']);
      $program->setFilename("file" . $i . ".catrobat");
      $program->setThumbnail("thumb.png");
      $program->setScreenshot("screenshot.png");
      $program->setViews($programs[$i]['views']);
      $program->setDownloads($programs[$i]['downloads']);
      $program->setUploadedAt(new \DateTime($programs[$i]['upload time'], new \DateTimeZone('UTC')));
      $program->setCatrobatVersion(1);
      $program->setCatrobatVersionName($programs[$i]['version']);
      $program->setLanguageVersion(1);
      $program->setUploadIp("127.0.0.1");
      $program->setRemixCount(0);
      $program->setFilesize(isset($programs[$i]['FileSize']) ? $programs[$i]['FileSize'] : 0);
      $program->setVisible(isset($programs[$i]['visible']) ? $programs[$i]['visible']=="true" : true);
      $program->setUploadLanguage("en");
      $program->setApproved(false);
      $em->persist($program);
    }
    $em->flush();
  }

  /**
   * @Given /^there are notifications:$/
   */
  public function thereAreNotifications(TableNode $table)
  {
    /* @var $user_manager UserManager*/
    $user_manager = $this->kernel->getContainer()->get('usermanager');
    $em = $this->kernel->getContainer()->get('doctrine.orm.entity_manager');
    $nots = $table->getHash();
    for($i = 0; $i < count($nots); $i ++)
    {
      $user = $em->getRepository('AppBundle:User')->findOneBy(array (
          'username' => $nots[$i]['user']
      ));
      $notification = new Notification();
      $notification->setUser($user);
      $notification->setReport($nots[$i]["report"]);
      $notification->setSummary($nots[$i]["summary"]);
      $notification->setUpload($nots[$i]["upload"]);
      $em->persist($notification);
    }
    $em->flush();
  }

    /**
     * @Given /^I am a valid user$/
     */

    public function iAmAValidUser()
    {
        $user_manager = $this->kernel->getContainer()->get('usermanager');
        $user = $user_manager->createUser();
        $user->setUsername("BehatGeneratedName");
        $user->setEmail("dev@pocketcode.org");
        $user->setPlainPassword("BehatGeneratedPassword");
        $user->setEnabled(true);
        $user->setUploadToken("BehatGeneratedToken");
        $user_manager->updateUser($user, true);
    }

    /**
     * @Given /^I activate the Profiler$/
     */
    public function iActivateTheProfiler()
    {
      $this->client = $this->kernel->getContainer()->get('test.client');
      $this->client->enableProfiler();
    }

    /**
     * @Then /^I should see (\d+) outgoing emails$/
     */
    public function iShouldSeeOutgoingEmailsInTheProfiler($email_amount)
    {
        $profile   = $this->getSymfonyProfile();
        $collector = $profile->getCollector('swiftmailer');
        assertEquals($email_amount, $collector->getMessageCount());
    }

    /**
     * @Then /^I should see a email with recipient "([^"]*)"$/
     */
    public function iShouldSeeAEmailWithRecipient($recipient)
    {
      /* @var $collector MessageDataCollector */
      /* @var $message \Swift_Message */
      $profile   = $this->getSymfonyProfile();
      $collector = $profile->getCollector('swiftmailer');
      foreach($collector->getMessages() as $message)
      {
        if($recipient == array_keys($message->getTo())[0])
          return;
      }
      assert(false,"Didn't find ".$recipient." in recipients.");
    }

    /**
     * @When /^I upload a program with (.*)$/
     */
    public function iUploadAProgramWith($programattribute)
    {
        $filename = "NOFILENAMEDEFINED";
        switch($programattribute)
        {
            case "a rude word in the description":
                $filename = "program_with_rudeword_in_description.catrobat";
                break;
            case "a rude word in the name":
                $filename = "program_with_rudeword_in_name.catrobat";
                break;
            case "a missing code.xml":
                $filename = "program_with_missing_code_xml.catrobat";
                break;
            case "an invalid code.xml":
                $filename = "program_with_invalid_code_xml.catrobat";
                break;
            case "a missing image":
                $filename = "program_with_missing_image.catrobat";
                break;
            case "an additional image":
                $filename = "program_with_extra_image.catrobat";
                break;
            case "valid parameters":
                $filename = "base.catrobat";
                break;

            default:
                throw new PendingException("No case defined for \"" . $programattribute . "\"");
        }
        $this->uploadProgramFileAsDefaultUser(self::FIXTUREDIR . "/GeneratedFixtures", $filename);
    }

    /**
     * @When /^I report program (\d+) with note "([^"]*)"$/
     */
    public function iReportProgramWithNote($program_id, $note)
    {
      $url = "/api/reportProgram/reportProgram.json";
      $parameters = array(
          "program" => $program_id,
          "note" => $note
      );

      $this->client = $this->kernel->getContainer()->get('test.client');
      $this->client->request('POST', $url, $parameters);
    }
}
