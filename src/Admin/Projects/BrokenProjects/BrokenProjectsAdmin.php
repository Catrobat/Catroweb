<?php

declare(strict_types=1);

namespace App\Admin\Projects\BrokenProjects;

use App\DB\Entity\Project\Program;
use App\Storage\ScreenshotRepository;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\DateTimeRangeFilter;
use Sonata\Form\Type\DateTimeRangePickerType;

/**
 * @phpstan-extends AbstractAdmin<Program>
 */
class BrokenProjectsAdmin extends AbstractAdmin
{
  #[\Override]
  protected function generateBaseRouteName(bool $isChildAdmin = false): string
  {
    return 'admin_broken_projects';
  }

  #[\Override]
  protected function generateBaseRoutePattern(bool $isChildAdmin = false): string
  {
    return 'project/broken';
  }

  public function __construct(
    private readonly ScreenshotRepository $screenshot_repository,
  ) {
  }

  #[\Override]
  protected function configureDefaultSortValues(array &$sortValues): void
  {
    $sortValues[DatagridInterface::SORT_BY] = 'uploaded_at';
    $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
  }

  #[\Override]
  protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
  {
    /** @var ProxyQuery $query */
    $query = parent::configureQuery($query);

    $qb = $query->getQueryBuilder();
    $qb->andWhere(
      $qb->expr()->eq($qb->getRootAliases()[0].'.has_missing_files', $qb->expr()->literal(true))
    );

    /* @psalm-suppress LessSpecificReturnStatement, MoreSpecificReturnType */
    return $query; // @phpstan-ignore return.type
  }

  public function getThumbnailImageUrl(mixed $object): string
  {
    $id = $object->getId();
    if (null === $id) {
      return '';
    }

    return '/'.$this->screenshot_repository->getThumbnailWebPath($id);
  }

  #[\Override]
  protected function configureDatagridFilters(DatagridMapper $filter): void
  {
    $filter
      ->add('id')
      ->add('name')
      ->add('user.username', null, ['label' => 'Username'])
      ->add('uploaded_at', DateTimeRangeFilter::class, [
        'field_type' => DateTimeRangePickerType::class,
        'label' => 'Upload Time',
      ])
    ;
  }

  #[\Override]
  protected function configureListFields(ListMapper $list): void
  {
    $list
      ->add('uploaded_at', null, ['label' => 'Upload Time'])
      ->addIdentifier('id')
      ->add('name')
      ->add('user')
      ->add('thumbnail', 'string', [
        'accessor' => $this->getThumbnailImageUrl(...),
        'template' => 'Admin/Projects/ThumbnailImageList.html.twig',
      ])
      ->add('downloads')
      ->add('views')
      ->add(ListMapper::NAME_ACTIONS, null, [
        'actions' => [
          'show' => ['template' => 'Admin/CRUD/list__action_show_project_details.html.twig'],
          'delete' => [],
        ],
      ])
    ;
  }

  #[\Override]
  protected function configureRoutes(RouteCollectionInterface $collection): void
  {
    $collection->remove('create')->remove('edit')->remove('export');
  }

  #[\Override]
  protected function configureBatchActions(array $actions): array
  {
    $actions['delete'] = [];

    return $actions;
  }
}
