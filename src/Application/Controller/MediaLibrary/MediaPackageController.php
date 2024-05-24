<?php

declare(strict_types=1);

namespace App\Application\Controller\MediaLibrary;

use App\DB\Entity\Flavor;
use App\DB\Entity\MediaLibrary\MediaPackage;
use App\DB\Entity\MediaLibrary\MediaPackageCategory;
use App\DB\Entity\MediaLibrary\MediaPackageFile;
use App\DB\EntityRepository\MediaLibrary\MediaPackageFileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MediaPackageController extends AbstractController
{
  public function __construct(
    private readonly string $catrobat_mediapackage_path,
    private readonly EntityManagerInterface $entity_manager
  ) {
  }

  /**
   * Legacy route:.
   */
  #[Route(path: '/media-library/', name: 'media_library_overview', methods: ['GET'])]
  #[Route(path: '/pocket-library/', name: 'pocket_library_overview', methods: ['GET'])]
  public function index(): Response
  {
    /** @var MediaPackage[] $packages */
    $packages = $this->entity_manager->getRepository(MediaPackage::class)->findAll();

    return $this->render('MediaLibrary/media_library_overview.html.twig',
      [
        'packages' => $packages,
      ]
    );
  }

  /**
   * Legacy route:.
   */
  #[Route(path: '/media-library/{package_name}', name: 'media_library', methods: ['GET'])]
  #[Route(path: '/pocket-library/{package_name}', name: 'pocket_library', methods: ['GET'])]
  public function mediaPackage(Request $request, string $package_name, TranslatorInterface $translator): Response
  {
    $flavor = $request->attributes->get('flavor') ?: Flavor::POCKETCODE;
    $package = $this->entity_manager->getRepository(MediaPackage::class)
      ->findOneBy([
        'nameUrl' => $package_name,
      ])
    ;

    if (null === $package) {
      throw $this->createNotFoundException('Unable to find Package entity.');
    }

    $categories = $this->sortCategoriesFlavoredFirst($package->getCategories()->toArray(), $flavor, $translator);

    return $this->render('MediaLibrary/media_library_package.html.twig', [
      'flavor' => $flavor,
      'package' => $package_name,
      'categories' => $categories,
      'mediaDir' => '/'.$this->catrobat_mediapackage_path,
    ]);
  }

  /**
   * Searching the whole media library and returning results via twig. If a themed app is used (e.g. luna),
   * MediaPackageFiles of that flavor plus all files of the standard flavor 'pocketcode'
   * are returned. If the standard app is used, only files of the standard flavor 'pocketcode' are returned.
   *
   * @param string|null $q            Search term
   * @param string      $package_name Name of MediaPackage to be searched for files
   *
   * @return Response the response containing the found media library objects
   */
  #[Route(path: '/media-library/{package_name}/search/{q}', name: 'medialibrary_search', requirements: ['q' => '.+'], methods: ['GET'])]
  #[Route(path: '/media-library/{package_name}/search/', name: 'medialibrary_empty_search', defaults: ['q' => null], methods: ['GET'])]
  #[Route(path: '/pocket-library/{package_name}/search/{q}', name: 'pocketlibrary_search', requirements: ['q' => '.+'], methods: ['GET'])]
  #[Route(path: '/pocket-library/{package_name}/search/', name: 'pocketlibrary_empty_search', defaults: ['q' => null], methods: ['GET'])]
  public function mediaPackageSearch(?string $q, string $package_name, TranslatorInterface $translator, MediaPackageFileRepository $media_file_repository, UrlGeneratorInterface $url_generator, Request $request): Response
  {
    $flavor = $request->attributes->get('flavor');
    $found_media_files = $media_file_repository->search($q ?? '', $flavor, $package_name);
    $categories_of_found_files = [];
    /** @var MediaPackageFile $found_media_file */
    foreach ($found_media_files as $found_media_file) {
      if (!in_array($found_media_file->getCategory(), $categories_of_found_files, true)) {
        $categories_of_found_files[] = $found_media_file->getCategory();
      }
    }
    $categories = $this->sortCategoriesFlavoredFirst($categories_of_found_files, $flavor, $translator);

    return $this->render('MediaLibrary/media_library_package.html.twig', [
      'mediasearch' => true,
      'flavor' => $flavor,
      'package' => $package_name,
      'categories' => $categories,
      'mediaDir' => '/'.$this->catrobat_mediapackage_path,
      'foundResults' => (count($found_media_files) ? true : false),
      'resultsCount' => is_countable($found_media_files) ? count($found_media_files) : 0,
      'mediaSearchPath' => $url_generator->generate(
        'open_api_server_mediaLibrary_mediafilessearchget',
        [
          'query' => $q,
          'flavor' => $flavor,
          'package_name' => $package_name,
        ],
        UrlGenerator::ABSOLUTE_URL),
    ]);
  }

  /** Sorts the given array of MediaPackageCategory according to importance for the given flavor. Also highlights
   *  "theme specials".
   *
   * @param array  $unsorted_categories The array of unsorted MediaPackageCategory
   * @param string $flavor              the flavor which should be used for sorting
   *
   * @return array the sorted array
   */
  private function sortCategoriesFlavoredFirst(array $unsorted_categories, string $flavor, TranslatorInterface $translator): array
  {
    $categories = [];

    if (Flavor::POCKETCODE !== $flavor) {
      $flavor_name = $translator->trans('flavor.'.$flavor, [], 'catroweb');
      $theme_special_name = $translator->trans('media-packages.theme-special',
        ['%flavor%' => $flavor_name], 'catroweb');

      $categories[] = [
        'displayID' => 'theme-special',
        'name' => $theme_special_name,
        'priority' => PHP_INT_MAX,
      ];
    }

    /** @var MediaPackageCategory $category */
    foreach ($unsorted_categories as $category) {
      if (str_starts_with((string) $category->getName(), 'ThemeSpecial')) {
        continue;
      }

      $categories[] = [
        'displayID' => preg_replace('#[^A-Za-z0-9-_:.]#', '', (string) $category->getName()),
        'name' => $category->getName(),
        'priority' => $category->getPriority(),
      ];
    }

    usort($categories, fn ($category_a, $category_b): int => $category_b['priority'] <=> $category_a['priority']);

    return $categories;
  }
}
