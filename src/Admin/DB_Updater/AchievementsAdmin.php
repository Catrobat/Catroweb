<?php

namespace App\Admin\DB_Updater;

use App\User\Achievements\AchievementManager;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class AchievementsAdmin extends AbstractAdmin
{
  /**
   * @var string
   */
  protected $baseRouteName = 'admin_catrobat_adminbundle_achievementsadmin';

  /**
   * @var string
   */
  protected $baseRoutePattern = 'achievements';

  protected AchievementManager $achievement_manager;

  public function __construct($code, $class, $baseControllerName, AchievementManager $achievement_manager)
  {
    parent::__construct($code, $class, $baseControllerName);

    $this->achievement_manager = $achievement_manager;
  }

  protected function configureRoutes(RouteCollection $collection): void
  {
    $collection
      ->remove('export')
      ->remove('acl')
      ->remove('delete')
      ->remove('create')
      ->add('update_achievements')
    ;
  }

  /**
   * @param mixed $object
   */
  public function getUnlockedByCount($object): int
  {
    $id = $object->getId();

    return $this->achievement_manager->countUserAchievementsOfAchievement($id);
  }

  /**
   * @param ListMapper $list
   *
   * Fields to be shown on lists
   */
  protected function configureListFields(ListMapper $list): void
  {
    $list
      ->add('priority')
      ->add('internal_title')
      ->add('internal_description')
      ->add('badge', null, ['template' => 'Admin/achievement_badge_image.html.twig'])
      ->add('badge_locked', null, ['template' => 'Admin/achievement_badge_locked_image.html.twig'])
      ->add('banner_color')
      ->add('enabled')
      ->add('unlocked_by', 'string', ['template' => 'Admin/achievement_unlocked_by.html.twig'])
    ;
  }
}
