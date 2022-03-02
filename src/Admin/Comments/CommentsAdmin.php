<?php

namespace App\Admin\Comments;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class CommentsAdmin extends AbstractAdmin
{
  /**
   * @var string
   */
  protected $baseRouteName = 'admin_catrobat_adminbundle_commentsadmin';

  /**
   * @var string
   */
  protected $baseRoutePattern = 'comments';

  /**
   * @var array
   */
  protected $datagridValues = [
    '_sort_by' => 'uploaded_at',
    '_sort_order' => 'DESC',
  ];

  /**
   * @param ListMapper $list
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

  protected function configureRoutes(RouteCollection $collection): void
  {
    $collection->remove('create')->remove('delete')->remove('export');
  }
}
