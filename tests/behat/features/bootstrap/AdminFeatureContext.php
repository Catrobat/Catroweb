<?php

use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use App\Entity\Notification;
use App\Entity\UserManager;
use App\Catrobat\Services\TestEnv\SymfonySupport;
use App\Catrobat\Services\TestEnv\LdapTestDriver;
use Sonata\AdminBundle\Command\SetupAclCommand;
use Symfony\Bundle\SwiftmailerBundle\DataCollector\MessageDataCollector;
use Behat\Gherkin\Node\TableNode;
use App\Entity\User;
use App\Entity\Program;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpKernel\Profiler\Profile;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use PHPUnit\Framework\Assert;


/**
 * Feature context.
 */
class AdminFeatureContext extends MinkContext implements KernelAwareContext
{
  /**
   * @var KernelInterface
   */
  private $kernel;

  /**
   * @var SymfonySupport
   */
  private $symfony_support;

  /**
   * @var string|string[]|null
   */
  private $screenshots_directory;
  /**
   * @var bool
   */
  private $use_real_oauth_javascript_code = false;
  /**
   * @var array
   */
  private $headers = [];
  /**
   * @var array
   */
  private $request_parameters = [];
  /**
   * @var string
   */
  private $hostname = "localhost";
  /**
   * @var bool
   */
  private $secure = false;
  /**
   * @var array
   */
  private $files = [];

  const AVATAR_DIR = './tests/testdata/DataFixtures/AvatarImages/';
  const MEDIAPACKAGE_DIR = './tests/testdata/DataFixtures/MediaPackage/';
  const FIXTUREDIR = './tests/testdata/DataFixtures/';
  const ALREADY_IN_DB_USER = 'AlreadyinDB';

  /**
   * Initializes context with parameters from behat.yml.
   *
   * @param array $screenshots_directory
   *
   * @throws \Exception
   */
  public function __construct($screenshots_directory)
  {
    $this->screenshots_directory = preg_replace('/([^\/]+)$/', '$1/', $screenshots_directory);
    if (!is_dir($this->screenshots_directory))
    {
      throw new \Exception('No screenshot directory specified!');
    }
    $this->setOauthServiceParameter('0');
    $this->symfony_support = new SymfonySupport(self::FIXTUREDIR);
  }

  ///////////////////////////////////////////
  // region STUBS
  ///////////////////////////////////////////

  /**
   * Sets HttpKernel instance.
   * This method will be automatically called by Symfony2Extension ContextInitializer.
   *
   * @param KernelInterface $kernel
   */
  public function setKernel(KernelInterface $kernel)
  {
    $this->kernel = $kernel;
    $this->symfony_support->setKernel($kernel);
  }

  /**
   * @return string
   */
  public static function getAcceptedSnippetType()
  {
    return 'regex';
  }

  // endregion STUBS

  ///////////////////////////////////////////
  // region BEFORE SCENARIO
  ///////////////////////////////////////////

  /**
   * @BeforeScenario
   */
  public function setup()
  {
    $this->getSession()->resizeWindow(1240, 1024);
  }

  /**
   * @BeforeScenario @Mobile
   */
  public function resizeWindowMobile()
  {
    $this->getSession()->resizeWindow(320, 1000);
  }

  /**
   * @BeforeScenario @Tablet
   */
  public function resizeWindowTablet()
  {
    $this->getSession()->resizeWindow(768, 1000);
  }

  /**
   * @BeforeScenario @RealOAuth
   */
  public function activateRealOAuthService()
  {
    $this->setOauthServiceParameter('1');
    $this->use_real_oauth_javascript_code = true;
  }

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
   * @throws \Exception
   */
  public function initACL()
  {
    $acl_command = new SetupAclCommand();

    $acl_command->setContainer($this->getClient()->getContainer());
    $return = $acl_command->run(new ArrayInput([]), new NullOutput());
    assert($return !== 0, "Oh no!");
  }

  // endregion BEFORE SCENARIO

  ///////////////////////////////////////////
  // region AFTER SCENARIO
  ///////////////////////////////////////////

  /**
   * @AfterScenario @RealOAuth
   */
  public function deactivateRealOAuthService()
  {
    $this->setOauthServiceParameter('0');
    $this->use_real_oauth_javascript_code = false;
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
     * @var $user             User
     */
    $ldap_test_driver = $this->getSymfonyService('fr3d_ldap.ldap_driver');
    $ldap_test_driver->resetFixtures();
  }

  /**
   * @AfterScenario
   */
  public function resetSession()
  {
    $this->getSession()->getDriver()->reset();
    $this->getSession()->getDriver()->reset();
  }

  // endregion AFTER SCENARIO

  ///////////////////////////////////////////
  // region AFTER STEP
  ///////////////////////////////////////////

  /**
   * @AfterStep
   *
   * @param AfterStepScope $scope
   */
  public function makeScreenshot(AfterStepScope $scope)
  {
    if (!$scope->getTestResult()->isPassed())
    {
      $this->saveScreenshot(null, $this->screenshots_directory);
    }
  }

  // endregion AFTER STEP

  ///////////////////////////////////////////
  // region GETTER & SETTER
  ///////////////////////////////////////////

  /**
   * @param $value
   */
  private function setOauthServiceParameter($value)
  {
    $new_content = 'parameters:' . chr(10) . '    oauth_use_real_service: ' . $value;
    file_put_contents("config/packages/test/parameters.yml", $new_content);
  }

  /**
   * @return \Symfony\Bundle\FrameworkBundle\Client
   */
  public function getClient()
  {
    return $this->symfony_support->getClient();
  }

  /**
   * @param $param
   *
   * @return mixed
   */
  public function getSymfonyService($param)
  {
    return $this->symfony_support->getSymfonyService($param);
  }

  /**
   * @return \App\Entity\ProgramManager
   */
  public function getProgramManger()
  {
    return $this->symfony_support->getProgramManager();
  }

  /**
   *
   * @return \App\Entity\UserManager
   */
  public function getUserManager()
  {
    return $this->symfony_support->getUserManager();
  }

  /**
   *
   * @return \Doctrine\ORM\EntityManager
   */
  public function getManager()
  {
    return $this->symfony_support->getManager();
  }

  /**
   * @param $program
   * @param $config
   *
   * @return \App\Entity\ProgramDownloads
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function insertProgramDownloadStatistics($program, $config)
  {
    return $this->symfony_support->insertProgramDownloadStatistics($program, $config);
  }

  /**
   *
   * @return User
   */
  public function getDefaultUser()
  {
    return $this->symfony_support->getDefaultUser();
  }

  /**
   *
   * @return \Symfony\Component\HttpKernel\Profiler\Profiler
   */
  public function getSymfonyProfile()
  {
    return $this->symfony_support->getSymfonyProfile();
  }

  /**
   *
   * @param $param
   *
   * @return mixed
   */
  public function getSymfonyParameter($param)
  {
    return $this->symfony_support->getSymfonyParameter($param);
  }

  /**
   * @param array $config
   *
   * @return \FOS\UserBundle\Model\UserInterface|mixed
   */
  public function insertUser($config = [])
  {
    return $this->symfony_support->insertUser($config);
  }

  /**
   * @param $user
   * @param $config
   *
   * @return Program
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function insertProgram($user, $config)
  {
    return $this->symfony_support->insertProgram($user, $config);
  }

  /**
   * @param        $file
   * @param        $user
   * @param string $flavor
   * @param null   $request_parameters
   *
   * @return null|\Symfony\Component\HttpFoundation\Response
   */
  public function upload($file, $user, $flavor = 'pocketcode', $request_parameters = null)
  {
    return $this->symfony_support->upload($file, $user, $flavor, $request_parameters);
  }

  // endregion GETTER & SETTER

  ///////////////////////////////////////////
  // region STEPS
  ///////////////////////////////////////////

  /**
   * @Given /^I( [^"]*)? log in as "([^"]*)" with the password "([^"]*)"$/
   * @param $arg1
   * @param $arg2
   * @param $arg3
   */
  public function iAmLoggedInAsAsWithThePassword($arg1, $arg2, $arg3)
  {
    $this->visitPath('/pocketcode/login');
    $this->fillField('username', $arg2);
    $this->fillField('password', $arg3);
    $this->pressButton('Login');
    if ($arg1 === 'try to')
    {
      $this->assertPageNotContainsText('Your password or username was incorrect.');
    }
  }

  /**
   * @When /^I logout$/
   */
  public function iLogout()
  {
    $this->assertElementOnPage("#btn-logout");
    $this->getSession()->getPage()->find("css", "#btn-logout")->click();

  }

  /**
   * @Given /^there are admins:$/
   * @param TableNode $table
   */
  public function thereAreAdmins(TableNode $table)
  {
    /**
     * @var $user_manager UserManager
     * @var $user         User
     */
    $user_manager = $this->kernel->getContainer()->get('usermanager');
    $users = $table->getHash();
    $user = null;
    $count = count($users);
    for ($i = 0; $i < $count; ++$i)
    {
      $user = $user_manager->createUser();
      $user->setUsername($users[$i]['name']);
      $user->setEmail($users[$i]['email']);
      $user->setAdditionalEmail('');
      $user->setPlainPassword($users[$i]['password']);
      $user->setEnabled(true);
      $user->setUploadToken($users[$i]['token']);
      $user->setCountry('at');
      $user->setSuperAdmin(true);
      $user_manager->updateUser($user, false);
    }
    $user_manager->updateUser($user, true);
  }

  /**
   * @Given /^there are notifications:$/
   * @param TableNode $table
   *
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function thereAreNotifications(TableNode $table)
  {
    /* @var $user_manager UserManager */
    $user_manager = $this->getUserManager();
    $em = $this->getManager();
    $nots = $table->getHash();
    foreach ($nots as $not)
    {
      /**
       * @var User $user
       */
      $user = $user_manager->findOneBy([
        'username' => $not['user'],
      ]);
      $notification = new Notification();
      $notification->setUser($user);
      $notification->setReport($not['report']);
      $notification->setSummary($not['summary']);
      $notification->setUpload($not['upload']);
      $em->persist($notification);
    }
    $em->flush();
  }

  /**
   * @Given /^there are program download statistics:$/
   * @param TableNode $table
   *
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function thereAreProgramDownloadStatistics(TableNode $table)
  {
    $program_stats = $table->getHash();
    foreach ($program_stats as $program_stat)
    {
      $program = $this->getProgramManger()->find($program_stat['program_id']);
      $config = [
        'downloaded_at' => $program_stat['downloaded_at'],
        'ip'            => $program_stat['ip'],
        'country_code'  => $program_stat['country_code'],
        'country_name'  => $program_stat['country_name'],
        'user_agent'    => @$program_stat['user_agent'],
        'username'      => @$program_stat['username'],
        'referrer'      => @$program_stat['referrer'],
      ];
      $this->insertProgramDownloadStatistics($program, $config);
    }
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
   * @param $email_amount
   */
  public function iShouldSeeOutgoingEmailsInTheProfiler($email_amount)
  {
    /**
     * @var Profile              $profile
     * @var MessageDataCollector $collector
     */
    $profile = $this->getSymfonyProfile();
    $collector = $profile->getCollector('swiftmailer');
    Assert::assertEquals($email_amount, $collector->getMessageCount());
  }

  /**
   * @Then /^I should see a email with recipient "([^"]*)"$/
   * @param $recipient
   */
  public function iShouldSeeAEmailWithRecipient($recipient)
  {
    /**
     * @var Profile              $profile
     * @var MessageDataCollector $collector
     * @var \Swift_Message       $message
     */
    $profile = $this->getSymfonyProfile();
    $collector = $profile->getCollector('swiftmailer');
    foreach ($collector->getMessages() as $message)
    {
      if ($recipient === array_keys($message->getTo())[0])
      {
        return;
      }
    }
    assert(false, "Didn't find " . $recipient . ' in recipients.');
  }

  /**
   * @When /^I report program (\d+) with category "([^"]*)" and note "([^"]*)"$/
   * @param $program_id
   * @param $category
   * @param $note
   */
  public function iReportProgramWithNote($program_id, $category, $note)
  {
    $url = '/pocketcode/api/reportProgram/reportProgram.json';
    $parameters = [
      'program'  => $program_id,
      'category' => $category,
      'note'     => $note,
    ];
    $this->getClient()->request('POST', $url, $parameters);
  }

  /**
   * @Given /^I am a user with role "([^"]*)"$/
   * @param $role
   */
  public function iAmAUserWithRole($role)
  {
    $this->insertUser([
      "role" => $role,
      "name" => "generatedBehatUser",
    ]);

    $client = $this->getClient();
    $client->getCookieJar()->set(new Cookie(session_name(), true));

    $session = $client->getContainer()->get('session');

    /**
     * @var User $user
     */
    $user = $this->getSymfonyService('fos_user.user_manager')
      ->findUserByUsername("generatedBehatUser");
    $providerKey = $this->getSymfonyParameter('fos_user.firewall_name');

    $token = new UsernamePasswordToken($user, null, $providerKey, $user->getRoles());
    $session->set('_security_' . $providerKey, serialize($token));
    $session->save();

    $cookie = new Cookie($session->getName(), $session->getId());
    $client->getCookieJar()->set($cookie);
  }

  /**
   * @Then /^the client response should contain "([^"]*)"$/
   * @param $needle
   */
  public function theResponseShouldContain($needle)
  {
    if (strpos($this->getClient()
        ->getResponse()
        ->getContent(), $needle) === false
    )
    {
      assert(false, $needle . " not found in the response ");
    }
  }

  /**
   * @Then /^the client response should contain the elements:$/
   * @param TableNode $table
   */
  public function theResponseShouldContainTheElements(TableNode $table)
  {
    $program_stats = $table->getHash();
    foreach ($program_stats as $program_stat)
    {
      $this->theResponseShouldContain($program_stat['id']);
      $this->theResponseShouldContain($program_stat['downloaded_at']);
      $this->theResponseShouldContain($program_stat['ip']);
      $this->theResponseShouldContain($program_stat['country_code']);
      $this->theResponseShouldContain($program_stat['country_name']);
      $this->theResponseShouldContain($program_stat['user_agent']);
      $this->theResponseShouldContain($program_stat['user']);
      $this->theResponseShouldContain($program_stat['referrer']);
    }
  }

  /**
   * @Then /^the client response should not contain "([^"]*)"$/
   * @param $needle
   */
  public function theResponseShouldNotContain($needle)
  {
    Assert::assertNotContains($needle, $this->getClient()->getResponse()->getContent());
  }

  /**
   * @Given /^there is a file "([^"]*)" with size "([^"]*)" bytes in the APK-folder$/
   * @param $filename
   * @param $size
   */
  public function thereIsAFileWithSizeBytesInTheApkFolder($filename, $size)
  {
    $this->generateFileInPath($this->getSymfonyParameter("catrobat.apk.dir"), $filename, $size);
  }

  /**
   * @param $path
   * @param $filename
   * @param $size
   */
  private function generateFileInPath($path, $filename, $size)
  {
    $full_filename = $path . "/" . $filename;
    $dirname = dirname($full_filename);
    if (!is_dir($dirname))
    {
      mkdir($dirname, 0755, true);
    }
    $file_path = fopen($full_filename, 'w'); // open in write mode.
    fseek($file_path, $size - 1, SEEK_CUR); // seek to SIZE-1
    fwrite($file_path, 'a'); // write a dummy char at SIZE position
    fclose($file_path); // close the file.
  }

  /**
   * @Then /^program with id "([^"]*)" should have no apk$/
   * @param $program_id
   */
  public function programWithIdShouldHaveNoApk($program_id)
  {
    $program_manager = $this->getProgramManger();
    $program = $program_manager->find($program_id);
    Assert::assertEquals(Program::APK_NONE, $program->getApkStatus());
  }

  /**
   * @Given /^there is a file "([^"]*)" with size "([^"]*)" bytes in the backup-folder$/
   * @param $filename
   * @param $size
   */
  public function thereIsAFileWithSizeBytesInTheBackupFolder($filename, $size)
  {
    $this->generateFileInPath($this->getSymfonyParameter("catrobat.backup.dir"),
      $filename, $size);
  }

  /**
   * @Given /^there is a file "([^"]*)" with size "([^"]*)" bytes in the extracted-folder$/
   * @param $filename
   * @param $size
   */
  public function thereIsAFileWithSizeBytesInTheExtractedFolder($filename, $size)
  {
    $this->generateFileInPath($this->getSymfonyParameter("catrobat.file.extract.dir"),
      $filename, $size);
  }

  /**
   * @Given /^there is no file in the backup-folder$/
   */
  public function thereIsNoFileInTheBackupFolder()
  {
    $backupDirectory = $this->getSymfonyParameter("catrobat.backup.dir");

    $files = glob($backupDirectory . '/*');
    foreach ($files as $file)
    {
      $ext = pathinfo($file, PATHINFO_EXTENSION);
      if (($ext === "zip" || $ext === "tar") && is_file($file))
      {
        unlink($file);
      }
    }
  }

  /**
   * @Then /^program with id "([^"]*)" should have no directory_hash$/
   * @param $program_id
   */
  public function programWithIdShouldHaveNoDirectoryHash($program_id)
  {
    $program_manager = $this->getProgramManger();
    $program = $program_manager->find($program_id);
    Assert::assertEquals('null', $program->getDirectoryHash());
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
   * @param $arg1
   */
  public function iGet($arg1)
  {
    $this->getClient()->request('GET', $arg1);
  }

  /**
   * @Given /^I have a parameter "([^"]*)" with value "([^"]*)"$/
   * @param $name
   * @param $value
   */
  public function iHaveAParameterWithValue($name, $value)
  {
    $this->request_parameters[$name] = $value;
  }

  /**
   * @When /^I POST these parameters to "([^"]*)"$/
   * @param $url
   */
  public function iPostTheseParametersTo($url)
  {
    $this->getClient()->request('POST', $url, $this->request_parameters, $this->files, [
      'HTTP_HOST' => $this->hostname,
      'HTTPS'     => $this->secure,
    ]);
  }

  /**
   * @When /^I POST login with user "([^"]*)" and password "([^"]*)"$/
   * @param $uname
   * @param $pwd
   */
  public function iPostLoginUserWithPassword($uname, $pwd)
  {
    $csrfToken = $this->getSymfonyService('security.csrf.token_manager')
      ->getToken('authenticate')->getValue();

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
   * @param $arg1
   * @param $arg2
   */
  public function uriFromShouldBe($arg1, $arg2)
  {
    $link = $this->getClient()->getCrawler()->selectLink($arg1)->link();

    if (!strcmp($link->getUri(), $arg2))
    {
      assert(false, "expected: " . $arg2 . "  get: " . $link->getURI());
    }
  }

  /**
   * @Then /^the response Header should contain the key "([^"]*)" with the value '([^']*)'$/
   * @param $headerKey
   * @param $headerValue
   */
  public function theResponseHeadershouldContainTheKeyWithTheValue($headerKey, $headerValue)
  {
    $headers = $this->getClient()->getResponse()->headers;
    Assert::assertEquals($headerValue, $headers->get($headerKey),
      "expected: " . $headerKey . ": " . $headerValue .
      "\nget: " . $headerKey . ": " . $headers->get($headerKey));
  }

  /**
   * @Given /^I am a valid user$/
   */
  public function iAmAValidUser()
  {
    $this->insertUser([
      'name'     => 'BehatGeneratedName',
      'token'    => 'BehatGeneratedToken',
      'password' => 'BehatGeneratedPassword',
    ]);
  }

  /**
   * @Given /^the response code should be "([^"]*)"$/
   * @param $code
   */
  public function theResponseCodeShouldBe($code)
  {
    $response = $this->getClient()->getResponse();
    Assert::assertEquals($code, $response->getStatusCode(),
      'Wrong response code. ' . $response->getContent());
  }

  /**
   * @Given /^there are users:$/
   * @param TableNode $table
   */
  public function thereAreUsers(TableNode $table)
  {
    $users = $table->getHash();
    foreach ($users as $user)
    {
      $this->insertUser(@[
        'name'     => $user['name'],
        'email'    => $user['email'],
        'token'    => isset($user['token']) ? $user['token'] : "",
        'password' => isset($user['password']) ? $user['password'] : "",
      ]);
    }
  }

  /**
   * @Given /^there are LDAP-users:$/
   * @param TableNode $table
   */
  public function thereAreLdapUsers(TableNode $table)
  {
    /**
     *
     * @var $ldap_test_driver LdapTestDriver
     */
    $ldap_test_driver = $this->getSymfonyService('fr3d_ldap.ldap_driver');
    $users = $table->getHash();
    $ldap_test_driver->resetFixtures();

    foreach ($users as $user)
    {
      $username = $user['name'];
      $pwd = $user['password'];
      $groups = array_key_exists("groups", $user) ? explode(",", $user["groups"]) : [];
      $ldap_test_driver->addTestUser($username, $pwd, $groups,
        isset($user['email']) ? $user['email'] : null);
    }
  }

  /**
   * @Given /^the element "([^"]*)" should be visible$/
   * @param $element
   */
  public function theElementShouldBeVisible($element)
  {
    $element = $this->getSession()->getPage()->find('css', $element);
    Assert::assertNotNull($element);
    Assert::assertTrue($element->isVisible());
  }

  /**
   * @Given /^there are programs:$/
   *
   * @param TableNode $table
   *
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function thereArePrograms(TableNode $table)
  {
    $programs = $table->getHash();
    foreach ($programs as $program)
    {
      $user = $this->getUserManager()->findOneBy([
        'username' => isset($program['owned by']) ? $program['owned by'] : "",
      ]);
      @$config = [
        'name'                => $program['name'],
        'description'         => $program['description'],
        'views'               => $program['views'],
        'downloads'           => $program['downloads'],
        'uploadtime'          => $program['upload time'],
        'apk_status'          => $program['apk_status'],
        'catrobatversionname' => $program['version'],
        'directory_hash'      => $program['directory_hash'],
        'filesize'            => @$program['FileSize'],
        'visible'             => filter_var($program["visible"], FILTER_VALIDATE_BOOLEAN),
        'approved'            => (isset($program['approved_by_user']) &&
          $program['approved_by_user'] === '') ? null : true,
        'tags'                => isset($program['tags_id']) ? $program['tags_id'] : null,
        'extensions'          => isset($program['extensions']) ? $program['extensions'] : null,
        'remix_root'          => filter_var($program["remix_root"], FILTER_VALIDATE_BOOLEAN),
      ];
      $this->insertProgram($user, $config);
    }
  }

  /**
   * @When /^I upload a program with (.*)$/
   * @param $program_attribute
   *
   * @throws PendingException
   */
  public function iUploadAProgramWith($program_attribute)
  {
    switch ($program_attribute)
    {
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
        throw new PendingException('No case defined for "' . $program_attribute . '"');
    }
    $this->upload(self::FIXTUREDIR . 'GeneratedFixtures/' . $filename, null);
  }

  /**
   * @When /^I click "([^"]*)"$/
   * @param $arg1
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   */
  public function iClick($arg1)
  {
    $arg1 = trim($arg1);

    $this->assertSession()->elementExists('css', $arg1);

    $this
      ->getSession()
      ->getPage()
      ->find('css', $arg1)
      ->click();
  }

  // endregion STEPS

}
