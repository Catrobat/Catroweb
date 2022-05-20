<?php

namespace App\Api\Services\Projects;

use App\Api\Services\Base\AbstractApiProcessor;
use App\DB\Entity\Project\Program;
use App\DB\Entity\User\User;
use App\Project\AddProgramRequest;
use App\Project\ProgramManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

final class ProjectsApiProcessor extends AbstractApiProcessor
{
  public function __construct(private readonly ProgramManager $project_manager, private readonly EntityManagerInterface $entity_manager)
  {
  }

  /**
   * @throws Exception
   */
  public function addProject(AddProgramRequest $add_program_request): ?Program
  {
    return $this->project_manager->addProgram($add_program_request);
  }

  public function saveProject(Program $project): void
  {
    $this->project_manager->save($project);
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

    $program = $this->project_manager->getProjectByID($id);

    if (!$program || $program[0]->getUser() != $user) {
      return false;
    }

    $this->project_manager->deleteProject($program[0]);

    return true;
  }
}
