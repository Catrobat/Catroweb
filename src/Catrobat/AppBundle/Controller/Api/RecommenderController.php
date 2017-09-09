<?php
namespace Catrobat\AppBundle\Controller\Api;

use Catrobat\AppBundle\StatusCode;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Catrobat\AppBundle\Responses\ProgramListResponse;

class RecommenderController extends Controller
{
    /**
     * @Route("/api/projects/recsys.json", name="api_recsys_programs", defaults={"_format": "json"})
     * @Method({"GET"})
     */
    public function listRecsysProgramAction(Request $request)
    {
        $limit = intval($request->query->get('limit', 20));
        $offset = intval($request->query->get('offset', 0));
        $program_id = intval($request->query->get('program_id'));

        $program_manager = $this->get('programmanager');
        $flavor = $request->getSession()->get('flavor');

        $programs_count = $program_manager->getRecommendedProgramsCount($program_id, $flavor);
        $programs = $program_manager->getRecommendedProgramsById($program_id, $flavor, $limit, $offset);

//      return JsonResponse::create($programs);
        return new ProgramListResponse($programs, $programs_count);
    }

    /**
     * @Route("/api/projects/recsys_specific_programs/{id}.json", name="api_recsys_specific_programs", defaults={"_format": "json"}, requirements={"id":"\d+"})
     * @Method({"GET"})
     */
    public function listRecsysSpecificProgramsAction(Request $request, $id)
    {
        $is_test_environment = ($this->get('kernel')->getEnvironment() == 'test');
        $limit = intval($request->query->get('limit', 20));
        $offset = intval($request->query->get('offset', 0));

        $program_manager = $this->get('programmanager');
        $flavor = $request->getSession()->get('flavor');

        $program = $program_manager->find($id);
        if ($program == null) {
            return JsonResponse::create(array('statusCode' => StatusCode::INVALID_PROGRAM));
        }

        $programs_count = $program_manager->getRecommendedProgramsCount($id, $flavor, $is_test_environment);
        $programs = $program_manager->getOtherMostDownloadedProgramsOfUsersThatAlsoDownloadedGivenProgram($flavor, $program, $limit, $offset, $is_test_environment);

        return new ProgramListResponse($programs, $programs_count);
    }

    /**
     * @Route("/api/projects/recsys_general_programs.json", name="api_recsys_general_programs", defaults={"_format": "json"})
     * @Method({"GET"})
     */
    public function listRecsysGeneralProgramsAction(Request $request)
    {
        $is_test_environment = ($this->get('kernel')->getEnvironment() == 'test');
        $test_user_id_for_like_recommendation = $is_test_environment ? intval($request->query->get('test_user_id_for_like_recommendation', 0)) : 0;
        $limit = intval($request->query->get('limit', 20));
        $offset = intval($request->query->get('offset', 0));

        $program_manager = $this->get('programmanager');
        $flavor = $request->getSession()->get('flavor');

        $programs_count = 0;
        $programs = [];
        $is_user_specific_recommendation = false;

        $user = ($test_user_id_for_like_recommendation == 0) ? $this->getUser() : $this->get('usermanager')->find($test_user_id_for_like_recommendation);
        if ($user != null) {
            $recommender_manager = $this->get('recommendermanager');
            $all_programs = $recommender_manager->recommendProgramsOfLikeSimilarUsers($user, $flavor);
            $programs_count = count($all_programs);
            $programs = array_slice($all_programs, $offset, $limit);
        }

        if (($user == null) || ($programs_count == 0)) {
            $programs_count = $program_manager->getTotalLikedProgramsCount($flavor);
            $programs = $program_manager->getMostLikedPrograms($flavor, $limit, $offset);
        } else {
            $is_user_specific_recommendation = true;
        }

        return new ProgramListResponse($programs, $programs_count, true, $is_user_specific_recommendation);
    }
}
