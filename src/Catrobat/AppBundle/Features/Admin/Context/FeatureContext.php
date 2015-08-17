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
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

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
    private $hostname;
    private $secure;
    private $last_response;

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
      $this->hostname = 'localhost';
      $this->secure = false;
  }

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////// Support Functions


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////// Hooks
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
      $program_class = new \ReflectionClass(get_class(new Program()));
      $entity_variables = $program_class->getDefaultProperties();
      for ($i = 0; $i < count($programs); ++$i ) {

          $config = array();
          foreach($entity_variables as $entity_key=>$entity_var)
          {
              if(array_key_exists($entity_key,$programs[$i]))
                  $config[$entity_key] = $programs[$i][$entity_key];
          }
          if(count(array_intersect_key($entity_variables,$programs[$i])) != count(array_keys($programs[$i])))
          {
              throw new \Exception("Program entity variables: '".implode("','",array_keys(array_diff_key($programs[$i],$entity_variables)))."' not known");
          }
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
     * @Given /^I am a valid admin$/
     */
    public function iAmAValidAdmin()
    {
        $this->getDefaultUser(array("role"=>"ROLE_ADMIN"));
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


    /**
     * @When /^I GET "([^"]*)"$/
     */
    public function iGet($url)
    {
        $this->getClient()->followRedirects(true);
        $this->getClient()->request('GET', $url, array(), $this->files, array(
            'HTTP_HOST' => $this->hostname, 'HTTPS' => $this->secure,
        ));
        $this->last_response = $this->getClient()->getResponse();
    }

    /**
     * @Given /^I am a user with role "([^"]*)"$/
     */
    public function iAmAUserWithRole($role)
    {
        $this->insertUser(array("role"=>$role, "name"=>"generatedBehatUser"));

        $client = $this->getClient();
        $client->getCookieJar()->set(new Cookie(session_name(), true));

        $session = $client->getContainer()->get('session');

        $user = $this->getSymfonyService('fos_user.user_manager')->findUserByUsername("generatedBehatUser");
        $providerKey = $this->getSymfonyParameter('fos_user.firewall_name');

        $token = new UsernamePasswordToken($user, null, $providerKey, $user->getRoles());
        $session->set('_security_'.$providerKey, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);
    }

    /**
     * @Then /^the response should contain "([^"]*)"$/
     */
    public function theResponseShouldContain($needle)
    {
        assertContains($needle,$this->last_response->getContent());
    }

    /**
     * @Then /^the response should not contain "([^"]*)"$/
     */
    public function theResponseShouldNotContain($needle)
    {
        assertNotContains($needle,$this->last_response->getContent());
    }

    /**
     * @Given /^there is a file "([^"]*)" with size "([^"]*)" bytes in the APK-folder$/
     */
    public function thereIsAFileWithSizeBytesInTheApkFolder($filename, $size)
    {
        $this->generateFileInPath($this->getSymfonyParameter("catrobat.apk.dir"),$filename,$size);
    }

    private function generateFileInPath($path, $filename, $size)
    {
        $full_filename = $path."/".$filename;
        $dirname = dirname($full_filename);
        if (!is_dir($dirname))
        {
            mkdir($dirname, 0755, true);
        }
        $fp = fopen($full_filename, 'w'); // open in write mode.
        fseek($fp, $size-1,SEEK_CUR); // seek to SIZE-1
        fwrite($fp,'a'); // write a dummy char at SIZE position
        fclose($fp); // close the file.
    }

    /**
     * @Then /^program with id "([^"]*)" should have no apk$/
     */
    public function programWithIdShouldHaveNoApk($program_id)
    {
        $program_manager = $this->getProgramManger();
        $program = $program_manager->find($program_id);
        assertEquals(Program::APK_NONE,$program->getApkStatus());
    }

    /**
     * @Given /^there is a file "([^"]*)" with size "([^"]*)" bytes in the backup-folder$/
     */
    public function thereIsAFileWithSizeBytesInTheBackupFolder($filename, $size)
    {
        $this->generateFileInPath($this->getSymfonyParameter("catrobat.backup.dir"),$filename,$size);
    }

    /**
     * @Given /^there is a file "([^"]*)" with size "([^"]*)" bytes in the extracted-folder$/
     */
    public function thereIsAFileWithSizeBytesInTheExtractedFolder($filename, $size)
    {
        $this->generateFileInPath($this->getSymfonyParameter("catrobat.file.extract.dir"),$filename,$size);
    }

    /**
     * @Then /^program with id "([^"]*)" should have no directory_hash$/
     */
    public function programWithIdShouldHaveNoDirectoryHash($program_id)
    {
        $program_manager = $this->getProgramManger();
        $program = $program_manager->find($program_id);
        assertEquals(null,$program->getDirectoryHash());
    }

    /**
     * @Given /^I am a logged in as super admin$/
     */
    public function iAmALoggedInAsSuperAdmin()
    {
        $this->iAmAUserWithRole("ROLE_SUPER_ADMIN");
    }

    /**
     * @Given /^I am logged in as normal user$/
     */
    public function iAmLoggedInAsNormalUser()
    {
        $this->iAmAUserWithRole("ROLE_USER");
    }

    /**
     * @Given /^I am a logged in as admin$/
     */
    public function iAmALoggedInAsAdmin()
    {
      $this->iAmAUserWithRole("ROLE_ADMIN");
    }
}
