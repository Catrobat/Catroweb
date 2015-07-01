<?php

namespace Catrobat\AppBundle\Features\Admin\Context;

use Behat\Behat\Tester\Exception\PendingException;
use Catrobat\AppBundle\Entity\Notification;
use Catrobat\AppBundle\Features\Helpers\BaseContext;
use Catrobat\AppBundle\Entity\UserManager;
use Symfony\Bundle\SwiftmailerBundle\DataCollector\MessageDataCollector;
use Behat\Gherkin\Node\TableNode;
use Catrobat\AppBundle\Entity\User;
use Catrobat\AppBundle\Entity\Program;

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
    private $request_parameters;
    private $files;

  /**
   * Initializes context with parameters from behat.yml.
   *
   * @param array $parameters
   */
  public function __construct($error_directory)
  {
      $this->setErrorDirectory($error_directory);
      $this->request_parameters = array();
      $this->files = array();
  }

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////// Support Functions


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////// Hooks

  /** @AfterSuite */
  protected function emptyDirectories()
  {
      $extract_dir = $this->getSymfonyParameter('catrobat.file.storage.dir');
      $this->emptyDirectory($extract_dir);
      $extract_dir = $this->getSymfonyParameter('catrobat.file.extract.dir');
      $this->emptyDirectory($extract_dir);
  }

  /** @BeforeScenario */
  public function emptyUploadFolder()
  {
      $extract_dir = $this->getSymfonyParameter('catrobat.file.storage.dir');
      $this->emptyDirectory($extract_dir);
  }

  /** @BeforeScenario */
  public function emptyExtraxtFolder()
  {
      $extract_dir = $this->getSymfonyParameter('catrobat.file.extract.dir');
      $this->emptyDirectory($extract_dir);
  }

  /** @AfterScenario */
  public function disableProfiler()
  {
      $this->getSymfonyService('profiler')->disable();
  }

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////// Steps

  /**
   * @Given /^there are users:$/
   */
  public function thereAreUsers(TableNode $table)
  {
      $users = $table->getHash();
      for ($i = 0; $i < count($users); ++$i ) {
          $this->insertUser(
        array(
          'name' => $users[$i]['name'],
          'email' => $users[$i]['email'],
        ));
      }
  }

  /**
   * @Given /^there are programs:$/
   */
  public function thereArePrograms(TableNode $table)
  {
      $programs = $table->getHash();
      for ($i = 0; $i < count($programs); ++$i ) {
          $config = array(
        'name' => $programs[$i]['name'],
      );

          $this->insertProgram(null, $config);
      }
  }

  /**
   * @Given /^there are notifications:$/
   */
  public function thereAreNotifications(TableNode $table)
  {
      /* @var $user_manager UserManager*/
    $user_manager = $this->getUserManager();
      $em = $this->getManager();
      $nots = $table->getHash();
      for ($i = 0; $i < count($nots); ++$i ) {
          $user = $user_manager->findOneBy(array(
          'username' => $nots[$i]['user'],
      ));
          $notification = new Notification();
          $notification->setUser($user);
          $notification->setReport($nots[$i]['report']);
          $notification->setSummary($nots[$i]['summary']);
          $notification->setUpload($nots[$i]['upload']);
          $em->persist($notification);
      }
      $em->flush();
  }

    /**
     * @Given /^I am a valid user$/
     */
    public function iAmAValidUser()
    {
        $this->getDefaultUser();
    }

    /**
     * @Given /^I activate the Profiler$/
     */
    public function iActivateTheProfiler()
    {
        $this->getClient()->enableProfiler();
    }

    /**
     * @Then /^I should see (\d+) outgoing emails$/
     */
    public function iShouldSeeOutgoingEmailsInTheProfiler($email_amount)
    {
        $profile = $this->getSymfonyProfile();
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
      $profile = $this->getSymfonyProfile();
        $collector = $profile->getCollector('swiftmailer');
        foreach ($collector->getMessages() as $message) {
            if ($recipient == array_keys($message->getTo())[0]) {
                return;
            }
        }
        assert(false, "Didn't find ".$recipient.' in recipients.');
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
            case 'valid parameters':
                $filename = 'base.catrobat';
                break;

            default:
                throw new PendingException('No case defined for "'.$programattribute.'"');
        }
        $this->upload(self::FIXTUREDIR.'/GeneratedFixtures/'.$filename, null);
    }

    /**
     * @When /^I report program (\d+) with note "([^"]*)"$/
     */
    public function iReportProgramWithNote($program_id, $note)
    {
        $url = '/pocketcode/api/reportProgram/reportProgram.json';
        $parameters = array(
          'program' => $program_id,
          'note' => $note,
      );
        $this->getClient()->request('POST', $url, $parameters);
    }
}
