<?php

declare(strict_types=1);

namespace App\Admin\Statistics\Translation;

use App\DB\Entity\Translation\CommentMachineTranslation;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

/**
 * @phpstan-extends AbstractAdmin<CommentMachineTranslation>
 */
class CommentMachineTranslationAdmin extends AbstractAdmin
{
  protected $baseRouteName = 'admin_catrobat_adminbundle_comment_machine_translation';

  protected $baseRoutePattern = 'comment_machine_translation';

  #[\Override]
  protected function configureExportFields(): array
  {
    return ['id', 'comment.id', 'source_language', 'target_language', 'provider', 'usage_count',
      'usage_per_month', 'last_modified_at', 'created_at', ];
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
      ->add('comment.id')
      ->add('source_language')
      ->add('target_language')
      ->add('provider')
      ->add('usage_count')
      ->add('usage_per_month')
      ->add('last_modified_at')
      ->add('created_at')
    ;
  }

  #[\Override]
  protected function configureRoutes(RouteCollectionInterface $collection): void
  {
    $collection
      ->remove('acl')
      ->remove('delete')
      ->remove('create')
      ->add('trim')
    ;
  }
}
