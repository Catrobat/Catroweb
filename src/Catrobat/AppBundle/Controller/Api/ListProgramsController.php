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

class ListProgramsController extends Controller
{

    /**
     * @Route("/api/projects/recent.json", name="api_recent_programs", defaults={"_format": "json"})
     * @Method({"GET"})
     */
    public function listProgramsAction(Request $request)
    {
        return $this->listSortedPrograms($request, 'recent');
    }

    /**
     * @Route("/api/projects/recentIDs.json", name="api_recent_program_ids", defaults={"_format": "json"})
     * @Method({"GET"})
     */
    public function listProgramIdsAction(Request $request)
    {
        return $this->listSortedPrograms($request, 'recent', false);
    }

    /**
     * @Route("/api/projects/mostDownloaded.json", name="api_most_downloaded_programs", defaults={"_format": "json"})
     * @Method({"GET"})
     */
    public function listMostDownloadedProgramsAction(Request $request)
    {
        return $this->listSortedPrograms($request, 'downloads');
    }

    /**
     * @Route("/api/projects/mostDownloadedIDs.json", name="api_most_downloaded_program_ids", defaults={"_format": "json"})
     * @Method({"GET"})
     */
    public function listMostDownloadedProgramIdsAction(Request $request)
    {
        return $this->listSortedPrograms($request, 'downloads', false);
    }

    /**
     * @Route("/api/projects/mostViewed.json", name="api_most_viewed_programs", defaults={"_format": "json"})
     * @Method({"GET"})
     */
    public function listMostViewedProgramsAction(Request $request)
    {
        return $this->listSortedPrograms($request, 'views');
    }

    /**
     * @Route("/api/projects/mostViewedIDs.json", name="api_most_viewed_programids", defaults={"_format": "json"})
     * @Method({"GET"})
     */
    public function listMostViewedProgramIdsAction(Request $request)
    {
        return $this->listSortedPrograms($request, 'views', false);
    }

    /**
     * @Route("/api/projects/randomPrograms.json", name="api_random_programs", defaults={"_format": "json"})
     * @Method({"GET"})
     */
    public function listRandomProgramsAction(Request $request)
    {
        return $this->listSortedPrograms($request, 'random');
    }

    /**
     * @Route("/api/projects/randomProgramIDs.json", name="api_random_programids", defaults={"_format": "json"})
     * @Method({"GET"})
     */
    public function listRandomProgramIdsAction(Request $request)
    {
        return $this->listSortedPrograms($request, 'random', false);
    }

    /**
     * @Route("/api/projects/userPrograms.json", name="api_user_programs", defaults={"_format": "json"})
     * @Method({"GET"})
     */
    public function listUserProgramsAction(Request $request)
    {
        return $this->listSortedPrograms($request, 'user');
    }

    private function listSortedPrograms(Request $request, $sortBy, $details = true)
    {
        $program_manager = $this->get('programmanager');
        $flavor = $request->getSession()->get('flavor');
        
        $limit = intval($request->query->get('limit', 20));
        $offset = intval($request->query->get('offset', 0));
        $user_id = intval($request->query->get('user_id', 0));
        $max_version = $request->query->get('max_version', 0);
        
        if ($sortBy == 'downloads') {
            $programs = $program_manager->getMostDownloadedPrograms($flavor, $limit, $offset, $max_version);
        } elseif ($sortBy == 'views') {
            $programs = $program_manager->getMostViewedPrograms($flavor, $limit, $offset);
        } elseif ($sortBy == 'user') {
            $programs = $program_manager->getUserPrograms($user_id);
        } elseif ($sortBy == 'random') {
            $programs = $program_manager->getRandomPrograms($flavor, $limit, $offset);
        } else {
            $programs = $program_manager->getRecentPrograms($flavor, $limit, $offset);
        }
        
        if ($sortBy == 'user') {
            $numbOfTotalProjects = count($programs);
        } else {
            $numbOfTotalProjects = $program_manager->getTotalPrograms($flavor);
        }

        if($max_version !== 0) {
            $cnt = count($programs);
            for($i=0; $i<$cnt; $i++) {
                $program_version = $programs[$i]->getCatrobatVersionName();
                if(version_compare($program_version, $max_version) > 0) {
                    unset($programs[$i]);
                }
            }
        }

        return new ProgramListResponse($programs, $numbOfTotalProjects, $details);
    }
}
