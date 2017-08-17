<?php

namespace Catrobat\AppBundle\Controller\Web;

use Buzz\Message\Response;
use Catrobat\AppBundle\Entity\Program;
use Catrobat\AppBundle\Entity\ProgramInappropriateReport;
use Catrobat\AppBundle\Entity\ProgramLike;
use Catrobat\AppBundle\Entity\ProgramManager;
use Catrobat\AppBundle\Entity\User;
use Catrobat\AppBundle\Entity\UserComment;
use Catrobat\AppBundle\RecommenderSystem\RecommendedPageId;
use Catrobat\AppBundle\StatusCode;
use Doctrine\Common\Collections\Criteria;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ProgramController extends Controller
{
    /**
     * @Route("/program/remixgraph/{id}", name="program_remix_graph", requirements={"id":"\d+"})
     * @Method({"GET"})
     */
    public function programRemixGraphAction(Request $request, $id)
    {
        $remix_graph_data = $this->get('remixmanager')->getFullRemixGraph($id);
        $screenshot_repository = $this->get('screenshotrepository');

        $catrobat_program_thumbnails = [];
        foreach ($remix_graph_data['catrobatNodes'] as $node_id) {
            if (!array_key_exists($node_id, $remix_graph_data['catrobatNodesData'])) {
                $catrobat_program_thumbnails[$node_id] = '/images/default/not_available.png';
                continue;
            }
            $catrobat_program_thumbnails[$node_id] = '/' . $screenshot_repository->getThumbnailWebPath($node_id);
        }

        $statistics = $this->get('statistics');
        $locale = strtolower($request->getLocale());
        $referrer = $request->headers->get('referer');
        $statistics->createClickStatistics($request, 'show_remix_graph', 0, $id, null, null, $referrer, $locale, false, false);

        return new JsonResponse([
            'id' => $id,
            'remixGraph' => $remix_graph_data,
            'catrobatProgramThumbnails' => $catrobat_program_thumbnails,
        ]);
    }

    /**
     * @Route("/program/{id}", name="program", requirements={"id":"\d+"})
     * @Route("/details/{id}", name="catrobat_web_detail", requirements={"id":"\d+"})
     * @Method({"GET"})
     */
    public function programAction(Request $request, $id, $flavor = 'pocketcode') {
        /**
         * @var $user User
         * @var $program Program
         * @var $reported_program ProgramInappropriateReport
         * @var $gamejam GameJam
         */
        $program_manager = $this->get('programmanager');
        $program = $program_manager->find($id);
        $featured_repository = $this->get('featuredrepository');
        $screenshot_repository = $this->get('screenshotrepository');
        $router = $this->get('router');
        $elapsed_time = $this->get('elapsedtime');

        if (!$program || !$program->isVisible()) {
            if (!$featured_repository->isFeatured($program))
            {
                throw $this->createNotFoundException('Unable to find Project entity.');
            }
        }

        $viewed = $request->getSession()->get('viewed', array());
        $this->checkAndAddViewed($request, $program, $viewed);
        $referrer = $request->headers->get('referer');
        $request->getSession()->set('referer', $referrer);

        $user = $this->getUser();
        $nolb_status = false;
        $user_name = "";
        $like_type = ProgramLike::TYPE_NONE;
        $like_type_count = 0;

        if($user != null) {
            $nolb_status = $user->getNolbUser();
            $user_name = $user->getUsername();
            $like = $program_manager->findUserLike($program->getId(), $user->getId());
            if ($like != null) {
                $like_type = $like->getType();
                $like_type_count = $program_manager->likeTypeCount($program->getId(), $like_type);
            }
        }

        $total_like_count = $program_manager->totalLikeCount($program->getId());
        $program_comments = $this->findCommentsById($program);
        $program_details = $this->createProgramDetailsArray($screenshot_repository, $program, $like_type, $like_type_count,
            $total_like_count, $elapsed_time, $referrer, $program_comments, $request);

        $user_programs = $this->findUserPrograms($user, $program);

        $isReportedByUser = $this->checkReportedByUser($program, $user);

        $program_url = $this->generateUrl('program', array('id' => $program->getId()), true);
        $share_text = trim($program->getName() . ' on ' . $program_url . ' ' . $program->getDescription());

        $jam = $this->extractGameJamConfig();
        return $this->get('templating')->renderResponse('::program.html.twig', array(
            'program_details_url_template' => $router->generate('program', array('id' => 0)),
            'program' => $program,
            'program_details' => $program_details,
            'my_program' => count($user_programs) > 0 ? true : false,
            'already_reported' => $isReportedByUser,
            'shareText' => $share_text,
            'program_url' => $program_url,
            'jam' => $jam,
            'nolb_status' => $nolb_status,
            'user_name' => $user_name,
        ));
    }

    /**
     * @Route("/program/like/{id}", name="program_like", requirements={"id":"\d+"})
     * @Method({"GET"})
     */
    public function programLikeAction(Request $request, $id)
    {
        $type = intval($request->query->get('type', ProgramLike::TYPE_THUMBS_UP));
        $no_unlike = (bool)$request->query->get('no_unlike', false);

        if (!ProgramLike::isValidType($type)) {
            if ($request->isXmlHttpRequest()) {
                return JsonResponse::create(['statusCode' => StatusCode::INVALID_PARAM, 'message' => 'Invalid like type given!']);
            } else {
                throw $this->createAccessDeniedException('Invalid like-type for program given!');
            }
        }

        /** @var ProgramManager $program_manager */
        $program_manager = $this->get('programmanager');
        $program = $program_manager->find($id);
        if ($program == null) {
            if ($request->isXmlHttpRequest()) {
                return JsonResponse::create(['statusCode' => StatusCode::INVALID_PARAM, 'message' => 'Program with given ID does not exist!']);
            } else {
                throw $this->createNotFoundException('Program with given ID does not exist!');
            }
        }

        $user = $this->getUser();
        if (!$user) {
            if ($request->isXmlHttpRequest()) {
                return JsonResponse::create(['statusCode' => StatusCode::LOGIN_ERROR]);
            } else {
                $request->getSession()->set('catroweb_login_redirect', $this->generateUrl(
                    'program_like', ['id' => $id, 'type' => $type, 'no_unlike' => 1]));
                return $this->redirectToRoute('login');
            }
        }

        $new_type = $program_manager->toggleLike($program, $user, $type, $no_unlike);
        $like_type_count = $program_manager->likeTypeCount($program->getId(), $type);
        $total_like_count = $program_manager->totalLikeCount($program->getId());

        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('program', ['id' => $id]);
        }

        return new JsonResponse(['statusCode' => StatusCode::OK, 'data' => [
            'id' => $id,
            'likeType' => $new_type,
            'likeTypeCount' => $like_type_count,
            'totalLikeCount' => $total_like_count
        ]]);
    }

    /**
     * @Route("/search/{q}", name="search", requirements={"q":".+"})
     * @Route("/search/", name="empty_search", defaults={"q":null})
     * @Method({"GET"})
     */
    public function searchAction($q) {
        return $this->get('templating')->renderResponse('::search.html.twig', array('q' => $q));
    }

    /**
     * @Route("/profileDeleteProgram/{id}", name="profile_delete_program", requirements={"id":"\d+"}, defaults={"id" = 0})
     * @Method({"GET"})
     */
    public function deleteProgramAction($id) {
        /*
       * @var $user \Catrobat\AppBundle\Entity\User
       * @var $program \Catrobat\AppBundle\Entity\Program
       */
        if ($id == 0) {
            return $this->redirectToRoute('profile');
        }

        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('fos_user_security_login');
        }

        $programs = $user->getPrograms()->matching(Criteria::create()
            ->where(Criteria::expr()->eq('id', $id)));

        $program = $programs[0];
        if (!$program) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }

        $program->setVisible(false);

        $em = $this->getDoctrine()->getManager();
        $em->persist($program);
        $em->flush();

        return $this->redirectToRoute('profile');
    }

    /**
     * @Route("/profileToggleProgramVisibility/{id}", name="profile_toggle_program_visibility", requirements={"id":"\d+"}, defaults={"id" = 0})
     * @Method({"GET"})
     */
    public function toggleProgramVisibilityAction($id) {
      /*
       * @var $user \Catrobat\AppBundle\Entity\User
       * @var $program \Catrobat\AppBundle\Entity\Program
       */

      if ($id == 0) {
          return $this->redirectToRoute('profile');
      }



      $user = $this->getUser();
      if (!$user) {
          return $this->redirectToRoute('fos_user_security_login');
      }

      $programs = $user->getPrograms()->matching(Criteria::create()
          ->where(Criteria::expr()->eq('id', $id)));

      $program = $programs[0];

      if (!$program) {
        throw $this->createNotFoundException('Unable to find Project entity.');
      }

      $version = $program->getLanguageVersion();
      $max_version = $this->container->get('kernel')->getContainer()->getParameter("catrobat.max_version");
      if (version_compare($version, $max_version, ">"))
      {
        return $this->redirectToRoute('profile');
      }

      $program->setPrivate(!$program->getPrivate());

      $em = $this->getDoctrine()->getManager();
      $em->persist($program);
      $em->flush();

      return $this->redirectToRoute('profile');
    }

    /**
     * @return array
     */
    private function extractGameJamConfig() {
        $jam = null;
        $gamejam = $this->get('gamejamrepository')->getCurrentGameJam();

        if ($gamejam) {
            $gamejam_flavor = $gamejam->getFlavor();
            if ($gamejam_flavor != null) {
                $config = $this->container->getParameter('gamejam');
                $gamejam_config = $config[$gamejam_flavor];
                if ($gamejam_config) {
                    $logo_url = $gamejam_config['logo_url'];
                    $display_name = $gamejam_config['display_name'];
                    $gamejam_url = $gamejam_config['gamejam_url'];
                    $jam = array(
                        'name' => $display_name,
                        'logo_url' => $logo_url,
                        'gamejam_url' => $gamejam_url
                    );
                }
            }
        }
        return $jam;
    }

    /**
     * @param Request $request
     * @param $program
     * @param $viewed
     */
    private function checkAndAddViewed(Request $request, $program, $viewed) {
        if (!in_array($program->getId(), $viewed)) {
            $this->get('programmanager')->increaseViews($program);
            $viewed[] = $program->getId();
            $request->getSession()->set('viewed', $viewed);
        }
    }

    /**
     * @param $screenshot_repository
     * @param $program
     * @param $elapsed_time
     * @param $referrer
     * @param $program_comments
     * @return array
     */
    private function createProgramDetailsArray($screenshot_repository, $program, $like_type, $like_type_count,
                                               $total_like_count, $elapsed_time, $referrer, $program_comments, $request) {
        $rec_by_page_id = intval($request->query->get('rec_by_page_id', RecommendedPageId::INVALID_PAGE));
        $rec_by_program_id = intval($request->query->get('rec_by_program_id', 0));
        $rec_user_specific = intval($request->query->get('rec_user_specific', 0));

        $rec_tag_by_program_id = intval($request->query->get('rec_from', 0));

        if (RecommendedPageId::isValidRecommendedPageId($rec_by_page_id)) {
            // all recommendations (except tag-recommendations -> see below) should generate this download link!
            // At the moment only recommendations based on remixes are supported!
            $url = $this->generateUrl('download', [
                'id' => $program->getId(),
                'rec_by_page_id' => $rec_by_page_id,
                'rec_by_program_id' => $rec_by_program_id,
                'rec_user_specific' => $rec_user_specific,
                'fname' => $program->getName()
            ]);
        } else if ($rec_tag_by_program_id > 0) {
            // tag-recommendations should generate this download link!
            $url = $this->generateUrl('download', [
                'id' => $program->getId(),
                'rec_from' => $rec_tag_by_program_id,
                'fname' => $program->getName()
            ]);
        } else {
            // case: NO recommendation
            $url = $this->generateUrl('download', ['id' => $program->getId(), 'fname' => $program->getName()]);
        }

        $program_details = array(
            'screenshotBig' => $screenshot_repository->getScreenshotWebPath($program->getId()),
            'downloadUrl' => $url,
            'languageVersion' => $program->getLanguageVersion(),
            'downloads' => $program->getDownloads() + $program->getApkDownloads(),
            'views' => $program->getViews(),
            'filesize' => sprintf('%.2f', $program->getFilesize() / 1048576),
            'age' => $elapsed_time->getElapsedTime($program->getUploadedAt()->getTimestamp()),
            'referrer' => $referrer,
            'id' => $program->getId(),
            'comments' => $program_comments,
            'commentsLength' => count($program_comments),
            'remixesLength' => $this->get('remixmanager')->remixCount($program->getId()),
            'likeType' => $like_type,
            'likeTypeCount' => $like_type_count,
            'totalLikeCount' => $total_like_count,
            'isAdmin' => $this->isGranted("ROLE_ADMIN")
        );
        return $program_details;
    }

    /**
     * @param $program
     * @return array|\Catrobat\AppBundle\Entity\UserComment[]
     */
    private function findCommentsById($program) {
        $program_comments = $this->getDoctrine()
            ->getRepository('AppBundle:UserComment')
            ->findBy(
                array('programId' => $program->getId()), array('id' => 'DESC'));
        return $program_comments;
    }

    /**
     * @param $user
     * @param $program
     * @return null
     */
    private function findUserPrograms($user, $program) {
        $user_programs = null;
        if ($user) {
            $user_programs = $user->getPrograms()->matching(Criteria::create()
                ->where(Criteria::expr()->eq('id', $program->getId())));
        }
        return $user_programs;
    }

    /**
     * @param $program
     * @param $user
     * @return bool
     */
    private function checkReportedByUser($program, $user) {
        $isReportedByUser = false;
        $em = $this->getDoctrine()->getManager();
        $reported_program = $em->getRepository("\Catrobat\AppBundle\Entity\ProgramInappropriateReport")
            ->findOneBy(array('program' => $program->getId()));

        if ($reported_program) {
            $isReportedByUser = ($user == $reported_program->getReportingUser());
        }
        return $isReportedByUser;
    }
}
