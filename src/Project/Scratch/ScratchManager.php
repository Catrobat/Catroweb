<?php

declare(strict_types=1);

namespace App\Project\Scratch;

use App\DB\Entity\Project\Program;
use App\Project\ProjectManager;
use App\User\UserManager;

class ScratchManager
{
  protected AsyncHttpClient $async_http_client;

  public function __construct(protected ProjectManager $project_manager,
    protected UserManager $user_manager)
  {
    $this->async_http_client = new AsyncHttpClient(['timeout' => 12, 'max_number_of_concurrent_requests' => 1]);
  }

  /**
   * @throws \Exception
   */
  public function createScratchProjectFromId(int $id): ?Program
  {
    $project_arr = $this->async_http_client->fetchScratchProjectDetails([$id]);
    if (null == $project_arr) {
      return null;
    }
    $project_data = $project_arr[$id];
    /** @var Program|null $old_project */
    $old_project = $this->project_manager->findOneByScratchId($id);
    if (null === $old_project) {
      $user = $this->user_manager->createUserFromScratch($project_data['author']);
    } else {
      $user = $old_project->getUser();
    }

    return $this->project_manager->createProjectFromScratch($old_project, $user, $project_data);
  }
}
