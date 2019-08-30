<?php

namespace App\Catrobat\Services\TestEnv;

use App\Catrobat\Services\ExtractedFileRepository;
use App\Catrobat\Services\MediaPackageFileRepository;
use App\Catrobat\Services\ProgramFileRepository;
use App\Entity\Extension;
use App\Entity\ProgramDownloads;
use App\Entity\ProgramLike;
use App\Entity\ProgramManager;
use App\Entity\ProgramRemixBackwardRelation;
use App\Entity\ProgramRemixRelation;
use App\Entity\ScratchProgramRemixRelation;
use App\Entity\User;
use App\Entity\UserLikeSimilarityRelation;
use App\Entity\UserManager;
use App\Repository\ExtensionRepository;
use App\Repository\ProgramRemixBackwardRepository;
use App\Repository\ProgramRemixRepository;
use App\Repository\ScratchProgramRemixRepository;
use App\Repository\ScratchProgramRepository;
use App\Repository\TagRepository;
use App\Repository\UserLikeSimilarityRelationRepository;
use App\Entity\UserRemixSimilarityRelation;
use App\Catrobat\RecommenderSystem\RecommenderManager;
use App\Repository\UserRemixSimilarityRelationRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use App\Entity\Program;
use App\Entity\Tag;
use Behat\Behat\Tester\Exception\PendingException;
use App\Catrobat\Services\CatrobatFileCompressor;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Behat\Behat\Hook\Scope\AfterStepScope;
use App\Entity\GameJam;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Component\Routing\Router;
use Symfony\Component\Validator\Constraints\DateTime;
use PHPUnit\Framework\Assert;


/**
 * Class SymfonySupport
 * @package App\Catrobat\Features\Helpers
 */
class SymfonySupport
{
  /**
   * @var
   */
  private $fixture_dir;

  /**
   * @var Kernel
   */
  private $kernel;
  /**
   * @var
   */
  private $client;
  /**
   * @var int
   */
  private $test_user_count = 0;
  /**
   * @var User
   */
  private $default_user;
  /**
   * @var
   */
  private $error_directory;

  /**
   * SymfonySupport constructor.
   *
   * @param $fixture_dir
   */
  public function __construct($fixture_dir)
  {
    $this->fixture_dir = $fixture_dir;
  }

  /**
   * @param KernelInterface $kernel
   */
  public function setKernel(KernelInterface $kernel)
  {
    $this->kernel = $kernel;
  }

  /**
   * @return Client
   */
  public function getClient()
  {
    if ($this->client == null)
    {
      $this->client = $this->kernel->getContainer()->get('test.client');
    }

    return $this->client;
  }

  /**
   * @return UserManager
   */
  public function getUserManager()
  {
    return $this->kernel->getContainer()->get(UserManager::class);
  }

  /**
   * @return ProgramManager
   */
  public function getProgramManager()
  {
    return $this->kernel->getContainer()->get(ProgramManager::class);
  }

  /**
   * @return TagRepository
   */
  public function getTagRepository()
  {
    return $this->kernel->getContainer()->get(TagRepository::class);
  }

  /**
   * @return ExtensionRepository
   */
  public function getExtensionRepository()
  {
    return $this->kernel->getContainer()->get(ExtensionRepository::class);
  }

  /**
   * @return ProgramRemixRepository
   */
  public function getProgramRemixForwardRepository()
  {
    return $this->kernel->getContainer()->get(ProgramRemixRepository::class);
  }

  /**
   * @return ProgramRemixBackwardRepository
   */
  public function getProgramRemixBackwardRepository()
  {
    return $this->kernel->getContainer()->get(ProgramRemixBackwardRepository::class);
  }

  /**
   * @return ScratchProgramRepository
   */
  public function getScratchProgramRepository()
  {
    return $this->kernel->getContainer()->get(ScratchProgramRepository::class);
  }

  /**
   * @return ScratchProgramRemixRepository
   */
  public function getScratchProgramRemixRepository()
  {
    return $this->kernel->getContainer()->get(ScratchProgramRemixRepository::class);
  }

  /**
   * @return ProgramFileRepository
   */
  public function getFileRepository()
  {
    return $this->kernel->getContainer()->get(ProgramFileRepository::class);
  }

  /**
   * @return ExtractedFileRepository
   */
  public function getExtractedFileRepository()
  {
    return $this->kernel->getContainer()->get(ExtractedFileRepository::class);
  }

  /**
   * @return MediaPackageFileRepository
   */
  public function getMediaPackageFileRepository()
  {
    return $this->kernel->getContainer()->get(MediaPackageFileRepository::class);
  }

  /**
   * @return RecommenderManager
   */
  public function getRecommenderManager()
  {
    return $this->kernel->getContainer()->get(RecommenderManager::class);
  }

  /**
   * @return UserLikeSimilarityRelationRepository
   */
  public function getUserLikeSimilarityRelationRepository()
  {
    return $this->kernel->getContainer()->get(UserLikeSimilarityRelationRepository::class);
  }

  /**
   * @return UserRemixSimilarityRelationRepository
   */
  public function getUserRemixSimilarityRelationRepository()
  {
    return $this->kernel->getContainer()->get(UserRemixSimilarityRelationRepository::class);
  }

  /**
   * @return EntityManager
   */
  public function getManager()
  {
    return $this->kernel->getContainer()->get('doctrine')->getManager();
  }

  /**
   * @return Router
   */
  public function getRouter()
  {
    return $this->kernel->getContainer()->get('router');
  }


  /**
   * @param $param
   *
   * @return mixed
   */
  public function getSymfonyParameter($param)
  {
    return $this->kernel->getContainer()->getParameter($param);
  }

  /**
   * @param $param
   *
   * @return object
   */
  public function getSymfonyService($param)
  {
    return $this->kernel->getContainer()->get($param);
  }

  /**
   * @return false | Profiler
   */
  public function getSymfonyProfile()
  {
    $profile = $this->getClient()->getProfile();
    if (!$profile)
    {
      throw new \RuntimeException(
        'The profiler is disabled. Activate it by setting ' .
        'framework.profiler.only_exceptions to false in ' .
        'your config'
      );
    }

    return $profile;
  }

  /**
   * @return User|object|null
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function getDefaultUser()
  {
    if ($this->default_user == null)
    {
      $this->default_user = $this->insertUser();
    }
    else
    {
      $this->default_user = $this->getUserManager()->find($this->default_user->getId());
    }

    return $this->default_user;
  }


  /**
   * @return string
   */
  public function getDefaultProgramFile()
  {
    $file = $this->fixture_dir . "/test.catrobat";
    Assert::assertTrue(is_file($file));

    return $file;
  }

  /**
   * @param $dir
   */
  public function setErrorDirectory($dir)
  {
    $this->error_directory = $dir;
  }

  /**
   * @param $directory
   */
  public function emptyDirectory($directory)
  {
    if (!is_dir($directory))
    {
      return;
    }
    $filesystem = new Filesystem();

    $finder = new Finder();
    $finder->in($directory)->depth(0);
    foreach ($finder as $file)
    {
      $filesystem->remove($file);
    }
  }

  /**
   * @param array $config
   *
   * @return GameJam
   * @throws ORMException
   * @throws OptimisticLockException
   * @throws \Exception
   */
  public function insertDefaultGamejam($config = [])
  {
    $gamejam = new GameJam();
    @$gamejam->setName($config['name'] ?: "pocketalice");
    @$gamejam->setHashtag($config['hashtag'] ?: null);
    @$gamejam->setFlavor($config['flavor'] == null ? "pocketalice" :
      $config['flavor'] != "no flavor" ?: null);

    $start_date = new \DateTime();
    $start_date->sub(new \DateInterval('P10D'));
    $end_date = new \DateTime();
    $end_date->add(new \DateInterval('P10D'));

    @$gamejam->setStart($config['start'] ?: $start_date);
    @$gamejam->setEnd($config['end'] ?: $end_date);

    @$gamejam->setFormUrl($config['formurl'] ?: "https://catrob.at/url/to/form");

    $this->getManager()->persist($gamejam);
    $this->getManager()->flush();

    return $gamejam;
  }

  /**
   * @param array $config
   *
   * @return object|null
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function insertUser($config = [])
  {
    ++$this->test_user_count;
    $user_manager = $this->getUserManager();
    $user = $user_manager->createUser();
    @$user->setUsername($config['name'] ?: 'GeneratedUser' . $this->test_user_count);
    @$user->setEmail($config['email'] ?: 'default' . $this->test_user_count . '@pocketcode.org');
    @$user->setPlainPassword($config['password'] ?: 'GeneratedPassword');
    @$user->setEnabled($config['enabled'] ?: true);
    @$user->setUploadToken($config['token'] ?: 'GeneratedToken');
    @$user->setCountry($config['country'] ?: 'at');
    @$user->setLimited($config['limited'] ?: 'false');
    @$user->addRole($config['role'] ?: 'ROLE_USER');
    $user_manager->updateUser($user, true);

    $em = $this->getManager();

    if (array_key_exists('id', $config) && $config['id'] !== null) {
      $user->setId($config['id']);
    }
    else {
      $user->setId('' . $this->test_user_count);
    }

    $em->flush();

    return $user_manager->find($user->getId());
  }

  /**
   * 
   */
  public function computeAllLikeSimilaritiesBetweenUsers()
  {
    //$this->getRecommenderManager()->computeUserLikeSimilarities(null);
    $catroweb_dir = $this->kernel->getRootDir() . '/..';
    $similarity_computation_service = $catroweb_dir . '/bin/recsys-similarity-computation-service.jar';
    $output_dir = $catroweb_dir;
    $sqlite_db_path = "$catroweb_dir/tests/behat/sqlite/behattest.sqlite";

    shell_exec("$catroweb_dir/bin/console catrobat:recommender:export --env=test");
    shell_exec("/usr/bin/env java -jar $similarity_computation_service catroweb user_like_similarity_relation $catroweb_dir $output_dir");
    shell_exec("/usr/bin/env printf \"with open('$catroweb_dir/import_likes.sql') as file:\\n  for line in file:" .
      "\\n    print line.replace('use catroweb;', '').replace('NOW()', '\\\"\\\"')\\n\" | " .
      "/usr/bin/env python2 > $catroweb_dir/import_likes_output.sql");
    shell_exec("/usr/bin/env cat $catroweb_dir/import_likes_output.sql | /usr/bin/env sqlite3 $sqlite_db_path");
    @unlink("$catroweb_dir/data_likes");
    @unlink("$catroweb_dir/data_remixes");
    @unlink("$catroweb_dir/import_likes.sql");
    @unlink("$catroweb_dir/import_likes_output.sql");
  }

  /**
   *
   */
  public function computeAllRemixSimilaritiesBetweenUsers()
  {
    //$this->getRecommenderManager()->computeUserRemixSimilarities(null);
    $catroweb_dir = $this->kernel->getRootDir() . '/..';
    $similarity_computation_service = $catroweb_dir . '/bin/recsys-similarity-computation-service.jar';
    $output_dir = $catroweb_dir;
    $sqlite_db_path = "$catroweb_dir/tests/behat/sqlite/behattest.sqlite";

    shell_exec("$catroweb_dir/bin/console catrobat:recommender:export --env=test");
    shell_exec("/usr/bin/env java -jar $similarity_computation_service catroweb user_remix_similarity_relation $catroweb_dir $output_dir");
    shell_exec("/usr/bin/env printf \"with open('$catroweb_dir/import_remixes.sql') as file:\\n  for line in file:" .
      "\\n    print line.replace('use catroweb;', '').replace('NOW()', '\\\"\\\"')\\n\" | " .
      "/usr/bin/env python2 > $catroweb_dir/import_remixes_output.sql");
    shell_exec("/usr/bin/env cat $catroweb_dir/import_remixes_output.sql | /usr/bin/env sqlite3 $sqlite_db_path");
    @unlink("$catroweb_dir/data_likes");
    @unlink("$catroweb_dir/data_remixes");
    @unlink("$catroweb_dir/import_remixes.sql");
    @unlink("$catroweb_dir/import_remixes_output.sql");
  }

  /**
   * @param array $config
   *
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function insertUserLikeSimilarity($config = [])
  {
    /**
     * @var $first_user  User
     * @var $second_user User
     */
    $em = $this->getManager();
    $user_manager = $this->getUserManager();
    $first_user = $user_manager->find($config['first_user_id']);
    $second_user = $user_manager->find($config['second_user_id']);
    $em->persist(new UserLikeSimilarityRelation($first_user, $second_user, $config['similarity']));
    $em->flush();
  }

  /**
   * @param array $config
   *
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function insertUserRemixSimilarity($config = [])
  {
    /**
     * @var $first_user  User
     * @var $second_user User
     */
    $em = $this->getManager();
    $user_manager = $this->getUserManager();
    $first_user = $user_manager->find($config['first_user_id']);
    $second_user = $user_manager->find($config['second_user_id']);
    $em->persist(new UserRemixSimilarityRelation($first_user, $second_user, $config['similarity']));
    $em->flush();
  }

  /**
   * @param array $config
   *
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function insertProgramLike($config = [])
  {
    /**
     * @var $user    User
     * @var $program Program
     */
    $em = $this->getManager();
    $user_manager = $this->getUserManager();
    $program_manager = $this->getProgramManager();
    $user = $user_manager->findOneBy(['username' => $config['username']]);
    $program = $program_manager->find($config['program_id']);

    $program_like = new ProgramLike($program, $user, $config['type']);
    $program_like->setCreatedAt(new \DateTime($config['created at'], new \DateTimeZone('UTC')));

    $em->persist($program_like);
    $em->flush();
  }

  /**
   * @param $config
   *
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function insertTag($config)
  {
    $em = $this->getManager();
    $tag = new Tag();

    $tag->setEn($config['en']);
    $tag->setDe($config['de']);

    $em->persist($tag);
    $em->flush();

  }

  /**
   * @param $config
   *
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function insertExtension($config)
  {
    $em = $this->getManager();
    $extension = new Extension();

    $extension->setName($config['name']);
    $extension->setPrefix($config['prefix']);

    $em->persist($extension);
    $em->flush();

  }

  /**
   * @param $config
   *
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function insertForwardRemixRelation($config)
  {
    /**
     * @var $ancestor   Program
     * @var $descendant Program
     */
    $ancestor = $this->getProgramManager()->find($config['ancestor_id']);
    $descendant = $this->getProgramManager()->find($config['descendant_id']);
    $forward_relation = new ProgramRemixRelation($ancestor, $descendant, (int)$config['depth']);

    $em = $this->getManager();
    $em->persist($forward_relation);
    $em->flush();
  }

  /**
   * @param $config
   *
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function insertBackwardRemixRelation($config)
  {
    /**
     * @var $parent Program
     * @var $child  Program
     */
    $parent = $this->getProgramManager()->find($config['parent_id']);
    $child = $this->getProgramManager()->find($config['child_id']);
    $forward_relation = new ProgramRemixBackwardRelation($parent, $child);

    $em = $this->getManager();
    $em->persist($forward_relation);
    $em->flush();
  }

  /**
   * @param $config
   *
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function insertScratchRemixRelation($config)
  {
    /**
     * @var $catrobat_child Program
     */
    $catrobat_child = $this->getProgramManager()->find($config['catrobat_child_id']);
    $scratch_relation = new ScratchProgramRemixRelation(
      $config['scratch_parent_id'],
      $catrobat_child
    );

    $em = $this->getManager();
    $em->persist($scratch_relation);
    $em->flush();
  }

  /**
   * @param $user
   * @param $config
   *
   * @return Program
   * @throws ORMException
   * @throws OptimisticLockException
   * @throws \Exception
   */
  public function insertProgram($user, $config)
  {
    /**
     * @var $tag       Tag
     * @var $extension Extension
     */
    if ($user == null)
    {
      $user = $this->getDefaultUser();
    }

    $em = $this->getManager();
    $program = new Program();
    $program->setUser($user);
    $program->setName($config['name'] ?: 'Generated program');
    $program->setDescription(isset($config['description']) ? $config['description'] : 'Generated');
    $program->setViews(isset($config['views']) ? $config['views'] : 1);
    $program->setDownloads(isset($config['downloads']) ? $config['downloads'] : 1);
    $program->setUploadedAt(isset($config['uploadtime']) ? new \DateTime($config['uploadtime'], new \DateTimeZone('UTC')) : new \DateTime());
    $program->setRemixMigratedAt(isset($config['remixmigratedtime']) ? new \DateTime($config['remixmigratedtime'], new \DateTimeZone('UTC')) : null);
    $program->setCatrobatVersion(isset($config['catrobatversion']) ? $config['catrobatversion'] : 1);
    $program->setCatrobatVersionName(isset($config['catrobatversionname']) ? $config['catrobatversionname'] : '0.9.1');
    $program->setLanguageVersion(isset($config['languageversion']) ? $config['languageversion'] : 1);
    $program->setUploadIp('127.0.0.1');
    $program->setFilesize(isset($config['filesize']) ? $config['filesize'] : 0);
    $program->setVisible(isset($config['visible']) ? boolval($config['visible']) : true);
    $program->setUploadLanguage('en');
    $program->setApproved(isset($config['approved']) ? $config['approved'] : true);
    $program->setFlavor(isset($config['flavor']) ? $config['flavor'] : 'pocketcode');
    $program->setApkStatus(isset($config['apk_status']) ? $config['apk_status'] : Program::APK_NONE);
    $program->setDirectoryHash(isset($config['directory_hash']) ? $config['directory_hash'] : null);
    $program->setAcceptedForGameJam(isset($config['accepted']) ? $config['accepted'] : false);
    $program->setGamejam(isset($config['gamejam']) ? $config['gamejam'] : null);
    $program->setRemixRoot(isset($config['remix_root']) ? $config['remix_root'] : true);
    $program->setDebugBuild(isset($config['debug']) ? $config['debug'] : false);

    $em->persist($program);

    // overwrite id if desired
    if (array_key_exists('id', $config) && $config['id'] !== null) {
      $program->setId($config['id']);
      $em->persist($program);
      $em->flush();
      $program_repo = $em->getRepository('App\Entity\Program');
      $program = $program_repo->find($config['id']);
    }

    if (isset($config['tags']) && $config['tags'] != null)
    {
      $tags = explode(',', $config['tags']);
      foreach ($tags as $tag_id)
      {
        $tag = $this->getTagRepository()->find($tag_id);
        $program->addTag($tag);
      }
    }

    if (isset($config['extensions']) && $config['extensions'] != null)
    {
      $extensions = explode(',', $config['extensions']);
      foreach ($extensions as $extension_name)
      {
        $extension = $this->getExtensionRepository()->findOneBy(["name" => $extension_name]);
        $program->addExtension($extension);
      }
    }

    $em->persist($program);

    $user->addProgram($program);
    $em->persist($user);

    // FIXXME: why exactly do we have to do this?
    if (isset($config['gamejam']))
    {
      /**
       * @var $jam GameJam
       */
      $jam = $config['gamejam'];
      $jam->addProgram($program);
      $em->persist($jam);
    }

    $em->flush();

    return $program;
  }

  /**
   * @param $program Program
   * @param $config
   *
   * @return ProgramDownloads
   * @throws ORMException
   * @throws OptimisticLockException
   * @throws \Exception
   */
  public function insertProgramDownloadStatistics($program, $config)
  {
    $em = $this->getManager();
    /**
     * @var $program_statistics ProgramDownloads
     * @var $program            Program;
     */
    $program_statistics = new ProgramDownloads();
    $program_statistics->setProgram($program);
    $program_statistics->setDownloadedAt(new \DateTime($config['downloaded_at']) ?: new DateTime());
    $program_statistics->setIp(isset($config['ip']) ? $config['ip'] : '88.116.169.222');
    $program_statistics->setCountryCode(isset($config['country_code']) ? $config['country_code'] : 'AT');
    $program_statistics->setCountryName(isset($config['country_name']) ? $config['country_name'] : 'Austria');
    $program_statistics->setUserAgent(isset($config['user_agent']) ? $config['user_agent'] : 'okhttp');
    $program_statistics->setReferrer(isset($config['referrer']) ? $config['referrer'] : 'Facebook');

    if (isset($config['username']))
    {
      $user_manager = $this->getUserManager();
      $user = $user_manager->createUser();
      $user->setUsername($config['username']);
      $user->setEmail('dog@robat.at');
      $user->setPassword('test');
      $user_manager->updateUser($user);
      $program_statistics->setUser($user);
    }

    $em->persist($program_statistics);

    $program->addProgramDownloads($program_statistics);
    $em->persist($program);

    $em->flush();

    return $program_statistics;
  }

  /**
   * @param $parameters
   *
   * @return string
   */
  public function generateProgramFileWith($parameters, $is_embroidery = false)
  {
    $filesystem = new Filesystem();
    $this->emptyDirectory(sys_get_temp_dir() . '/program_generated/');
    $new_program_dir = sys_get_temp_dir() . '/program_generated/';
    if ($is_embroidery) {
      $filesystem->mirror($this->fixture_dir . '/GeneratedFixtures/embroidery', $new_program_dir);
    }
    else {
      $filesystem->mirror($this->fixture_dir . '/GeneratedFixtures/base', $new_program_dir);
    }
    $properties = simplexml_load_file($new_program_dir . '/code.xml');

    foreach ($parameters as $name => $value)
    {
      switch ($name)
      {
        case 'description':
          $properties->header->description = $value;
          break;
        case 'name':
          $properties->header->programName = $value;
          break;
        case 'platform':
          $properties->header->platform = $value;
          break;
        case 'catrobatLanguageVersion':
          $properties->header->catrobatLanguageVersion = $value;
          break;
        case 'applicationVersion':
          $properties->header->applicationVersion = $value;
          break;
        case 'applicationName':
          $properties->header->applicationName = $value;
          break;
        case 'url':
          $properties->header->url = $value;
          break;
        case 'tags':
          $properties->header->tags = $value;
          break;

        default:
          throw new PendingException('unknown xml field ' . $name);
      }
    }

    $properties->asXML($new_program_dir . '/code.xml');
    $compressor = new CatrobatFileCompressor();

    return $compressor->compress($new_program_dir, sys_get_temp_dir() . '/', 'program_generated');
  }

  /**
   * @param $file
   * @param $user
   * @param $desired_id
   * @param string $flavor
   * @param null $request_param
   *
   * @return Response | null
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function upload($file, $user, $desired_id, $flavor = "pocketcode", $request_param = null)
  {
    if ($user == null)
    {
      $user = $this->getDefaultUser();
    }

    if (is_string($file))
    {
      try
      {
        $file = new UploadedFile($file, 'uploadedFile');
      } catch (\Exception $e)
      {
        throw new PendingException('No case defined for ' . $e);
      }
    }

    $parameters = [];
    $parameters['username'] = $user->getUsername();
    $parameters['token'] = $user->getUploadToken();
    $parameters['fileChecksum'] = md5_file($file->getPathname());

    if ($request_param['deviceLanguage'] != null)
    {
      $parameters['deviceLanguage'] = $request_param['deviceLanguage'];
    }

    $client = $this->getClient();
    $client->request('POST', '/' . $flavor . '/api/upload/upload.json', $parameters, [$file]);
    $response = $client->getResponse();

    if ($desired_id)
    {
      /**
       * @var $new_program Program
       */
      $matches = [];
      preg_match('/"projectId":".*?"/', $response->getContent(), $matches);
      $old_id = substr($matches[0], strlen('"projectId":"'), -1);
      $new_program = $this->getProgramManager()->find($old_id);

      $new_program->setId($desired_id);
      $this->getManager()->persist($new_program);
      $this->getManager()->flush();

      foreach ($this->getProgramRemixForwardRepository()->findAll() as $entry) {
        /**
         * @var $entry ProgramRemixRelation
         */
        if ($entry->getDescendantId() == $old_id) {
          $entry->setDescendant($new_program);
        }
        if ($entry->getAncestorId() == $old_id) {
          $entry->setAncestor($new_program);
        }
      }

      $em = $this->getManager();
      $em->persist($new_program);
      $em->flush();

      // rename project file
      rename("./public/resources_test/projects/" . $old_id . ".catrobat",
        "./public/resources_test/projects/" . $desired_id . ".catrobat");

      $content = json_decode($response->getContent(), true);
      $content['projectId'] = $desired_id;
      $client->getResponse()->setContent(json_encode($content));
      $response = $client->getResponse();
    }

    return $response;
  }

  /**
   * @param $file
   * @param $user
   *
   * @return \Symfony\Component\HttpFoundation\Response|null
   */
  public function submit($file, $user)
  {
    if ($user == null)
    {
      $user = $this->getDefaultUser();
    }

    if (is_string($file))
    {
      $file = new UploadedFile($file, 'uploadedFile');
    }

    $parameters = [];
    $parameters['username'] = $user->getUsername();
    $parameters['token'] = $user->getUploadToken();
    $parameters['fileChecksum'] = md5_file($file->getPathname());
    $client = $this->getClient();
    $client->request('POST', '/app/api/gamejam/submit.json', $parameters, [$file]);
    $response = $client->getResponse();

    return $response;
  }

  /**
   * @param AfterStepScope $scope
   */
  public function logOnError(AfterStepScope $scope)
  {
    if ($this->error_directory == null)
    {
      return;
    }

    if (!$scope->getTestResult()->isPassed() && $this->getClient() != null)
    {
      $response = $this->getClient()->getResponse();
      if ($response != null && $response->getContent() != '')
      {
        file_put_contents($this->error_directory . 'errors.json', $response->getContent());
      }
    }
  }

  /**
   * @param $path
   *
   * @return bool|string
   */
  protected function getTempCopy($path)
  {
    $temppath = tempnam(sys_get_temp_dir(), 'apktest');
    copy($path, $temppath);

    return $temppath;
  }

  /**
   *
   */
  public function clearDefaultUser()
  {
    $this->default_user = null;
  }
}