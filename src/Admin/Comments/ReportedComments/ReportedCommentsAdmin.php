<?php

namespace App\Admin\Comments\ReportedComments;

use App\DB\Entity\Project\Program;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

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

  protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
  {
    /** @var ProxyQuery $query */
    $query = parent::configureQuery($query);

    $qb = $query->getQueryBuilder();

    $qb->andWhere(
      $qb->expr()->eq($qb->getRootAliases()[0].'.isReported', $qb->expr()->literal(true))
    );

    return $query;
  }

  /**
   * @param DatagridMapper $filter
   *
   * Fields to be shown on filter forms
   */
  protected function configureDatagridFilters(DatagridMapper $filter): void
  {
    $filter
      ->add('user')
          ;
  }

  /**
   * @param ListMapper $list
   *
   * Fields to be shown on lists
   */
  protected function configureListFields(ListMapper $list): void
  {
    $list
      ->add('id')
      ->add('program', EntityType::class,
        [
          'class' => Program::class,
          'editable' => false,
        ])
      ->add('user')
      ->add('uploadDate')
      ->add('text')
      ->add('username')
      ->add('_action', 'actions', ['actions' => [
        'delete' => ['template' => 'Admin/CRUD/list__action_delete_comment.html.twig'],
        'unreportComment' => ['template' => 'Admin/CRUD/list__action_unreportComment.html.twig'],
      ]])
    ;
  }

  protected function configureRoutes(RouteCollection $collection): void
  {
    $collection->add('deleteComment');
    $collection->add('unreportComment');
    $collection->remove('create')->remove('delete')->remove('export');
  }
}
