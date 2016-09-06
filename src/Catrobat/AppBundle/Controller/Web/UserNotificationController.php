<?php

namespace Catrobat\AppBundle\Controller\Web;

use Catrobat\AppBundle\StatusCode;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class UserNotificationController extends Controller
{
    /**
     * @Route("/user/notifications", name="user_notifications")
     * @Method({"GET"})
     */
    public function userNotificationsAction()
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('fos_user_security_login');
        }

        $unseen_remixed_program_data = $this->get('remixmanager')->getUnseenRemixProgramsDataOfUser($this->getUser());
        $screenshot_repository = $this->get('screenshotrepository');
        $elapsed_time = $this->get('elapsedtime');

        $unseen_remixes_grouped = [];
        foreach ($unseen_remixed_program_data as $remix_data) {
            $remix_data['age'] = $elapsed_time->getElapsedTime($remix_data['createdAt']->getTimestamp());
            $remix_data['thumbnail'] = '/' . $screenshot_repository->getThumbnailWebPath($remix_data['remixProgramId']);
            $original_program_id = $remix_data['originalProgramId'];

            if (!array_key_exists($original_program_id, $unseen_remixes_grouped)) {
                $unseen_remixes_grouped[$original_program_id] = [
                    'originalProgramName' => $remix_data['originalProgramName'],
                    'remixes' => [],
                ];
            }
            $unseen_remixes_grouped[$original_program_id]['remixes'][] = $remix_data;
        }

        $response = $this->get('templating')->renderResponse('::usernotifications.html.twig', array(
            'unseenRemixesGrouped' => $unseen_remixes_grouped
        ));

        $response->headers->set('Cache-Control', 'no-store, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        return $response;

    }

    /**
     * @Route("/user/notifications/count", name="user_notifications_count")
     * @Method({"GET"})
     */
    public function userNotificationsCountAction()
    {
        $user = $this->getUser();
        if (!$user) {
            return JsonResponse::create(array('statusCode' => StatusCode::LOGIN_ERROR));
        }
        $unseen_remixed_program_data = $this->get('remixmanager')->getUnseenRemixProgramsDataOfUser($user);
        return new JsonResponse(['count' => count($unseen_remixed_program_data)]);
    }

    /**
     * @Route("/user/notifications/seen", name="user_notifications_seen")
     * @Method({"GET"})
     */
    public function userNotificationsSeenAction()
    {
        $user = $this->getUser();
        if (!$user) {
            return JsonResponse::create(array('statusCode' => StatusCode::LOGIN_ERROR));
        }
        $this->get('remixmanager')->markAllUnseenRemixRelationsOfUserAsSeen($user);
        return new JsonResponse(['success' => true]);
    }

    /**
     * @Route("/user/notification/ancestor/{ancestor_id}/descendant/{descendant_id}", name="see_user_notification",
     *        requirements={"ancestor_id":"\d+", "descendant_id":"\d+"})
     * @Method({"GET"})
     */
    public function seeUserNotificationAction($ancestor_id, $descendant_id)
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('fos_user_security_login');
        }
        $remix_manager = $this->get('remixmanager');
        $remix_relation = $remix_manager->findCatrobatRelation($ancestor_id, $descendant_id);
        if ($remix_relation == null) {
            throw $this->createNotFoundException('Unable to find Remix relation entity.');
        }
        if ($user->getId() != $remix_relation->getAncestor()->getUser()->getId()) {
            throw $this->createNotFoundException('You are not allowed to update Remix relation entity '
                . 'because you do not own the parent program.');
        }

        $remix_manager->markRemixRelationAsSeen($remix_relation);
        return $this->redirectToRoute('program', ['id' => $descendant_id]);
    }
}
