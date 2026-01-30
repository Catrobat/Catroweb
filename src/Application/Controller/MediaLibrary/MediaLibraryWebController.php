<?php

declare(strict_types=1);

namespace App\Application\Controller\MediaLibrary;

use App\DB\Entity\Flavor;
use App\DB\EntityRepository\MediaLibrary\MediaCategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MediaLibraryWebController extends AbstractController
{
  public function __construct(
    private readonly MediaCategoryRepository $category_repository,
  ) {
  }

  /**
   * Media Library Overview - shows all categories.
   */
  #[Route(path: '/media-library/', name: 'media_library_overview', methods: ['GET'])]
  public function overview(Request $request): Response
  {
    $theme = $request->attributes->get('theme', 'app');

    return $this->render('MediaLibrary/Overview.html.twig', [
      'theme' => $theme,
    ]);
  }

  /**
   * Media Library Category Detail - shows assets in a category.
   */
  #[Route(path: '/media-library/{id}', name: 'media_library_category', methods: ['GET'])]
  public function categoryDetail(Request $request, string $id): Response
  {
    $flavor = $request->attributes->get('flavor') ?: Flavor::POCKETCODE;
    $search_query = $request->query->get('search');

    // Find category by UUID
    $category = $this->category_repository->find($id);

    if (null === $category) {
      throw $this->createNotFoundException('Media category not found.');
    }

    return $this->render('MediaLibrary/CategoryDetail.html.twig', [
      'category' => $category,
      'category_name' => $category->getName(),
      'flavor' => $flavor,
      'searchQuery' => $search_query,
    ]);
  }
}
