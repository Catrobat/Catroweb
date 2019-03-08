<?php

namespace App\Catrobat\Controller\Admin;

use App\Entity\UserComment;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


/**
 * Class ReportController
 * @package App\Catrobat\Controller\Admin
 */
class ReportController extends Controller
{

  /**
   * @param Request|null $request
   *
   * @return RedirectResponse
   */
  public function unreportProgramAction(Request $request = null)
  {
    /**
      * @var $object \App\Entity\ProgramInappropriateReport
      * @var $program \App\Entity\Program
      */

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


  /**
   * @param Request|null $request
   *
   * @return RedirectResponse
   */
  public function unreportCommentAction(Request $request = null)
  {

    /* @var $object \App\Entity\UserComment */
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


  /**
   * @param Request|null $request
   *
   * @return RedirectResponse
   */
  public function deleteCommentAction(Request $request = null)
  {
    /* @var $object \App\Entity\UserComment */
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