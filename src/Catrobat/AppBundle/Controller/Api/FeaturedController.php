<?php
namespace Catrobat\AppBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Catrobat\AppBundle\Entity\FeaturedRepository;
use Catrobat\AppBundle\Services\FeaturedImageRepository;

class FeaturedController extends Controller
{

    /**
     * @Route("/api/projects/featured.json", name="api_featured_programs", defaults={"_format": "json"})
     * @Method({"GET"})
     */
    public function getFeaturedProgramsAction(Request $request)
    {
        /* @var $image_repository FeaturedImageRepository */
        $image_repository = $this->get('featuredimagerepository');
        /* @var $repository FeaturedRepository */
        $repository = $this->get('featuredrepository');
        
        $flavor = $request->getSession()->get('flavor');
        
        $limit = intval($request->query->get('limit'));
        $offset = intval($request->query->get('offset'));
        
        $programs = $repository->getFeaturedItems($flavor, $limit, $offset);
        $numbOfTotalProjects = $repository->getFeaturedItemCount($flavor);
        
        $retArray = array();
        $retArray['CatrobatProjects'] = array();
        foreach ($programs as $program) {
            $new_program = array();
            if ($program->getProgram() !== null) {
                $new_program['ProjectId'] = $program->getProgram()->getId();
                $new_program['ProjectName'] = $program->getProgram()->getName();
                $new_program['Author'] = $program->getProgram()
                  ->getUser()
                  ->getUserName();
            } else {
                $new_program['Url'] = $program->getUrl();
            }
            $new_program['FeaturedImage'] = $image_repository->getWebPath($program->getId(), $program->getImageType());
            $retArray['CatrobatProjects'][] = $new_program;
        }
        $retArray['preHeaderMessages'] = '';
        $retArray['CatrobatInformation'] = array(
            'BaseUrl' => ($request->isSecure() ? 'https://' : 'http://') . $request->getHttpHost() . '/',
            'TotalProjects' => $numbOfTotalProjects,
            'ProjectsExtension' => '.catrobat'
        );
        
        return JsonResponse::create($retArray);
    }
}
