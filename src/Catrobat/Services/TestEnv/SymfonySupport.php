<?php

namespace App\Catrobat\Services\TestEnv;

use App\Catrobat\RecommenderSystem\RecommenderManager;
use App\Catrobat\Services\CatrobatFileCompressor;
use App\Catrobat\Services\ExtractedFileRepository;
use App\Catrobat\Services\MediaPackageFileRepository;
use App\Catrobat\Services\ProgramFileRepository;
use App\Catrobat\Services\TestEnv\DataFixtures\ProjectDataFixtures;
use App\Catrobat\Services\TestEnv\DataFixtures\UserDataFixtures;
use App\Entity\Extension;
use App\Entity\FeaturedProgram;
use App\Entity\GameJam;
use App\Entity\Notification;
use App\Entity\Program;
use App\Entity\ProgramDownloads;
use App\Entity\ProgramInappropriateReport;
use App\Entity\ProgramLike;
use App\Entity\ProgramManager;
use App\Entity\ProgramRemixBackwardRelation;
use App\Entity\ProgramRemixRelation;
use App\Entity\ScratchProgramRemixRelation;
use App\Entity\Tag;
use App\Entity\User;
use App\Entity\UserComment;
use App\Entity\UserLikeSimilarityRelation;
use App\Entity\UserManager;
use App\Entity\UserRemixSimilarityRelation;
use App\Kernel;
use App\Repository\CatroNotificationRepository;
use App\Repository\ExtensionRepository;
use App\Repository\ProgramRemixBackwardRepository;
use App\Repository\ProgramRemixRepository;
use App\Repository\ScratchProgramRemixRepository;
use App\Repository\ScratchProgramRepository;
use App\Repository\TagRepository;
use App\Repository\UserLikeSimilarityRelationRepository;
use App\Repository\UserRemixSimilarityRelationRepository;
use App\Utils\TimeUtils;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Symfony2Extension\Context\KernelDictionary;
use DateInterval;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use PHPUnit\Framework\Assert;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Router;

/**
 * Trait SymfonySupport.
 *
 * Php only supports single inheritance, therefore we can't extend all our Context Classes from the same BaseContext.
 * Some Context Classes must extend the (Mink)BrowserContext. That's why we use this trait in all our Context
 * files to provide them with the same basic functionality.
 *
 * A trait is basically just a copy & paste and therefore every context uses its own instances.
 * Since some variables must exist only once, we have to set them to static members. (E.g. kernel_browser)
 */
trait SymfonySupport
{
  use KernelDictionary;

  public string $ERROR_DIR;

  public string $FIXTURES_DIR;

  public string $SCREENSHOT_DIR;

  public string $MEDIA_PACKAGE_DIR = './tests/testdata/DataFixtures/MediaPackage/';

  public string $EXTRACT_RESOURCES_DIR;

  /**
   * @override
   * Sets Kernel instance.
   */
  public function setKernel(KernelInterface $kernel): void
  {
    $this->kernel = $kernel;
    $this->ERROR_DIR = $this->getSymfonyParameter('catrobat.testreports.behat');
    $this->SCREENSHOT_DIR = $this->getSymfonyParameter('catrobat.testreports.screenshot');
    $this->FIXTURES_DIR = $this->getSymfonyParameter('catrobat.test.directory.source');
    $this->MEDIA_PACKAGE_DIR = $this->FIXTURES_DIR.'MediaPackage/';
    $this->EXTRACT_RESOURCES_DIR = $this->getSymfonyParameter('catrobat.file.extract.dir');
  }

  public function getUserManager(): UserManager
  {
    return $this->kernel->getContainer()->get(UserManager::class);
  }

  public function getUserDataFixtures(): UserDataFixtures
  {
    return $this->kernel->getContainer()->get(UserDataFixtures::class);
  }

  public function getProgramManager(): ProgramManager
  {
    return $this->kernel->getContainer()->get(ProgramManager::class);
  }

  public function getProjectDataFixtures(): ProjectDataFixtures
  {
    return $this->kernel->getContainer()->get(ProjectDataFixtures::class);
  }

  public function getJwtManager(): JWTManager
  {
    return $this->kernel->getContainer()->get('lexik_jwt_authentication.jwt_manager');
  }

  public function getJwtEncoder(): JWTEncoderInterface
  {
    return $this->kernel->getContainer()->get('lexik_jwt_authentication.encoder');
  }

  public function getTagRepository(): TagRepository
  {
    return $this->kernel->getContainer()->get(TagRepository::class);
  }

  public function getExtensionRepository(): ExtensionRepository
  {
    return $this->kernel->getContainer()->get(ExtensionRepository::class);
  }

  public function getProgramRemixForwardRepository(): ProgramRemixRepository
  {
    return $this->kernel->getContainer()->get(ProgramRemixRepository::class);
  }

  public function getProgramRemixBackwardRepository(): ProgramRemixBackwardRepository
  {
    return $this->kernel->getContainer()->get(ProgramRemixBackwardRepository::class);
  }

  public function getScratchProgramRepository(): ScratchProgramRepository
  {
    return $this->kernel->getContainer()->get(ScratchProgramRepository::class);
  }

  public function getScratchProgramRemixRepository(): ScratchProgramRemixRepository
  {
    return $this->kernel->getContainer()->get(ScratchProgramRemixRepository::class);
  }

  public function getFileRepository(): ProgramFileRepository
  {
    return $this->kernel->getContainer()->get(ProgramFileRepository::class);
  }

  public function getExtractedFileRepository(): ExtractedFileRepository
  {
    return $this->kernel->getContainer()->get(ExtractedFileRepository::class);
  }

  public function getMediaPackageFileRepository(): MediaPackageFileRepository
  {
    return $this->kernel->getContainer()->get(MediaPackageFileRepository::class);
  }

  public function getRecommenderManager(): RecommenderManager
  {
    return $this->kernel->getContainer()->get(RecommenderManager::class);
  }

  public function getUserLikeSimilarityRelationRepository(): UserLikeSimilarityRelationRepository
  {
    return $this->kernel->getContainer()->get(UserLikeSimilarityRelationRepository::class);
  }

  public function getUserRemixSimilarityRelationRepository(): UserRemixSimilarityRelationRepository
  {
    return $this->kernel->getContainer()->get(UserRemixSimilarityRelationRepository::class);
  }

  public function getCatroNotificationRepository(): CatroNotificationRepository
  {
    return $this->kernel->getContainer()->get(CatroNotificationRepository::class);
  }

  public function getManager(): EntityManagerInterface
  {
    return $this->kernel->getContainer()->get('doctrine')->getManager();
  }

  public function getRouter(): Router
  {
    return $this->kernel->getContainer()->get('router');
  }

  /**
   * @return mixed
   */
  public function getSymfonyParameter(string $param)
  {
    return $this->kernel->getContainer()->getParameter($param);
  }

  public function getSymfonyService(string $service_name): object
  {
    return $this->kernel->getContainer()->get($service_name);
  }

  public function getDefaultProgramFile(): string
  {
    $file = $this->FIXTURES_DIR.'/test.catrobat';
    Assert::assertTrue(is_file($file));

    return $file;
  }

  public function emptyDirectory(string $directory): void
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
   * @throws Exception
   */
  public function insertDefaultGameJam(array $config = []): GameJam
  {
    $game_jam = new GameJam();
    $game_jam->setName($config['name'] ?? 'pocketalice');
    $game_jam->setHashtag($config['hashtag'] ?? null);

    if (isset($config['flavor']) && 'no-flavor' !== $config['flavor'])
    {
      $game_jam->setFlavor($config['flavor']);
    }
    elseif (!isset($config['flavor']))
    {
      $game_jam->setFlavor('pocketalice');
    }

    $start_date = TimeUtils::getDateTime();
    $start_date->sub(new DateInterval('P10D'));

    $end_date = TimeUtils::getDateTime();

    $end_date->add(new DateInterval('P10D'));

    $game_jam->setStart($config['start'] ?? $start_date);
    $game_jam->setEnd($config['end'] ?? $end_date);

    $game_jam->setFormUrl($config['formurl'] ?? 'https://catrob.at/url/to/form');

    $this->getManager()->persist($game_jam);
    $this->getManager()->flush();

    return $game_jam;
  }

  public function insertUser(array $config = [], bool $andFlush = true): User
  {
    return $this->getUserDataFixtures()->insertUser($config, $andFlush);
  }

  public function assertUser(array $config = []): void
  {
    $this->getUserDataFixtures()->assertUser($config);
  }

  public function insertUserLikeSimilarity(array $config = [], bool $andFlush = true): UserLikeSimilarityRelation
  {
    $user_manager = $this->getUserManager();

    /** @var User $first_user */
    $first_user = $user_manager->find($config['first_user_id']);

    /** @var User $second_user */
    $second_user = $user_manager->find($config['second_user_id']);

    $relation = new UserLikeSimilarityRelation($first_user, $second_user, $config['similarity']);

    $this->getManager()->persist($relation);
    if ($andFlush)
    {
      $this->getManager()->flush();
    }

    return $relation;
  }

  public function insertUserRemixSimilarity(array $config = [], bool $andFlush = true): UserRemixSimilarityRelation
  {
    $user_manager = $this->getUserManager();

    /** @var User $first_user */
    $first_user = $user_manager->find($config['first_user_id']);

    /** @var User $second_user */
    $second_user = $user_manager->find($config['second_user_id']);

    $relation = new UserRemixSimilarityRelation($first_user, $second_user, $config['similarity']);

    $this->getManager()->persist($relation);
    if ($andFlush)
    {
      $this->getManager()->flush();
    }

    return $relation;
  }

  /**
   * @throws Exception
   */
  public function insertProgramLike(array $config = [], bool $andFlush = true): ProgramLike
  {
    $user_manager = $this->getUserManager();
    $program_manager = $this->getProgramManager();

    /** @var User|null $user */
    $user = $user_manager->findUserByUsername($config['username']);

    $program = $program_manager->find($config['program_id']);

    $program_like = new ProgramLike($program, $user, $config['type']);
    $program_like->setCreatedAt(new DateTime($config['created at'], new DateTimeZone('UTC')));

    $this->getManager()->persist($program_like);
    if ($andFlush)
    {
      $this->getManager()->flush();
    }

    return $program_like;
  }

  public function insertTag(array $config = [], bool $andFlush = true): Tag
  {
    $tag = new Tag();
    $tag->setEn($config['en']);
    $tag->setDe($config['de'] ?? null);

    $this->getManager()->persist($tag);
    if ($andFlush)
    {
      $this->getManager()->flush();
    }

    return $tag;
  }

  public function insertExtension(array $config = [], bool $andFlush = true): Extension
  {
    $extension = new Extension();
    $extension->setName($config['name']);
    $extension->setPrefix($config['prefix']);

    $this->getManager()->persist($extension);
    if ($andFlush)
    {
      $this->getManager()->flush();
    }

    return $extension;
  }

  public function insertForwardRemixRelation(array $config = [], bool $andFlush = true): ProgramRemixRelation
  {
    /** @var Program $ancestor */
    $ancestor = $this->getProgramManager()->find($config['ancestor_id']);

    /** @var Program $descendant */
    $descendant = $this->getProgramManager()->find($config['descendant_id']);

    $forward_relation = new ProgramRemixRelation($ancestor, $descendant, (int) $config['depth']);

    $this->getManager()->persist($forward_relation);
    if ($andFlush)
    {
      $this->getManager()->flush();
    }

    return $forward_relation;
  }

  public function insertBackwardRemixRelation(array $config = [], bool $andFlush = true): ProgramRemixBackwardRelation
  {
    /** @var Program $parent */
    $parent = $this->getProgramManager()->find($config['parent_id']);

    /** @var Program $child */
    $child = $this->getProgramManager()->find($config['child_id']);

    $backward_relation = new ProgramRemixBackwardRelation($parent, $child);

    $this->getManager()->persist($backward_relation);
    if ($andFlush)
    {
      $this->getManager()->flush();
    }

    return $backward_relation;
  }

  public function insertScratchRemixRelation(array $config = [], bool $andFlush = true): ScratchProgramRemixRelation
  {
    /** @var Program $catrobat_child */
    $catrobat_child = $this->getProgramManager()->find($config['catrobat_child_id']);

    $scratch_relation = new ScratchProgramRemixRelation(
      $config['scratch_parent_id'],
      $catrobat_child
    );

    $this->getManager()->persist($scratch_relation);
    if ($andFlush)
    {
      $this->getManager()->flush();
    }

    return $scratch_relation;
  }

  /**
   * @throws Exception
   */
  public function insertProject(array $config, bool $andFlush = true): Program
  {
    return $this->getProjectDataFixtures()->insertProject($config, $andFlush);
  }

  public function insertFeaturedProject(array $config, bool $andFlush = true): FeaturedProgram
  {
    $featured_program = new FeaturedProgram();

    /* @var Program $program */
    if (isset($config['program_id']))
    {
      $program = $this->getProgramManager()->find($config['program_id']);
      $featured_program->setProgram($program);
    }
    else
    {
      $program = $this->getProgramManager()->findOneByName($config['name']);
      $featured_program->setProgram($program);
    }

    $featured_program->setUrl($config['url'] ?? null);
    $featured_program->setImageType($config['imagetype'] ?? 'jpg');
    $featured_program->setActive(isset($config['active']) ? (int) $config['active'] : true);
    $featured_program->setFlavor($config['flavor'] ?? 'pocketcode');
    $featured_program->setPriority(isset($config['priority']) ? (int) $config['priority'] : 1);
    $featured_program->setForIos(isset($config['ios_only']) ? 'yes' === $config['ios_only'] : false);

    $this->getManager()->persist($featured_program);
    if ($andFlush)
    {
      $this->getManager()->flush();
    }

    return $featured_program;
  }

  /**
   * @throws Exception
   */
  public function insertUserComment(array $config, bool $andFlush = true): UserComment
  {
    /** @var Program $project */
    $project = $this->getProgramManager()->find($config['program_id']);

    /** @var User|null $user */
    $user = $this->getUserManager()->find($config['user_id']);

    $new_comment = new UserComment();
    $new_comment->setUploadDate(new DateTime($config['upload_date'], new DateTimeZone('UTC')));
    $new_comment->setProgram($project);
    $new_comment->setUser($user);
    $new_comment->setUsername($config['user_name']);
    $new_comment->setIsReported($config['reported']);
    $new_comment->setText($config['text']);

    $this->getManager()->persist($new_comment);

    if (isset($config['id']))
    {
      // overwrite id if desired
      $new_comment->setId($config['id']);
      $this->getManager()->persist($new_comment);
    }

    if ($andFlush)
    {
      $this->getManager()->flush();
    }

    return $new_comment;
  }

  /**
   * @throws Exception
   */
  public function insertProjectReport(array $config, bool $andFlush = true): ProgramInappropriateReport
  {
    /** @var Program $project */
    $project = $this->getProgramManager()->find($config['program_id']);

    /** @var User|null $user */
    $user = $this->getUserManager()->find($config['user_id']);

    $new_report = new ProgramInappropriateReport();
    $new_report->setCategory($config['category']);
    $new_report->setProgram($project);
    $new_report->setReportingUser($user);
    $new_report->setTime(new DateTime($config['time'], new DateTimeZone('UTC')));
    $new_report->setNote($config['note']);
    $this->getManager()->persist($new_report);

    if ($andFlush)
    {
      $this->getManager()->flush();
    }

    return $new_report;
  }

  /**
   * @throws Exception
   */
  public function insertProgramDownloadStatistics(array $config, bool $andFlush = true): ProgramDownloads
  {
    /** @var Program $project */
    $project = $this->getProgramManager()->find($config['program_id']);

    $program_statistics = new ProgramDownloads();
    $program_statistics->setProgram($project);
    $program_statistics->setDownloadedAt(isset($config['downloaded_at']) ? new DateTime($config['downloaded_at']) : TimeUtils::getDateTime());
    $program_statistics->setIp($config['ip'] ?? '88.116.169.222');
    $program_statistics->setCountryCode($config['country_code'] ?? 'AT');
    $program_statistics->setCountryName($config['country_name'] ?? 'Austria');
    $program_statistics->setUserAgent($config['user_agent'] ?? 'okhttp');
    $program_statistics->setReferrer($config['referrer'] ?? 'Facebook');

    if (isset($config['username']))
    {
      $user_manager = $this->getUserManager();
      /** @var User|null $user */
      $user = $user_manager->findUserByUsername($config['username']);
      if (null === $user)
      {
        $this->insertUser(['name' => $config['username']], false);
      }
      $program_statistics->setUser($user);
    }

    $this->getManager()->persist($program_statistics);
    $project->addProgramDownloads($program_statistics);
    $this->getManager()->persist($project);

    if ($andFlush)
    {
      $this->getManager()->flush();
    }

    return $program_statistics;
  }

  public function insertNotification(array $config, bool $andFlush = true): Notification
  {
    /** @var User|null $user */
    $user = $this->getUserManager()->findUserByUsername($config['user']);

    $notification = new Notification();
    $notification->setUser($user);
    $notification->setReport($config['report']);
    $notification->setSummary($config['summary']);
    $notification->setUpload($config['upload']);
    $this->getManager()->persist($notification);

    if ($andFlush)
    {
      $this->getManager()->flush();
    }

    return $notification;
  }

  /**
   * @param mixed $is_embroidery
   * @param mixed $parameters
   */
  public function generateProgramFileWith($parameters, $is_embroidery = false): string
  {
    $filesystem = new Filesystem();
    $this->emptyDirectory(sys_get_temp_dir().'/program_generated/');
    $new_program_dir = sys_get_temp_dir().'/program_generated/';

    if ($is_embroidery)
    {
      $filesystem->mirror($this->FIXTURES_DIR.'/GeneratedFixtures/embroidery', $new_program_dir);
    }
    else
    {
      $filesystem->mirror($this->FIXTURES_DIR.'/GeneratedFixtures/base', $new_program_dir);
    }
    $properties = simplexml_load_file($new_program_dir.'/code.xml');

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
          throw new PendingException('unknown xml field '.$name);
      }
    }

    $properties->asXML($new_program_dir.'/code.xml');
    $compressor = new CatrobatFileCompressor();

    return $compressor->compress($new_program_dir, sys_get_temp_dir().'/', 'program_generated');
  }

  public function getStandardProgramFile(): UploadedFile
  {
    $filepath = $this->FIXTURES_DIR.'test.catrobat';
    Assert::assertTrue(file_exists($filepath), 'File not found');

    return new UploadedFile($filepath, 'test.catrobat');
  }

  public function assertJsonRegex(string $pattern, string $json): void
  {
    // allows to compare strings using a regex wildcard (.*?)
    $pattern = json_encode(json_decode($pattern, false, 512, JSON_THROW_ON_ERROR), JSON_THROW_ON_ERROR); // reformat string

    if (is_countable(json_decode($pattern)))
    {
      Assert::assertEquals(count(json_decode($pattern)), count(json_decode($json)));
    }

    // escape chars that should not be used as regex
    $pattern = str_replace('\\', '\\\\', $pattern);
    $pattern = str_replace('[', '\\[', $pattern);
    $pattern = str_replace(']', '\\]', $pattern);
    $pattern = str_replace('?', '\\?', $pattern);
    $pattern = str_replace('*', '\\*', $pattern);
    $pattern = str_replace('(', '\\(', $pattern);
    $pattern = str_replace(')', '\\)', $pattern);
    $pattern = str_replace('+', '\\+', $pattern);

    // define regex wildcards
    $pattern = str_replace('REGEX_STRING_WILDCARD', '(.+?)', $pattern);
    $pattern = str_replace('"REGEX_INT_WILDCARD"', '([0-9]+?)', $pattern);

    $delimter = '#';
    $json = json_encode(json_decode($json, false, 512, JSON_THROW_ON_ERROR), JSON_THROW_ON_ERROR);
    Assert::assertRegExp($delimter.$pattern.$delimter, $json);
  }

  /**
   * @param mixed $path
   *
   * @return bool|string
   */
  public function getTempCopy($path)
  {
    $temp_path = tempnam(sys_get_temp_dir(), 'apktest');
    copy($path, $temp_path);

    return $temp_path;
  }
}
