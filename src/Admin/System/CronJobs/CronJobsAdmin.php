<?php

declare(strict_types=1);

namespace App\Admin\System\CronJobs;

use App\DB\Entity\System\CronJob;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

/**
 * @phpstan-extends AbstractAdmin<CronJob>
 */
class CronJobsAdmin extends AbstractAdmin
{
  #[\Override]
  protected function generateBaseRouteName(bool $isChildAdmin = false): string
  {
    return 'admin_cronjobs';
  }

  #[\Override]
  protected function generateBaseRoutePattern(bool $isChildAdmin = false): string
  {
    return 'system/cron-job';
  }

  #[\Override]
  protected function configureRoutes(RouteCollectionInterface $collection): void
  {
    $collection
      ->remove('export')
      ->remove('acl')
      ->remove('delete')
      ->remove('create')
      ->add('trigger_cron_jobs')
      ->add('reset_cron_job')
    ;
  }

  /**
   * {@inheritdoc}
   *
   * Fields to be shown on lists
   */
  #[\Override]
  protected function configureListFields(ListMapper $list): void
  {
    $list
      ->add('name')
      ->add('state', null, [
        'template' => 'Admin/SystemManagement/DbUpdater/cron_job_state.html.twig',
      ])
      ->add('cron_interval')
      ->add('start_at')
      ->add('duration_seconds', null, [
        'label' => 'Duration',
        'template' => 'Admin/SystemManagement/DbUpdater/cron_job_duration.html.twig',
      ])
      ->add('result_code', null, [
        'template' => 'Admin/SystemManagement/DbUpdater/cron_job_result.html.twig',
      ])
      ->add(ListMapper::NAME_ACTIONS, null, [
        'actions' => [
          'reset_cron_job' => [
            'template' => 'Admin/CRUD/list__action_reset_cron_job.html.twig',
          ],
        ],
      ])
    ;
  }
}
