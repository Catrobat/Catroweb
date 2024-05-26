<?php

declare(strict_types=1);

namespace App\System\Testing\DataFixtures;

use App\DB\Entity\Flavor;
use App\DB\Entity\Project\Extension;
use App\DB\Entity\Project\Program;
use App\DB\Entity\Project\Tag;
use App\DB\Entity\User\User;
use App\DB\Generator\MyUuidGenerator;
use App\Project\Apk\ApkRepository;
use App\Project\CatrobatFile\ProjectFileRepository;
use App\Project\ProjectManager;
use App\Storage\FileHelper;
use App\User\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Assert;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class ProjectDataFixtures.
 *
 * Use this class in the test environment to easily create new projects in the database.
 */
class ProjectDataFixtures
{
  private readonly string $FIXTURE_DIR;

  private readonly string $GENERATED_FIXTURE_DIR;

  private readonly string $EXTRACT_DIR;

  private static int $number_of_projects = 0;

  public function __construct(private readonly UserManager $user_manager, private readonly ProjectManager $project_manager,
    private readonly EntityManagerInterface $entity_manager, private readonly ProjectFileRepository $project_file_repository,
    private readonly ApkRepository $apk_repository, private readonly UserDataFixtures $user_data_fixtures,
    ParameterBagInterface $parameter_bag)
  {
    $this->FIXTURE_DIR = (string) $parameter_bag->get('catrobat.test.directory.source');
    $this->GENERATED_FIXTURE_DIR = (string) $parameter_bag->get('catrobat.test.directory.target');
    $this->EXTRACT_DIR = (string) $parameter_bag->get('catrobat.file.extract.dir');
    FileHelper::verifyDirectoryExists($this->FIXTURE_DIR);
    FileHelper::verifyDirectoryExists($this->EXTRACT_DIR);
  }

  /**
   * @throws \Exception
   */
  public function insertProject(array $config, bool $andFlush = true): Program
  {
    ++ProjectDataFixtures::$number_of_projects;

    // get user before setting the fixing the next id, else it might get used for the default user
    /** @var User|null $user */
    $user = isset($config['owned by']) ?
      $this->user_manager->findUserByUsername($config['owned by']) : $this->user_data_fixtures->getDefaultUser();

    if (array_key_exists('id', $config)) {
      // use a fixed ID
      MyUuidGenerator::setNextValue($config['id']);
    }

    $project = new Program();
    $project->setUser($user);

    $project->setName($config['name'] ?? 'Project '.ProjectDataFixtures::$number_of_projects);
    $project->setDescription($config['description'] ?? '');
    $project->setCredits($config['credit'] ?? '');
    $project->setScratchId((isset($config['scratch_id']) && 0 != (int) $config['scratch_id']) ? (int) $config['scratch_id'] : null);
    $project->setViews(isset($config['views']) ? (int) $config['views'] : 0);
    $project->setDownloads(isset($config['downloads']) ? (int) $config['downloads'] : 0);
    $project->setApkDownloads(isset($config['apk_downloads']) ? (int) $config['apk_downloads'] : 0);
    if (isset($config['apk_status']) && 'ready' === $config['apk_status']
      || (isset($config['apk_ready']) && 'true' === $config['apk_ready'])) {
      $project->setApkStatus(Program::APK_READY);
    } elseif (isset($config['apk_status']) && 'pending' === $config['apk_status']) {
      $project->setApkStatus(Program::APK_PENDING);
    } else {
      $project->setApkStatus(Program::APK_NONE);
    }

    $project->setUploadedAt(
      isset($config['upload time']) ?
        new \DateTime($config['upload time'], new \DateTimeZone('UTC')) :
        new \DateTime('01.01.2013 12:00', new \DateTimeZone('UTC'))
    );
    $project->setRemixMigratedAt(null);
    $project->setCatrobatVersionName($config['version'] ?? '0.8.5');
    $project->setLanguageVersion($config['language version'] ?? '0.925');
    $project->setUploadIp($config['upload_ip'] ?? '127.0.0.1');
    $project->setFilesize((int) ($config['file_size'] ?? 0));
    $project->setVisible(!isset($config['visible']) || 'true' === $config['visible']);
    $project->setUploadLanguage($config['upload_language'] ?? 'en');
    $project->setApproved(isset($config['approved']) && 'true' === $config['approved']);
    $project->setRemixRoot(!isset($config['remix_root']) || 'true' === $config['remix_root']);
    $project->setPrivate(isset($config['private']) && 'true' === $config['private']);
    $project->setDebugBuild(isset($config['debug']) && 'true' === $config['debug']);
    $project->setFlavor($config['flavor'] ?? Flavor::POCKETCODE);
    $project->setRand((int) ($config['rand'] ?? 0));
    $project->setPopularity((float) ($config['popularity'] ?? 0));
    $project->setNotForKids((int) ($config['not_for_kids'] ?? 0));

    if (isset($config['apk request time'])) {
      $project->setApkRequestTime(new \DateTime($config['apk request time'], new \DateTimeZone('UTC')));
    }

    $this->entity_manager->persist($project);

    if (!empty($config['tags'])) {
      $tag_repo = $this->entity_manager->getRepository(Tag::class);
      $arr_tag_internal_title = explode(',', (string) $config['tags']);
      foreach ($arr_tag_internal_title as $internal_title) {
        /** @var Tag $tag */
        $tag = $tag_repo->findOneBy(['internal_title' => trim($internal_title)]);
        $project->addTag($tag);
      }
    }

    if (isset($config['extensions']) && '' !== $config['extensions']) {
      $extension_repo = $this->entity_manager->getRepository(Extension::class);
      $arr_extension_internal_title = explode(',', (string) $config['extensions']);
      foreach ($arr_extension_internal_title as $internal_title) {
        /** @var Extension $extension */
        $extension = $extension_repo->findOneBy(['internal_title' => $internal_title]);
        $project->addExtension($extension);
      }
    }

    if (Program::APK_READY === $project->getApkStatus()) {
      $temp_path = tempnam(sys_get_temp_dir(), 'apktest');
      copy($this->FIXTURE_DIR.'test.catrobat', $temp_path);
      $this->apk_repository->save(new File($temp_path), $project->getId());
      $this->project_file_repository->saveProjectZipFile(
        new File($this->FIXTURE_DIR.'test.catrobat'), $project->getId()
      );
    }

    if ($andFlush) {
      $this->entity_manager->flush();
    }

    // Every project should have project files
    FileHelper::copyDirectory($this->GENERATED_FIXTURE_DIR.'base', $this->EXTRACT_DIR.$project->getId());
    FileHelper::setDirectoryPermissionsRecursive($this->EXTRACT_DIR.$project->getId(), 0777);

    return $project;
  }

  public function assertProject(array $config = []): void
  {
    Assert::assertNotNull($config['id'], 'Project ID needs to be specified.');
    $project = $this->project_manager->find($config['id']);
    Assert::assertNotNull($project, 'Project with id '.$config['id'].' not found.');

    if (isset($config['name'])) {
      Assert::assertEquals($config['name'], $project->getName(), 'Project name wrong.');
    }

    if (isset($config['author'])) {
      $author = $project->getUser() ? $project->getUser()->getUserIdentifier() : 'null';
      Assert::assertEquals($config['author'], $author, 'Project author wrong.');
    }

    if (isset($config['description'])) {
      Assert::assertEquals($config['description'], $project->getDescription(), 'Project description wrong.');
    }

    if (isset($config['credits'])) {
      Assert::assertEquals($config['credits'], $project->getCredits(), 'Project credits wrong.');
    }

    if (isset($config['version'])) {
      Assert::assertEquals($config['version'], $project->getCatrobatVersionName(), 'Project version wrong.');
    }

    if (isset($config['views'])) {
      Assert::assertEquals($config['views'], $project->getViews(), 'Project view count wrong.');
    }

    if (isset($config['downloads'])) {
      Assert::assertEquals($config['downloads'], $project->getDownloads(), 'Project download count wrong.');
    }

    if (isset($config['reactions'])) {
      Assert::assertEquals($config['reactions'], $project->getLikes()->count(), 'Project reaction count wrong.');
    }

    if (isset($config['comments'])) {
      Assert::assertEquals($config['comments'], $project->getComments()->count(), 'Project comment count wrong.');
    }

    if (isset($config['private'])) {
      $private = 'true' === strtolower((string) $config['private']);
      Assert::assertEquals($private, $project->getPrivate(), 'Project private flag wrong.');
    }

    if (isset($config['visible'])) {
      $visible = 'true' === strtolower((string) $config['visible']);
      Assert::assertEquals($visible, $project->getVisible(), 'Project visible flag wrong.');
    }

    if (isset($config['flavor'])) {
      Assert::assertEquals($config['flavor'], $project->getFlavor(), 'Project flavor wrong.');
    }
  }

  public static function clear(): void
  {
    ProjectDataFixtures::$number_of_projects = 0;
  }
}
