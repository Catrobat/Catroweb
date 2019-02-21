<?php

namespace Catrobat\AppBundle\Admin;

use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Route\RouteCollection;


/**
 * Class ReportedCommentsAdmin
 * @package Catrobat\AppBundle\Admin
 */
class ReportedCommentsAdmin extends AbstractAdmin
{

  /**
   * @var string
   */
  protected $baseRouteName = 'admin_report';

  /**
   * @var string
   */
  protected $baseRoutePattern = 'report';


  /**
   * @param string $context
   *
   * @return QueryBuilder
   */
  public function createQuery($context = 'list')
  {
    /**
     * @var $query QueryBuilder
     */
    $query = parent::createQuery();
    $query->andWhere(
      $query->expr()->eq($query->getRootAliases()[0] . '.isReported', $query->expr()->literal(true))
    );

    return $query;
  }


  /**
   * @param DatagridMapper $datagridMapper
   *
   * Fields to be shown on filter forms
   */
  protected function configureDatagridFilters(DatagridMapper $datagridMapper)
  {
  }


  /**
   * @param ListMapper $listMapper
   *
   * Fields to be shown on lists
   */
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
        'delete'          => ['template' => 'Admin/CRUD/list__action_delete_comment.html.twig'],
        'unreportComment' => ['template' => 'Admin/CRUD/list__action_unreportComment.html.twig'],
      ]]);
  }


  /**
   * @param RouteCollection $collection
   */
  protected function configureRoutes(RouteCollection $collection)
  {
    $collection->add('deleteComment');
    $collection->add('unreportComment');
    $collection->remove('create')->remove('delete')->remove('export');
  }
}