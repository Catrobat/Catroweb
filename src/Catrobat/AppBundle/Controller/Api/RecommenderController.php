<?php
namespace Catrobat\AppBundle\Controller\Api;

use Catrobat\AppBundle\Entity\ProgramManager;
use Catrobat\AppBundle\StatusCode;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Catrobat\AppBundle\Services\ScreenshotRepository;
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
        $limit = intval($request->query->get('limit', 20));
        $offset = intval($request->query->get('offset', 0));

        $program_manager = $this->get('programmanager');
        $flavor = $request->getSession()->get('flavor');

        $locale = strtolower($request->getLocale());

        if (substr($locale, 0, 2) == 'de') {
            $programs_count = $program_manager->getTotalLikedProgramsCount($flavor);
            $programs = $program_manager->getMostLikedPrograms($flavor, $limit, $offset);
        } else {
            $programs_count = $program_manager->getTotalRemixedProgramsCount($flavor);
            $programs = $program_manager->getMostRemixedPrograms($flavor, $limit, $offset);
        }

        return new ProgramListResponse($programs, $programs_count);
    }
}
