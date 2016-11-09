<?php

namespace Catrobat\AppBundle\Controller\Admin;

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class ReportController extends Controller
{
    public function unreportAction(Request $request = null) {

        /* @var $object \Catrobat\AppBundle\Entity\UserComment */
        $object = $this->admin->getSubject();

        if (!$object) {
            throw new NotFoundHttpException();
        }

        $object->setIsReported(false);
        $this->admin->update($object);

        $this->addFlash('sonata_flash_success', 'Report ' . $object->getId() . ' removed from list');

        return new RedirectResponse($this->admin->generateUrl('list'));
    }

    public function deleteCommentAction(Request $request = null) {
        /* @var $object \Catrobat\AppBundle\Entity\UserComment */
        $object = $this->admin->getSubject();

        if (!$object) {
            throw new NotFoundHttpException();
        }
        $em = $this->getDoctrine()->getManager();
        $comment = $em->getRepository('AppBundle:UserComment')->find($object->getId());

        if (!$comment) {
            throw $this->createNotFoundException(
                'No comment found for this id ' . $object->getId());
        }
        $em->remove($comment);
        $em->flush();
        $this->addFlash('sonata_flash_success', 'Comment ' . $object->getId() . ' deleted');
        return new RedirectResponse($this->admin->generateUrl('list'));
    }
}