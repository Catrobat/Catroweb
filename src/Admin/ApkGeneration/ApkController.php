<?php

declare(strict_types=1);

namespace App\Admin\ApkGeneration;

use App\DB\Entity\Project\Program;
use App\Project\Apk\JenkinsDispatcher;
use App\Project\ProjectManager;
use App\Utils\TimeUtils;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @phpstan-extends CRUDController<Program>
 */
class ApkController extends CRUDController
{
  public function __construct(
    protected ProjectManager $project_manager,
    protected JenkinsDispatcher $jenkins_dispatcher,
    protected EntityManagerInterface $entity_manager
  ) {
  }

  public function resetApkBuildStatusAction(): RedirectResponse
  {
    /** @var Program|null $project */
    $project = $this->admin->getSubject();

    if (null === $project) {
      $this->addFlash('sonata_flash_error', 'Can\'t reset APK status');
    } else {
      $project->setApkStatus(Program::APK_NONE);
      $project->setApkRequestTime(null);
      $this->admin->update($project);
      $this->addFlash('sonata_flash_success', 'Reset APK status of '.$project->getName().' successful');
    }

    return new RedirectResponse($this->admin->generateUrl('list'));
  }

  /**
   * @throws \Exception
   */
  public function requestApkRebuildAction(): RedirectResponse
  {
    /** @var Program|null $project */
    $project = $this->admin->getSubject();

    if (null === $project) {
      $this->addFlash('sonata_flash_error', 'Can\'t trigger APK rebuild');
    } else {
      $this->jenkins_dispatcher->sendBuildRequest($project->getId());
      $project->setApkRequestTime(TimeUtils::getDateTime());
      $project->setApkStatus(Program::APK_PENDING);
      $this->admin->update($project);
      $this->addFlash('sonata_flash_success', 'Requested a rebuild of '.$project->getName());
    }

    return new RedirectResponse($this->admin->generateUrl('list'));
  }

  public function resetPendingProjectsAction(): RedirectResponse
  {
    $this->entity_manager->createQueryBuilder()
      ->update(Program::class, 'p')
      ->set('p.apk_status', ':apk_none')
      ->set('p.apk_request_time', ':time')
      ->where('p.apk_status = :apk_pending')
      ->setParameter('apk_none', Program::APK_NONE)
      ->setParameter('time', null)
      ->setParameter('apk_pending', Program::APK_PENDING)
      ->getQuery()
      ->execute()
    ;

    $this->addFlash('sonata_flash_success', 'All pending APKs have been reset');

    return new RedirectResponse($this->admin->generateUrl('list'));
  }

  /**
   * @throws \Exception
   */
  public function rebuildAllApkAction(): RedirectResponse
  {
    $projects = $this->project_manager->findBy(['apk_status' => Program::APK_PENDING]);

    /* @var $project Program */
    foreach ($projects as $project) {
      $this->jenkins_dispatcher->sendBuildRequest($project->getId());
      $project->setApkRequestTime(TimeUtils::getDateTime());
      $project->setApkStatus(Program::APK_PENDING);
      $this->admin->update($project);
    }

    $this->addFlash('sonata_flash_success', 'A new build request for all pending APKs has been sent');

    return new RedirectResponse($this->admin->generateUrl('list'));
  }
}
