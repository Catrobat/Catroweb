<?php

declare(strict_types=1);

namespace App\Admin\Tools\BroadcastNotification;

use App\DB\Entity\User\Notifications\BroadcastNotification;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

/**
 * @phpstan-extends AbstractAdmin<BroadcastNotification>
 */
class BroadcastNotificationAdmin extends AbstractAdmin
{
  protected $baseRouteName = 'admin_broadcast';

  protected $baseRoutePattern = 'broadcast';

  protected function configureRoutes(RouteCollectionInterface $collection): void
  {
    $collection->clearExcept(['list']);
    $collection->add('send');
  }
}
