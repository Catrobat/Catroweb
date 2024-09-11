<?php

declare(strict_types=1);

namespace App\Admin\ApkGeneration;

use App\DB\Entity\Project\Program;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\DateTimeRangeFilter;
use Sonata\Form\Type\DateTimeRangePickerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType as SymfonyChoiceType;

class ApkPendingAdmin extends ApkAdmin
{
  #[\Override]
  protected function generateBaseRouteName(bool $isChildAdmin = false): string
  {
    return 'admin_catrobat_apk_pending';
  }

  #[\Override]
  protected function generateBaseRoutePattern(bool $isChildAdmin = false): string
  {
    return 'catrobat/apk/pending';
  }

  #[\Override]
  protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
  {
    /** @var ProxyQuery $query */
    $query = parent::configureQuery($query);
    $query->getQueryBuilder()->setParameter('apk_status', Program::APK_PENDING);

    return $query;
  }

  /**
   * {@inheritdoc}
   *
   * Fields to be shown on filter forms
   */
  #[\Override]
  protected function configureDatagridFilters(DatagridMapper $filter): void
  {
    $filter
      ->add('id')
      ->add('user.username', null, ['label' => 'User'])
      ->add('name')
      ->add('apk_request_time', DateTimeRangeFilter::class,
        [
          'field_type' => DateTimeRangePickerType::class,
        ])
      ->add('apk_status', null,
        [
          'field_type' => SymfonyChoiceType::class,
          'field_options' => ['choices' => ['None' => Program::APK_NONE, 'Pending' => Program::APK_PENDING, 'Ready' => Program::APK_READY]],
        ])
    ;
  }

  /**
   * {@inheritdoc}
   *
   * Fields to be shown on lists
   */
  #[\Override]
  protected function configureListFields(ListMapper $list): void
  {
    $list
      ->add('id')
      ->add('user', null, [
        'route' => [
          'name' => 'show',
        ],
      ])
      ->add('name')
      ->add('apk_request_time')
      ->add('thumbnail', 'string', [
        'accessor' => fn ($subject): string => $this->getThumbnailImageUrl($subject),
        'template' => 'Admin/Projects/ThumbnailImageList.html.twig',
      ])
      ->add('apk_status', 'choice', [
        'choices' => [
          Program::APK_NONE => 'None',
          Program::APK_PENDING => 'Pending',
          Program::APK_READY => 'Ready',
        ], ])
      ->add(ListMapper::NAME_ACTIONS, null, [
        'actions' => [
          'Reset' => [
            'template' => 'Admin/CRUD/list__action_reset_status.html.twig',
          ],
          'Rebuild' => [
            'template' => 'Admin/CRUD/list__action_rebuild_apk.html.twig',
          ],
        ],
      ])
    ;
  }

  #[\Override]
  protected function configureRoutes(RouteCollectionInterface $collection): void
  {
    $collection->add('resetApkBuildStatus', $this->getRouterIdParameter().'/resetApkBuildStatus');
    $collection->add('requestApkRebuild', $this->getRouterIdParameter().'/requestApkRebuild');
    $collection->add('rebuildAllApk');
    $collection->add('resetPendingProjects');
    $collection->remove('create')->remove('delete')->remove('export');
  }
}
