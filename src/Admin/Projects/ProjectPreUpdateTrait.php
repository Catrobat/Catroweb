<?php

declare(strict_types=1);

namespace App\Admin\Projects;

use App\DB\Entity\Project\Project;
use App\DB\Entity\User\User;
use Sonata\AdminBundle\Exception\ModelManagerThrowable;
use Sonata\DoctrineORMAdminBundle\Model\ModelManager;

trait ProjectPreUpdateTrait
{
  /**
   * @throws ModelManagerThrowable
   */
  public function preUpdate(object $object): void
  {
    /** @var Project $project */
    $project = $object;
    /** @var ModelManager $model_manager */
    $model_manager = $this->getModelManager();
    $old_project = $model_manager->getEntityManager($this->getClass())
      ->getUnitOfWork()->getOriginalEntityData($project)
    ;

    if (!$old_project['approved'] && $project->getApproved()) {
      $token = $this->security_token_storage->getToken();
      $user = $token?->getUser();
      if ($user instanceof User) {
        $project->setApprovedByUser($user);
      }

      $this->getModelManager()->update($project);
    } elseif ($old_project['approved'] && !$project->getApproved()) {
      $project->setApprovedByUser(null);
      $this->getModelManager()->update($project);
    }
  }
}
