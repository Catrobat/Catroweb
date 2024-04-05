<?php

declare(strict_types=1);

namespace App\Admin\Tools\SendMailToUser;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

/**
 * @phpstan-extends AbstractAdmin<\stdClass>
 */
class SendMailToUserAdmin extends AbstractAdmin
{
  protected $baseRouteName = 'admin_mail';

  protected $baseRoutePattern = 'mail';

  protected function configureRoutes(RouteCollectionInterface $collection): void
  {
    $collection->clearExcept(['list']);
    $collection->add('send');
    $collection->add('preview');
  }
}
