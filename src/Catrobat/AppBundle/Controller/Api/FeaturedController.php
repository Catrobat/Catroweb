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
    /* @var $repository FeaturedRepository */

    $image_repository = $this->get('featuredimagerepository');
    $repository = $this->get('featuredrepository');

    $flavor = $request->getSession()->get('flavor');

    $limit = intval($request->query->get('limit'));
    $offset = intval($request->query->get('offset'));
    $max_version = $request->query->get('max_version', 0);


    $programs = $repository->getFeaturedPrograms($flavor, $limit, $offset, $max_version);
    $numbOfTotalProjects = $repository->getFeaturedProgramCount($flavor, $max_version);

    $retArray = [];
    $retArray['CatrobatProjects'] = [];
    foreach ($programs as $program)
    {
      $retArray['CatrobatProjects'][] = $this->generateProgramObject($program, $image_repository);
    }
    $retArray['preHeaderMessages'] = '';
    $retArray['CatrobatInformation'] = [
      'BaseUrl'           => ($request->isSecure() ? 'https://' : 'http://') . $request->getHttpHost() . '/',
      'TotalProjects'     => $numbOfTotalProjects,
      'ProjectsExtension' => '.catrobat',
    ];

    return JsonResponse::create($retArray);
  }

  /**
   * @param $program
   * @param $image_repository
   *
   * @return array
   */
  private function generateProgramObject($program, $image_repository)
  {
    $new_program = [];
    $new_program['ProjectId'] = $program->getProgram()->getId();
    $new_program['ProjectName'] = $program->getProgram()->getName();
    $new_program['Author'] = $program->getProgram()
      ->getUser()
      ->getUserName();

    $new_program['FeaturedImage'] = $image_repository->getWebPath($program->getId(), $program->getImageType());

    return $new_program;
  }
}
