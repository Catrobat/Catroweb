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
        $max_version = $request->query->get('max_version', 0);

        $programs = $program_manager->search($query, $limit, $offset);

        $numbOfTotalProjects = $program_manager->searchCount($query);

        if($max_version !== 0) {
            $cnt = count($programs);
            for($i=0; $i<$cnt; $i++) {
                $program_version = $programs[$i]->getLanguageVersion();
                if(version_compare($program_version, $max_version) > 0) {
                    unset($programs[$i]);
                }
            }
        }

        return new ProgramListResponse($programs, $numbOfTotalProjects);
    }

    /**
     * @Route("/api/projects/search/tagPrograms.json", name="api_search_tag", defaults={"_format": "json"})
     * @Method({"GET"})
     */
    public function tagSearchProgramsAction(Request $request)
    {
        $program_manager = $this->get('programmanager');
        $query = $request->query->get('q');
        $limit = intval($request->query->get('limit', 20));
        $offset = intval($request->query->get('offset', 0));
        $numbOfTotalProjects = $program_manager->searchTagCount($query);
        $programs = $program_manager->getProgramsByTagId($query, $limit, $offset);

        return new ProgramListResponse($programs, $numbOfTotalProjects);
    }

    /**
     * @Route("/api/projects/search/extensionPrograms.json", name="api_search_extension", defaults={"_format": "json"})
     * @Method({"GET"})
     */
    public function extensionSearchProgramsAction(Request $request)
    {
        $program_manager = $this->get('programmanager');
        $query = $request->query->get('q');
        $limit = intval($request->query->get('limit', 20));
        $offset = intval($request->query->get('offset', 0));
        $numbOfTotalProjects = $program_manager->searchExtensionCount($query);
        $programs = $program_manager->getProgramsByExtensionName($query, $limit, $offset);

        return new ProgramListResponse($programs, $numbOfTotalProjects);
    }
}
