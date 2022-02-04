<?php

namespace App\Admin\Projects\ReportedProjects;

use App\Entity\ProgramInappropriateReport;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ReportedProjectsController extends CRUDController
{
  public function unreportProgramAction(): RedirectResponse
  {
    /** @var ProgramInappropriateReport|null $object */
    $object = $this->admin->getSubject();
    if (null === $object) {
      throw new NotFoundHttpException();
    }
    $program = $object->getProgram();
    $program->setVisible(true);
    $program->setApproved(true);
    $object->setState(3);
    $this->admin->update($object);
    $this->addFlash('sonata_flash_success', 'Program '.$object->getId().' is no longer reported');

    return new RedirectResponse($this->admin->generateUrl('list'));
  }

  public function acceptProgramReportAction(): RedirectResponse
  {
    /** @var ProgramInappropriateReport|null $object */
    $object = $this->admin->getSubject();
    if (null === $object) {
      throw new NotFoundHttpException();
    }
    $program = $object->getProgram();
    $program->setVisible(false);
    $program->setApproved(false);
    $object->setState(2);
    $this->admin->update($object);
    $this->addFlash('sonata_flash_error', 'Program '.$object->getId().' report got accepted');

    return new RedirectResponse($this->admin->generateUrl('list'));
  }
}
