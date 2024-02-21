<?php

namespace App\Admin\Projects\ReportedProjects;

use App\DB\Entity\Project\ProjectInappropriateReport;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @phpstan-extends CRUDController<ProjectInappropriateReport>
 */
class ReportedProjectsController extends CRUDController
{
  public function unreportProjectAction(): RedirectResponse
  {
    /** @var ProjectInappropriateReport|null $object */
    $object = $this->admin->getSubject();
    if (null === $object) {
      throw new NotFoundHttpException();
    }
    $project = $object->getProject();
    $project->setVisible(true);
    $project->setApproved(true);
    $object->setState(3);
    $this->admin->update($object);
    $this->addFlash('sonata_flash_success', 'Project '.$object->getId().' is no longer reported');

    return new RedirectResponse($this->admin->generateUrl('list'));
  }

  public function acceptProjectReportAction(): RedirectResponse
  {
    /** @var ProjectInappropriateReport|null $object */
    $object = $this->admin->getSubject();
    if (null === $object) {
      throw new NotFoundHttpException();
    }
    $project = $object->getProject();
    $project->setVisible(false);
    $project->setApproved(false);
    $object->setState(2);
    $this->admin->update($object);
    $this->addFlash('sonata_flash_error', 'Project '.$object->getId().' report got accepted');

    return new RedirectResponse($this->admin->generateUrl('list'));
  }
}
