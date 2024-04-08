<?php

declare(strict_types=1);

namespace App\Admin\DB_Updater;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

/**
 * @phpstan-extends AbstractAdmin<\stdClass>
 */
class SpecialUpdaterAdmin extends AbstractAdmin
{
  protected $baseRouteName = 'admin_catrobat_adminbundle_specialupdateradmin';

  protected $baseRoutePattern = 'special_updater';

  protected function configureRoutes(RouteCollectionInterface $collection): void
  {
    $collection
      ->remove('export')
      ->remove('acl')
      ->remove('delete')
      ->remove('create')
      ->add('update_special')
    ;
  }
}
