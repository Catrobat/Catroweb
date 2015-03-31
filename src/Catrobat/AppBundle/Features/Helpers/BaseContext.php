<?php

namespace Catrobat\AppBundle\Features\Helpers;

use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Behat\Tester\Exception\PendingException;
use Catrobat\AppBundle\Entity\RudeWord;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\KernelInterface;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Catrobat\AppBundle\Entity\User;
use Catrobat\AppBundle\Entity\Program;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Behat\Behat\Context\CustomSnippetAcceptingContext;
use Catrobat\AppBundle\Services\TokenGenerator;
use Catrobat\AppBundle\Services\CatrobatFileCompressor;
use Catrobat\AppBundle\Entity\FeaturedProgram;
use Catrobat\AppBundle\Model\ProgramManager;

require_once 'PHPUnit/Framework/Assert/Functions.php';

/**
 * Feature context.
 */
class BaseContext implements KernelAwareContext, CustomSnippetAcceptingContext
{
  const FIXTUREDIR = "./testdata/DataFixtures/";

  private $kernel;
  private $client;
  private $test_user_count = 0;
  private $default_user;
  private $error_directory;

  public function __construct()
  {

  }

  public static function getAcceptedSnippetType()
  {
    return 'regex';
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
////////////////////////////////////////////// Getter & Setter

  /**
   * @return \Symfony\Bundle\FrameworkBundle\Client
   */
  public function getClient()
  {
    if($this->client == null)
      $this->client = $this->kernel->getContainer()->get('test.client');
    return $this->client;
  }

  /**
   * @return \Catrobat\AppBundle\Model\UserManager
   */
  public function getUserManager()
  {
    return $this->kernel->getContainer()->get('usermanager');
  }

  /**
   * @return \Catrobat\AppBundle\Model\ProgramManager
   */
  public function getProgramManger()
  {
    return $this->kernel->getContainer()->get('programmanager');
  }

  /**
   * @return \Catrobat\AppBundle\Services\ProgramFileRepository
   */
  public function getFileRepository()
  {
    return $this->kernel->getContainer()->get('filerepository');
  }

  /**
   * @return \Doctrine\ORM\EntityManager
   */
  public function getManager()
  {
    return $this->kernel->getContainer()->get('doctrine')->getManager();
  }

  /**
   * @return mixed
   */
  public function getSymfonyParameter($param)
  {
    return $this->kernel->getContainer()->getParameter($param);
  }

  /**
   * @return mixed
   */
  public function getSymfonyService($param)
  {
    return $this->kernel->getContainer()->get($param);
  }

  /**
   * @return \Symfony\Component\HttpKernel\Profiler\Profiler
   */
  public function getSymfonyProfile()
  {
    $profile = $this->getClient()->getProfile();
    if (false === $profile) {
      throw new \RuntimeException(
        'The profiler is disabled. Activate it by setting '.
        'framework.profiler.only_exceptions to false in '.
        'your config'
      );
    }

    return $profile;
  }

  /**
   * @return \Catrobat\AppBundle\Entity\User
   */
  public function getDefaultUser()
  {
    if($this->default_user == null)
      $this->default_user = $this->insertUser();

    return $this->default_user;
  }


  public function setErrorDirectory($dir)
  {
    $this->error_directory = $dir;
  }

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////// HOOKS

  /** @BeforeScenario */
  public function clearDefaultUser()
  {
    $this->default_user = null;
  }

  /** @BeforeScenario */
  public function emptyStorage()
  {
    $this->emptyDirectory($this->getSymfonyParameter("catrobat.file.extract.dir"));
    $this->emptyDirectory($this->getSymfonyParameter("catrobat.file.storage.dir"));
    $this->emptyDirectory($this->getSymfonyParameter("catrobat.screenshot.dir"));
    $this->emptyDirectory($this->getSymfonyParameter("catrobat.thumbnail.dir"));
    $this->emptyDirectory($this->getSymfonyParameter("catrobat.featuredimage.dir"));
  }

  /** @AfterStep */
  public function saveResponseToFile(AfterStepScope $scope)
  {
    if($this->error_directory == null)
      return;

    if (!$scope->getTestResult()->isPassed() && $this->getClient() != null)
    {
      $response = $this->getClient()->getResponse();
      if ($response != null && $response->getContent() != "")
      {
        file_put_contents($this->error_directory . "error.json", $response->getContent());
      }
    }
  }


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////// Support Functions

  public function emptyDirectory($directory)
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

  public function insertUser($config = array())
  {
    $this->test_user_count++;
    $user_manager = $this->getUserManager();
    $user = $user_manager->createUser();
    @$user->setUsername($config['name'] ?: 'GeneratedUser' . $this->test_user_count);
    @$user->setEmail($config['email'] ?: "default" . $this->test_user_count . "@pocketcode.org");
    @$user->setPlainPassword($config['password'] ?: "GeneratedPassword");
    @$user->setEnabled($config['enabled'] ?: true);
    @$user->setUploadToken($config['token'] ?: "GeneratedToken");
    @$user->setCountry($config['country'] ?: "at");
    @$user_manager->updateUser($user, true);

    return $user;
  }

  public function insertProgram($user, $config)
  {
    if($user == null)
      $user = $this->getDefaultUser();

    $em = $this->getManager();
    $program = new Program();
    $program->setUser($user);
    $program->setName($config['name'] ?: "Generated program");
    $program->setDescription(isset($config['description']) ? $config['description'] : "Generated");
    $program->setViews(isset($config['views']) ? $config['views'] : 1);
    $program->setDownloads(isset($config['downloads']) ? $config['downloads'] : 1);
    $program->setUploadedAt(isset($config['uploadtime']) ? new \DateTime($config['uploadtime'], new \DateTimeZone('UTC')) : new \DateTime());
    $program->setCatrobatVersion(isset($config['catrobatversion']) ? $config['catrobatversion'] : 1);
    $program->setCatrobatVersionName(isset($config['catrobatversionname']) ? $config['catrobatversionname'] : "0.9.1");
    $program->setLanguageVersion(isset($config['languageversion']) ? $config['languageversion'] : 1);
    $program->setUploadIp("127.0.0.1");
    $program->setRemixCount(0);
    $program->setFilesize(isset($config['filesize']) ? $config['filesize'] : 0);
    $program->setVisible(isset($config['visible']) ? boolval($config['visible']) : true);
    $program->setUploadLanguage("en");
    $program->setApproved(isset($config['approved']) ? $config['approved'] : true);
    $program->setFlavor(isset($config['flavor']) ? $config['flavor'] : "pocketcode");
    $program->setApkStatus(isset($config['apk_status']) ? $config['apk_status'] : Program::APK_NONE);
    $em->persist($program);
    $em->flush();

    return $program;
  }

  public function generateProgramFileWith($parameters)
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

  public function upload($file, $user)
  {
    if($user == null)
      $user = $this->getDefaultUser();

    if(is_string($file))
      $file = new UploadedFile($file, 'uploadedFile');

    $parameters =  array();
    $parameters["username"] = $user->getUsername();
    $parameters["token"] = $user->getUploadToken();
    $parameters["fileChecksum"] = md5_file($file->getPathname());
    $client = $this->getClient();
    $client->request('POST', "/api/upload/upload.json", $parameters, array($file));
    $response = $client->getResponse();
    return $response;
  }

  protected function getTempCopy($path)
  {
    $temppath = tempnam(sys_get_temp_dir(), "apktest");
    copy($path, $temppath);
    return $temppath;
  }
  
  
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
    
}
