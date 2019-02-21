<?php

namespace Catrobat\AppBundle\Features\Ci\Context;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Catrobat\AppBundle\Entity\Program;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Finder\Finder;
use Catrobat\AppBundle\Features\Helpers\BaseContext;
use PHPUnit\Framework\Assert;


/**
 * Feature context.
 */
class FeatureContext extends BaseContext
{

  /**
   * @var string
   */
  private $hostname;

  /**
   * @var bool
   */
  private $secure;

  /**
   * FeatureContext constructor.
   *
   * @param $error_directory
   */
  public function __construct($error_directory)
  {
    parent::__construct();
    $this->setErrorDirectory($error_directory);
    $this->hostname = 'localhost';
    $this->secure = false;
  }

  // ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // //////////////////////////////////////////// Hooks

  /**
   * @BeforeScenario
   */
  public function emptyUploadFolder()
  {
    $extract_dir = $this->getSymfonyParameter('catrobat.apk.dir');
    $this->emptyDirectory($extract_dir);
  }

  // ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // //////////////////////////////////////////// Support Functions
  /**
   * @return UploadedFile
   */
  private function getStandardProgramFile()
  {
    $filepath = self::FIXTUREDIR . 'test.catrobat';
    Assert::assertTrue(file_exists($filepath), 'File not found');

    return new UploadedFile($filepath, 'test.catrobat');
  }

  // ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // //////////////////////////////////////////// Steps

  /**
   * @Given /^the server name is "([^"]*)"$/
   *
   * @param $arg1
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
   *
   * @param $arg1
   */
  public function theTokenToUploadAnApkFileIs($arg1)
  {
    // Defined in config_test.yml
  }

  /**
   * @Given /^I have a program "([^"]*)" with id "([^"]*)"$/
   * @param $arg1
   * @param $arg2
   *
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function iHaveAProgramWithId($arg1, $arg2)
  {
    $config = [
      'name' => $arg1,
    ];
    $program = $this->insertProgram(null, $config);

    $file_repo = $this->getFileRepository();

    $file_repo->saveProgramfile(new File(self::FIXTUREDIR . 'test.catrobat'), $program->getId());
  }

  /**
   * @Given /^I have a program "([^"]*)" with id "([^"]*)" and a vibrator brick$/
   *
   * @param $arg1
   * @param $arg2
   *
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function iHaveAProgramWithIdAndAVibratorBrick($arg1, $arg2)
  {
    $config = [
      'name' => $arg1,
    ];
    $program = $this->insertProgram(null, $config);

    $file_repo = $this->getFileRepository();

    $file_repo->saveProgramfile(new File(self::FIXTUREDIR . '/GeneratedFixtures/phiro.catrobat'), $program->getId());
  }

  /**
   * @Given /^the jenkins job id is "([^"]*)"$/
   *
   * @param $arg1
   */
  public function theJenkinsJobIdIs($arg1)
  {
    // Defined in config_test.yml
  }

  /**
   * @Given /^the jenkins token is "([^"]*)"$/
   *
   * @param $arg1
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
    $client = $this->getClient();
    $client->request('GET', 'http://' . $this->hostname . '/pocketcode/ci/build/1', [], [], [
      'HTTP_HOST' => $this->hostname,
      'HTTPS'     => $this->secure,
    ]);
    $response = $client->getResponse();
    Assert::assertEquals(200, $response->getStatusCode(), 'Wrong response code. ' . $response->getContent());
  }

  /**
   * @Then /^following parameters are sent to jenkins:$/
   *
   * @param TableNode $table
   */
  public function followingParametersAreSentToJenkins(TableNode $table)
  {
    $parameter_defs = $table->getHash();
    $expected_parameters = [];
    for ($i = 0; $i < count($parameter_defs); ++$i)
    {
      $expected_parameters[$parameter_defs[$i]['parameter']] = $parameter_defs[$i]['value'];
    }
    $dispatcher = $this->getSymfonyService('ci.jenkins.dispatcher');
    $parameters = $dispatcher->getLastParameters();
    Assert::assertEquals($expected_parameters, $parameters);
  }

  /**
   * @Then /^the program apk status will.* be flagged "([^"]*)"$/
   *
   * @param $arg1
   */
  public function theProgramApkStatusWillBeFlagged($arg1)
  {
    $pm = $this->getProgramManger();
    /* @var $program \Catrobat\AppBundle\Entity\Program */
    $program = $pm->find(1);
    switch ($arg1)
    {
      case 'pending':
        Assert::assertEquals(Program::APK_PENDING, $program->getApkStatus());
        break;
      case 'ready':
        Assert::assertEquals(Program::APK_READY, $program->getApkStatus());
        break;
      case 'none':
        Assert::assertEquals(Program::APK_NONE, $program->getApkStatus());
        break;
      default:
        throw new PendingException('Unknown state: ' . $arg1);
    }
  }

  /**
   * @Given /^I requested jenkins to build it$/
   */
  public function iRequestedJenkinsToBuildIt()
  {
    //
  }

  /**
   * @When /^jenkins uploads the apk file to the given upload url$/
   */
  public function jenkinsUploadsTheApkFileToTheGivenUploadUrl()
  {
    $filepath = self::FIXTUREDIR . '/test.catrobat';
    Assert::assertTrue(file_exists($filepath), 'File not found');
    $temppath = $this->getTempCopy($filepath);
    $files = [
      new UploadedFile($temppath, 'test.apk'),
    ];
    $url = '/pocketcode/ci/upload/1?token=UPLOADTOKEN';
    $parameters = [];
    $this->getClient()->request('POST', $url, $parameters, $files);
  }

  /**
   * @Then /^it will be stored on the server$/
   */
  public function itWillBeStoredOnTheServer()
  {
    $directory = $this->getSymfonyParameter('catrobat.apk.dir');
    $finder = new Finder();
    $finder->in($directory)->depth(0);
    Assert::assertEquals(1, $finder->count());
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
    for ($i = 0; $i < count($programs); ++$i)
    {
      $apk_status = Program::APK_NONE;
      if ($programs[$i]['apk status'] === 'ready')
      {
        $apk_status = Program::APK_READY;
      }
      elseif ($programs[$i]['apk status'] === 'pending')
      {
        $apk_status = Program::APK_PENDING;
      }
      elseif ($programs[$i]['apk status'] === 'none')
      {
        $apk_status = Program::APK_NONE;
      }

      $config = [
        'name'       => $programs[$i]['name'],
        'visible'    => ($programs[$i]['visible'] === 'true'),
        'apk_status' => $apk_status,
      ];
      $this->insertProgram(null, $config);

      if ($programs[$i]['apk status'] == 'ready')
      {
        /* @var $apkrepository \Catrobat\AppBundle\Services\ApkRepository */
        $apkrepository = $this->getSymfonyService('apkrepository');
        $apkrepository->save(new File($this->getTempCopy(self::FIXTUREDIR . '/test.catrobat')), $i);
      }
    }
  }

  /**
   * @When /^I want to download the apk file of "([^"]*)"$/
   *
   * @param $arg1
   */
  public function iWantToDownloadTheApkFileOf($arg1)
  {
    $pm = $this->getProgramManger();
    $program = $pm->findOneByName($arg1);
    if ($program === null)
    {
      throw new PendingException('Program not found: ' . $arg1);
    }
    $router = $this->getSymfonyService('router');
    $url = $router->generate('ci_download', [
      'id'     => $program->getId(),
      'flavor' => 'pocketcode',
    ]);

    $this->getClient()->request('GET', $url, [], []);
  }

  /**
   * @Then /^I should receive the apk file$/
   */
  public function iShouldReceiveTheApkFile()
  {
    $content_type = $this->getClient()->getResponse()->headers->get('Content-Type');
    $code = $this->getClient()
      ->getResponse()
      ->getStatusCode();
    Assert::assertEquals(200, $code);
    Assert::assertEquals('application/vnd.android.package-archive', $content_type);
  }

  /**
   * @Then /^the apk file will not be found$/
   */
  public function theApkFileWillNotBeFound()
  {
    $code = $this->getClient()
      ->getResponse()
      ->getStatusCode();
    Assert::assertEquals(404, $code);
  }

  /**
   * @Given /^the program apk status is flagged "([^"]*)"$/
   *
   * @param $arg1
   *
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function theProgramApkStatusIsFlagged($arg1)
  {
    $pm = $this->getProgramManger();
    /* @var $program \Catrobat\AppBundle\Entity\Program */
    $program = $pm->find(1);
    switch ($arg1)
    {
      case 'pending':
        $program->setApkStatus(Program::APK_PENDING);
        break;
      case 'ready':
        $program->setApkStatus(Program::APK_READY);
        /* @var $apkrepository \Catrobat\AppBundle\Services\ApkRepository */
        $apkrepository = $this->getSymfonyService('apkrepository');
        $apkrepository->save(new File($this->getTempCopy(self::FIXTUREDIR . '/test.catrobat')), $program->getId());
        break;
      default:
        $program->setApkStatus(Program::APK_NONE);
    }
    $pm->save($program);
  }

  /**
   * @Then /^no build request will be sent to jenkins$/
   */
  public function noBuildRequestWillBeSentToJenkins()
  {
    $dispatcher = $this->getSymfonyService('ci.jenkins.dispatcher');
    $parameters = $dispatcher->getLastParameters();
    Assert::assertNull($parameters);
  }

  /**
   * @When /^I report a build error$/
   */
  public function iReportABuildError()
  {
    $url = '/pocketcode/ci/failed/1?token=UPLOADTOKEN';
    $this->getClient()->request('GET', $url);
    Assert::assertEquals(200, $this->getClient()
      ->getResponse()
      ->getStatusCode());
  }

  /**
   * @When /^I update this program$/
   */
  public function iUpdateThisProgram()
  {
    $pm = $this->getProgramManger();
    $program = $pm->find(1);
    if ($program === null)
    {
      throw new PendingException('last program not found');
    }
    $file = $this->generateProgramFileWith([
      'name' => $program->getName(),
    ]);
    $this->upload($file, null);
  }

  /**
   * @Then /^the apk file will be deleted$/
   */
  public function theApkFileWillBeDeleted()
  {
    $directory = $this->getSymfonyParameter('catrobat.apk.dir');
    $finder = new Finder();
    $finder->in($directory)->depth(0);
    Assert::assertEquals(0, $finder->count());
  }

  /**
   * @When /^I GET "([^"]*)"$/
   *
   * @param $uri
   */
  public function iGet($uri)
  {
    $this->getClient()->request('GET', $uri);
  }

  /**
   * @Then /^will get the following JSON:$/
   *
   * @param PyStringNode $string
   */
  public function willGetTheFollowingJson(PyStringNode $string)
  {
    $response = $this->getClient()->getResponse();
    Assert::assertEquals(200, $response->getStatusCode());
    Assert::assertJsonStringEqualsJsonString($string->getRaw(), $response->getContent());
  }
}
