<?php

namespace App\Project\Scratch;

use App\DB\Entity\Project\Program;
use App\Project\ProjectManager;
use App\User\UserManager;

class ScratchManager
{
  protected AsyncHttpClient $async_http_client;

  public function __construct(protected ProjectManager $program_manager,
    protected UserManager $user_manager)
  {
    $this->async_http_client = new AsyncHttpClient(['timeout' => 12, 'max_number_of_concurrent_requests' => 1]);
  }

  /**
   * @throws \Exception
   */
  public function createScratchProgramFromId(int $id): ?Program
  {
    $program_arr = $this->async_http_client->fetchScratchProgramDetails([$id]);
    if (null == $program_arr) {
      return null;
    }
    $program_data = $program_arr[$id];
    /** @var Program|null $old_program */
    $old_program = $this->program_manager->findOneByScratchId($id);
    if (null === $old_program) {
      $user = $this->user_manager->createUserFromScratch($program_data['author']);
    } else {
      $user = $old_program->getUser();
    }

    return $this->program_manager->createProgramFromScratch($old_program, $user, $program_data);
  }
}
