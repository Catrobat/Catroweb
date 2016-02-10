<?php
namespace Catrobat\AppBundle\Features\Admin\Context;

use Behat\Behat\Tester\Exception\PendingException;
use Catrobat\AppBundle\Entity\Notification;
use Catrobat\AppBundle\Entity\UserLDAPManager;
use Catrobat\AppBundle\Features\Helpers\BaseContext;
use Catrobat\AppBundle\Entity\UserManager;
use Catrobat\AppBundle\Services\TestEnv\LdapTestDriver;
use Sonata\AdminBundle\Command\SetupAclCommand;
use Symfony\Bundle\SwiftmailerBundle\DataCollector\MessageDataCollector;
use Behat\Gherkin\Node\TableNode;
use Catrobat\AppBundle\Entity\User;
use Catrobat\AppBundle\Entity\Program;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

//
// Require 3rd-party libraries here:
//
// require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';
//

/**
 * Feature context.
 */
class FeatureContext extends \Catrobat\AppBundle\Features\Api\Context\FeatureContext
{

    // //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // //////////////////////////////////////////// Support Functions

    // //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // //////////////////////////////////////////// Hooks

    /**
     * @BeforeScenario
     */
    public function followRedirects()
    {
        $this->getClient()->followRedirects(true);
    }

    /**
     * @BeforeScenario
     */
    public function generateSessionCookie()
    {
        $client = $this->getClient();

        $session = $this->getClient()
            ->getContainer()
            ->get("session");

        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);
    }

    /**
     * @BeforeScenario
     */
    public function initACL()
    {
        $acl_command = new SetupAclCommand();

        $acl_command->setContainer($this->getClient()
            ->getContainer());
        $return = $acl_command->run(new ArrayInput(array()), new NullOutput());
        if ($return != 0) {
            assert(false, "Oh no!");
        }
    }

    /**
     * @AfterScenario
     */
    public function disableProfiler()
    {
        $this->getSymfonyService('profiler')->disable();
    }

    /**
     * @AfterScenario
     */
    public function resetLdapTestDriver()
    {
        /**
         *
         * @var $ldap_test_driver LdapTestDriver
         * @var $user User
         */
        $ldap_test_driver = $this->getSymfonyService('fr3d_ldap.ldap_driver');
        $ldap_test_driver->resetFixtures();
    }

    // //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // //////////////////////////////////////////// Steps

    /**
     * @Given /^there are notifications:$/
     */
    public function thereAreNotifications(TableNode $table)
    {
        /* @var $user_manager UserManager */
        $user_manager = $this->getUserManager();
        $em = $this->getManager();
        $nots = $table->getHash();
        for ($i = 0; $i < count($nots); ++$i) {
            $user = $user_manager->findOneBy(array(
                'username' => $nots[$i]['user']
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
     * @Given /^there are program download statistics:$/
     */
    public function thereAreProgramDownloadStatistics(TableNode $table)
    {
        $program_stats = $table->getHash();
        for ($i = 0; $i < count($program_stats); ++$i) {
            $program = $this->getProgramManger()->find($program_stats[$i]['program_id']);
            @$config = array(
                'downloaded_at' => $program_stats[$i]['downloaded_at'],
                'ip' => $program_stats[$i]['ip'],
                'latitude' => $program_stats[$i]['latitude'],
                'longitude' => $program_stats[$i]['longitude'],
                'country_code' => $program_stats[$i]['country_code'],
                'country_name' => $program_stats[$i]['country_name'],
                'street' => $program_stats[$i]['street'],
                'postal_code' => @$program_stats[$i]['postal_code'],
                'locality' => @$program_stats[$i]['locality'],
                'user_agent' => @$program_stats[$i]['user_agent'],
                'username' => @$program_stats[$i]['username'],
                'referrer' => @$program_stats[$i]['referrer'],
            );

            $this->insertProgramDownloadStatistics($program, $config);
        }
    }

    /**
     * @Given /^I am a valid admin$/
     */
    public function iAmAValidAdmin()
    {
        $this->getDefaultUser(array(
            "role" => "ROLE_ADMIN"
        ));
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
        assert(false, "Didn't find " . $recipient . ' in recipients.');
    }

    /**
     * @When /^I report program (\d+) with note "([^"]*)"$/
     */
    public function iReportProgramWithNote($program_id, $note)
    {
        $url = '/pocketcode/api/reportProgram/reportProgram.json';
        $parameters = array(
            'program' => $program_id,
            'note' => $note
        );
        $this->getClient()->request('POST', $url, $parameters);
    }

    /**
     * @Given /^I am a user with role "([^"]*)"$/
     */
    public function iAmAUserWithRole($role)
    {
        $this->insertUser(array(
            "role" => $role,
            "name" => "generatedBehatUser"
        ));

        $client = $this->getClient();
        $client->getCookieJar()->set(new Cookie(session_name(), true));

        $session = $client->getContainer()->get('session');

        $user = $this->getSymfonyService('fos_user.user_manager')->findUserByUsername("generatedBehatUser");
        $providerKey = $this->getSymfonyParameter('fos_user.firewall_name');

        $token = new UsernamePasswordToken($user, null, $providerKey, $user->getRoles());
        $session->set('_security_' . $providerKey, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);
    }

    /**
     * @Then /^the response should contain "([^"]*)"$/
     */
    public function theResponseShouldContain($needle)
    {
        if (strpos($this->getClient()
                ->getResponse()
                ->getContent(), $needle) === false
        )
            assert(false, $needle . " not found in the response ");
    }

    /**
     * @Then /^the response should contain the elements:$/
     */
    public function theResponseShouldContainTheElements(TableNode $table)
    {
        $program_stats = $table->getHash();
        for ($i = 0; $i < count($program_stats); ++$i) {
            $this->theResponseShouldContain($program_stats[$i]['id']);
            $this->theResponseShouldContain($program_stats[$i]['downloaded_at']);
            $this->theResponseShouldContain($program_stats[$i]['ip']);
            $this->theResponseShouldContain($program_stats[$i]['latitude']);
            $this->theResponseShouldContain($program_stats[$i]['longitude']);
            $this->theResponseShouldContain($program_stats[$i]['country_code']);
            $this->theResponseShouldContain($program_stats[$i]['country_name']);
            $this->theResponseShouldContain($program_stats[$i]['street']);
            $this->theResponseShouldContain($program_stats[$i]['postal_code']);
            $this->theResponseShouldContain($program_stats[$i]['locality']);
            $this->theResponseShouldContain($program_stats[$i]['user_agent']);
            $this->theResponseShouldContain($program_stats[$i]['user']);
            $this->theResponseShouldContain($program_stats[$i]['referrer']);
        }
    }

    /**
     * @Then /^the response should not contain "([^"]*)"$/
     */
    public function theResponseShouldNotContain($needle)
    {
        assertNotContains($needle, $this->getClient()
            ->getResponse()
            ->getContent());
    }

    /**
     * @Given /^there is a file "([^"]*)" with size "([^"]*)" bytes in the APK-folder$/
     */
    public function thereIsAFileWithSizeBytesInTheApkFolder($filename, $size)
    {
        $this->generateFileInPath($this->getSymfonyParameter("catrobat.apk.dir"), $filename, $size);
    }

    private function generateFileInPath($path, $filename, $size)
    {
        $full_filename = $path . "/" . $filename;
        $dirname = dirname($full_filename);
        if (!is_dir($dirname)) {
            mkdir($dirname, 0755, true);
        }
        $fp = fopen($full_filename, 'w'); // open in write mode.
        fseek($fp, $size - 1, SEEK_CUR); // seek to SIZE-1
        fwrite($fp, 'a'); // write a dummy char at SIZE position
        fclose($fp); // close the file.
    }

    /**
     * @Then /^program with id "([^"]*)" should have no apk$/
     */
    public function programWithIdShouldHaveNoApk($program_id)
    {
        $program_manager = $this->getProgramManger();
        $program = $program_manager->find($program_id);
        assertEquals(Program::APK_NONE, $program->getApkStatus());
    }

    /**
     * @Given /^there is a file "([^"]*)" with size "([^"]*)" bytes in the backup-folder$/
     */
    public function thereIsAFileWithSizeBytesInTheBackupFolder($filename, $size)
    {
        $this->generateFileInPath($this->getSymfonyParameter("catrobat.backup.dir"), $filename, $size);
    }

    /**
     * @Given /^there is a file "([^"]*)" with size "([^"]*)" bytes in the extracted-folder$/
     */
    public function thereIsAFileWithSizeBytesInTheExtractedFolder($filename, $size)
    {
        $this->generateFileInPath($this->getSymfonyParameter("catrobat.file.extract.dir"), $filename, $size);
    }

    /**
     * @Then /^program with id "([^"]*)" should have no directory_hash$/
     */
    public function programWithIdShouldHaveNoDirectoryHash($program_id)
    {
        $program_manager = $this->getProgramManger();
        $program = $program_manager->find($program_id);
        assertEquals('null', $program->getDirectoryHash());
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

    /**
     * @When /^I GET "([^"]*)"$/
     */
    public function iGet($arg1)
    {
        $this->getClient()->request('GET', $arg1);
    }

    /**
     * @When /^I POST login with user "([^"]*)" and password "([^"]*)"$/
     */
    public function iPostLoginUserWithPassword($uname, $pwd)
    {
        $csrfToken = $this->getSymfonyService('form.csrf_provider')->generateCsrfToken('authenticate');

        $session = $this->getClient()
            ->getContainer()
            ->get("session");
        $session->set('_csrf_token', $csrfToken);
        $session->set('something', $csrfToken);
        $session->save();
        $cookie = new Cookie($session->getName(), $session->getId());
        $this->getClient()
            ->getCookieJar()
            ->set($cookie);

        $this->iHaveAParameterWithValue("_username", $uname);
        $this->iHaveAParameterWithValue("_password", $pwd);
        $this->iHaveAParameterWithValue("_csrf_token", $csrfToken);
        $this->iPostTheseParametersTo("/login_check");
    }

    /**
     * @Given /^the LDAP server is not available$/
     */
    public function theLdapServerIsNotAvailable()
    {
        /**
         *
         * @var $ldap_test_driver LdapTestDriver
         */
        $ldap_test_driver = $this->getSymfonyService('fr3d_ldap.ldap_driver');
        $ldap_test_driver->setThrowExceptionOnSearch(true);
    }

    /**
     * @Then /^URI from "([^"]*)" should be "([^"]*)"$/
     */
    public function uriFromShouldBe($arg1, $arg2)
    {
        $link = $this->getClient()->getCrawler()->selectLink($arg1)->link();

        if (!strcmp($link->getUri(), $arg2))
            assert(false, "expected: " . $arg2 . "  get: " . $link->getURI());
    }
}
