<?php

namespace App\Admin\Comments;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

class CommentsAdmin extends AbstractAdmin
{
  /**
   * {@inheritdoc}
   */
  protected $baseRouteName = 'admin_catrobat_adminbundle_commentsadmin';

  /**
   * {@inheritdoc}
   */
  protected $baseRoutePattern = 'comments';

  /**
   * {@inheritDoc}
   */
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
  protected function configureListFields(ListMapper $list): void
  {
    $list
      ->add('uploadDate')
      ->add('username')
      ->add('text')
    ;
  }

  protected function configureRoutes(RouteCollectionInterface $collection): void
  {
    $collection->remove('create')->remove('delete')->remove('export');
  }
}
