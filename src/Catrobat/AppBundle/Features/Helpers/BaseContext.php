<?php

namespace Catrobat\AppBundle\Features\Helpers;

use Behat\Behat\Tester\Exception\PendingException;
use Catrobat\AppBundle\Entity\RudeWord;
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
    return $this->kernel->getContainer()->get('test.client');
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
   * @return \Doctrine\ORM\EntityManager
   */
  public function getManager()
  {
    return $this->kernel->getContainer()->get('doctrine')->getManager();
  }




////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////// HOOKS






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
    $user_manager = $this->getUserManager();
    $user = $user_manager->createUser();
    @$user->setUsername($config['name'] ?: 'GeneratedUser');
    @$user->setEmail($config['email'] ?: "dev@pocketcode.org");
    @$user->setPlainPassword($config['password'] ?: "GeneratedPassword");
    @$user->setEnabled($config['enabled'] ?: true);
    @$user->setUploadToken($config['token'] ?: "GeneratedToken");
    @$user->setCountry($config['country'] ?: "at");
    @$user_manager->updateUser($user, true);
    return $user;
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

  public function insertProgram($user, $config)
  {
    $em = $this->getManager();
    $program = new Program();
    $program->setUser($user);
    $program->setName($config['name'] ?: "Generated program");
    $program->setDescription($config['description'] ?: "Generated");
    $program->setFilename("file.catrobat");
    $program->setThumbnail("thumb.png");
    $program->setScreenshot("screenshot.png");
    $program->setViews($config['views'] ?: 1);
    $program->setDownloads($config['downloads'] ?: 1);
    $program->setUploadedAt($config['uploadtime'] ? new \DateTime($config['uploadtime'], new \DateTimeZone('UTC')) : new \DateTime());
    $program->setCatrobatVersion($config['catrobatversion'] ?: 1);
    $program->setCatrobatVersionName($config['catrobatversionname'] ?: "0.9.1");
    $program->setLanguageVersion($config['languageversion'] ?: 1);
    $program->setUploadIp("127.0.0.1");
    $program->setRemixCount(0);
    $program->setFilesize(0);
    $program->setVisible($config['visible'] ?: true);
    $program->setUploadLanguage("en");
    $program->setApproved($config['approve'] ?: true);
    $program->setApkStatus($config['apkstatus'] ?: Program::APK_NONE);
    $em->persist($program);
    $em->flush();
  }

  public function upload($file, $user)
  {
    $parameters =  array();
    $parameters["username"] = $user->getUsername();
    $parameters["token"] = $user->getUploadToken();
    $parameters["fileChecksum"] = md5_file($file->getPathname());
    $client = $this->getClient();
    $client->request('POST', "/api/upload/upload.json", $parameters, array($file));
    $response = $client->getResponse();
    return $response;
  }


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
    
}
