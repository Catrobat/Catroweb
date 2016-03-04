<?php

namespace Catrobat\AppBundle\Controller\Admin;

use Catrobat\AppBundle\Services\ApkRepository;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Catrobat\AppBundle\Entity\Program;

class ApkController extends CRUDController
{
    public function resetStatusAction()
    {
        /* @var $object Program */
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
        /* @var $object Program */
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
        /* @var $object Program */
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

    public function resetAllApkAction()
    {
        /* @var $program Program */

        $datagrid = $this->admin->getDatagrid();

        $objects = $datagrid->getResults();

        foreach($objects as $program) {
            $program->setApkStatus(Program::APK_NONE);
            $this->admin->update($program);
        }

        if(count($objects) != 0) {
            $this->addFlash('sonata_flash_success', 'All Apks reseted');
        }
        else {
            $this->addFlash('sonata_flash_info', 'No Apks to be reseted');
        }

        return new RedirectResponse($this->admin->generateUrl('list'));
    }

    public function rebuildAllApkAction()
    {
        /* @var $program Program */

        $datagrid = $this->admin->getDatagrid();

        $objects = $datagrid->getResults();
        $dispatcher = $this->container->get('ci.jenkins.dispatcher');

        foreach($objects as $program) {
            $dispatcher->sendBuildRequest($program->getId());
            $program->setApkRequestTime(new \DateTime());
            $program->setApkStatus(Program::APK_PENDING);
            $this->admin->update($program);
        }

        if(count($objects) != 0) {
            $this->addFlash('sonata_flash_success', 'Requested rebuild for all Apks');
        }
        else {
            $this->addFlash('sonata_flash_info', 'No Rebuild-Requests were sent');
        }

        return new RedirectResponse($this->admin->generateUrl('list'));
    }

    public function deleteAllApkAction()
    {
        /* @var $apk_repo ApkRepository */
        /* @var $program Program */

        $apk_repo = $this->container->get('apkrepository');

        $datagrid = $this->admin->getDatagrid();

        $objects = $datagrid->getResults();

        foreach($objects as $program) {
            $apk_repo->remove($program->getId());
            $program->setApkStatus(Program::APK_NONE);
            $this->admin->update($program);
        }

        if(count($objects) != 0) {
            $this->addFlash('sonata_flash_success', 'Removed all Apks');
        }
        else {
            $this->addFlash('sonata_flash_info', 'No Apks were removed');
        }

        return new RedirectResponse($this->admin->generateUrl('list'));
    }
}
