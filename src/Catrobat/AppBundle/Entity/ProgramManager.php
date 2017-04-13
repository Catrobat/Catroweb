<?php

namespace Catrobat\AppBundle\Entity;

use Catrobat\AppBundle\Events\InvalidProgramUploadedEvent;
use Catrobat\AppBundle\Events\ProgramAfterInsertEvent;
use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\AppBundle\Requests\AddProgramRequest;
use Knp\Component\Pager\Paginator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Catrobat\AppBundle\Entity\UserManager;
use Catrobat\AppBundle\Events\ProgramBeforeInsertEvent;
use Catrobat\AppBundle\Events\ProgramInsertEvent;
use Catrobat\AppBundle\Events\ProgramBeforePersistEvent;
use Catrobat\AppBundle\Entity\TagRepository;

class ProgramManager
{

  protected $file_extractor;

  protected $file_repository;

  protected $screenshot_repository;

  protected $event_dispatcher;

  protected $entity_manager;

  protected $program_repository;

  protected $pagination;

  protected $tag_repository;

  protected $program_like_repository;

  public function __construct($file_extractor, $file_repository, $screenshot_repository, $entity_manager, $program_repository,
                              $tag_repository, $program_like_repository, EventDispatcherInterface $event_dispatcher)
  {
    /** @var $program_repository ProgramRepository */
    $this->file_extractor = $file_extractor;
    $this->event_dispatcher = $event_dispatcher;
    $this->file_repository = $file_repository;
    $this->screenshot_repository = $screenshot_repository;
    $this->entity_manager = $entity_manager;
    $this->program_repository = $program_repository;
    $this->tag_repository = $tag_repository;
    $this->program_like_repository = $program_like_repository;
  }

  public function addProgram(AddProgramRequest $request)
  {
    $file = $request->getProgramfile();

    $extracted_file = $this->file_extractor->extract($file);
    try
    {
      $event = $this->event_dispatcher->dispatch('catrobat.program.before', new ProgramBeforeInsertEvent($extracted_file));
    } catch (InvalidCatrobatFileException $e)
    {
      $event = $this->event_dispatcher->dispatch('catrobat.program.invalid.upload', new InvalidProgramUploadedEvent($file, $e));
      throw $e;
    }

    if ($event->isPropagationStopped())
    {
      return;
    }

    /* @var $program Program */

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
    $this->addTags($program, $extracted_file, $request->getLanguage());

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

    $this->entity_manager->persist($program);
    $this->entity_manager->flush();

    if ($extracted_file->getScreenshotPath() == null)
    {
      // Todo: maybe for later implementations
    }
    else
    {
      $this->screenshot_repository->saveProgramAssets($extracted_file->getScreenshotPath(), $program->getId());
    }
    $this->file_repository->saveProgram($extracted_file, $program->getId());

    $event = $this->event_dispatcher->dispatch('catrobat.program.successful.upload', new ProgramInsertEvent());

    return $program;
  }

  public function findUserLike($program_id, $user_id)
  {
    return $this->program_like_repository->findOneBy(['program_id' => $program_id, 'user_id' => $user_id]);
  }

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

  public function likeTypeCount($program_id, $type)
  {
    return $this->program_like_repository->likeTypeCount($program_id, $type);
  }

  public function totalLikeCount($program_id)
  {
    return $this->program_like_repository->totalLikeCount($program_id);
  }

  public function addTags($program, $extracted_file, $language)
  {
    $metadata = $this->entity_manager->getClassMetadata('Catrobat\AppBundle\Entity\Tag')->getFieldNames();

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

  public function markAllProgramsAsNotYetMigrated()
  {
    $this->program_repository->markAllProgramsAsNotYetMigrated();
  }

  public function findOneByNameAndUser($program_name, $user)
  {
    return $this->program_repository->findOneBy([
      'name' => $program_name,
      'user' => $user,
    ]);
  }

  public function findOneByName($programName)
  {
    return $this->program_repository->findOneByName($programName);
  }

  public function findBy($array)
  {
    return $this->program_repository->findBy($array);
  }

  public function getUserPrograms($user_id, $max_version = 0)
  {
    return $this->program_repository->getUserPrograms($user_id, $max_version);
  }

  public function findAll()
  {
    return $this->program_repository->findAll();
  }

  public function findNext($previous_program_id)
  {
    return $this->program_repository->findNext($previous_program_id);
  }

  /**
   *
   * @param
   *            $id
   *
   * @return \Catrobat\AppBundle\Entity\Program
   */
  public function find($id)
  {
    return $this->program_repository->find($id);
  }

  public function getProgramsWithApkStatus($apk_status)
  {
    return $this->program_repository->getProgramsWithApkStatus($apk_status);
  }

  public function getProgramsWithExtractedDirectoryHash()
  {
    return $this->program_repository->getProgramsWithExtractedDirectoryHash();
  }

  public function getRecentPrograms($flavor, $limit = null, $offset = null, $max_version = 0)
  {
    return $this->program_repository->getRecentPrograms($flavor, $limit, $offset, $max_version);
  }

  public function getMostViewedPrograms($flavor, $limit = null, $offset = null, $max_version = 0)
  {
    return $this->program_repository->getMostViewedPrograms($flavor, $limit, $offset, $max_version);
  }

  public function getMostDownloadedPrograms($flavor, $limit = null, $offset = null, $max_version = 0)
  {
    return $this->program_repository->getMostDownloadedPrograms($flavor, $limit, $offset, $max_version);
  }

  public function getRandomPrograms($flavor, $limit = null, $offset = null, $max_version = 0)
  {
    return $this->program_repository->getRandomPrograms($flavor, $limit, $offset, $max_version);
  }

  public function search($query, $limit = 10, $offset = 0, $max_version = 0)
  {
    return $this->program_repository->search($query, $limit, $offset, $max_version);
  }

  public function searchCount($query, $max_version = 0)
  {
    return $this->program_repository->searchCount($query, $max_version);
  }

  public function searchCountUserPrograms($user_id)
  {
    return $this->program_repository->searchCountUserPrograms($user_id);
  }

  public function getTotalPrograms($flavor, $max_version = 0)
  {
    return $this->program_repository->getTotalPrograms($flavor, $max_version);
  }

  public function increaseViews(Program $program)
  {
    $program->setViews($program->getViews() + 1);
    $this->save($program);
  }

  public function increaseDownloads(Program $program)
  {
    $program->setDownloads($program->getDownloads() + 1);
    $this->save($program);
  }

  public function increaseApkDownloads(Program $program)
  {
    $program->setApkDownloads($program->getApkDownloads() + 1);
    $this->save($program);
  }

  public function save(Program $program)
  {
    $this->entity_manager->persist($program);
    $this->entity_manager->flush();
  }

  public function getProgramsByTagId($id, $limit, $offset)
  {
    return $this->program_repository->getProgramsByTagId($id, $limit, $offset);
  }

  public function getProgramsByExtensionName($name, $limit, $offset)
  {
    return $this->program_repository->getProgramsByExtensionName($name, $limit, $offset);
  }

  public function searchTagCount($query)
  {
    return $this->program_repository->searchTagCount($query);
  }

  public function searchExtensionCount($query)
  {
    return $this->program_repository->searchExtensionCount($query);
  }

  public function getRecommendedProgramsById($id, $flavor, $limit, $offset)
  {
    return $this->program_repository->getRecommendedProgramsById($id, $flavor, $limit, $offset);
  }

  public function getRecommendedProgramsCount($id, $flavor)
  {
    return $this->program_repository->getRecommendedProgramsCount($id, $flavor);
  }

  public function getMostRemixedPrograms($flavor, $limit, $offset)
  {
    return $this->program_repository->getMostRemixedPrograms($flavor, $limit, $offset);
  }

  public function getTotalRemixedProgramsCount($flavor)
  {
    return $this->program_repository->getTotalRemixedProgramsCount($flavor);
  }

  public function getMostLikedPrograms($flavor, $limit, $offset)
  {
    return $this->program_repository->getMostLikedPrograms($flavor, $limit, $offset);
  }

  public function getTotalLikedProgramsCount($flavor)
  {
    return $this->program_repository->getTotalLikedProgramsCount($flavor);
  }

  public function getOtherMostDownloadedProgramsOfUsersThatAlsoDownloadedGivenProgram($flavor, $program, $limit, $offset, $is_test_environment)
  {
    return $this->program_repository->getOtherMostDownloadedProgramsOfUsersThatAlsoDownloadedGivenProgram($flavor, $program, $limit, $offset, $is_test_environment);
  }

  public function getOtherMostDownloadedProgramsOfUsersThatAlsoDownloadedGivenProgramCount($flavor, $program, $is_test_environment)
  {
    return $this->program_repository->getOtherMostDownloadedProgramsOfUsersThatAlsoDownloadedGivenProgramCount($flavor, $program, $is_test_environment);
  }

  /**
   * @param $remix_migrated_at
   *
   * @return Program|null
   */
  public function findOneByRemixMigratedAt($remix_migrated_at)
  {
    return $this->program_repository->findOneBy(['remix_migrated_at' => $remix_migrated_at]);
  }
}
