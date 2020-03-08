<?php

namespace App\Catrobat\Controller\Admin;

use App\Entity\Program;
use App\Utils\TimeUtils;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class ApkController.
 */
class ApkController extends CRUDController
{
  public function resetStatusAction()
  {
    /**
     * @var Program
     */
    $object = $this->admin->getSubject();

    if (!$object)
    {
      throw new NotFoundHttpException();
    }

    $object->setApkStatus(Program::APK_NONE);

    $this->admin->update($object);

    $this->addFlash('sonata_flash_success', 'Reseted APK status of '.$object->getName());

    return new RedirectResponse($this->admin->generateUrl('list'));
  }

  /**
   * @throws \Exception
   *
   * @return RedirectResponse
   */
  public function rebuildApkAction()
  {
    /**
     * @var Program
     */
    $object = $this->admin->getSubject();

    if (!$object)
    {
      throw new NotFoundHttpException();
    }

    $dispatcher = $this->container->get('App\Catrobat\Services\Ci\JenkinsDispatcher');
    $dispatcher->sendBuildRequest($object->getId());

    $object->setApkRequestTime(TimeUtils::getDateTime());
    $object->setApkStatus(Program::APK_PENDING);

    $this->admin->update($object);

    $this->addFlash('sonata_flash_success', 'Requested a rebuild of '.$object->getName());

    return new RedirectResponse($this->admin->generateUrl('list'));
  }

  /**
   * @return RedirectResponse
   */
  public function resetAllApkAction()
  {
    /**
     * @var Program
     */
    $datagrid = $this->admin->getDatagrid();

    $objects = $datagrid->getResults();

    foreach ($objects as $program)
    {
      $program->setApkStatus(Program::APK_NONE);
      $this->admin->update($program);
    }

    if (0 != count($objects))
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
   * @throws \Exception
   *
   * @return RedirectResponse
   */
  public function rebuildAllApkAction()
  {
    /* @var $program Program */

    $datagrid = $this->admin->getDatagrid();

    $objects = $datagrid->getResults();
    $dispatcher = $this->container->get('App\Catrobat\Services\Ci\JenkinsDispatcher');

    foreach ($objects as $program)
    {
      $dispatcher->sendBuildRequest($program->getId());
      $program->setApkRequestTime(TimeUtils::getDateTime());
      $program->setApkStatus(Program::APK_PENDING);
      $this->admin->update($program);
    }

    if (0 != count($objects))
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
