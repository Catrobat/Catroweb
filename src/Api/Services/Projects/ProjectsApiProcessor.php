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
  private ProgramManager $project_manager;
  private EntityManagerInterface $entity_manager;

  public function __construct(ProgramManager $project_manager, EntityManagerInterface $entity_manager)
  {
    $this->project_manager = $project_manager;
    $this->entity_manager = $entity_manager;
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
}
