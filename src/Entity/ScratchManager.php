<?php

namespace App\Entity;

use App\Catrobat\Services\ScratchHttpClient;

class ScratchManager
{
  protected ProgramManager $program_manager;
  protected UserManager $user_manager;
  protected ScratchHttpClient $scratch_http_client;

  public function __construct(ProgramManager $program_manager,
                              UserManager $user_manager)
  {
    $this->program_manager = $program_manager;
    $this->user_manager = $user_manager;
    $this->scratch_http_client = new ScratchHttpClient(['timeout' => 12]);
  }

  public function createScratchProgramFromId(int $id): ?Program
  {
    $program_data = $this->scratch_http_client->getProjectData($id);
    if (null === $program_data)
    {
      return null;
    }
    /** @var Program|null $old_program */
    $old_program = $this->program_manager->findOneByScratchId($id);
    if (null === $old_program)
    {
      $user = $this->user_manager->createUserFromScratch($program_data['author']);
    }
    else
    {
      $user = $old_program->getUser();
    }

    return $this->program_manager->createProgramFromScratch($old_program, $user, $program_data);
  }

  public function createScratchUserFromName(string $name): ?User
  {
    $user_data = $this->scratch_http_client->getUserData($name);
    if (null === $user_data)
    {
      return null;
    }

    return $this->user_manager->createUserFromScratch($user_data);
  }

  public function getPseudoProgramFromData(array $program_data): Program
  {
    $user = $this->user_manager->createUserFromScratch($program_data['author'], false);

    return $this->program_manager->createProgramFromScratch(null, $user, $program_data, false);
  }
}
