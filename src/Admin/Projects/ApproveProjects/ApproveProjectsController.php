<?php

declare(strict_types=1);

namespace App\Admin\Projects\ApproveProjects;

use App\DB\Entity\Project\Program;
use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Exception\LockException;
use Sonata\AdminBundle\Exception\ModelManagerThrowable;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @phpstan-extends CRUDController<Program>
 */
class ApproveProjectsController extends CRUDController
{
  /**
   * @throws LockException
   * @throws ModelManagerThrowable
   */
  public function approveAction(): RedirectResponse
  {
    /** @var Program|null $object */
    $object = $this->admin->getSubject();
    $object->setApproved(true);
    $object->setVisible(true);

    $this->admin->update($object);
    $this->addFlash('sonata_flash_success', $object->getName().' approved. '.$this->getRemainingProjectCount().' remaining.');

    return new RedirectResponse($this->getRedirectionUrl());
  }

  public function skipAction(): RedirectResponse
  {
    $object = $this->admin->getSubject();
    $this->addFlash('sonata_flash_warning', $object->getName().' skipped');

    return new RedirectResponse($this->getRedirectionUrl());
  }

  /**
   * @throws LockException
   * @throws ModelManagerThrowable
   */
  public function invisibleAction(): RedirectResponse
  {
    /** @var Program $project */
    $project = $this->admin->getSubject();
    $project->setApproved(true);
    $project->setVisible(false);

    $this->admin->update($project);

    $this->addFlash('sonata_flash_success', $project->getName().' set to invisible'.$this->getRemainingProjectCount().' remaining.');

    return new RedirectResponse($this->getRedirectionUrl());
  }

  private function getRedirectionUrl(): string
  {
    $nextId = $this->getNextRandomApproveProjectId();
    if (null == $nextId) {
      return $this->admin->generateUrl('list');
    }

    return $this->admin->generateUrl('show', ['id' => $nextId]);
  }

  private function getNextRandomApproveProjectId(): ?string
  {
    $data_grid = $this->admin->getDatagrid();
    $objects = $data_grid->getResults();
    $objectsArray = $this->getObjectsArrayByObjects($objects);

    if ([] === $objectsArray) {
      return null;
    }

    $object_key = array_rand($objectsArray);
    $object = $objectsArray[$object_key];

    if (!$object instanceof Program) {
      return null;
    }

    return $object->getId();
  }

  private function getRemainingProjectCount(): int
  {
    $datagrid = $this->admin->getDatagrid();
    $objects = $datagrid->getResults();
    $objectsArray = $this->getObjectsArrayByObjects($objects);

    return count($objectsArray);
  }

  private function getObjectsArrayByObjects(iterable $objects): array
  {
    $objectsArray = [];
    if (!is_countable($objects)) {
      foreach ($objects as $object) {
        $objectsArray[] = $object;
      }
    }

    return $objectsArray;
  }
}
