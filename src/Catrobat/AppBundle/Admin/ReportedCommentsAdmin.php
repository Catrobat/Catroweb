<?php
/**
 * Created by PhpStorm.
 * User: eric
 * Date: 20.03.16
 * Time: 17:23
 */

namespace Catrobat\AppBundle\Admin;

use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class ReportedCommentsAdmin extends AbstractAdmin
{
  protected $baseRouteName = 'admin_report';
  protected $baseRoutePattern = 'report';


  public function createQuery($context = 'list')
  {
    /**
     * @var $query QueryBuilder
     */
    $query = parent::createQuery();
    $query->andWhere(
      $query->expr()->eq($query->getRootAlias() . '.isReported', $query->expr()->literal(true))
    );

    return $query;
  }


  // Fields to be shown on filter forms
  protected function configureDatagridFilters(DatagridMapper $datagridMapper)
  {
  }

  // Fields to be shown on lists
  protected function configureListFields(ListMapper $listMapper)
  {
    $listMapper
      ->add('id')
      ->add('programId')
      ->add('userId')
      ->add('uploadDate')
      ->add('text')
      ->add('username')
      ->add('_action', 'actions', ['actions' => [
        'delete'          => ['template' => 'CRUD/list__action_delete_comment.html.twig'],
        'unreportComment' => ['template' => 'CRUD/list__action_unreportComment.html.twig'],
      ]]);
  }


  protected function configureRoutes(RouteCollection $collection)
  {
    $collection->add('deleteComment');
    $collection->add('unreportComment');
    $collection->remove('create')->remove('delete')->remove('export');
  }

}