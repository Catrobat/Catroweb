<?php

namespace App\Catrobat\Controller\Web;

use App\Catrobat\Services\CatroNotificationService;
use App\Catrobat\Services\StatisticsService;
use App\Catrobat\Services\TestEnv\FakeStatisticsService;
use App\Entity\CatroNotification;
use App\Entity\CommentNotification;
use App\Entity\FollowNotification;
use App\Entity\LikeNotification;
use App\Entity\NewProgramNotification;
use App\Entity\RemixManager;
use App\Entity\User;
use App\Catrobat\RecommenderSystem\RecommendedPageId;
use App\Catrobat\Services\Formatter\ElapsedTimeStringFormatter;
use App\Catrobat\StatusCode;
use App\Repository\CatroNotificationRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Twig\Error\Error;


/**
 * Class UserNotificationController
 * @package App\Catrobat\Controller\Web
 */
class UserNotificationController extends AbstractController
{
  /**
   * @var StatisticsService | FakeStatisticsService
   */
  private $statistics;

  /**
   * UserNotificationController constructor.
   *
   * @param ParameterBagInterface $parameter_bag
   * @param StatisticsService $statistics_service
   */
  public function __construct(ParameterBagInterface $parameter_bag,
  StatisticsService $statistics_service)
  {
    $this->statistics = $statistics_service;
  }

  /**
   * @Route("/notifications/{notification_type}", name="user_notifications", methods={"GET"})
   * @param $notification_type
   * @param CatroNotificationRepository $notification_repo
   *
   * @return RedirectResponse|Response
   */
  public function userNotificationsAction($notification_type,
  CatroNotificationRepository $notification_repo)
  {
    /**
     * @var $notification CatroNotification
     * @var $user         User
     * @var $em           EntityManager
     * @var $elapsed_time ElapsedTimeStringFormatter
     * @var $remix_data
     */

    $user = $this->getUser();
    if (!$user)
    {
      return $this->redirectToRoute('fos_user_security_login');
    }

    $catro_user_notifications = $notification_repo->findByUser($user, ['id' => 'DESC']);
    $avatars = [];
    $new_notifications = [];
    $old_notifications = [];

    foreach ($catro_user_notifications as $notification)
    {

      $found_notification = false;


      $user = null;
      if ($notification_type === "allNotifications")
      {
        $found_notification = true;
      }
      if ($notification instanceof LikeNotification && $notification_type === "likes")
      {
        $found_notification = true;

        $user = $notification->getLikeFrom();
      }
      elseif ($notification instanceof CommentNotification && $notification_type === "comments")
      {

        $found_notification = true;
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->findOneBy([
          'id' => $notification->getComment()->getUser(),
        ]);

      }
      elseif ($notification instanceof NewProgramNotification)
      {
        $user = $notification->getProgram()->getUser();
      }
      elseif ($notification instanceof FollowNotification && $notification_type === "followers")
      {

        $found_notification = true;
        $user = $notification->getFollower();
      }
      if ($user !== null)
      {
        $avatar = $user->getAvatar();
        if ($avatar)
        {
          $avatars[$notification->getId()] = $avatar;
        }
      }
      if ($notification->getSeen() && $found_notification === true)
      {
        $old_notifications[$notification->getId()] = $notification;
      }
      else
      {
        if (!$notification->getSeen() && $found_notification === true)
        {
          $new_notifications[$notification->getId()] = $notification;

        }
      }
    }
    $response = $this->render('Notifications/usernotifications.html.twig'
      , [
        'oldNotifications' => $old_notifications,
        'newNotifications' => $new_notifications,
        'avatars'          => $avatars,
        'notificationType' => $notification_type,
      ]);

    $response->headers->set('Cache-Control', 'no-store, must-revalidate, max-age=0');
    $response->headers->set('Pragma', 'no-cache');

    return $response;
  }


  /**
   * @Route("/notifications/notifications/count", name="user_notifications_count", methods={"GET"})
   *
   * @param CatroNotificationRepository $notification_repo
   * @param RemixManager $remix_manager
   *
   * @return JsonResponse
   */
  public function userNotificationsCountAction(CatroNotificationRepository $notification_repo,
  RemixManager $remix_manager)
  {
    $user = $this->getUser();
    if (!$user)
    {
      return JsonResponse::create(['statusCode' => StatusCode::LOGIN_ERROR]);
    }

    $catro_user_notifications_all = $notification_repo->findByUser($user);
    $likes = 0;
    $followers = 0;
    $comments = 0;
    $all = 0;
    foreach ($catro_user_notifications_all as $notification)
    {

      if ($notification instanceof LikeNotification && !$notification->getSeen())
      {
        $likes++;
      }
      else
      {
        if ($notification instanceof FollowNotification && !$notification->getSeen())
        {
          $followers++;
        }
        else
        {
          if ($notification instanceof CommentNotification && !$notification->getSeen())
          {
            $comments++;

          }
        }
      }
      if (!$notification->getSeen())
      {

        $all++;

      }


    }


    $unseen_remixed_program_data = $remix_manager->getUnseenRemixProgramsDataOfUser($user);

    return new JsonResponse([
      'count'      => ['all-notifications'          => count($unseen_remixed_program_data) + $all,
                       'all-notifications-dropdown' => count($unseen_remixed_program_data) + $all,
                       "likes"                      => $likes,
                       "followers"                  => $followers,
                       "comments"                   => $comments],
      'statusCode' => 200,
    ]);
  }


  /**
   * @Route("/notifications/{notification_type}/seen", name="user_notifications_seen",
   *                                                   methods={"GET"})
   *
   * @param $notification_type
   * @param CatroNotificationRepository $notification_repo
   * @param CatroNotificationService $notification_service
   * @param RemixManager $remix_manager
   *
   * @return JsonResponse
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function userNotificationsSeenAction($notification_type,
  CatroNotificationRepository $notification_repo, CatroNotificationService $notification_service,
  RemixManager $remix_manager)
  {
    $user = $this->getUser();
    if (!$user)
    {
      return JsonResponse::create(['statusCode' => StatusCode::LOGIN_ERROR]);
    }
    $catro_user_notifications = $notification_repo->findByUser($user);
    $notifications_seen = [];
    foreach ($catro_user_notifications as $notification)
    {

      if ($notification_type === "likes" && $notification instanceof LikeNotification)
      {
        $notifications_seen[$notification->getID()] = $notification;
      }
      else
      {
        if ($notification_type === "followers" && $notification instanceof FollowNotification)
        {
          $notifications_seen[$notification->getID()] = $notification;
        }
        else
        {
          if ($notification_type === "comments" && $notification instanceof CommentNotification)
          {
            $notifications_seen[$notification->getID()] = $notification;
          }
          else
          {
            if ($notification_type === "allNotifications")
            {

              $notifications_seen[$notification->getID()] = $notification;

            }
          }
        }
      }

    }
    $notification_service->markSeen($notifications_seen);
    $remix_manager->markAllUnseenRemixRelationsOfUserAsSeen($user);

    return new JsonResponse(['success' => true]);
  }


  /**
   * @Route("/notifications/{notification_type}/deleteAll", name="delete_all_notifications",
   *                                                        methods={"GET"})
   *
   * @param $notification_type
   * @param CatroNotificationRepository $notification_repo
   * @param CatroNotificationService $notification_service
   * @param RemixManager $remix_manager
   *
   * @return JsonResponse
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function userNotificationsDeleteAllAction($notification_type,
  CatroNotificationRepository $notification_repo, CatroNotificationService $notification_service,
  RemixManager $remix_manager)
  {
    $user = $this->getUser();
    if (!$user)
    {
      return JsonResponse::create(['statusCode' => StatusCode::LOGIN_ERROR]);
    }
    $catro_user_notifications = $notification_repo->findByUser($user);
    $notifications_to_delete = [];
    foreach ($catro_user_notifications as $notification)
    {

      if ($notification_type === "likes" && $notification instanceof LikeNotification)
      {
        $notifications_to_delete[$notification->getID()] = $notification;
      }
      else
      {
        if ($notification_type === "followers" && $notification instanceof FollowNotification)
        {
          $notifications_to_delete[$notification->getID()] = $notification;
        }
        else
        {
          if ($notification_type === "comments" && $notification instanceof CommentNotification)
          {
            $notifications_to_delete[$notification->getID()] = $notification;
          }
          else
          {
            if ($notification_type === "allNotifications")
            {

              $notifications_to_delete[$notification->getID()] = $notification;

            }
          }
        }
      }

    }
    $notification_service->deleteNotifications($notifications_to_delete);
    $remix_manager->markAllUnseenRemixRelationsOfUserAsSeen($user);

    return new JsonResponse(['success' => true]);
  }

  /**
   * @Route("/notification/ancestor/{ancestor_id}/descendant/{descendant_id}",
   *  name="see_user_notification", methods={"GET"})
   *
   * @param Request $request
   * @param         $ancestor_id
   * @param         $descendant_id
   * @param RemixManager $remix_manager
   *
   * @return RedirectResponse
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function seeUserNotificationAction(Request $request, $ancestor_id, $descendant_id,
  RemixManager $remix_manager)
  {
    $user = $this->getUser();
    if (!$user)
    {
      return $this->redirectToRoute('fos_user_security_login');
    }

    $remix_relation = $remix_manager->findCatrobatRelation($ancestor_id, $descendant_id);
    if ($remix_relation === null)
    {
      throw $this->createNotFoundException('Unable to find Remix relation entity.');
    }
    if ($user->getId() !== $remix_relation->getAncestor()->getUser()->getId())
    {
      throw $this->createNotFoundException('You are not allowed to update Remix relation entity '
        . 'because you do not own the parent program.');
    }

    $referrer = $request->headers->get('referer');
    $locale = strtolower($request->getLocale());
    $this->statistics->createClickStatistics($request, 'rec_remix_notification',
      $ancestor_id, $descendant_id, null, null,
      $referrer, $locale, false);

    $remix_manager->markRemixRelationAsSeen($remix_relation);

    return $this->redirectToRoute('program', [
      'id'                => $descendant_id,
      'rec_by_page_id'    => RecommendedPageId::NOTIFICATION_CENTER_PAGE,
      'rec_by_program_id' => $ancestor_id,
    ]);
  }


  /**
   * @Route("/notifications/markasread/{notification_id}", name="catro_notification_mark_as_read",
   *   requirements={"notification_id":"\d+"}, defaults={"notification_id" = null}, methods={"GET"})
   *
   * @param $notification_id
   * @param CatroNotificationService $notification_service
   * @param CatroNotificationRepository $notification_repo
   *
   * @return JsonResponse
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function markCatroNotificationAsRead($notification_id,
  CatroNotificationService $notification_service, CatroNotificationRepository $notification_repo)
  {
    $user = $this->getUser();
    if (!$user)
    {
      return JsonResponse::create(['success' => false, "message" => "User not logged in"]);
    }
    $notification_seen = $notification_repo->findOneBy(["id" => $notification_id, "user" => $user]);
    if ($notification_seen === null)
    {
      return new JsonResponse(["success" => false,
      "message" => "Notification not found or doesnt belong to user"]);
    }
    $notification_service->markSeen([$notification_seen]);

    return new JsonResponse(['success' => true]);
  }

  /**
   * @Route("/notifications/deleteNotification/{notification_id}", name="delete_notification",
   *   requirements={"notification_id":"\d+"}, defaults={"notification_id" = null}, methods={"GET"})
   *
   * @param $notification_id
   * @param CatroNotificationService $notification_service
   * @param CatroNotificationRepository $notification_repo
   *
   * @return JsonResponse
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function userNotificationDeleteAction($notification_id,
  CatroNotificationService $notification_service, CatroNotificationRepository $notification_repo)
  {
    $user = $this->getUser();
    if (!$user)
    {
      return JsonResponse::create(['statusCode' => StatusCode::LOGIN_ERROR]);
    }
    $delete_notification_ = $notification_repo->findOneBy(["id" => $notification_id,
    "user" => $user]);
    if ($delete_notification_ === null)
    {
      return new JsonResponse(["success" => false,
      "message" => "Notification not found or doesnt belong to user"]);
    }

    $notification_service->deleteNotifications([$delete_notification_]);


    return new JsonResponse(['success' => true]);
  }

}
