<?php

namespace App\Admin\Projects;

use App\Entity\Program;
use App\Entity\User;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Sonata\DoctrineORMAdminBundle\Model\ModelManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait ProjectPreUpdateTrait
{
  /**
   * @param mixed $object
   *
   * @throws ModelManagerException
   */
  public function preUpdate($object): void
  {
    /** @var Program $object */
    /** @var ModelManager $model_manager */
    $model_manager = $this->getModelManager();
    $old_program = $model_manager->getEntityManager($this->getClass())
      ->getUnitOfWork()->getOriginalEntityData($object);

    if (false == $old_program['approved'] && true == $object->getApproved()) {
      /** @var User $user */
      $user = $this->getConfigurationPool()->getContainer()
        ->get('security.token_storage')->getToken()->getUser();
      $object->setApprovedByUser($user);
      $this->getModelManager()->update($object);
    } elseif (true == $old_program['approved'] && false == $object->getApproved()) {
      $object->setApprovedByUser(null);
      $this->getModelManager()->update($object);
    }
    $this->checkFlavor();
  }

  protected function checkFlavor(): void
  {
    if (!$this->getForm()->has('flavor')) {
      return;
    } //then it is on approved programs

    $flavor = $this->getForm()->get('flavor')->getData();

    if (!$flavor) {
      return; // There was no required flavor form field in this Action, so no check is needed!
    }

    $flavor_options = $this->getConfigurationPool()->getContainer()->getParameter('flavors');

    if (!in_array($flavor, $flavor_options, true)) {
      throw new NotFoundHttpException('"'.$flavor.'"Flavor is unknown! Choose either '.implode(',', $flavor_options));
    }
  }
}
