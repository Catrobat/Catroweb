<?php

declare(strict_types=1);

namespace App\Admin\Comments;

use App\DB\Entity\User\Comment\UserComment;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

/**
 * @phpstan-extends AbstractAdmin<UserComment>
 */
class CommentsAdmin extends AbstractAdmin
{
  protected $baseRouteName = 'admin_catrobat_adminbundle_commentsadmin';

  protected $baseRoutePattern = 'comments';

  #[\Override]
  protected function configureDefaultSortValues(array &$sortValues): void
  {
    $sortValues[DatagridInterface::SORT_BY] = 'uploaded_at';
    $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
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
      ->add('uploadDate')
      ->add('username')
      ->add('text')
    ;
  }

  #[\Override]
  protected function configureRoutes(RouteCollectionInterface $collection): void
  {
    $collection->remove('create')->remove('delete')->remove('export');
  }
}
