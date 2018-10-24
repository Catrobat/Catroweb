<?php

namespace Catrobat\AppBundle\Controller\Admin;

use Catrobat\AppBundle\Entity\UserComment;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ReportController extends Controller
{

  public function unreportProgramAction(Request $request = null)
  {
    /* @var $object \Catrobat\AppBundle\Entity\ProgramInappropriateReport */
    /* @var $program \Catrobat\AppBundle\Entity\Program */
    $object = $this->admin->getSubject();

    if (!$object)
    {
      throw new NotFoundHttpException();
    }

    $program = $object->getProgram();
    $program->setVisible(true);

    $em = $this->getDoctrine()->getManager();
    $em->remove($object);
    $em->flush();


    $this->addFlash('sonata_flash_success', 'Program ' . $object->getId() . ' is no longer reported');

    return new RedirectResponse($this->admin->generateUrl('list'));
  }

  public function unreportCommentAction(Request $request = null)
  {

    /* @var $object \Catrobat\AppBundle\Entity\UserComment */
    $object = $this->admin->getSubject();

    if (!$object)
    {
      throw new NotFoundHttpException();
    }

    $object->setIsReported(false);
    $this->admin->update($object);

    $this->addFlash('sonata_flash_success', 'Comment ' . $object->getId() . ' is no longer reported');

    return new RedirectResponse($this->admin->generateUrl('list'));
  }

  public function deleteCommentAction(Request $request = null)
  {
    /* @var $object \Catrobat\AppBundle\Entity\UserComment */
    $object = $this->admin->getSubject();

    if (!$object)
    {
      throw new NotFoundHttpException();
    }
    $em = $this->getDoctrine()->getManager();
    $comment = $em->getRepository(UserComment::class)->find($object->getId());

    if (!$comment)
    {
      throw $this->createNotFoundException(
        'No comment found for this id ' . $object->getId());
    }
    $em->remove($comment);
    $em->flush();
    $this->addFlash('sonata_flash_success', 'Comment ' . $object->getId() . ' deleted');

    return new RedirectResponse($this->admin->generateUrl('list'));
  }
}