<?php

namespace App\Catrobat\Controller\Api;

use App\Entity\FeaturedProgram;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Repository\FeaturedRepository;
use App\Catrobat\Services\FeaturedImageRepository;


/**
 * Class FeaturedController
 * @package App\Catrobat\Controller\Api
 */
class FeaturedController extends Controller
{

  /**
   * @Route("/api/projects/featured.json", name="api_featured_programs",
   *   defaults={"_format": "json"}, methods={"GET"})
   *
   * @param Request $request
   *
   * @return JsonResponse
   * @throws NonUniqueResultException
   */
  public function getFeaturedProgramsAction(Request $request)
  {
    return $this->getFeaturedPrograms($request, false);
  }


  /**
   * @Route("/api/projects/ios-featured.json", name="api_ios_featured_programs",
   *   defaults={"_format": "json"}, methods={"GET"})
   *
   * @param Request $request
   *
   * @return JsonResponse
   * @throws NonUniqueResultException
   */
  public function getFeaturedIOSProgramsAction(Request $request)
  {
    return $this->getFeaturedPrograms($request, true);
  }


  /**
   * @param Request $request
   * @param         $ios_only
   *
   * @return JsonResponse
   * @throws NonUniqueResultException
   */
  private function getFeaturedPrograms(Request $request, $ios_only)
  {
    /**
     * @var $image_repository FeaturedImageRepository
     * @var $repository FeaturedRepository
     * @var $featured_program FeaturedProgram
     */

    $image_repository = $this->get('featuredimagerepository');
    $repository = $this->get('featuredrepository');

    $flavor = $request->get('flavor');

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
   * @param $featured_program FeaturedProgram
   * @param $image_repository FeaturedImageRepository
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
