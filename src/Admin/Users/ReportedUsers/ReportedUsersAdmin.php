<?php

namespace App\Admin\Users\ReportedUsers;

use Doctrine\ORM\Query\Expr\Join;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;

class ReportedUsersAdmin extends AbstractAdmin
{
  /**
   * @var string
   */
  protected $baseRouteName = 'admin_reported_users';

  /**
   * @var string
   */
  protected $baseRoutePattern = 'reported_users';

  protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
  {
    /** @var ProxyQuery $query */
    $query = parent::configureQuery($query);

    $qb = $query->getQueryBuilder();

    $rootAlias = $qb->getRootAliases()[0];
    $parameters = $this->getFilterParameters();

    if ('getReportedCommentsCount' === $parameters['_sort_by']) {
      $qb->from('App\Entity\User', 'fos_user')
        ->leftJoin('App\Entity\UserComment', 'user_comment', Join::WITH, $rootAlias.'.id=user_comment.user')
        ->leftJoin('App\Entity\Program', 'p', Join::WITH, $rootAlias.'.id = p.user')
        ->leftJoin('App\Entity\ProgramInappropriateReport', 'repProg', Join::WITH, 'p.id = repProg.program')
        ->where($qb->expr()->eq('user_comment.isReported', '1'))
        ->groupBy($rootAlias.'.id')
        ->orderBy('COUNT(user_comment.user )', $parameters['_sort_order'])
          ;
    } elseif ('getProgramInappropriateReportsCount' === $parameters['_sort_by']) {
      $qb->from('App\Entity\User', 'fos_user')
        ->leftJoin('App\Entity\UserComment', 'user_comment', Join::WITH, $rootAlias.'.id=user_comment.user')
        ->leftJoin('App\Entity\Program', 'p', Join::WITH, $rootAlias.'.id = p.user')
        ->leftJoin('App\Entity\ProgramInappropriateReport', 'repProg', Join::WITH, 'p.id = repProg.program')
        ->where($qb->expr()->isNotNull('repProg.program'))
        ->groupBy($rootAlias.'.id')
        ->orderBy('COUNT(repProg.program)', $parameters['_sort_order'])
      ;
    } else {
      $qb->from('App\Entity\User', 'fos_user')
        ->leftJoin('App\Entity\UserComment', 'user_comment', Join::WITH, $rootAlias.'.id=user_comment.user')
        ->leftJoin('App\Entity\Program', 'p', Join::WITH, $rootAlias.'.id = p.user')
        ->leftJoin('App\Entity\ProgramInappropriateReport', 'repProg', Join::WITH, 'p.id = repProg.program')
        ->where($qb->expr()->eq('user_comment.isReported', '1'))->orWhere($qb->expr()->isNotNull('repProg.program'))
     ;
    }

    return $query;
  }

  protected function configureListFields(ListMapper $list): void
  {
    $list
      ->add('id')
      ->add('username')
      ->add('email')
      ->add('_action', 'actions', ['actions' => [
        'createUrlComments' => ['template' => 'Admin/CRUD/list__action_create_url_comments.html.twig'],
        'createUrlPrograms' => ['template' => 'Admin/CRUD/list__action_create_url_programs.html.twig'],
      ]])
      ->add(
          'getReportedCommentsCount',
          null,
          [
            'label' => '#Reported Comments',
            'sortable' => true,
            'sort_field_mapping' => ['fieldName' => 'id'],
            'sort_parent_association_mappings' => [],
          ])
      ->add(
          'getProgramInappropriateReportsCount',
          null,
          [
            'label' => '#Reported Programs',
            'sortable' => true,
            'sort_field_mapping' => ['fieldName' => 'id'],
            'sort_parent_association_mappings' => [],
          ])
    ;
  }

  /**
   * @param DatagridMapper $filter
   *
   * Fields to be shown on filter forms
   */
  protected function configureDatagridFilters(DatagridMapper $filter): void
  {
    $filter->add('username', null, [
      'show_filter' => true,
    ])
      ->add('email')
      ;
  }

  protected function configureRoutes(RouteCollection $collection): void
  {
    $collection->remove('create')->remove('delete');
    $collection->add('createUrlComments');
    $collection->add('createUrlPrograms');
  }
}
