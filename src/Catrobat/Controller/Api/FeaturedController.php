<?php

namespace App\Catrobat\Controller\Api;

use App\Catrobat\Services\FeaturedImageRepository;
use App\Entity\FeaturedProgram;
use App\Repository\FeaturedRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FeaturedController extends AbstractController
{
  /**
   * @deprecated
   *
   * @Route("/api/projects/featured.json", name="api_featured_programs",
   * defaults={"_format": "json"}, methods={"GET"})
   *
   * @throws NonUniqueResultException
   */
  public function getFeaturedProgramsAction(Request $request, FeaturedImageRepository $image_repository,
                                            FeaturedRepository $repository): JsonResponse
  {
    return $this->getFeaturedPrograms($request, false, $image_repository, $repository);
  }

  /**
   * @deprecated
   *
   * @Route("/api/projects/ios-featured.json", name="api_ios_featured_programs",
   * defaults={"_format": "json"}, methods={"GET"})
   *
   * @throws NonUniqueResultException
   */
  public function getFeaturedIOSProgramsAction(Request $request, FeaturedImageRepository $image_repository,
                                               FeaturedRepository $repository): JsonResponse
  {
    return $this->getFeaturedPrograms($request, true, $image_repository, $repository);
  }

  /**
   * @throws NonUniqueResultException
   */
  private function getFeaturedPrograms(Request $request, bool $ios_only, FeaturedImageRepository $image_repository,
                                       FeaturedRepository $repository): JsonResponse
  {
    $flavor = $request->get('flavor');

    $limit = (int) $request->query->get('limit', 20);
    $offset = (int) $request->query->get('offset', 0);

    $platform = null;

    if ($ios_only)
    {
      $platform = 'ios';
    }

    $featured_programs = $repository->getFeaturedPrograms($flavor, $limit, $offset, $platform);
    $numbOfTotalProjects = $repository->getFeaturedProgramCount($flavor, $ios_only);

    $retArray = [];
    $retArray['CatrobatProjects'] = [];
    foreach ($featured_programs as $featured_program)
    {
      $retArray['CatrobatProjects'][] = $this->generateProgramObject($featured_program, $image_repository);
    }
    $retArray['preHeaderMessages'] = '';
    $retArray['CatrobatInformation'] = [
      'BaseUrl' => $request->getSchemeAndHttpHost().'/',
      'TotalProjects' => $numbOfTotalProjects,
      'ProjectsExtension' => '.catrobat',
    ];

    return JsonResponse::create($retArray);
  }

  private function generateProgramObject(FeaturedProgram $featured_program, FeaturedImageRepository $image_repository): array
  {
    $new_program = [];
    $new_program['ProjectId'] = $featured_program->getProgram()->getId();
    $new_program['ProjectName'] = $featured_program->getProgram()->getName();
    $new_program['Author'] = $featured_program->getProgram()
      ->getUser()
      ->getUserName()
    ;

    $new_program['FeaturedImage'] = $image_repository->getWebPath(
      $featured_program->getId(),
      $featured_program->getImageType()
    );

    return $new_program;
  }
}
