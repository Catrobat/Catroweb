<?php

namespace App\Entity;

use App\Repository\ProgramLikeRepository;
use App\Repository\ProgramRepository;
use App\Repository\TagRepository;
use App\Catrobat\Events\InvalidProgramUploadedEvent;
use App\Catrobat\Events\ProgramAfterInsertEvent;
use App\Catrobat\Exceptions\InvalidCatrobatFileException;
use App\Catrobat\Requests\AddProgramRequest;
use App\Catrobat\Services\CatrobatFileExtractor;
use App\Catrobat\Services\ExtractedCatrobatFile;
use App\Catrobat\Services\ProgramFileRepository;
use App\Catrobat\Services\ScreenshotRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Catrobat\Events\ProgramBeforeInsertEvent;
use App\Catrobat\Events\ProgramInsertEvent;
use App\Catrobat\Events\ProgramBeforePersistEvent;
use Symfony\Component\Filesystem\Exception\IOException;


/**
 * Class ProgramManager
 * @package App\Entity
 */
class ProgramManager
{

  /**
   * @var CatrobatFileExtractor
   */
  protected $file_extractor;

  /**
   * @var ProgramFileRepository
   */
  protected $file_repository;

  /**
   * @var ScreenshotRepository
   */
  protected $screenshot_repository;

  /**
   * @var EventDispatcherInterface
   */
  protected $event_dispatcher;

  /**
   * @var EntityManager
   */
  protected $entity_manager;

  /**
   * @var ProgramRepository
   */
  protected $program_repository;

  /**
   * @var
   */
  protected $pagination;

  /**
   * @var TagRepository
   */
  protected $tag_repository;

  /**
   * @var ProgramLikeRepository
   */
  protected $program_like_repository;

  /**
   * @var int
   */
  protected $max_version;


  /**
   * ProgramManager constructor.
   *
   * @param CatrobatFileExtractor    $file_extractor
   * @param ProgramFileRepository    $file_repository
   * @param ScreenshotRepository     $screenshot_repository
   * @param EntityManager            $entity_manager
   * @param ProgramRepository        $program_repository
   * @param TagRepository            $tag_repository
   * @param ProgramLikeRepository    $program_like_repository
   * @param EventDispatcherInterface $event_dispatcher
   * @param                          $max_version
   */
  public function __construct(CatrobatFileExtractor $file_extractor, ProgramFileRepository $file_repository,
                              ScreenshotRepository $screenshot_repository, EntityManager $entity_manager,
                              ProgramRepository $program_repository, TagRepository $tag_repository,
                              ProgramLikeRepository $program_like_repository,
                              EventDispatcherInterface $event_dispatcher, $max_version=0)
  {
    $this->file_extractor = $file_extractor;
    $this->event_dispatcher = $event_dispatcher;
    $this->file_repository = $file_repository;
    $this->screenshot_repository = $screenshot_repository;
    $this->entity_manager = $entity_manager;
    $this->program_repository = $program_repository;
    $this->tag_repository = $tag_repository;
    $this->program_like_repository = $program_like_repository;
    $this->max_version = $max_version;
  }


  /**
   * @param AddProgramRequest $request
   *
   * @return Program|null
   * @throws \Exception
   */
  public function addProgram(AddProgramRequest $request)
  {
    $file = $request->getProgramfile();

    $extracted_file = $this->file_extractor->extract($file);
    try
    {
      $event = $this->event_dispatcher->dispatch('catrobat.program.before', new ProgramBeforeInsertEvent($extracted_file));
    } catch (InvalidCatrobatFileException $e)
    {
      $this->event_dispatcher->dispatch('catrobat.program.invalid.upload', new InvalidProgramUploadedEvent($file, $e));
      throw $e;
    }

    if ($event->isPropagationStopped())
    {
      return null;
    }

    $old_program = $this->findOneByNameAndUser($extracted_file->getName(), $request->getUser());
    if ($old_program != null)
    {
      $program = $old_program;
      $this->removeAllTags($program);
      // it's an update
      $program->incrementVersion();
    }
    else
    {
      $program = new Program();
      $program->setRemixRoot(true);
    }
    $program->setName($extracted_file->getName());
    $program->setDescription($extracted_file->getDescription());
    $program->setUser($request->getUser());
    $program->setCatrobatVersion(1);
    $program->setCatrobatVersionName($extracted_file->getApplicationVersion());
    $program->setLanguageVersion($extracted_file->getLanguageVersion());
    $program->setUploadIp($request->getIp());
    $program->setFilesize($file->getSize());
    $program->setVisible(true);
    $program->setApproved(false);
    $program->setUploadLanguage('en');
    $program->setUploadedAt(new \DateTime());
    $program->setRemixMigratedAt(null);
    $program->setFlavor($request->getFlavor());
    $this->addTags($program, $extracted_file, $request->getLanguage());

    $version = $program->getLanguageVersion();

    $max_version = $this->max_version;
    if (version_compare($version, $max_version, ">"))
    {
      $program->setPrivate(true);
    }

    if ($request->getGamejam() != null)
    {
      $program->setGamejam($request->getGamejam());
      $program->setGameJamSubmissionDate(new \DateTime());
    }

    $this->event_dispatcher->dispatch('catrobat.program.before.persist', new ProgramBeforePersistEvent($extracted_file, $program));

    $this->entity_manager->persist($program);
    $this->entity_manager->flush();
    $this->entity_manager->refresh($program);

    $this->event_dispatcher->dispatch('catrobat.program.after.insert', new ProgramAfterInsertEvent($extracted_file, $program));


    try
    {
      if ($extracted_file->getScreenshotPath() == null)
      {
        // Todo: maybe for later implementations
      }
      else
      {
        $this->screenshot_repository->saveProgramAssetsTemp($extracted_file->getScreenshotPath(), $program->getId());
      }

      $this->file_repository->saveProgramTemp($extracted_file, $program->getId());
    } catch (\Exception $e)
    {
      $program_id = $program->getId();
      $this->entity_manager->remove($program);
      $this->entity_manager->flush();
      try
      {
        $this->screenshot_repository->deleteTempFilesForProgram($program_id);
      } catch (IOException $error)
      {

        throw $error;
      }

      return null;
    }

    try
    {
      if ($extracted_file->getScreenshotPath() == null)
      {
        // Todo: maybe for later implementations
      }
      else
      {
        $this->screenshot_repository->makeTempProgramAssetsPerm($program->getId());
      }
      $this->file_repository->makeTempProgramPerm($program->getId());
    } catch (\Exception $e)
    {
      $program_id = $program->getId();
      $this->entity_manager->remove($program);
      $this->entity_manager->flush();
      try
      {
        $this->screenshot_repository->deletePermProgramAssets($program_id);
        $this->file_repository->deleteProgramFile($program_id);
      } catch (IOException $error)
      {
        throw $error;
      }

      return null;
    }

    $this->entity_manager->persist($program);
    $this->entity_manager->flush();
    $this->entity_manager->refresh($program);

    $this->event_dispatcher->dispatch('catrobat.program.successful.upload', new ProgramInsertEvent());

    return $program;
  }

  /**
   * @param $program_id
   * @param $user_id
   *
   * @return mixed
   */
  public function findUserLike($program_id, $user_id)
  {
    return $this->program_like_repository->findOneBy(['program_id' => $program_id, 'user_id' => $user_id]);
  }

  /**
   * @param Program $program
   * @param User    $user
   * @param         $type
   * @param         $no_unlike
   *
   * @return int
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function toggleLike(Program $program, User $user, $type, $no_unlike)
  {
    $existing_program_like = $this->program_like_repository->findOneBy(['program_id' => $program->getId(), 'user_id' => $user->getId()]);
    if ($existing_program_like != null)
    {
      $existing_program_like_type = $existing_program_like->getType();
      $this->entity_manager->remove($existing_program_like);

      // case: unlike
      if ($existing_program_like_type == $type)
      {
        if ($no_unlike)
        {
          return $type;
        }
        $this->entity_manager->flush();

        return ProgramLike::TYPE_NONE;
      }
    }

    // case: like
    $program_like = new ProgramLike($program, $user, $type);
    $this->entity_manager->persist($program_like);
    $this->entity_manager->flush();

    return $type;
  }

  /**
   * @param $program_id
   * @param $type
   *
   * @return mixed
   */
  public function likeTypeCount($program_id, $type)
  {
    return $this->program_like_repository->likeTypeCount($program_id, $type);
  }

  /**
   * @param $program_id
   *
   * @return mixed
   */
  public function totalLikeCount($program_id)
  {
    return $this->program_like_repository->totalLikeCount($program_id);
  }

  /**
   * @param $program Program
   * @param $extracted_file ExtractedCatrobatFile
   * @param $language
   */
  public function addTags($program, $extracted_file, $language)
  {
    /**
     * @var $db_tag Tag
     */
    $metadata = $this->entity_manager->getClassMetadata(Tag::class)->getFieldNames();

    if (!in_array($language, $metadata))
    {
      $language = 'en';
    }

    $tags = $extracted_file->getTags();

    if (!empty($tags))
    {
      $i = 0;
      foreach ($tags as $tag)
      {
        $db_tag = $this->tag_repository->findOneBy([$language => $tag]);

        if ($db_tag != null)
        {
          $program->addTag($db_tag);
          $i++;
        }

        if ($i == 3)
        {
          break;
        }
      }
    }
  }

  /**
   * @param $program
   */
  public function removeAllTags($program)
  {
    /* @var $program Program */
    $tags = $program->getTags();
    if ($tags == null)
    {
      return;
    }

    foreach ($tags as $tag)
      $program->removeTag($tag);
  }

  /**
   *
   */
  public function markAllProgramsAsNotYetMigrated()
  {
    $this->program_repository->markAllProgramsAsNotYetMigrated();
  }

  /**
   * @param $program_name
   * @param $user
   *
   * @return object|null
   */
  public function findOneByNameAndUser($program_name, $user)
  {
    return $this->program_repository->findOneBy([
      'name' => $program_name,
      'user' => $user,
    ]);
  }

  /**
   * @param $programName
   *
   * @return mixed
   */
  public function findOneByName($programName)
  {
    return $this->program_repository->findOneBy(["name" => $programName]);
  }

  /**
   * @param $array
   *
   * @return array|Program[]|StarterCategory[]
   */
  public function findBy($array)
  {
    return $this->program_repository->findBy($array);
  }

  /**
   * @param     $user_id
   * @param int $max_version
   *
   * @return mixed
   */
  public function getUserPrograms($user_id, $max_version = 0)
  {
    return $this->program_repository->getUserPrograms($user_id, $max_version);
  }

  /**
   * @return array
   */
  public function findAll()
  {
    return $this->program_repository->findAll();
  }

  /**
   * @param $previous_program_id
   *
   * @return mixed
   * @throws \Doctrine\ORM\NonUniqueResultException
   */
  public function findNext($previous_program_id)
  {
    return $this->program_repository->findNext($previous_program_id);
  }

  /**
   * @param $id
   *
   * @return object|null
   */
  public function find($id)
  {
    return $this->program_repository->find($id);
  }

  /**
   * @param $apk_status
   *
   * @return mixed
   */
  public function getProgramsWithApkStatus($apk_status)
  {
    return $this->program_repository->getProgramsWithApkStatus($apk_status);
  }

  /**
   * @return mixed
   */
  public function getProgramsWithExtractedDirectoryHash()
  {
    return $this->program_repository->getProgramsWithExtractedDirectoryHash();
  }

  /**
   * @param      $flavor
   * @param null $limit
   * @param null $offset
   * @param int  $max_version
   *
   * @return mixed
   */
  public function getRecentPrograms($flavor, $limit = null, $offset = null, $max_version = 0)
  {
    return $this->program_repository->getRecentPrograms($flavor, $limit, $offset, $max_version);
  }

  /**
   * @param      $flavor
   * @param null $limit
   * @param null $offset
   * @param int  $max_version
   *
   * @return mixed
   */
  public function getMostViewedPrograms($flavor, $limit = null, $offset = null, $max_version = 0)
  {
    return $this->program_repository->getMostViewedPrograms($flavor, $limit, $offset, $max_version);
  }

  /**
   * @param      $flavor
   * @param null $limit
   * @param null $offset
   * @param int  $max_version
   *
   * @return mixed
   */
  public function getMostDownloadedPrograms($flavor, $limit = null, $offset = null, $max_version = 0)
  {
    return $this->program_repository->getMostDownloadedPrograms($flavor, $limit, $offset, $max_version);
  }

  /**
   * @param      $flavor
   * @param null $limit
   * @param null $offset
   * @param int  $max_version
   *
   * @return array
   */
  public function getRandomPrograms($flavor, $limit = null, $offset = null, $max_version = 0)
  {
    return $this->program_repository->getRandomPrograms($flavor, $limit, $offset, $max_version);
  }

  /**
   * @param     $query
   * @param int $limit
   * @param int $offset
   * @param int $max_version
   *
   * @return array
   */
  public function search($query, $limit = 10, $offset = 0, $max_version = 0)
  {
    return $this->program_repository->search($query, $limit, $offset, $max_version);
  }

  /**
   * @param     $query
   * @param int $max_version
   *
   * @return int
   */
  public function searchCount($query, $max_version = 0)
  {
    return $this->program_repository->searchCount($query, $max_version);
  }

  /**
   * @param $user_id
   *
   * @return mixed
   */
  public function searchCountUserPrograms($user_id)
  {
    return $this->program_repository->searchCountUserPrograms($user_id);
  }

  /**
   * @param     $flavor
   * @param int $max_version
   *
   * @return mixed
   * @throws \Doctrine\ORM\NonUniqueResultException
   */
  public function getTotalPrograms($flavor, $max_version = 0)
  {
    return $this->program_repository->getTotalPrograms($flavor, $max_version);
  }

  /**
   * @param Program $program
   *
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function increaseViews(Program $program)
  {
    $program->setViews($program->getViews() + 1);
    $this->save($program);
  }

  /**
   * @param Program $program
   *
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function increaseDownloads(Program $program)
  {
    $program->setDownloads($program->getDownloads() + 1);
    $this->save($program);
  }

  /**
   * @param Program $program
   *
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function increaseApkDownloads(Program $program)
  {
    $program->setApkDownloads($program->getApkDownloads() + 1);
    $this->save($program);
  }

  /**
   * @param Program $program
   *
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function save(Program $program)
  {
    $this->entity_manager->persist($program);
    $this->entity_manager->flush();
  }

  /**
   * @param $id
   * @param $limit
   * @param $offset
   *
   * @return mixed
   */
  public function getProgramsByTagId($id, $limit, $offset)
  {
    return $this->program_repository->getProgramsByTagId($id, $limit, $offset);
  }

  /**
   * @param $name
   * @param $limit
   * @param $offset
   *
   * @return mixed
   */
  public function getProgramsByExtensionName($name, $limit, $offset)
  {
    return $this->program_repository->getProgramsByExtensionName($name, $limit, $offset);
  }

  /**
   * @param $query
   *
   * @return int
   */
  public function searchTagCount($query)
  {
    return $this->program_repository->searchTagCount($query);
  }

  /**
   * @param $query
   *
   * @return int
   */
  public function searchExtensionCount($query)
  {
    return $this->program_repository->searchExtensionCount($query);
  }

  /**
   * @param $id
   * @param $flavor
   * @param $limit
   * @param $offset
   *
   * @return array
   */
  public function getRecommendedProgramsById($id, $flavor, $limit, $offset)
  {
    return $this->program_repository->getRecommendedProgramsById($id, $flavor, $limit, $offset);
  }

  /**
   * @param $id
   * @param $flavor
   *
   * @return int
   */
  public function getRecommendedProgramsCount($id, $flavor)
  {
    return $this->program_repository->getRecommendedProgramsCount($id, $flavor);
  }

  /**
   * @param $flavor
   * @param $limit
   * @param $offset
   *
   * @return array
   * @throws \Doctrine\DBAL\DBALException
   */
  public function getMostRemixedPrograms($flavor, $limit, $offset)
  {
    return $this->program_repository->getMostRemixedPrograms($flavor, $limit, $offset);
  }

  /**
   * @param $flavor
   *
   * @return int
   * @throws \Doctrine\DBAL\DBALException
   */
  public function getTotalRemixedProgramsCount($flavor)
  {
    return $this->program_repository->getTotalRemixedProgramsCount($flavor);
  }

  /**
   * @param $flavor
   * @param $limit
   * @param $offset
   *
   * @return array
   */
  public function getMostLikedPrograms($flavor, $limit, $offset)
  {
    return $this->program_repository->getMostLikedPrograms($flavor, $limit, $offset);
  }

  /**
   * @param $flavor
   *
   * @return int
   */
  public function getTotalLikedProgramsCount($flavor)
  {
    return $this->program_repository->getTotalLikedProgramsCount($flavor);
  }

  /**
   * @param $flavor
   * @param $program
   * @param $limit
   * @param $offset
   * @param $is_test_environment
   *
   * @return array
   */
  public function getOtherMostDownloadedProgramsOfUsersThatAlsoDownloadedGivenProgram($flavor, $program, $limit, $offset, $is_test_environment)
  {
    return $this->program_repository->getOtherMostDownloadedProgramsOfUsersThatAlsoDownloadedGivenProgram($flavor, $program, $limit, $offset, $is_test_environment);
  }

  /**
   * @param $flavor
   * @param $program
   * @param $is_test_environment
   *
   * @return int
   */
  public function getOtherMostDownloadedProgramsOfUsersThatAlsoDownloadedGivenProgramCount($flavor, $program, $is_test_environment)
  {
    return $this->program_repository->getOtherMostDownloadedProgramsOfUsersThatAlsoDownloadedGivenProgramCount($flavor, $program, $is_test_environment);
  }

  /**
   * @param $remix_migrated_at
   *
   * @return object|null
   */
  public function findOneByRemixMigratedAt($remix_migrated_at)
  {
    return $this->program_repository->findOneBy(['remix_migrated_at' => $remix_migrated_at]);
  }
}
