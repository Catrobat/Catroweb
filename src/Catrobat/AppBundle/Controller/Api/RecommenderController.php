<?php
namespace Catrobat\AppBundle\Controller\Api;

use Catrobat\AppBundle\Entity\ProgramManager;
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
}
