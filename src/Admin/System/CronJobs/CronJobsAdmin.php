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
      ->add('state')
      ->add('cron_interval')
      ->add('start_at')
      ->add('end_at')
      ->add('result_code')
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
