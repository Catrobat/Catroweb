<?php

namespace App\Admin\Comments\ReportedComments;

use App\DB\Entity\Project\Project;
use App\DB\Entity\User\Comment\UserComment;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

/**
 * @phpstan-extends AbstractAdmin<UserComment>
 */
class ReportedCommentsAdmin extends AbstractAdmin
{
  protected $baseRouteName = 'admin_report';

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
   * {@inheritdoc}
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
   * {@inheritdoc}
   *
   * Fields to be shown on lists
   */
  protected function configureListFields(ListMapper $list): void
  {
    $list
      ->add('id')
      ->add('project', EntityType::class,
        [
          'class' => Project::class,
          'editable' => false,
        ])
      ->add('user')
      ->add('uploadDate')
      ->add('text')
      ->add('username')
      ->add(ListMapper::NAME_ACTIONS, null, ['actions' => [
        'delete' => ['template' => 'Admin/CRUD/list__action_delete_comment.html.twig'],
        'unreportComment' => ['template' => 'Admin/CRUD/list__action_unreportComment.html.twig'],
      ]])
    ;
  }

  protected function configureRoutes(RouteCollectionInterface $collection): void
  {
    $collection->add('deleteComment');
    $collection->add('unreportComment');
    $collection->remove('create')->remove('delete')->remove('export');
  }
}
