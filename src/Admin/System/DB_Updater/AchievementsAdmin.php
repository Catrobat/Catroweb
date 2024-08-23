<?php

declare(strict_types=1);

namespace App\Admin\System\DB_Updater;

use App\DB\Entity\User\Achievements\Achievement;
use App\User\Achievements\AchievementManager;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

/**
 * @phpstan-extends AbstractAdmin<Achievement>
 */
class AchievementsAdmin extends AbstractAdmin
{
  #[\Override]
  protected function generateBaseRouteName(bool $isChildAdmin = false): string
  {
    return 'admin_achievements';
  }

  #[\Override]
  protected function generateBaseRoutePattern(bool $isChildAdmin = false): string
  {
    return 'system/db/achievements';
  }

  public function __construct(
    protected AchievementManager $achievement_manager
  ) {
  }

  #[\Override]
  protected function configureRoutes(RouteCollectionInterface $collection): void
  {
    $collection
      ->remove('export')
      ->remove('acl')
      ->remove('delete')
      ->remove('create')
      ->add('update_achievements')
    ;
  }

  public function getUnlockedByCount(mixed $object): int
  {
    $id = $object->getId();

    return $this->achievement_manager->countUserAchievementsOfAchievement($id);
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
      ->add('priority')
      ->add('internal_title')
      ->add('internal_description')
      ->add('badge_svg_path', null, ['template' => 'Admin/SystemManagement/DbUpdater/Achievements/achievement_badge_image.html.twig'])
      ->add('badge_locked_svg_path', null, ['template' => 'Admin/SystemManagement/DbUpdater/Achievements/achievement_badge_locked_image.html.twig'])
      ->add('banner_color')
      ->add('enabled')
      ->add('unlocked_by', 'string', [
        'accessor' => fn ($subject): string => $this->getUnlockedByCount($subject).' users',
      ])
    ;
  }
}
