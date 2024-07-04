<?php

declare(strict_types=1);

namespace App\Admin\System\Maintenance;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

/**
 * @phpstan-extends AbstractAdmin<\stdClass>
 */
class MaintenanceAdmin extends AbstractAdmin
{
  #[\Override]
  protected function generateBaseRouteName(bool $isChildAdmin = false): string
  {
    return 'admin_maintenance';
  }

  #[\Override]
  protected function generateBaseRoutePattern(bool $isChildAdmin = false): string
  {
    return 'system/maintenance';
  }

  #[\Override]
  protected function configureRoutes(RouteCollectionInterface $collection): void
  {
    // Find the implementation in the Controller-Folder
    $collection->clearExcept(['list']);
    $collection->add('apk')
      ->add('compressed')
      ->add('archive_logs')
      ->add('delete_logs')
    ;
  }
}
