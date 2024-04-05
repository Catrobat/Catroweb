<?php

declare(strict_types=1);

namespace App\Api_deprecated\Controller;

use App\DB\Entity\Project\Special\FeaturedProgram;
use App\DB\EntityRepository\Project\Special\FeaturedRepository;
use App\Storage\ImageRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @deprecated
 */
class FeaturedController extends AbstractController
{
  /**
   * @deprecated
   *
   * @throws NonUniqueResultException
   */
  #[Route(path: '/api/projects/ios-featured.json', name: 'api_ios_featured_programs', defaults: ['_format' => 'json'], methods: ['GET'])]
  public function getFeaturedIOSProjects(Request $request, ImageRepository $image_repository, FeaturedRepository $repository): JsonResponse
  {
    return $this->getFeaturedProjects($request, true, $image_repository, $repository);
  }

  /**
   * @throws NonUniqueResultException
   */
  private function getFeaturedProjects(Request $request, bool $ios_only, ImageRepository $image_repository,
    FeaturedRepository $repository): JsonResponse
  {
    $flavor = $request->attributes->get('flavor');

    $limit = (int) $request->query->get('limit', 20);
    $offset = (int) $request->query->get('offset', 0);

    $platform = null;

    if ($ios_only) {
      $platform = 'ios';
    }

    $featured_projects = $repository->getFeaturedPrograms($flavor, $limit, $offset, $platform);
    $numbOfTotalProjects = $repository->getFeaturedProgramCount($flavor, $ios_only);

    $retArray = [];
    $retArray['CatrobatProjects'] = [];
    foreach ($featured_projects as $featured_project) {
      $retArray['CatrobatProjects'][] = $this->generateProjectObject($featured_project, $image_repository);
    }
    $retArray['preHeaderMessages'] = '';
    $retArray['CatrobatInformation'] = [
      'BaseUrl' => $request->getSchemeAndHttpHost().'/',
      'TotalProjects' => $numbOfTotalProjects,
      'ProjectsExtension' => '.catrobat',
    ];

    return new JsonResponse($retArray);
  }

  private function generateProjectObject(FeaturedProgram $featured_project, ImageRepository $image_repository): array
  {
    $new_project = [];
    $new_project['ProjectId'] = $featured_project->getProgram()->getId();
    $new_project['ProjectName'] = $featured_project->getProgram()->getName();
    $new_project['Author'] = $featured_project->getProgram()
      ->getUser()
      ->getUserIdentifier()
    ;

    $new_project['FeaturedImage'] = $image_repository->getWebPath(
      $featured_project->getId(),
      $featured_project->getImageType(),
      true
    );

    return $new_project;
  }
}
