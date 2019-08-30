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
   * @param FakeStatisticsService $fakeStatisticsService
   */
  public function __construct(ParameterBagInterface $parameter_bag, StatisticsService $statistics_service) {
    $this->statistics = $statistics_service;
  }

  /**
   * @Route("/notifications", name="user_notifications", methods={"GET"})
   *
   * @param CatroNotificationRepository $nr
   *
   * @return RedirectResponse|Response
   */
  public function userNotificationsAction(CatroNotificationRepository $nr)
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

    $catro_user_notifications = $nr->findByUser($user, ['id' => 'DESC']);
    $avatars = [];

    foreach ($catro_user_notifications as $notification)
    {
      $user = null;
      if ($notification instanceof LikeNotification)
      {
        $user = $notification->getLikeFrom();
      }
      elseif ($notification instanceof CommentNotification)
      {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->findOneBy([
          'id' => $notification->getComment()->getUserId(),
        ]);

      }
      elseif ($notification instanceof NewProgramNotification)
      {
        $user = $notification->getProgram()->getUser();
      }
      elseif ($notification instanceof FollowNotification)
      {
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
    }

    $response = $this->get('templating')->renderResponse('Notifications/usernotifications.html.twig', [
      'catroUserNotifications' => $catro_user_notifications,
      'avatars'                => $avatars,
    ]);

    $response->headers->set('Cache-Control', 'no-store, must-revalidate, max-age=0');
    $response->headers->set('Pragma', 'no-cache');

    return $response;
  }


  /**
   * @Route("/notifications/count", name="user_notifications_count", methods={"GET"})
   *
   * @param CatroNotificationRepository $nr
   * @param RemixManager $remix_manager
   *
   * @return JsonResponse
   */
  public function userNotificationsCountAction(CatroNotificationRepository $nr, RemixManager $remix_manager)
  {
    $user = $this->getUser();
    if (!$user)
    {
      return JsonResponse::create(['statusCode' => StatusCode::LOGIN_ERROR]);
    }

    $catro_user_notifications = $nr->findByUser($user);
    $unseen_remixed_program_data = $remix_manager->getUnseenRemixProgramsDataOfUser($user);

    return new JsonResponse([
      'count'      => count($unseen_remixed_program_data) + count($catro_user_notifications),
      'statusCode' => 200,
    ]);
  }


  /**
   * @Route("/notifications/seen", name="user_notifications_seen", methods={"GET"})
   *
   * @param CatroNotificationRepository $nr
   * @param CatroNotificationService $ns
   * @param RemixManager $remix_manager
   *
   * @return JsonResponse
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function userNotificationsSeenAction(CatroNotificationRepository $nr, CatroNotificationService $ns,
                                              RemixManager $remix_manager)
  {
    $user = $this->getUser();
    if (!$user)
    {
      return JsonResponse::create(['statusCode' => StatusCode::LOGIN_ERROR]);
    }
    $catro_user_notifications = $nr->findByUser($user);
    $ns->deleteNotifications($catro_user_notifications);
    $remix_manager->markAllUnseenRemixRelationsOfUserAsSeen($user);

    return new JsonResponse(['success' => true]);
  }


  /**
   * @Route("/notification/ancestor/{ancestor_id}/descendant/{descendant_id}", name="see_user_notification",
   *        methods={"GET"})
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
  public function seeUserNotificationAction(Request $request, $ancestor_id, $descendant_id, RemixManager $remix_manager)
  {
    $user = $this->getUser();
    if (!$user)
    {
      return $this->redirectToRoute('fos_user_security_login');
    }

    $remix_relation = $remix_manager->findCatrobatRelation($ancestor_id, $descendant_id);
    if ($remix_relation == null)
    {
      throw $this->createNotFoundException('Unable to find Remix relation entity.');
    }
    if ($user->getId() != $remix_relation->getAncestor()->getUser()->getId())
    {
      throw $this->createNotFoundException('You are not allowed to update Remix relation entity '
        . 'because you do not own the parent program.');
    }

    $referrer = $request->headers->get('referer');
    $locale = strtolower($request->getLocale());
    $this->statistics->createClickStatistics($request, 'rec_remix_notification', $ancestor_id, $descendant_id, null, null,
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
   * @param CatroNotificationService $ns
   * @param CatroNotificationRepository $nr
   *
   * @return JsonResponse
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function markCatroNotificationAsRead($notification_id, CatroNotificationService $ns, CatroNotificationRepository $nr)
  {
    $user = $this->getUser();
    if (!$user)
    {
      return JsonResponse::create(['success' => false, "message" => "User not logged in"]);
    }
    $notification_to_delete = $nr->findOneBy(["id" => $notification_id, "user" => $user]);
    if ($notification_to_delete === null)
    {
      return new JsonResponse(["success" => false, "message" => "Notification not found or doesnt belong to user"]);
    }
    $ns->deleteNotifications([$notification_to_delete]);

    return new JsonResponse(['success' => true]);
  }
}
