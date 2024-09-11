<?php

declare(strict_types=1);

namespace App\Admin\Projects\ReportedProjects;

use App\DB\Entity\Project\ProgramInappropriateReport;
use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Exception\LockException;
use Sonata\AdminBundle\Exception\ModelManagerThrowable;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @phpstan-extends CRUDController<ProgramInappropriateReport>
 */
class ReportedProjectsController extends CRUDController
{
  /**
   * @throws LockException
   * @throws ModelManagerThrowable
   */
  public function unreportProjectAction(): RedirectResponse
  {
    /** @var ProgramInappropriateReport|null $object */
    $object = $this->admin->getSubject();

    $project = $object->getProgram();
    $project->setVisible(true);
    $project->setApproved(true);

    $object->setState(3);
    $this->admin->update($object);
    $this->addFlash('sonata_flash_success', 'Project '.$object->getId().' is no longer reported');

    return new RedirectResponse($this->admin->generateUrl('list'));
  }

  /**
   * @throws LockException
   * @throws ModelManagerThrowable
   */
  public function acceptProjectReportAction(): RedirectResponse
  {
    /** @var ProgramInappropriateReport|null $object */
    $object = $this->admin->getSubject();

    $project = $object->getProgram();
    $project->setVisible(false);
    $project->setApproved(false);

    $object->setState(2);
    $this->admin->update($object);
    $this->addFlash('sonata_flash_error', 'Project '.$object->getId().' report got accepted');

    return new RedirectResponse($this->admin->generateUrl('list'));
  }
}
