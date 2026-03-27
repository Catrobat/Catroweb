<?php

declare(strict_types=1);

namespace App\Admin\Projects;

use App\DB\Entity\Project\Program;
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
    /** @var Program $program */
    $program = $object;
    /** @var ModelManager $model_manager */
    $model_manager = $this->getModelManager();
    $old_project = $model_manager->getEntityManager($this->getClass())
      ->getUnitOfWork()->getOriginalEntityData($program)
    ;

    if (!$old_project['approved'] && $program->getApproved()) {
      $token = $this->security_token_storage->getToken();
      $user = $token?->getUser();
      if ($user instanceof User) {
        $program->setApprovedByUser($user);
      }

      $this->getModelManager()->update($program);
    } elseif ($old_project['approved'] && !$program->getApproved()) {
      $program->setApprovedByUser(null);
      $this->getModelManager()->update($program);
    }
  }
}
