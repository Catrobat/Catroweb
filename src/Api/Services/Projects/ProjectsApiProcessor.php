<?php

namespace App\Api\Services\Projects;

use App\Api\Services\Base\AbstractApiProcessor;
use App\DB\Entity\Project\Program;
use App\DB\Entity\User\User;
use App\Project\AddProjectRequest;
use App\Project\CatrobatFile\ExtractedFileRepository;
use App\Project\CatrobatFile\ProjectFileRepository;
use App\Project\ProjectManager;
use App\Storage\ScreenshotRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenAPI\Server\Model\UpdateProjectRequest;

class ProjectsApiProcessor extends AbstractApiProcessor
{
  final public const SERVER_ERROR_SAVE_XML = 1;
  final public const SERVER_ERROR_SCREENSHOT = 2;

  public function __construct(private readonly ProjectManager $project_manager,
    private readonly EntityManagerInterface $entity_manager,
    private readonly ExtractedFileRepository $extracted_file_repository,
    private readonly ProjectFileRepository $file_repository,
    private readonly ScreenshotRepository $screenshot_repository)
  {
  }

  /**
   * @throws \Exception
   */
  public function addProject(AddProjectRequest $add_program_request): ?Program
  {
    return $this->project_manager->addProject($add_program_request);
  }

  public function saveProject(Program $project): void
  {
    $this->project_manager->save($project);
  }

  public function updateProject(Program $project, UpdateProjectRequest $request): bool|int
  {
    $project_touched = false;
    $extracted_file = null;
    $extracted_file_properties_before_update = [];

    if (!is_null($request->getName()) || !is_null($request->getDescription()) || !is_null($request->getCredits())) {
      $name = $request->getName();
      $description = $request->getDescription();
      $credits = $request->getCredits();

      $project_touched = true;

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
      if ($extracted_file) {
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

    if (!is_null($request->getScreenshot())) {
      try {
        $this->screenshot_repository->updateProjectAssets($request->getScreenshot(), $project->getId());
      } catch (\Exception) {
        if ($extracted_file) {
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

    return true;
  }

  public function refreshUser(User $user): void
  {
    $this->entity_manager->refresh($user);
  }

  public function deleteProjectById(string $id, User $user): bool
  {
    if ('' === $id) {
      return false;
    }

    $program = $this->project_manager->getProjectByID($id, true);

    if (!$program || $program[0]->getUser() != $user) {
      return false;
    }

    $this->project_manager->deleteProject($program[0]);

    return true;
  }
}
