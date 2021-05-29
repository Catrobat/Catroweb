<?php

namespace App\Commands\DBUpdater;

use App\Entity\User;
use App\Entity\UserManager;
use App\Manager\AchievementManager;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateActiveUserAchievementsCommand extends Command
{
  /**
   * @var string|null
   *
   * @override from Command
   */
  protected static $defaultName = 'catrobat:update:achievements:active_user';

  protected EntityManagerInterface $entity_manager;
  protected AchievementManager $achievement_manager;
  protected UserManager $user_manager;

  public function __construct(EntityManagerInterface $entity_manager, AchievementManager $achievement_manager, UserManager $user_manager)
  {
    parent::__construct();
    $this->entity_manager = $entity_manager;
    $this->achievement_manager = $achievement_manager;
    $this->user_manager = $user_manager;
  }

  protected function configure(): void
  {
    $this->setName(self::$defaultName)
      ->setDescription('Unlocking our active user achievements: silver_user, gold_user, and diamond_user')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $this->addSilverUserAchievementToEveryUser($output);
    $this->addGoldUserAchievementToEveryUser($output);
    $this->addDiamondUserAchievementToEveryUser($output);

    return 0;
  }

  /**
   * SHARE-487: Already registered users must also have the verified_developer badge.
   *            Can move to a workflow later.
   */
  protected function addSilverUserAchievementToEveryUser(OutputInterface $output): void
  {
    $possible_silver_user_id_list = $this->getActiveUserIDList(1);

    foreach ($possible_silver_user_id_list as $user_id) {
      /** @var User|null $user */
      $user = $this->user_manager->find($user_id);
      if (is_null($user)) {
        continue;
      }
      try {
        $this->achievement_manager->unlockAchievementSilverUser($user);
      } catch (Exception $exception) {
        $output->writeln($exception->getMessage());
      }
    }
  }

  /**
   * SHARE-487: Already registered users must also have the verified_developer badge.
   *            Can move to a workflow later.
   */
  protected function addGoldUserAchievementToEveryUser(OutputInterface $output): void
  {
    $possible_gold_user_list = $this->getActiveUserIDList(4);

    foreach ($possible_gold_user_list as $user_id) {
      /** @var User|null $user */
      $user = $this->user_manager->find($user_id);
      if (is_null($user)) {
        continue;
      }
      try {
        $this->achievement_manager->unlockAchievementGoldUser($user);
      } catch (Exception $exception) {
        $output->writeln($exception->getMessage());
      }
    }
  }

  /**
   * SHARE-487: Already registered users must also have the verified_developer badge.
   *            Can move to a workflow later.
   */
  protected function addDiamondUserAchievementToEveryUser(OutputInterface $output): void
  {
    $possible_diamond_user_list = $this->getActiveUserIDList(7);

    foreach ($possible_diamond_user_list as $user_id) {
      /** @var User|null $user */
      $user = $this->user_manager->find($user_id);
      if (is_null($user)) {
        continue;
      }
      try {
        $this->achievement_manager->unlockAchievementDiamondUser($user);
      } catch (Exception $exception) {
        $output->writeln($exception->getMessage());
      }
    }
  }

  protected function getActiveUserIDList(int $years): array
  {
    return $this->entity_manager->createQueryBuilder()
      ->select('user.id as id')
      ->from('App\Entity\User', 'user')
      ->leftjoin('App\Entity\Program', 'project', Join::WITH, 'user.id = project.user')
      ->where('user.createdAt <= :date')
      ->setParameter('date', new DateTime("-{$years} years"))
      ->groupBy('user.id')
      ->having("COUNT(user.id) >= {$years}")
      ->getQuery()
      ->execute()
    ;
  }
}
