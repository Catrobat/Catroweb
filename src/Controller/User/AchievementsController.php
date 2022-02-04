<?php

namespace App\Controller\User;

use App\Entity\User;
use App\Manager\AchievementManager;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
    $most_recent_achievement_seen_at = is_null($most_recent_user_achievement) ? null : $most_recent_user_achievement->getSeenAt();

    $achievements_unlocked = $this->achievement_manager->findUnlockedAchievements($user);
    $achievements_locked = $this->achievement_manager->findLockedAchievements($user);
    $total_number_of_achievements = $this->achievement_manager->countAllEnabledAchievements();
    $number_of_unlocked_achievements = count($achievements_unlocked);

    return $this->render('Achievements/achievement_overview.html.twig', [
      'most_recent_achievement' => $most_recent_achievement,
      'most_recent_achievement_unlocked_at' => $most_recent_achievement_unlocked_at,
      'most_recent_achievement_seen_at' => $most_recent_achievement_seen_at,
      'total_number_of_achievements' => $total_number_of_achievements,
      'number_of_unlocked_achievements' => $number_of_unlocked_achievements,
      'achievements_unlocked' => $achievements_unlocked,
      'achievements_locked' => $achievements_locked,
    ]);
  }

  /**
   * @Route("/achievements/count", name="sidebar_achievements_count", methods={"GET"})
   * Todo -> move to CAPI
   */
  public function countUnseenAchievements(): JsonResponse
  {
    /** @var User|null $user */
    $user = $this->getUser();
    if (is_null($user)) {
      return JsonResponse::create([], Response::HTTP_UNAUTHORIZED);
    }

    return new JsonResponse([
      'count' => $this->achievement_manager->countUnseenUserAchievements($user),
    ]);
  }

  /**
   * @Route("/achievements", name="achievements_read", methods={"PUT"})
   * Todo -> move to CAPI
   */
  public function readUnseenAchievements(): JsonResponse
  {
    /** @var User|null $user */
    $user = $this->getUser();
    if (is_null($user)) {
      return JsonResponse::create(null, Response::HTTP_UNAUTHORIZED);
    }

    try {
      $this->achievement_manager->readAllUnseenAchievements($user);
    } catch (Exception $e) {
      return new JsonResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    return new JsonResponse(null, Response::HTTP_NO_CONTENT);
  }
}
