<?php

namespace App\Catrobat\Controller\Admin;

use App\Catrobat\Services\Ci\JenkinsDispatcher;
use App\Entity\Program;
use App\Manager\ProgramManager;
use App\Utils\TimeUtils;
use Exception;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ApkController extends CRUDController
{
  protected ProgramManager $program_manager;

  public function __construct(ProgramManager $program_manager)
  {
    $this->program_manager = $program_manager;
  }

  public function resetStatusAction(): RedirectResponse
  {
    /** @var Program|null $object */
    $object = $this->admin->getSubject();

    if (null === $object) {
      throw new NotFoundHttpException();
    }

    $object->setApkStatus(Program::APK_NONE);
    $object->setApkRequestTime(null);

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

    if (null === $object) {
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
    $projects = $this->program_manager->findBy(['apk_status' => Program::APK_PENDING]);

    /** @var Program $program */
    foreach ($projects as $program) {
      $program->setApkStatus(Program::APK_NONE);
      $program->setApkRequestTime(null);
      $this->admin->update($program);
    }

    if (0 != count($projects)) {
      $this->addFlash('sonata_flash_success', 'All APKs reset');
    } else {
      $this->addFlash('sonata_flash_info', 'No APKs to reset');
    }

    return new RedirectResponse($this->admin->generateUrl('list'));
  }

  /**
   * @throws Exception
   */
  public function rebuildAllApkAction(): RedirectResponse
  {
    $projects = $this->program_manager->findBy(['apk_status' => Program::APK_PENDING]);
    $dispatcher = $this->container->get(JenkinsDispatcher::class);

    /* @var $program Program */
    foreach ($projects as $program) {
      $dispatcher->sendBuildRequest($program->getId());
      $program->setApkRequestTime(TimeUtils::getDateTime());
      $program->setApkStatus(Program::APK_PENDING);
      $this->admin->update($program);
    }

    if (0 != count($projects)) {
      $this->addFlash('sonata_flash_success', 'Requested rebuild for all APks');
    } else {
      $this->addFlash('sonata_flash_info', 'No Rebuild-Requests were sent');
    }

    return new RedirectResponse($this->admin->generateUrl('list'));
  }
}
