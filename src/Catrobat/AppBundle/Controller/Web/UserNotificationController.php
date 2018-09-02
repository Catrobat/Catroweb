<?php

namespace Catrobat\AppBundle\Controller\Web;

use Catrobat\AppBundle\Entity\ProgramLike;
use Catrobat\AppBundle\RecommenderSystem\RecommendedPageId;
use Catrobat\AppBundle\StatusCode;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class UserNotificationController extends Controller
{
  /**
   * @Route("/user/notifications", name="user_notifications", methods={"GET"})
   */
  public function userNotificationsAction()
  {
    $user = $this->getUser();
    if (!$user)
    {
      return $this->redirectToRoute('fos_user_security_login');
    }

    $unseen_remixed_program_data = $this->get('remixmanager')->getUnseenRemixProgramsDataOfUser($this->getUser());
    $screenshot_repository = $this->get('screenshotrepository');
    $elapsed_time = $this->get('elapsedtime');

    $unseen_remixes_grouped = [];
    foreach ($unseen_remixed_program_data as $remix_data)
    {
      $remix_data['age'] = $elapsed_time->getElapsedTime($remix_data['createdAt']->getTimestamp());
      $remix_data['thumbnail'] = '/' . $screenshot_repository->getThumbnailWebPath($remix_data['remixProgramId']);
      $original_program_id = $remix_data['originalProgramId'];

      if (!array_key_exists($original_program_id, $unseen_remixes_grouped))
      {
        $unseen_remixes_grouped[$original_program_id] = [
          'originalProgramName' => $remix_data['originalProgramName'],
          'remixes'             => [],
        ];
      }
      $unseen_remixes_grouped[$original_program_id]['remixes'][] = $remix_data;
    }

    $nr = $this->get("catro_notification_repository");
    $catro_user_notifications = $nr->findByUser($user);

    $response = $this->get('templating')->renderResponse('usernotifications.html.twig', [
      'unseenRemixesGrouped'   => $unseen_remixes_grouped,
      'catroUserNotifications' => $catro_user_notifications,
    ]);

    $response->headers->set('Cache-Control', 'no-store, must-revalidate, max-age=0');
    $response->headers->set('Pragma', 'no-cache');

    return $response;

  }

  /**
   * @Route("/user/notifications/count", name="user_notifications_count", methods={"GET"})
   */
  public function userNotificationsCountAction()
  {
    $user = $this->getUser();
    if (!$user)
    {
      return JsonResponse::create(['statusCode' => StatusCode::LOGIN_ERROR]);
    }
    $nr = $this->get("catro_notification_repository");
    $catro_user_notifications = $nr->findByUser($user);
    $unseen_remixed_program_data = $this->get('remixmanager')->getUnseenRemixProgramsDataOfUser($user);

    return new JsonResponse([
      'count'      => count($unseen_remixed_program_data) + count($catro_user_notifications),
      'statusCode' => 200,
    ]);
  }

  /**
   * @Route("/user/notifications/seen", name="user_notifications_seen", methods={"GET"})
   */
  public function userNotificationsSeenAction()
  {
    $user = $this->getUser();
    if (!$user)
    {
      return JsonResponse::create(['statusCode' => StatusCode::LOGIN_ERROR]);
    }
    $nr = $this->get("catro_notification_repository");
    $ns = $this->get("catro_notification_service");
    $catro_user_notifications = $nr->findByUser($user);
    $ns->deleteNotifications($catro_user_notifications);
    $this->get('remixmanager')->markAllUnseenRemixRelationsOfUserAsSeen($user);

    return new JsonResponse(['success' => true]);
  }

  /**
   * @Route("/user/notification/ancestor/{ancestor_id}/descendant/{descendant_id}", name="see_user_notification",
   *        requirements={"ancestor_id":"\d+", "descendant_id":"\d+"}, methods={"GET"})
   */
  public function seeUserNotificationAction(Request $request, $ancestor_id, $descendant_id)
  {
    $user = $this->getUser();
    if (!$user)
    {
      return $this->redirectToRoute('fos_user_security_login');
    }
    $remix_manager = $this->get('remixmanager');
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

    $statistics = $this->get('statistics');
    $referrer = $request->headers->get('referer');
    $locale = strtolower($request->getLocale());
    $statistics->createClickStatistics($request, 'rec_remix_notification', $ancestor_id, $descendant_id, null, null,
      $referrer, $locale, false);

    $remix_manager->markRemixRelationAsSeen($remix_relation);

    return $this->redirectToRoute('program', [
      'id'                => $descendant_id,
      'rec_by_page_id'    => RecommendedPageId::NOTIFICATION_CENTER_PAGE,
      'rec_by_program_id' => $ancestor_id,
    ]);
  }

  /**
   * @Route("/user/notifications/markasread/{notification_id}", name="catro_notification_mark_as_read",
   *   requirements={"notification_id":"\d+"}, defaults={"notification_id" = null}, methods={"GET"})
   */
  public function markCatroNotificationAsRead($notification_id)
  {
    $user = $this->getUser();
    if (!$user)
    {
      return JsonResponse::create(['success' => false, "message" => "User not logged in"]);
    }
    $ns = $this->get("catro_notification_service");
    $nr = $this->get("catro_notification_repository");
    $notification_to_delete = $nr->findOneBy(["id" => $notification_id, "user" => $user]);
    if ($notification_to_delete === null)
    {
      return new JsonResponse(["success" => false, "message" => "Notification not found or doesnt belong to user"]);
    }
    $ns->deleteNotifications([$notification_to_delete]);

    return new JsonResponse(['success' => true]);

  }
}
