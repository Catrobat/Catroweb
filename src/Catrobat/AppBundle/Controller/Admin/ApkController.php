<?php

namespace Catrobat\AppBundle\Controller\Admin;

use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Catrobat\AppBundle\Entity\Program;


/**
 * Class ApkController
 * @package Catrobat\AppBundle\Controller\Admin
 */
class ApkController extends CRUDController
{

  public function resetStatusAction()
  {
    /**
     * @var $object Program
     */

    $object = $this->admin->getSubject();

    if (!$object)
    {
      throw new NotFoundHttpException();
    }

    $object->setApkStatus(Program::APK_NONE);

    $this->admin->update($object);

    $this->addFlash('sonata_flash_success', 'Reseted APK status of ' . $object->getName());

    return new RedirectResponse($this->admin->generateUrl('list'));
  }


  /**
   * @return RedirectResponse
   * @throws \Exception
   */
  public function rebuildApkAction()
  {
    /**
     * @var $object Program
     */

    $object = $this->admin->getSubject();

    if (!$object)
    {
      throw new NotFoundHttpException();
    }

    $dispatcher = $this->container->get('ci.jenkins.dispatcher');
    $dispatcher->sendBuildRequest($object->getId());

    $object->setApkRequestTime(new \DateTime());
    $object->setApkStatus(Program::APK_PENDING);

    $this->admin->update($object);

    $this->addFlash('sonata_flash_success', 'Requested a rebuild of ' . $object->getName());

    return new RedirectResponse($this->admin->generateUrl('list'));
  }


  /**
   * @return RedirectResponse
   */
  public function resetAllApkAction()
  {
    /**
     * @var $program Program
     */

    $datagrid = $this->admin->getDatagrid();

    $objects = $datagrid->getResults();

    foreach ($objects as $program)
    {
      $program->setApkStatus(Program::APK_NONE);
      $this->admin->update($program);
    }

    if (count($objects) != 0)
    {
      $this->addFlash('sonata_flash_success', 'All Apks reseted');
    }
    else
    {
      $this->addFlash('sonata_flash_info', 'No Apks to be reseted');
    }

    return new RedirectResponse($this->admin->generateUrl('list'));
  }


  /**
   * @return RedirectResponse
   * @throws \Exception
   */
  public function rebuildAllApkAction()
  {
    /* @var $program Program */

    $datagrid = $this->admin->getDatagrid();

    $objects = $datagrid->getResults();
    $dispatcher = $this->container->get('ci.jenkins.dispatcher');

    foreach ($objects as $program)
    {
      $dispatcher->sendBuildRequest($program->getId());
      $program->setApkRequestTime(new \DateTime());
      $program->setApkStatus(Program::APK_PENDING);
      $this->admin->update($program);
    }

    if (count($objects) != 0)
    {
      $this->addFlash('sonata_flash_success', 'Requested rebuild for all Apks');
    }
    else
    {
      $this->addFlash('sonata_flash_info', 'No Rebuild-Requests were sent');
    }

    return new RedirectResponse($this->admin->generateUrl('list'));
  }
}
