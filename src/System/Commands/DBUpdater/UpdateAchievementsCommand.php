<?php

declare(strict_types=1);

namespace App\System\Commands\DBUpdater;

use App\DB\Entity\User\Achievements\Achievement;
use App\DB\EntityRepository\User\Achievements\AchievementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'catrobat:update:achievements', description: 'Inserting our static achievements into the Database')]
class UpdateAchievementsCommand extends Command
{
  final public const ACHIEVEMENT_IMAGE_ASSETS_PATH = 'images/achievements/';
  final public const ACHIEVEMENT_LTM_PREFIX = 'achievements.achievement.type.';

  public function __construct(protected EntityManagerInterface $entity_manager, protected AchievementRepository $achievement_repository)
  {
    parent::__construct();
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $priority = 0;

    // The internal_title must not change!
    // Do not delete Achievements, better disable them

    $achievement = $this->getOrCreateAchievement(Achievement::BRONZE_USER)
      ->setInternalDescription('Follow another user and upload at least one project')
      ->setTitleLtmCode(self::ACHIEVEMENT_LTM_PREFIX.'bronze_user.title')
      ->setDescriptionLtmCode(self::ACHIEVEMENT_LTM_PREFIX.'bronze_user.description')
      ->setBadgeSvgPath(self::ACHIEVEMENT_IMAGE_ASSETS_PATH.'achievement_badge_2_v1.svg')
      ->setBadgeLockedSvgPath(self::ACHIEVEMENT_IMAGE_ASSETS_PATH.'achievement_badge_locked_2.svg')
      ->setBannerSvgPath(self::ACHIEVEMENT_IMAGE_ASSETS_PATH.'achievement_banner.svg')
      ->setBannerColor('#3DB730')
      ->setEnabled(true)
      ->setPriority(++$priority)
    ;
    $this->entity_manager->persist($achievement);

    $achievement = $this->getOrCreateAchievement(Achievement::SILVER_USER)
      ->setInternalDescription('Community member for > 1 year with at least 1 project upload in every year')
      ->setTitleLtmCode(self::ACHIEVEMENT_LTM_PREFIX.'silver_user.title')
      ->setDescriptionLtmCode(self::ACHIEVEMENT_LTM_PREFIX.'silver_user.description')
      ->setBadgeSvgPath(self::ACHIEVEMENT_IMAGE_ASSETS_PATH.'achievement_badge_2_v3.svg')
      ->setBadgeLockedSvgPath(self::ACHIEVEMENT_IMAGE_ASSETS_PATH.'achievement_badge_locked_2.svg')
      ->setBannerSvgPath(self::ACHIEVEMENT_IMAGE_ASSETS_PATH.'achievement_banner.svg')
      ->setBannerColor('#3DB730')
      ->setEnabled(true)
      ->setPriority(++$priority)
    ;
    $this->entity_manager->persist($achievement);

    $achievement = $this->getOrCreateAchievement(Achievement::GOLD_USER)
      ->setInternalDescription('Community member for > 4 years with at least 1 project upload in every year')
      ->setTitleLtmCode(self::ACHIEVEMENT_LTM_PREFIX.'gold_user.title')
      ->setDescriptionLtmCode(self::ACHIEVEMENT_LTM_PREFIX.'gold_user.description')
      ->setBadgeSvgPath(self::ACHIEVEMENT_IMAGE_ASSETS_PATH.'achievement_badge_2_v2.svg')
      ->setBadgeLockedSvgPath(self::ACHIEVEMENT_IMAGE_ASSETS_PATH.'achievement_badge_locked_2.svg')
      ->setBannerSvgPath(self::ACHIEVEMENT_IMAGE_ASSETS_PATH.'achievement_banner.svg')
      ->setBannerColor('#3DB730')
      ->setEnabled(true)
      ->setPriority(++$priority)
    ;
    $this->entity_manager->persist($achievement);

    $achievement = $this->getOrCreateAchievement(Achievement::DIAMOND_USER)
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

    $achievement = $this->getOrCreateAchievement(Achievement::PERFECT_PROFILE)
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

    $achievement = $this->getOrCreateAchievement(Achievement::VERIFIED_DEVELOPER)
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

    $achievement = $this->getOrCreateAchievement(Achievement::CODING_JAM_09_2021)
      ->setInternalDescription('This achievement can only be reached if a project with the tag #catrobatfestival2021 is uploaded during the period 25.09.2021 00:00 - 26.09.2021 23:59')
      ->setTitleLtmCode(self::ACHIEVEMENT_LTM_PREFIX.'coding_jam_09_2021.title')
      ->setDescriptionLtmCode(self::ACHIEVEMENT_LTM_PREFIX.'coding_jam_09_2021.description')
      ->setBadgeSvgPath(self::ACHIEVEMENT_IMAGE_ASSETS_PATH.'achievement_badge_2_v5.svg')
      ->setBadgeLockedSvgPath(self::ACHIEVEMENT_IMAGE_ASSETS_PATH.'achievement_badge_locked_2.svg')
      ->setBannerSvgPath(self::ACHIEVEMENT_IMAGE_ASSETS_PATH.'achievement_banner.svg')
      ->setBannerColor('#EA7B0C')
      ->setEnabled(true)
      ->setPriority(++$priority)
    ;
    $this->entity_manager->persist($achievement);

    $achievement = $this->getOrCreateAchievement(Achievement::BILINGUAL)
      ->setInternalDescription('Translate your projects to 2 languages')
      ->setTitleLtmCode(self::ACHIEVEMENT_LTM_PREFIX.'bilingual.title')
      ->setDescriptionLtmCode(self::ACHIEVEMENT_LTM_PREFIX.'bilingual.description')
      ->setBadgeSvgPath(self::ACHIEVEMENT_IMAGE_ASSETS_PATH.'multi_lingual_bronze.svg')
      ->setBadgeLockedSvgPath(self::ACHIEVEMENT_IMAGE_ASSETS_PATH.'achievement_badge_locked_2.svg')
      ->setBannerSvgPath(self::ACHIEVEMENT_IMAGE_ASSETS_PATH.'achievement_banner.svg')
      ->setBannerColor('#EA7B0C')
      ->setEnabled(true)
      ->setPriority(++$priority)
    ;
    $this->entity_manager->persist($achievement);

    $achievement = $this->getOrCreateAchievement(Achievement::TRILINGUAL)
      ->setInternalDescription('Translate your projects to 3 languages')
      ->setTitleLtmCode(self::ACHIEVEMENT_LTM_PREFIX.'trilingual.title')
      ->setDescriptionLtmCode(self::ACHIEVEMENT_LTM_PREFIX.'trilingual.description')
      ->setBadgeSvgPath(self::ACHIEVEMENT_IMAGE_ASSETS_PATH.'multi_lingual_silver.svg')
      ->setBadgeLockedSvgPath(self::ACHIEVEMENT_IMAGE_ASSETS_PATH.'achievement_badge_locked_2.svg')
      ->setBannerSvgPath(self::ACHIEVEMENT_IMAGE_ASSETS_PATH.'achievement_banner.svg')
      ->setBannerColor('#EA7B0C')
      ->setEnabled(true)
      ->setPriority(++$priority)
    ;
    $this->entity_manager->persist($achievement);

    $achievement = $this->getOrCreateAchievement(Achievement::LINGUIST)
      ->setInternalDescription('Translate your projects to 5 languages')
      ->setTitleLtmCode(self::ACHIEVEMENT_LTM_PREFIX.'linguist.title')
      ->setDescriptionLtmCode(self::ACHIEVEMENT_LTM_PREFIX.'linguist.description')
      ->setBadgeSvgPath(self::ACHIEVEMENT_IMAGE_ASSETS_PATH.'multi_lingual_gold.svg')
      ->setBadgeLockedSvgPath(self::ACHIEVEMENT_IMAGE_ASSETS_PATH.'achievement_badge_locked_2.svg')
      ->setBannerSvgPath(self::ACHIEVEMENT_IMAGE_ASSETS_PATH.'achievement_banner.svg')
      ->setBannerColor('#EA7B0C')
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
