<?php

namespace App\Admin\Translation;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class CommentMachineTranslationAdmin extends AbstractAdmin
{
  /**
   * @var string
   */
  protected $baseRouteName = 'admin_catrobat_adminbundle_comment_machine_translation';

  /**
   * @var string
   */
  protected $baseRoutePattern = 'comment_machine_translation';

  /**
   * @param ListMapper $list
   *
   * Fields to be shown on lists
   */
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

  protected function configureRoutes(RouteCollection $collection): void
  {
    $collection
      ->remove('export')
      ->remove('acl')
      ->remove('delete')
      ->remove('create')
      ->add('trim')
    ;
  }
}
