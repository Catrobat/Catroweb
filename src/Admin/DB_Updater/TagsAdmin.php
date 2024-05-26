<?php

declare(strict_types=1);

namespace App\Admin\DB_Updater;

use App\DB\Entity\Project\Tag;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

/**
 * @phpstan-extends AbstractAdmin<Tag>
 */
class TagsAdmin extends AbstractAdmin
{
  protected $baseRouteName = 'admin_catrobat_adminbundle_tagssadmin';

  protected $baseRoutePattern = 'tags';

  #[\Override]
  protected function configureRoutes(RouteCollectionInterface $collection): void
  {
    $collection
      ->remove('export')
      ->remove('acl')
      ->remove('delete')
      ->remove('create')
      ->add('update_tags')
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
      ->add('internal_title')
      ->add('enabled')
      ->add('projects_with_tag', 'int', ['accessor' => 'getProjectCount'])
    ;
  }
}
