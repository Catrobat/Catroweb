<?php

namespace App\Catrobat\Services\TestEnv\DataFixtures;

use App\Catrobat\Services\ApkRepository;
use App\Catrobat\Services\ProgramFileRepository;
use App\Entity\Extension;
use App\Entity\Program;
use App\Entity\ProgramManager;
use App\Entity\Tag;
use App\Entity\User;
use App\Entity\UserManager;
use App\Utils\MyUuidGenerator;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class ProjectDataFixtures.
 *
 * Use this class in the test environment to easily create new projects in the database.
 */
class ProjectDataFixtures
{
  /**
   * @var string
   */
  private $FIXTURE_DIR;

  /**
   * @var ProgramManager
   */
  private $project_manager;

  /**
   * @var EntityManager
   */
  private $entity_manager;

  /**
   * @var UserManager
   */
  private $user_manager;

  /**
   * @var UserManager
   */
  private $apk_repository;

  /**
   * @var UserManager
   */
  private $project_file_repository;

  /**
   * @var int
   */
  private static $number_of_projects = 0;

  /**
   * @var UserDataFixtures
   */
  private $user_data_fixtures;

  /**
   * ProjectDataFixtures constructor.
   */
  public function __construct(ProgramManager $project_manager, UserManager $user_manager,
                              EntityManagerInterface $entity_manager, ProgramFileRepository $project_file_repository,
                              ApkRepository $apk_repository, UserDataFixtures $user_data_fixtures,
                              ParameterBagInterface $parameter_bag)
  {
    $this->project_manager = $project_manager;
    $this->user_manager = $user_manager;
    $this->entity_manager = $entity_manager;
    $this->project_file_repository = $project_file_repository;
    $this->apk_repository = $apk_repository;
    $this->user_data_fixtures = $user_data_fixtures;
    $this->FIXTURE_DIR = $parameter_bag->get('catrobat.test.directory.source');
  }

  /**
   * @throws Exception
   */
  public function insertProject(array $config, bool $andFlush = true): Program
  {
    ++ProjectDataFixtures::$number_of_projects;

    /** @var User $user */
    // get user before setting the fixing the next id, else it might get used for the default user
    $user = isset($config['owned by']) ?
      $this->user_manager->findUserByUsername($config['owned by']) : $this->user_data_fixtures->getDefaultUser();

    if (array_key_exists('id', $config))
    {
      // use a fixed ID
      MyUuidGenerator::setNextValue($config['id']);
    }

    $project = new Program();
    $project->setUser($user);

    $project->setName(isset($config['name']) ?
      $config['name'] : 'Project '.ProjectDataFixtures::$number_of_projects);
    $project->setDescription(isset($config['description']) ? $config['description'] : '');
    $project->setViews(isset($config['views']) ? (int) $config['views'] : 0);
    $project->setDownloads(isset($config['downloads']) ? (int) $config['downloads'] : 0);
    $project->setApkDownloads(isset($config['apk_downloads']) ? (int) $config['apk_downloads'] : 0);
    if (isset($config['apk_status']) && 'ready' === $config['apk_status']
      || (isset($config['apk_ready']) && 'true' === $config['apk_ready']))
    {
      $project->setApkStatus(Program::APK_READY);
    }
    elseif (isset($config['apk_status']) && 'pending' === $config['apk_status'])
    {
      $project->setApkStatus(Program::APK_PENDING);
    }
    else
    {
      $project->setApkStatus(Program::APK_NONE);
    }
    $project->setUploadedAt(
      isset($config['upload time']) ?
        new DateTime($config['upload time'], new DateTimeZone('UTC')) :
        new DateTime('01.01.2013 12:00', new DateTimeZone('UTC'))
    );
    $project->setRemixMigratedAt(null);
    $project->setCatrobatVersion(1);
    $project->setCatrobatVersionName(isset($config['version']) ? $config['version'] : '');
    $project->setLanguageVersion(isset($config['language version']) ? $config['language version'] : 1);
    $project->setUploadIp(isset($config['upload_ip']) ? $config['upload_ip'] : '127.0.0.1');
    $project->setFilesize(isset($config['file_size']) ? $config['file_size'] : 0);
    $project->setVisible(isset($config['visible']) ? 'true' === $config['visible'] : true);
    $project->setUploadLanguage(isset($config['upload_language']) ? $config['upload_language'] : 'en');
    $project->setApproved(isset($config['approved']) ? 'true' === $config['approved'] : false);
    $project->setRemixRoot(isset($config['remix_root']) ? 'true' === $config['remix_root'] : true);
    $project->setPrivate(isset($config['private']) ? 'true' === $config['private'] : false);
    $project->setDebugBuild(isset($config['debug']) ? 'true' === $config['debug'] : false);
    $project->setFlavor(isset($config['flavor']) ? $config['flavor'] : 'pocketcode');
    $project->setDirectoryHash(isset($config['directory_hash']) ? $config['directory_hash'] : null);

    $project->setAcceptedForGameJam(isset($config['accepted']) ? $config['accepted'] : false);
    $project->setGamejam(isset($config['gamejam']) ? $config['gamejam'] : null);

    $this->entity_manager->persist($project);

    if (isset($config['tags_id']) && null !== $config['tags_id'])
    {
      $tag_repo = $this->entity_manager->getRepository(Tag::class);
      $tags = explode(',', $config['tags_id']);
      foreach ($tags as $tag_id)
      {
        /** @var Tag $tag */
        $tag = $tag_repo->find($tag_id);
        $project->addTag($tag);
      }
    }

    if (isset($config['extensions']) && '' !== $config['extensions'])
    {
      $extension_repo = $this->entity_manager->getRepository(Extension::class);
      $extensions = explode(',', $config['extensions']);
      foreach ($extensions as $extension_name)
      {
        /** @var Extension $extension */
        $extension = $extension_repo->findOneBy(['name' => $extension_name]);
        $project->addExtension($extension);
      }
    }

    if (Program::APK_READY === $project->getApkStatus())
    {
      $temp_path = tempnam(sys_get_temp_dir(), 'apktest');
      copy($this->FIXTURE_DIR.'test.catrobat', $temp_path);
      $this->apk_repository->save(new File($temp_path), $project->getId());
      $this->project_file_repository->saveProgramfile(
        new File($this->FIXTURE_DIR.'test.catrobat'), $project->getId()
      );
    }

    if ($andFlush)
    {
      $this->entity_manager->flush();
    }

    return $project;
  }

  public static function clear()
  {
    ProjectDataFixtures::$number_of_projects = 0;
  }
}
