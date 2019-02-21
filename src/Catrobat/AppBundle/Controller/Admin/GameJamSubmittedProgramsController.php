<?php

namespace Catrobat\AppBundle\Controller\Admin;

use Catrobat\AppBundle\Entity\Program;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;


/**
 * Class GameJamSubmittedProgramsController
 * @package Catrobat\AppBundle\Controller\Admin
 */
class GameJamSubmittedProgramsController extends CRUDController
{

  /**
   * @return RedirectResponse
   */
  public function removeFromGameJamAction()
  {
    /**
      * @var $object Program
      */

    $object = $this->admin->getSubject();

    if (!$object)
    {
      throw new NotFoundHttpException();
    }

    $object->setGamejam(null);
    $object->setAcceptedForGameJam(false);
    $object->setGameJamSubmissionDate(null);

    $this->admin->update($object);

    $this->addFlash('sonata_flash_success', 'Removed ' . $object->getName() . ' from gamejam');

    return new RedirectResponse($this->admin->generateUrl('list'));
  }
}