<?php

declare(strict_types=1);

namespace App\Api\Services\Projects;

use App\Api\Services\Base\AbstractApiProcessor;
use App\DB\Entity\Project\Project;
use App\DB\Entity\System\BackgroundJob;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\System\BackgroundJobRepository;
use App\DB\Enum\ContentType;
use App\Moderation\AutoModerationService;
use App\Moderation\TextSanitizer;
use App\Project\AddProjectRequest;
use App\Project\CatrobatFile\ExtractedCatrobatFile;
use App\Project\CatrobatFile\ExtractedFileRepository;
use App\Project\CatrobatFile\ProjectFileRepository;
use App\Project\ProjectManager;
use App\Project\Upload\CompleteProjectUpload;
use App\Storage\ScreenshotRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use OpenAPI\Server\Model\UpdateProjectRequest;
use Symfony\Component\Messenger\MessageBusInterface;

class ProjectsApiProcessor extends AbstractApiProcessor
{
  final public const int SERVER_ERROR_SAVE_XML = 1;

  final public const int SERVER_ERROR_SCREENSHOT = 2;

  public function __construct(private readonly ProjectManager $project_manager,
    private readonly EntityManagerInterface $entity_manager,
    private readonly ExtractedFileRepository $extracted_file_repository,
    private readonly ProjectFileRepository $file_repository,
    private readonly ScreenshotRepository $screenshot_repository,
    private readonly TextSanitizer $textSanitizer,
    private readonly BackgroundJobRepository $job_repository,
    private readonly MessageBusInterface $message_bus,
    private readonly AutoModerationService $auto_moderation)
  {
  }

  /**
   * @throws \Exception
   * @throws ORMException
   */
  public function addProject(AddProjectRequest $add_program_request): ?Project
  {
    return $this->project_manager->addProject($add_program_request);
  }

  public function saveProject(Project $project): void
  {
    $this->project_manager->save($project);
  }

  public function updateProject(Project $project, UpdateProjectRequest $request): bool|int
  {
    $project_touched = false;
    $text_touched = false;
    $extracted_file = null;
    $extracted_file_properties_before_update = [];

    if (!is_null($request->getName()) || !is_null($request->getDescription()) || !is_null($request->getCredits())) {
      $name = $this->textSanitizer->sanitize($request->getName());
      $description = $this->textSanitizer->sanitize($request->getDescription());
      $credits = $this->textSanitizer->sanitize($request->getCredits());

      $project_touched = true;
      $text_touched = true;

      if (!is_null($name)) {
        $project->setName($name);
      }

      if (!is_null($description)) {
        $project->setDescription($description);
      }

      if (!is_null($credits)) {
        $project->setCredits($credits);
      }

      $extracted_file = $this->extracted_file_repository->loadProjectExtractedFile($project);
      if ($extracted_file instanceof ExtractedCatrobatFile) {
        if (!is_null($name)) {
          $extracted_file_properties_before_update['name'] = $extracted_file->getName();
          $extracted_file->setName($name);
        }

        if (!is_null($description)) {
          $extracted_file_properties_before_update['description'] = $extracted_file->getDescription();
          $extracted_file->setDescription($description);
        }

        if (!is_null($credits)) {
          $extracted_file_properties_before_update['credits'] = $extracted_file->getNotesAndCredits();
          $extracted_file->setNotesAndCredits($credits);
        }

        try {
          $this->extracted_file_repository->saveProjectExtractedFile($extracted_file);
        } catch (\Exception) {
          return self::SERVER_ERROR_SAVE_XML;
        }

        $this->file_repository->deleteProjectZipFileIfExists($project->getId());
      }
    }

    if (!is_null($request->isPrivate())) {
      $project->setPrivate($request->isPrivate());
      $project_touched = true;
    }

    $slug = $request->getSlug();
    if (!is_null($slug)) {
      $project->setSlug(ProjectsRequestValidator::normalizeSlug($slug));
      $project_touched = true;
    }

    if (!is_null($request->isNotForKids())) {
      if (2 !== $project->getNotForKids()) {
        $project->setNotForKids($request->isNotForKids() ? 1 : 0);
        $project_touched = true;
      }
    }

    if (!is_null($request->getScreenshot())) {
      try {
        $this->screenshot_repository->updateProjectAssets($request->getScreenshot(), $project->getId());
      } catch (\Exception) {
        if ($extracted_file instanceof ExtractedCatrobatFile) {
          // restore old values
          foreach ($extracted_file_properties_before_update as $key => $value) {
            switch ($key) {
              case 'name':
                $extracted_file->setName($value);
                break;
              case 'description':
                $extracted_file->setDescription($value);
                break;
              case 'credits':
                $extracted_file->setNotesAndCredits($value);
                break;
            }
          }

          try {
            $extracted_file->saveProjectXmlProperties();
          } catch (\Exception) {
            // ignore
          }
        }

        return self::SERVER_ERROR_SCREENSHOT;
      }
    }

    if ($project_touched) {
      $this->project_manager->save($project);
    }

    $project_id = (string) $project->getId();
    if ($text_touched) {
      $this->auto_moderation->flagAgeSignal(ContentType::Project, $project_id, $project->getName(), $project->getDescription(), $project->getCredits());
    }

    if (!is_null($request->getScreenshot())) {
      $this->auto_moderation->queueImageScan(ContentType::Project, $project_id);
    }

    return true;
  }

  /**
   * @throws ORMException
   */
  public function refreshUser(User $user): void
  {
    $this->entity_manager->refresh($user);
  }

  public function deleteProjectById(string $id, User $user): bool
  {
    if ('' === $id) {
      return false;
    }

    $project = $this->project_manager->getProjectByID($id, true);

    if ([] === $project || $project[0]->getUser() != $user) {
      return false;
    }

    $this->project_manager->deleteProject($project[0]);

    return true;
  }

  /** Queues the completion for the uploads worker; calling again while it is queued returns the same job. */
  public function queueUploadCompletion(string $upload_id, User $user, string $accept_language, ?string $ip): BackgroundJob
  {
    $job = $this->job_repository->findUnfinished(CompleteProjectUpload::JOB_TYPE, $upload_id);
    if (null !== $job) {
      return $job;
    }

    $job = new BackgroundJob(CompleteProjectUpload::JOB_TYPE, $upload_id, (string) $user->getId());
    $this->entity_manager->persist($job);
    $this->entity_manager->flush();
    $this->message_bus->dispatch(new CompleteProjectUpload($job->getId(), $upload_id, (string) $user->getId(), $accept_language, $ip));

    return $job;
  }
}
