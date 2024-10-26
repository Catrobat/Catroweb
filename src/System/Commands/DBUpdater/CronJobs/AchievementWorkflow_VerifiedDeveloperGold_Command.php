<?php

declare(strict_types=1);

namespace App\System\Commands\DBUpdater\CronJobs;

use App\DB\Entity\User\Achievements\Achievement;
use App\DB\Entity\User\Achievements\UserAchievement;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\User\UserRepository;
use App\User\Achievements\AchievementManager;
use App\Utils\TimeUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'catrobat:workflow:achievement:verified_developer_gold', description: 'Unlocking verified_developer user achievements')]
class AchievementWorkflow_VerifiedDeveloperGold_Command extends Command
{
  public function __construct(protected AchievementManager $achievement_manager, protected UserRepository $user_repository, protected EntityManagerInterface $entity_manager)
  {
    parent::__construct();
  }

  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $this->addVerifiedDeveloperAchievementToEveryUser($output);

    return 0;
  }

  protected function addVerifiedDeveloperAchievementToEveryUser(OutputInterface $output): void
  {
    $achievement = $this->achievement_manager->findAchievementByInternalTitle(Achievement::ACCOUNT_VERIFICATION);
    if (!$achievement) {
      $output->writeln('Achievement not found');

      return;
    }

    $qb = $this->user_repository->createQueryBuilder('u');
    $qb->leftJoin(UserAchievement::class, 'ua', 'WITH', 'u.id = ua.user AND ua.achievement = :aID')
      ->where('ua.id IS NULL')
      ->setParameter('aID', $achievement->getId())
      ->setMaxResults(500)
    ;

    $user_list = $qb->getQuery()->getResult();
    /** @var User $user */
    foreach ($user_list as $user) {
      try {
        $user_achievement = new UserAchievement();
        $user_achievement->setUser($user);
        $user_achievement->setAchievement($achievement);
        $user_achievement->setUnlockedAt(TimeUtils::getDateTime());
        $this->entity_manager->persist($user_achievement);
      } catch (\Exception $exception) {
        $output->writeln($exception->getMessage());
      }
    }
    $this->entity_manager->flush();
  }
}
