<?php

declare(strict_types=1);

namespace App\Admin\DB_Updater;

use App\DB\Entity\Flavor;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

/**
 * @phpstan-extends AbstractAdmin<Flavor>
 */
class FlavorsAdmin extends AbstractAdmin
{
  protected $baseRouteName = 'admin_catrobat_adminbundle_flavorssadmin';

  protected $baseRoutePattern = 'flavors';

  #[\Override]
  protected function configureRoutes(RouteCollectionInterface $collection): void
  {
    $collection
      ->remove('export')
      ->remove('acl')
      ->remove('delete')
      ->remove('create')
      ->add('update_flavors')
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
    ;
  }
}
