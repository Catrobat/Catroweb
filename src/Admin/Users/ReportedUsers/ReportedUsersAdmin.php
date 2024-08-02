<?php

declare(strict_types=1);

namespace App\Admin\Users\ReportedUsers;

use App\DB\Entity\Project\Program;
use App\DB\Entity\Project\ProgramInappropriateReport;
use App\DB\Entity\User\Comment\UserComment;
use App\DB\Entity\User\User;
use Doctrine\ORM\Query\Expr\Join;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;

/**
 * @phpstan-extends AbstractAdmin<User>
 */
class ReportedUsersAdmin extends AbstractAdmin
{
  #[\Override]
  protected function generateBaseRouteName(bool $isChildAdmin = false): string
  {
    return 'admin_reported_users';
  }

  #[\Override]
  protected function generateBaseRoutePattern(bool $isChildAdmin = false): string
  {
    return 'user/report';
  }

  #[\Override]
  protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
  {
    /** @var ProxyQuery $query */
    $query = parent::configureQuery($query);

    $qb = $query->getQueryBuilder();

    $rootAlias = $qb->getRootAliases()[0];
    $parameters = $this->getFilterParameters();

    if (isset($parameters['_sort_by']) && 'getReportedCommentsCount' === $parameters['_sort_by']) {
      $qb
        ->leftJoin(UserComment::class, 'user_comment', Join::WITH, $rootAlias.'.id=user_comment.user')
        ->leftJoin(Program::class, 'p', Join::WITH, $rootAlias.'.id = p.user')
        ->leftJoin(ProgramInappropriateReport::class, 'repProg', Join::WITH, 'p.id = repProg.program')
        ->where($qb->expr()->eq('user_comment.isReported', '1'))
        ->groupBy($rootAlias.'.id')
        ->orderBy('COUNT(user_comment.user )', $parameters['_sort_order'])
      ;
    } elseif (isset($parameters['_sort_by']) && 'getReportsOfThisUserCount' === $parameters['_sort_by']) {
      $qb
        ->leftJoin(UserComment::class, 'user_comment', Join::WITH, $rootAlias.'.id=user_comment.user')
        ->leftJoin(Program::class, 'p', Join::WITH, $rootAlias.'.id = p.user')
        ->leftJoin(ProgramInappropriateReport::class, 'repProg', Join::WITH, 'p.id = repProg.program')
        ->where($qb->expr()->isNotNull('repProg.program'))
        ->groupBy($rootAlias.'.id')
        ->orderBy('COUNT(repProg.program)', $parameters['_sort_order'])
      ;
    } else {
      $qb
        ->leftJoin(UserComment::class, 'user_comment', Join::WITH, $rootAlias.'.id=user_comment.user')
        ->leftJoin(Program::class, 'p', Join::WITH, $rootAlias.'.id = p.user')
        ->leftJoin(ProgramInappropriateReport::class, 'repProg', Join::WITH, 'p.id = repProg.program')
        ->where($qb->expr()->eq('user_comment.isReported', '1'))->orWhere($qb->expr()->isNotNull('repProg.program'))
      ;
    }

    return $query;
  }

  #[\Override]
  protected function configureListFields(ListMapper $list): void
  {
    $list
      ->add('id')
      ->add('username')
      ->add('email')
      ->add(ListMapper::NAME_ACTIONS, null, ['actions' => [
        'createUrlComments' => ['template' => 'Admin/CRUD/list__action_create_url_comments.html.twig'],
        'createUrlProjects' => ['template' => 'Admin/CRUD/list__action_create_url_programs.html.twig'],
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
        'getReportsOfThisUserCount',
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
   * {@inheritdoc}
   *
   * Fields to be shown on filter forms
   */
  #[\Override]
  protected function configureDatagridFilters(DatagridMapper $filter): void
  {
    $filter->add('username', null, [
      'show_filter' => true,
    ])
      ->add('email')
    ;
  }

  #[\Override]
  protected function configureRoutes(RouteCollectionInterface $collection): void
  {
    $collection->remove('create')->remove('delete');
    $collection->add('createUrlComments');
    $collection->add('createUrlProjects');
  }
}
