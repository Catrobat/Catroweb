<?php

declare(strict_types=1);

namespace App\Admin\System\CronJobs;

use App\DB\Entity\System\CronJob;
use App\DB\EntityRepository\System\CronJobRepository;
use App\System\Commands\Helpers\CommandHelper;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @phpstan-extends CRUDController<CronJob>
 */
class CronJobsAdminController extends CRUDController
{
  public function __construct(
    protected CronJobRepository $cron_job_repository,
    protected EntityManagerInterface $entity_manager,
    protected KernelInterface $kernel
  ) {
  }

  #[\Override]
  public function listAction(Request $request): Response
  {
    return $this->render('Admin/SystemManagement/DbUpdater/CronJobs.html.twig', [
      'action' => 'reset_cron_job',
      'triggerCronJobsUrl' => $this->admin->generateUrl('trigger_cron_jobs'),
    ]);
  }

  /**
   * @throws \Exception
   */
  public function resetCronJobAction(Request $request): RedirectResponse
  {
    if (!$this->admin->isGranted('RESET_CRON_JOB')) {
      throw new AccessDeniedException();
    }

    $cron_job = $this->cron_job_repository->findByName(strval($request->query->get('id')));
    if (is_null($cron_job)) {
      $this->addFlash('sonata_flash_error', 'Resetting cron job failed');
    }

    $this->entity_manager->remove($cron_job);
    $this->entity_manager->flush();
    $this->addFlash('sonata_flash_success', 'Resetting cron job successful. Job will be executed and added back to the list on the next run.');

    return new RedirectResponse($this->admin->generateUrl('list'));
  }

  /**
   * @throws \Exception
   */
  public function triggerCronJobsAction(): RedirectResponse
  {
    if (!$this->admin->isGranted('TRIGGER_CRON_JOB')) {
      throw new AccessDeniedException();
    }

    $output = new BufferedOutput();
    $result = CommandHelper::executeShellCommand(
      ['bin/console', 'catrobat:cronjob'], ['timeout' => 86400], '', $output, $this->kernel
    );

    if (0 === $result) {
      $this->addFlash('sonata_flash_success', 'Cron jobs finished successfully');
    } else {
      $this->addFlash('sonata_flash_error', "Running cron jobs failed!\n".$output->fetch());
    }

    return new RedirectResponse($this->admin->generateUrl('list'));
  }
}
