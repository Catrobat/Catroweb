<?php

namespace App\Commands\DBUpdater;

use App\Entity\Achievements\Achievement;
use App\Repository\Achievements\AchievementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateAchievementsCommand extends Command
{
  /**
   * @var string|null
   *
   * @override from Command
   */
  protected static $defaultName = 'catrobat:update:achievements';

  protected EntityManagerInterface $entity_manager;
  protected AchievementRepository $achievement_repository;

  public const ACHIEVEMENT_IMAGE_ASSETS_PATH = 'images/achievements/';
  public const ACHIEVEMENT_LTM_PREFIX = 'achievements.achievement.type.';

  public function __construct(EntityManagerInterface $entity_manager, AchievementRepository $achievement_repository)
  {
    parent::__construct();
    $this->entity_manager = $entity_manager;
    $this->achievement_repository = $achievement_repository;
  }

  protected function configure(): void
  {
    $this->setName(self::$defaultName)
      ->setDescription('Inserting our static achievements into the Database')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $priority = 0;

    $achievement = $this->getOrCreateAchievement('bronze_user')
      ->setInternalDescription('Follow another user and upload at least one project')
      ->setTitleLtmCode(self::ACHIEVEMENT_LTM_PREFIX.'bronze_user.title')
      ->setDescriptionLtmCode(self::ACHIEVEMENT_LTM_PREFIX.'bronze_user.description')
      ->setBadgeSvgPath(self::ACHIEVEMENT_IMAGE_ASSETS_PATH.'achievement_badge_2_v3.svg')
      ->setBadgeLockedSvgPath(self::ACHIEVEMENT_IMAGE_ASSETS_PATH.'achievement_badge_locked_2.svg')
      ->setBannerSvgPath(self::ACHIEVEMENT_IMAGE_ASSETS_PATH.'achievement_banner.svg')
      ->setBannerColor('#3DB730')
      ->setEnabled(true)
      ->setPriority(++$priority)
    ;
    $this->entity_manager->persist($achievement);

    $achievement = $this->getOrCreateAchievement('silver_user')
      ->setInternalDescription('Community member for > 1 year with at least 1 project upload in every year')
      ->setTitleLtmCode(self::ACHIEVEMENT_LTM_PREFIX.'silver_user.title')
      ->setDescriptionLtmCode(self::ACHIEVEMENT_LTM_PREFIX.'silver_user.description')
      ->setBadgeSvgPath(self::ACHIEVEMENT_IMAGE_ASSETS_PATH.'achievement_badge_2_v2.svg')
      ->setBadgeLockedSvgPath(self::ACHIEVEMENT_IMAGE_ASSETS_PATH.'achievement_badge_locked_2.svg')
      ->setBannerSvgPath(self::ACHIEVEMENT_IMAGE_ASSETS_PATH.'achievement_banner.svg')
      ->setBannerColor('#3DB730')
      ->setEnabled(true)
      ->setPriority(++$priority)
    ;
    $this->entity_manager->persist($achievement);

    $achievement = $this->getOrCreateAchievement('gold_user')
      ->setInternalDescription('Community member for > 4 years with at least 1 project upload in every year')
      ->setTitleLtmCode(self::ACHIEVEMENT_LTM_PREFIX.'gold_user.title')
      ->setDescriptionLtmCode(self::ACHIEVEMENT_LTM_PREFIX.'gold_user.description')
      ->setBadgeSvgPath(self::ACHIEVEMENT_IMAGE_ASSETS_PATH.'achievement_badge_2_v1.svg')
      ->setBadgeLockedSvgPath(self::ACHIEVEMENT_IMAGE_ASSETS_PATH.'achievement_badge_locked_2.svg')
      ->setBannerSvgPath(self::ACHIEVEMENT_IMAGE_ASSETS_PATH.'achievement_banner.svg')
      ->setBannerColor('#3DB730')
      ->setEnabled(true)
      ->setPriority(++$priority)
    ;
    $this->entity_manager->persist($achievement);

    $achievement = $this->getOrCreateAchievement('diamond_user')
      ->setInternalDescription('Community member for > 7 years with at least 1 project upload in every year')
      ->setTitleLtmCode(self::ACHIEVEMENT_LTM_PREFIX.'diamond_user.title')
      ->setDescriptionLtmCode(self::ACHIEVEMENT_LTM_PREFIX.'diamond_user.description')
      ->setBadgeSvgPath(self::ACHIEVEMENT_IMAGE_ASSETS_PATH.'achievement_badge_2_v4.svg')
      ->setBadgeLockedSvgPath(self::ACHIEVEMENT_IMAGE_ASSETS_PATH.'achievement_badge_locked_2.svg')
      ->setBannerSvgPath(self::ACHIEVEMENT_IMAGE_ASSETS_PATH.'achievement_banner.svg')
      ->setBannerColor('#3DB729')
      ->setEnabled(true)
      ->setPriority(++$priority)
    ;
    $this->entity_manager->persist($achievement);

    $achievement = $this->getOrCreateAchievement('perfect_profile')
      ->setInternalDescription('Add your first profile picture')
      ->setTitleLtmCode(self::ACHIEVEMENT_LTM_PREFIX.'perfect_profile.title')
      ->setDescriptionLtmCode(self::ACHIEVEMENT_LTM_PREFIX.'perfect_profile.description')
      ->setBadgeSvgPath(self::ACHIEVEMENT_IMAGE_ASSETS_PATH.'achievement_badge_1.svg')
      ->setBadgeLockedSvgPath(self::ACHIEVEMENT_IMAGE_ASSETS_PATH.'achievement_badge_locked_1.svg')
      ->setBannerSvgPath(self::ACHIEVEMENT_IMAGE_ASSETS_PATH.'achievement_banner.svg')
      ->setBannerColor('#FF8C18')
      ->setEnabled(true)
      ->setPriority(++$priority)
    ;
    $this->entity_manager->persist($achievement);

    $achievement = $this->getOrCreateAchievement('verified_developer')
      ->setInternalDescription('Create a user account')
      ->setTitleLtmCode(self::ACHIEVEMENT_LTM_PREFIX.'verified_developer.title')
      ->setDescriptionLtmCode(self::ACHIEVEMENT_LTM_PREFIX.'verified_developer.description')
      ->setBadgeSvgPath(self::ACHIEVEMENT_IMAGE_ASSETS_PATH.'achievement_badge_3.svg')
      ->setBadgeLockedSvgPath(self::ACHIEVEMENT_IMAGE_ASSETS_PATH.'achievement_badge_locked_3.svg')
      ->setBannerSvgPath(self::ACHIEVEMENT_IMAGE_ASSETS_PATH.'achievement_banner.svg')
      ->setBannerColor('#3DB729')
      ->setEnabled(true)
      ->setPriority(++$priority)
    ;
    $this->entity_manager->persist($achievement);

    $this->entity_manager->flush();

    $output->writeln("{$priority} Achievements in the Database have been inserted/updated");

    return 0;
  }

  protected function getOrCreateAchievement(string $internal_title): Achievement
  {
    $achievement = $this->achievement_repository->findAchievementByInternalTitle($internal_title) ?? new Achievement();

    return $achievement->setInternalTitle($internal_title);
  }
}
