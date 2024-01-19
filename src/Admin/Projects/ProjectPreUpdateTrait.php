<?php

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
    /** @var Program $object */
    /** @var ModelManager $model_manager */
    $model_manager = $this->getModelManager();
    $old_project = $model_manager->getEntityManager($this->getClass())
      ->getUnitOfWork()->getOriginalEntityData($object)
    ;

    if (false == $old_project['approved'] && true == $object->getApproved()) {
      /** @var User $user */
      $user = $this->security_token_storage->getToken()->getUser();
      $object->setApprovedByUser($user);
      $this->getModelManager()->update($object);
    } elseif (true == $old_project['approved'] && false == $object->getApproved()) {
      $object->setApprovedByUser(null);
      $this->getModelManager()->update($object);
    }
  }
}
