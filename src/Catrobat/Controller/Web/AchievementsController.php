<?php

namespace App\Catrobat\Controller\Web;

use App\Entity\User;
use App\Manager\AchievementManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AchievementsController extends AbstractController
{
  protected AchievementManager $achievement_manager;

  public function __construct(AchievementManager $achievement_manager)
  {
    $this->achievement_manager = $achievement_manager;
  }

  /**
   * @Route("/achievements", name="achievements_overview", methods={"GET"})
   */
  public function AchievementsOverview(): Response
  {
    /** @var User|null $user */
    $user = $this->getUser();
    if (is_null($user)) {
      return $this->redirectToRoute('login');
    }

    $most_recent_user_achievement = $this->achievement_manager->findMostRecentUserAchievement($user);
    $most_recent_achievement = is_null($most_recent_user_achievement) ? null : $most_recent_user_achievement->getAchievement();
    $most_recent_achievement_unlocked_at = is_null($most_recent_user_achievement) ? null : $most_recent_user_achievement->getUnlockedAt()->format('Y-m-d');

    $achievements_unlocked = $this->achievement_manager->findUnlockedAchievements($user);
    $achievements_locked = $this->achievement_manager->findLockedAchievements($user);
    $total_number_of_achievements = $this->achievement_manager->countAllEnabledAchievements();
    $number_of_unlocked_achievements = count($achievements_unlocked);

    return $this->render('Achievements/achievement_overview.html.twig', [
      'most_recent_achievement' => $most_recent_achievement,
      'most_recent_achievement_unlocked_at' => $most_recent_achievement_unlocked_at,
      'total_number_of_achievements' => $total_number_of_achievements,
      'number_of_unlocked_achievements' => $number_of_unlocked_achievements,
      'achievements_unlocked' => $achievements_unlocked,
      'achievements_locked' => $achievements_locked,
    ]);
  }
}
