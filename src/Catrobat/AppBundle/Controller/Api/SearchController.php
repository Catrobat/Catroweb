<?php
namespace Catrobat\AppBundle\Controller\Api;

use Catrobat\AppBundle\Entity\ProgramManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Catrobat\AppBundle\Responses\ProgramListResponse;

class SearchController extends Controller
{

    /**
     * @Route("/api/projects/search.json", name="api_search_programs", defaults={"_format": "json"})
     * @Method({"GET"})
     */
    public function searchProgramsAction(Request $request)
    {
        $program_manager = $this->get('programmanager');
        $query = $request->query->get('q');
        $limit = intval($request->query->get('limit'));
        $offset = intval($request->query->get('offset'));
        $numbOfTotalProjects = $program_manager->searchCount($query);
        $programs = $program_manager->search($query, $limit, $offset);
        
        return new ProgramListResponse($programs, $numbOfTotalProjects);
    }
}
