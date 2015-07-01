<?php

namespace Catrobat\AppBundle\Controller\Admin;

use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Catrobat\AppBundle\Entity\Program;

class ApkController extends CRUDController
{
    public function resetStatusAction()
    {
        /* @var $object \Catrobat\AppBundle\Entity\Program */
        $object = $this->admin->getSubject();

        if (!$object) {
            throw new NotFoundHttpException();
        }

        $object->setApkStatus(Program::APK_NONE);

        $this->admin->update($object);

        $this->addFlash('sonata_flash_success', 'Reseted APK status of '.$object->getName());

        return new RedirectResponse($this->admin->generateUrl('list'));
    }

    public function rebuildApkAction()
    {
        /* @var $object \Catrobat\AppBundle\Entity\Program */
        $object = $this->admin->getSubject();

        if (!$object) {
            throw new NotFoundHttpException();
        }

        $dispatcher = $this->container->get('ci.jenkins.dispatcher');
        $dispatcher->sendBuildRequest($object->getId());

        $object->setApkRequestTime(new \DateTime());
        $object->setApkStatus(Program::APK_PENDING);

        $this->admin->update($object);

        $this->addFlash('sonata_flash_success', 'Requested a rebuild of '.$object->getName());

        return new RedirectResponse($this->admin->generateUrl('list'));
    }

    public function deleteApkAction()
    {
        /* @var $object \Catrobat\AppBundle\Entity\Program */
        $object = $this->admin->getSubject();

        if (!$object) {
            throw new NotFoundHttpException();
        }

        $this->container->get('apkrepository')->remove($object->getId());

        $object->setApkStatus(Program::APK_NONE);

        $this->admin->update($object);

        $this->addFlash('sonata_flash_success', 'Removed Apk of '.$object->getName());

        return new RedirectResponse($this->admin->generateUrl('list'));
    }
}
