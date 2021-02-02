<?php

namespace App\Admin;

use App\Entity\User;
use Doctrine\ORM\Query\Expr\Join;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

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

    if ('getReportedCommentsCount' === $parameters['_sort_by'])
    {
      $qb->leftJoin('App\Entity\UserComment', 'cm',
            Join::WITH, $rootAlias.'.id = cm.user')
        ->where($qb->expr()->eq('cm.isReported', '1'))
        ->groupBy($rootAlias.'.id')
        ->orderBy('COUNT(cm.user )', $parameters['_sort_order'])
          ;
    }

    if ('getProgramInappropriateReportsCount' === $parameters['_sort_by'])
    {
      $qb
        ->leftJoin('App\Entity\Program', 'p', Join::WITH,
              $rootAlias.'.id = p.user')
        ->leftJoin('App\Entity\ProgramInappropriateReport',
              'pr', Join::WITH, 'p.id = pr.program')
        ->where($qb->expr()->isNotNull('pr.program'))
        ->groupBy($rootAlias.'.id')
        ->orderBy('COUNT(pr.program)', $parameters['_sort_order'])
          ;
    }

    return $query;
  }

  protected function configureFormFields(FormMapper $form): void
  {
    $form
      ->add('user', EntityType::class, ['class' => User::class])
      ;
  }

  protected function configureListFields(ListMapper $list): void
  {
    $list
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
      ->add('username')
      ->add('email')
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
  }
}
