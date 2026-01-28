<?php

declare(strict_types=1);

namespace App\Admin\MediaLibrary;

use App\DB\Entity\MediaLibrary\MediaCategory;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * @phpstan-extends AbstractAdmin<MediaCategory>
 */
class MediaCategoryAdmin extends AbstractAdmin
{
  #[\Override]
  protected function generateBaseRouteName(bool $isChildAdmin = false): string
  {
    return 'admin_media_library_category';
  }

  #[\Override]
  protected function generateBaseRoutePattern(bool $isChildAdmin = false): string
  {
    return 'media-library/category';
  }

  #[\Override]
  protected function configureDefaultSortValues(array &$sortValues): void
  {
    $sortValues[DatagridInterface::SORT_BY] = 'priority';
    $sortValues[DatagridInterface::SORT_ORDER] = 'ASC';
  }

  #[\Override]
  protected function configureFormFields(FormMapper $form): void
  {
    $form
      ->add('name', TextType::class)
      ->add('description', TextareaType::class, ['required' => false])
      ->add('priority', IntegerType::class)
    ;
  }

  #[\Override]
  protected function configureDatagridFilters(DatagridMapper $filter): void
  {
    $filter
      ->add('name')
      ->add('priority')
    ;
  }

  #[\Override]
  protected function configureListFields(ListMapper $list): void
  {
    $list
      ->addIdentifier('name')
      ->add('description')
      ->add('priority')
      ->add('created_at')
      ->add('updated_at')
      ->add(ListMapper::NAME_ACTIONS, null, ['actions' => ['edit' => [], 'delete' => []]])
    ;
  }

  #[\Override]
  protected function configureShowFields(ShowMapper $show): void
  {
    $show
      ->add('name')
      ->add('description')
      ->add('priority')
      ->add('created_at')
      ->add('updated_at')
    ;
  }

  #[\Override]
  protected function configureRoutes(RouteCollectionInterface $collection): void
  {
    $collection->clearExcept(['list', 'create', 'edit', 'delete', 'show']);
  }
}
