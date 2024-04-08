<?php

declare(strict_types=1);

namespace App\System\Testing\Behat;

use App\Admin\Tools\FeatureFlag\FeatureFlagManager;
use App\DB\Entity\Flavor;
use App\DB\Entity\Project\Extension;
use App\DB\Entity\Project\Program;
use App\DB\Entity\Project\ProgramInappropriateReport;
use App\DB\Entity\Project\ProgramLike;
use App\DB\Entity\Project\Remix\ProgramRemixBackwardRelation;
use App\DB\Entity\Project\Remix\ProgramRemixRelation;
use App\DB\Entity\Project\Scratch\ScratchProgramRemixRelation;
use App\DB\Entity\Project\Special\ExampleProgram;
use App\DB\Entity\Project\Special\FeaturedProgram;
use App\DB\Entity\Project\Tag;
use App\DB\Entity\User\Comment\UserComment;
use App\DB\Entity\User\RecommenderSystem\UserLikeSimilarityRelation;
use App\DB\Entity\User\RecommenderSystem\UserRemixSimilarityRelation;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\FlavorRepository;
use App\DB\EntityRepository\MediaLibrary\MediaPackageFileRepository;
use App\DB\EntityRepository\Project\ExtensionRepository;
use App\DB\EntityRepository\Project\ProgramRemixBackwardRepository;
use App\DB\EntityRepository\Project\ProgramRemixRepository;
use App\DB\EntityRepository\Project\ScratchProgramRemixRepository;
use App\DB\EntityRepository\Project\ScratchProgramRepository;
use App\DB\EntityRepository\Project\TagRepository;
use App\DB\EntityRepository\System\CronJobRepository;
use App\DB\EntityRepository\User\Notification\NotificationRepository;
use App\DB\EntityRepository\User\RecommenderSystem\UserLikeSimilarityRelationRepository;
use App\DB\EntityRepository\User\RecommenderSystem\UserRemixSimilarityRelationRepository;
use App\Project\CatrobatFile\CatrobatFileCompressor;
use App\Project\CatrobatFile\ExtractedFileRepository;
use App\Project\CatrobatFile\ProjectFileRepository;
use App\Project\ProjectManager;
use App\Storage\FileHelper;
use App\Studio\StudioManager;
use App\System\Testing\DataFixtures\ProjectDataFixtures;
use App\System\Testing\DataFixtures\UserDataFixtures;
use App\User\Achievements\AchievementManager;
use App\User\UserManager;
use Behat\Behat\Tester\Exception\PendingException;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use PHPUnit\Framework\Assert;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Router;

/**
 * Trait ContextTrait.
 *
 * Php only supports single inheritance, therefore we can't extend all our Context Classes from the same BaseContext.
 * Some Context Classes must extend the (Mink)BrowserContext. That's why we use this trait in all our Context
 * files to provide them with the same basic functionality.
 *
 * A trait is basically just a copy & paste and therefore every context uses its own instances.
 * Since some variables must exist only once, we have to set them to static members. (E.g. kernel_browser)
 */
trait ContextTrait
{
  private readonly KernelInterface $kernel;

  public string $ERROR_DIR;
  public string $FIXTURES_DIR;
  public string $SCREENSHOT_DIR;
  public string $MEDIA_PACKAGE_DIR = './tests/TestData/DataFixtures/MediaPackage/';
  public string $EXTRACT_RESOURCES_DIR;

  public function __construct(KernelInterface $kernel)
  {
    $this->kernel = $kernel;
    $this->ERROR_DIR = $this->getSymfonyParameterAsString('catrobat.testreports.behat');
    $this->SCREENSHOT_DIR = $this->getSymfonyParameterAsString('catrobat.testreports.screenshot');
    $this->FIXTURES_DIR = $this->getSymfonyParameterAsString('catrobat.test.directory.source');
    $this->MEDIA_PACKAGE_DIR = $this->FIXTURES_DIR.'MediaPackage/';
    $this->EXTRACT_RESOURCES_DIR = strval($this->getSymfonyParameterAsString('catrobat.file.extract.dir'));
  }

  public function getKernel(): KernelInterface
  {
    return $this->kernel;
  }

  public function getUserManager(): ?UserManager
  {
    return $this->kernel->getContainer()->get(UserManager::class);
  }

  public function getUserDataFixtures(): ?UserDataFixtures
  {
    return $this->kernel->getContainer()->get(UserDataFixtures::class);
  }

  public function getProjectManager(): ?ProjectManager
  {
    return $this->kernel->getContainer()->get(ProjectManager::class);
  }

  public function getProjectDataFixtures(): ?ProjectDataFixtures
  {
    return $this->kernel->getContainer()->get(ProjectDataFixtures::class);
  }

  public function getJwtManager(): ?JWTManager
  {
    return $this->kernel->getContainer()->get('lexik_jwt_authentication.jwt_manager');
  }

  public function getJwtEncoder(): ?JWTEncoderInterface
  {
    return $this->kernel->getContainer()->get('lexik_jwt_authentication.encoder');
  }

  public function getTagRepository(): ?TagRepository
  {
    return $this->kernel->getContainer()->get(TagRepository::class);
  }

  public function getExtensionRepository(): ?ExtensionRepository
  {
    return $this->kernel->getContainer()->get(ExtensionRepository::class);
  }

  public function getProjectRemixForwardRepository(): ?ProgramRemixRepository
  {
    return $this->kernel->getContainer()->get(ProgramRemixRepository::class);
  }

  public function getProjectRemixBackwardRepository(): ?ProgramRemixBackwardRepository
  {
    return $this->kernel->getContainer()->get(ProgramRemixBackwardRepository::class);
  }

  public function getScratchProjectRepository(): ?ScratchProgramRepository
  {
    return $this->kernel->getContainer()->get(ScratchProgramRepository::class);
  }

  public function getScratchProjectRemixRepository(): ?ScratchProgramRemixRepository
  {
    return $this->kernel->getContainer()->get(ScratchProgramRemixRepository::class);
  }

  public function getFileRepository(): ?ProjectFileRepository
  {
    return $this->kernel->getContainer()->get(ProjectFileRepository::class);
  }

  public function getExtractedFileRepository(): ?ExtractedFileRepository
  {
    return $this->kernel->getContainer()->get(ExtractedFileRepository::class);
  }

  public function getMediaPackageFileRepository(): ?MediaPackageFileRepository
  {
    return $this->kernel->getContainer()->get(MediaPackageFileRepository::class);
  }

  public function getUserLikeSimilarityRelationRepository(): ?UserLikeSimilarityRelationRepository
  {
    return $this->kernel->getContainer()->get(UserLikeSimilarityRelationRepository::class);
  }

  public function getUserRemixSimilarityRelationRepository(): ?UserRemixSimilarityRelationRepository
  {
    return $this->kernel->getContainer()->get(UserRemixSimilarityRelationRepository::class);
  }

  public function getCatroNotificationRepository(): ?NotificationRepository
  {
    return $this->kernel->getContainer()->get(NotificationRepository::class);
  }

  public function getFlavorRepository(): ?FlavorRepository
  {
    return $this->kernel->getContainer()->get(FlavorRepository::class);
  }

  public function getFeatureFlagManager(): ?FeatureFlagManager
  {
    return $this->kernel->getContainer()->get(FeatureFlagManager::class);
  }

  public function getManager(): ?EntityManagerInterface
  {
    return $this->kernel->getContainer()->get('doctrine')->getManager();
  }

  public function getRouter(): ?Router
  {
    return $this->kernel->getContainer()->get('router');
  }

  public function getAchievementManager(): ?AchievementManager
  {
    return $this->kernel->getContainer()->get(AchievementManager::class);
  }

  public function getCronJobRepository(): ?CronJobRepository
  {
    return $this->kernel->getContainer()->get(CronJobRepository::class);
  }

  public function getStudioManager(): ?StudioManager
  {
    return $this->kernel->getContainer()->get(StudioManager::class);
  }

  public function getSymfonyParameter(string $param): mixed
  {
    return $this->kernel->getContainer()->getParameter($param);
  }

  public function getSymfonyParameterAsString(string $param): string
  {
    return strval($this->kernel->getContainer()->getParameter($param));
  }

  /**
   * @throws \Exception
   */
  public function getSymfonyService(string $service_class): ?object
  {
    return $this->kernel->getContainer()->get($service_class);
  }

  public function getDefaultProjectFile(): string
  {
    $file = $this->FIXTURES_DIR.'/test.catrobat';
    Assert::assertTrue(is_file($file));

    return $file;
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
    if ($andFlush) {
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
    if ($andFlush) {
      $this->getManager()->flush();
    }

    return $relation;
  }

  /**
   * @throws \Exception
   */
  public function insertProjectLike(array $config = [], bool $andFlush = true): ProgramLike
  {
    $user_manager = $this->getUserManager();
    $project_manager = $this->getProjectManager();

    /** @var User|null $user */
    $user = $user_manager->findUserByUsername($config['username']);

    $project = $project_manager->find($config['project_id']);

    $project_like = new ProgramLike($project, $user, $config['type']);
    $project_like->setCreatedAt(new \DateTime($config['created at'], new \DateTimeZone('UTC')));

    $this->getManager()->persist($project_like);
    if ($andFlush) {
      $this->getManager()->flush();
    }

    return $project_like;
  }

  public function insertTag(array $config = [], bool $andFlush = true): Tag
  {
    $tag = new Tag();
    $tag->setInternalTitle($config['internal_title']);
    $tag->setTitleLtmCode($config['title_ltm_code'] ?? 'tag_ltm');
    $tag->setEnabled((bool) ($config['enabled'] ?? true));

    $this->getManager()->persist($tag);
    if ($andFlush) {
      $this->getManager()->flush();
    }

    return $tag;
  }

  public function insertExtension(array $config = [], bool $andFlush = true): Extension
  {
    $extension = new Extension();
    $extension->setInternalTitle($config['internal_title']);
    $extension->setTitleLtmCode($config['title_ltm_code'] ?? 'extension_ltm');
    $extension->setEnabled((bool) ($config['enabled'] ?? true));

    $this->getManager()->persist($extension);
    if ($andFlush) {
      $this->getManager()->flush();
    }

    return $extension;
  }

  public function insertForwardRemixRelation(array $config = [], bool $andFlush = true): ProgramRemixRelation
  {
    /** @var Program $ancestor */
    $ancestor = $this->getProjectManager()->find($config['ancestor_id']);

    /** @var Program $descendant */
    $descendant = $this->getProjectManager()->find($config['descendant_id']);

    $forward_relation = new ProgramRemixRelation($ancestor, $descendant, (int) $config['depth']);

    $this->getManager()->persist($forward_relation);
    if ($andFlush) {
      $this->getManager()->flush();
    }

    return $forward_relation;
  }

  public function insertBackwardRemixRelation(array $config = [], bool $andFlush = true): ProgramRemixBackwardRelation
  {
    /** @var Program $parent */
    $parent = $this->getProjectManager()->find($config['parent_id']);

    /** @var Program $child */
    $child = $this->getProjectManager()->find($config['child_id']);

    $backward_relation = new ProgramRemixBackwardRelation($parent, $child);

    $this->getManager()->persist($backward_relation);
    if ($andFlush) {
      $this->getManager()->flush();
    }

    return $backward_relation;
  }

  public function insertScratchRemixRelation(array $config = [], bool $andFlush = true): ScratchProgramRemixRelation
  {
    /** @var Program $catrobat_child */
    $catrobat_child = $this->getProjectManager()->find($config['catrobat_child_id']);

    $scratch_relation = new ScratchProgramRemixRelation(
      $config['scratch_parent_id'],
      $catrobat_child
    );

    $this->getManager()->persist($scratch_relation);
    if ($andFlush) {
      $this->getManager()->flush();
    }

    return $scratch_relation;
  }

  /**
   * @throws \Exception
   */
  public function insertProject(array $config, bool $andFlush = true): Program
  {
    return $this->getProjectDataFixtures()->insertProject($config, $andFlush);
  }

  public function assertProject(array $config = []): void
  {
    $this->getProjectDataFixtures()->assertProject($config);
  }

  public function insertFeaturedProject(array $config, bool $andFlush = true): FeaturedProgram
  {
    $new_flavor = [];
    $featured_project = new FeaturedProgram();

    /* @var Program $project */
    if (isset($config['project_id'])) {
      $project = $this->getProjectManager()->find($config['project_id']);
    } else {
      $project = $this->getProjectManager()->findOneByName($config['name']);
    }
    $featured_project->setProgram($project);

    /* @var Flavor $flavor */
    $flavor = $this->getFlavorRepository()->getFlavorByName($config['flavor'] ?? 'pocketcode');
    if (null == $flavor) {
      $new_flavor['name'] = $config['flavor'] ?? 'pocketcode';
      $flavor = $this->insertFlavor($new_flavor);
    }
    $featured_project->setFlavor($flavor);

    $featured_project->setUrl($config['url'] ?? null);
    $featured_project->setImageType($config['imagetype'] ?? 'jpg');
    $featured_project->setActive(isset($config['active']) && '1' === $config['active']);
    $featured_project->setPriority(isset($config['priority']) ? (int) $config['priority'] : 1);
    $featured_project->setForIos(isset($config['ios_only']) && 'yes' === $config['ios_only']);

    $this->getManager()->persist($featured_project);
    if ($andFlush) {
      $this->getManager()->flush();
    }

    return $featured_project;
  }

  public function insertExampleProject(array $config, bool $andFlush = true): ExampleProgram
  {
    $new_flavor = [];
    $example_project = new ExampleProgram();

    /* @var Program $project */
    if (isset($config['project_id'])) {
      $project = $this->getProjectManager()->find($config['project_id']);
      $example_project->setProgram($project);
    } else {
      $project = $this->getProjectManager()->findOneByName($config['name']);
      $example_project->setProgram($project);
    }

    /* @var Flavor $flavor */
    $flavor = $this->getFlavorRepository()->getFlavorByName($config['flavor'] ?? 'pocketcode');
    if (null == $flavor) {
      $new_flavor['name'] = $config['flavor'] ?? 'pocketcode';
      $flavor = $this->insertFlavor($new_flavor);
    }
    $example_project->setFlavor($flavor);

    $example_project->setImageType($config['imagetype'] ?? 'jpg');
    $example_project->setActive(isset($config['active']) && '1' === $config['active']);
    $example_project->setPriority(isset($config['priority']) ? (int) $config['priority'] : 1);
    $example_project->setForIos(isset($config['ios_only']) && 'yes' === $config['ios_only']);

    $this->getManager()->persist($example_project);
    if ($andFlush) {
      $this->getManager()->flush();
    }

    return $example_project;
  }

  /**
   * @throws \Exception
   */
  public function insertUserComment(array $config, bool $andFlush = true): UserComment
  {
    /** @var Program $project */
    $project = $this->getProjectManager()->find($config['project_id']);

    /** @var User|null $user */
    $user = $this->getUserManager()->find($config['user_id']);

    $parent_id = $config['parent_id'] ?? null;
    $parent_id = ('NULL' === $parent_id || is_null($parent_id)) ? null : intval($parent_id);
    $is_deleted = $config['is_deleted'] ?? false;
    $is_deleted = 'true' === $is_deleted;
    $new_comment = new UserComment();
    $new_comment->setUploadDate(isset($config['upload_date']) ?
      new \DateTime($config['upload_date'], new \DateTimeZone('UTC')) :
      new \DateTime('01.01.2013 12:00', new \DateTimeZone('UTC'))
    );
    $new_comment->setProgram($project);
    $new_comment->setUser($user);
    $new_comment->setParentId($parent_id);
    $new_comment->setIsDeleted($is_deleted);
    $new_comment->setUsername($user->getUserIdentifier());
    $new_comment->setIsReported((bool) ($config['reported'] ?? false));
    $new_comment->setText($config['text']);

    $this->getManager()->persist($new_comment);

    if (isset($config['id'])) {
      // overwrite id if desired
      $new_comment->setId((int) $config['id']);
      $this->getManager()->persist($new_comment);
    }

    if ($andFlush) {
      $this->getManager()->flush();
    }

    return $new_comment;
  }

  /**
   * @throws \Exception
   */
  public function insertProjectReport(array $config, bool $andFlush = true): ProgramInappropriateReport
  {
    /** @var Program $project */
    $project = $this->getProjectManager()->find($config['project_id']);

    /** @var User|null $user */
    $user = $this->getUserManager()->find($config['user_id']);

    $new_report = new ProgramInappropriateReport();
    $new_report->setCategory($config['category']);
    $new_report->setProgram($project);
    $new_report->setReportingUser($user);
    $new_report->setTime(new \DateTime($config['time'], new \DateTimeZone('UTC')));
    $new_report->setNote($config['note']);
    $this->getManager()->persist($new_report);

    if ($andFlush) {
      $this->getManager()->flush();
    }

    return $new_report;
  }

  /**
   * @throws \Exception
   *
   * @psalm-suppress UndefinedPropertyAssignment
   */
  public function generateProjectFileWith(mixed $parameters, mixed $is_embroidery = false): string
  {
    $filesystem = new Filesystem();
    FileHelper::emptyDirectory(sys_get_temp_dir().'/project_generated/');
    $new_project_dir = sys_get_temp_dir().'/project_generated/';

    if ($is_embroidery) {
      $filesystem->mirror($this->FIXTURES_DIR.'/GeneratedFixtures/embroidery', $new_project_dir);
    } else {
      $filesystem->mirror($this->FIXTURES_DIR.'/GeneratedFixtures/base', $new_project_dir);
    }
    $properties = simplexml_load_file($new_project_dir.'/code.xml');
    if (!$properties) {
      throw new \Exception("Can't load code.xml file");
    }

    foreach ($parameters as $name => $value) {
      switch ($name) {
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

    $file_overwritten = $properties->asXML($new_project_dir.'/code.xml');
    if (!$file_overwritten) {
      throw new \Exception("Can't overwrite code.xml file");
    }

    $compressor = new CatrobatFileCompressor();

    return $compressor->compress($new_project_dir, sys_get_temp_dir().'/', 'project_generated');
  }

  public function getStandardProjectFile(): UploadedFile
  {
    $filepath = $this->FIXTURES_DIR.'test.catrobat';
    Assert::assertTrue(file_exists($filepath), 'File not found');

    return new UploadedFile($filepath, 'test.catrobat');
  }

  /**
   * @throws \JsonException
   */
  public function assertJsonRegex(string $pattern, string $json): void
  {
    // allows to compare strings using a regex wildcard (.*?)
    $pattern = json_encode(json_decode($pattern, false, 512, JSON_THROW_ON_ERROR), JSON_THROW_ON_ERROR); // reformat string

    if (is_countable(json_decode($pattern, null, 512, JSON_THROW_ON_ERROR))) {
      Assert::assertEquals(count(json_decode($pattern, null, 512, JSON_THROW_ON_ERROR)), count(json_decode($json, null, 512, JSON_THROW_ON_ERROR)));
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

    $delimiter = '#';
    try {
      $json = json_encode(json_decode($json, false, 512, JSON_THROW_ON_ERROR), JSON_THROW_ON_ERROR);
      Assert::assertMatchesRegularExpression($delimiter.$pattern.$delimiter, $json);
    } catch (\Exception) {
      $delimiter = '~';
      $json = json_encode(json_decode($json, false, 512, JSON_THROW_ON_ERROR), JSON_THROW_ON_ERROR);
      Assert::assertMatchesRegularExpression($delimiter.$pattern.$delimiter, $json);
    }
  }

  public function insertFlavor(array $config = [], bool $andFlush = true): Flavor
  {
    $flavor = new Flavor();
    $flavor->setName($config['name']);

    $this->getManager()->persist($flavor);
    if ($andFlush) {
      $this->getManager()->flush();
    }

    return $flavor;
  }

  public function getTempCopy(mixed $path): bool|string
  {
    $temp_path = tempnam(sys_get_temp_dir(), 'apktest');
    copy($path, $temp_path);

    return $temp_path;
  }
}
