<?php

namespace Catrobat\AppBundle\Controller\Admin;

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ApproveProgramController extends Controller
{
    public function approveAction() {
        $object = $this->admin->getSubject();
        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object'));
        }
        /* @var $object \Catrobat\AppBundle\Entity\Program */
        $object->setApproved(true);
        $object->setVisible(true);
        $this->admin->update($object);
        $this->addFlash('sonata_flash_success', $object->getName() . ' approved. ' . $this->getRemainingProgramCount() . ' remaining.');
        return new RedirectResponse($this->getRedirectionUrl());
    }

    public function skipAction() {
        $object = $this->admin->getSubject();
        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object'));
        }
        $this->addFlash('sonata_flash_warning', $object->getName() . ' skipped');

        return new RedirectResponse($this->getRedirectionUrl());
    }

    public function invisibleAction() {
        $object = $this->admin->getSubject();

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object'));
        }
        /* @var $object \Catrobat\AppBundle\Entity\Program */

        $object->setApproved(true);
        $object->setVisible(false);
        $this->admin->update($object);

        $this->addFlash('sonata_flash_success', $object->getName() . ' set to invisible' . $this->getRemainingProgramCount() . ' remaining.');

        return new RedirectResponse($this->getRedirectionUrl());
    }

    private function getRedirectionUrl() {
        $nextId = $this->getNextRandomApproveProgramId();
        if ($nextId == null) {
            return $this->admin->generateUrl('list');
        }

        return $this->admin->generateUrl('show', array('id' => $nextId));
    }

    private function getNextRandomApproveProgramId() {
        $datagrid = $this->admin->getDatagrid();

        $objects = $datagrid->getResults();
        if (count($objects) == 0) {
            return;
        }
        $object_key = array_rand($objects);

        return $objects[$object_key]->getId();
    }

    private function getRemainingProgramCount() {
        $datagrid = $this->admin->getDatagrid();
        $objects = $datagrid->getResults();

        return count($objects);
    }
}
