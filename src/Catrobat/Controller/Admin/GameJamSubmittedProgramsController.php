<?php

namespace App\Catrobat\Controller\Admin;

use App\Entity\Program;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GameJamSubmittedProgramsController extends CRUDController
{
  public function removeFromGameJamAction(): RedirectResponse
  {
    /** @var Program|null $object */
    $object = $this->admin->getSubject();

    if (!$object)
    {
      throw new NotFoundHttpException();
    }

    $object->setGamejam(null);
    $object->setAcceptedForGameJam(false);
    $object->setGameJamSubmissionDate(null);

    $this->admin->update($object);

    $this->addFlash('sonata_flash_success', 'Removed '.$object->getName().' from gamejam');

    return new RedirectResponse($this->admin->generateUrl('list'));
  }
}
