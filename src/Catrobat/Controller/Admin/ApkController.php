<?php

namespace App\Catrobat\Controller\Admin;

use App\Catrobat\Services\Ci\JenkinsDispatcher;
use App\Entity\Program;
use App\Utils\TimeUtils;
use Exception;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ApkController extends CRUDController
{
  public function resetStatusAction(): RedirectResponse
  {
    /** @var Program|null $object */
    $object = $this->admin->getSubject();

    if (null === $object)
    {
      throw new NotFoundHttpException();
    }

    $object->setApkStatus(Program::APK_NONE);

    $this->admin->update($object);

    $this->addFlash('sonata_flash_success', 'Reseted APK status of '.$object->getName());

    return new RedirectResponse($this->admin->generateUrl('list'));
  }

  /**
   * @throws Exception
   */
  public function rebuildApkAction(): RedirectResponse
  {
    /** @var Program|null $object */
    $object = $this->admin->getSubject();

    if (null === $object)
    {
      throw new NotFoundHttpException();
    }

    $dispatcher = $this->container->get(JenkinsDispatcher::class);
    $dispatcher->sendBuildRequest($object->getId());

    $object->setApkRequestTime(TimeUtils::getDateTime());
    $object->setApkStatus(Program::APK_PENDING);

    $this->admin->update($object);

    $this->addFlash('sonata_flash_success', 'Requested a rebuild of '.$object->getName());

    return new RedirectResponse($this->admin->generateUrl('list'));
  }

  public function resetAllApkAction(): RedirectResponse
  {
    $data_grid = $this->admin->getDatagrid();
    $objects = $data_grid->getResults();

    /** @var Program $program */
    foreach ($objects as $program)
    {
      $program->setApkStatus(Program::APK_NONE);
      $this->admin->update($program);
    }

    if (0 != (is_countable($objects) ? count($objects) : 0))
    {
      $this->addFlash('sonata_flash_success', 'All APKs reset');
    }
    else
    {
      $this->addFlash('sonata_flash_info', 'No APKs to reset');
    }

    return new RedirectResponse($this->admin->generateUrl('list'));
  }

  /**
   * @throws Exception
   */
  public function rebuildAllApkAction(): RedirectResponse
  {
    $data_grid = $this->admin->getDatagrid();

    $objects = $data_grid->getResults();
    $dispatcher = $this->container->get(JenkinsDispatcher::class);

    /* @var $program Program */
    foreach ($objects as $program)
    {
      $dispatcher->sendBuildRequest($program->getId());
      $program->setApkRequestTime(TimeUtils::getDateTime());
      $program->setApkStatus(Program::APK_PENDING);
      $this->admin->update($program);
    }

    if (0 != count($objects))
    {
      $this->addFlash('sonata_flash_success', 'Requested rebuild for all APks');
    }
    else
    {
      $this->addFlash('sonata_flash_info', 'No Rebuild-Requests were sent');
    }

    return new RedirectResponse($this->admin->generateUrl('list'));
  }
}
