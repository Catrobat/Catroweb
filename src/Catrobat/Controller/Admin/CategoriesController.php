<?php

namespace App\Catrobat\Controller\Admin;

use App\Entity\StarterCategory;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CategoriesController extends CRUDController
{
  public function removeFromStarterTableAction(): RedirectResponse
  {
    /** @var StarterCategory|null $object */
    $object = $this->admin->getSubject();
    if (null === $object)
    {
      throw new NotFoundHttpException();
    }

    $programs = $object->getPrograms();
    foreach ($programs as $program)
    {
      $object->removeProgram($program);
    }

    $em = $this->getDoctrine()->getManager();
    $em->remove($object);
    $em->flush();
    $this->addFlash('sonata_flash_error', 'Category '.$object->getAlias().' got removed');

    return new RedirectResponse($this->admin->generateUrl('list'));
  }
}
