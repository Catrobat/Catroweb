<?php

namespace Catrobat\AppBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Catrobat\AppBundle\Entity\FeaturedRepository;
use Catrobat\AppBundle\Services\FeaturedImageRepository;

class FeaturedController extends Controller
{

  /**
   * @Route("/api/projects/featured.json", name="api_featured_programs", defaults={"_format": "json"}, methods={"GET"})
   */
  public function getFeaturedProgramsAction(Request $request)
  {
    return $this->getFeaturedPrograms($request, false);
  }

  /**
   * @Route("/api/projects/ios-featured.json", name="api_ios_featured_programs", defaults={"_format": "json"},
   *                                           methods={"GET"})
   */
  public function getFeaturedIOSProgramsAction(Request $request)
  {
    return $this->getFeaturedPrograms($request, true);
  }

  private function getFeaturedPrograms(Request $request, $ios_only)
  {
    /* @var $image_repository FeaturedImageRepository */
    /* @var $repository FeaturedRepository */

    $image_repository = $this->get('featuredimagerepository');
    $repository = $this->get('featuredrepository');

    $flavor = $request->getSession()->get('flavor');

    $limit = intval($request->query->get('limit', 20));
    $offset = intval($request->query->get('offset', 0));

    $featured_programs = $repository->getFeaturedPrograms($flavor, $limit, $offset, $ios_only);
    $numbOfTotalProjects = $repository->getFeaturedProgramCount($flavor, $ios_only);

    $retArray = [];
    $retArray['CatrobatProjects'] = [];
    foreach ($featured_programs as $featured_program)
    {
      $retArray['CatrobatProjects'][] = $this->generateProgramObject($featured_program, $image_repository);
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
  private function generateProgramObject($featured_program, $image_repository)
  {
    $new_program = [];
    $new_program['ProjectId'] = $featured_program->getProgram()->getId();
    $new_program['ProjectName'] = $featured_program->getProgram()->getName();
    $new_program['Author'] = $featured_program->getProgram()
      ->getUser()
      ->getUserName();

    $new_program['FeaturedImage'] = $image_repository->getWebPath($featured_program->getId(), $featured_program->getImageType());

    return $new_program;
  }
}
