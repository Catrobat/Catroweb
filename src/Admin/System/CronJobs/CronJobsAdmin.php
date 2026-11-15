<?php

declare(strict_types=1);

namespace App\Admin\System\CronJobs;

use App\DB\Entity\System\CronJob;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

/**
 * Background tasks page; its list is rendered by CronJobsAdminController::listAction.
 *
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
    return 'system/background-tasks';
  }

  #[\Override]
  protected function configureRoutes(RouteCollectionInterface $collection): void
  {
    $collection
      ->remove('export')
      ->remove('acl')
      ->remove('delete')
      ->remove('create')
      ->add('reset_cron_job')
      ->add('retry_failed_job')
      ->add('remove_failed_job')
    ;
  }
}
