<?php

namespace App\Application\Controller\MediaLibrary;

use App\Api\MediaLibraryApi;
use App\Api\Services\MediaLibrary\MediaLibraryApiFacade;
use App\DB\Entity\MediaLibrary\MediaPackage;
use App\DB\Entity\MediaLibrary\MediaPackageCategory;
use App\DB\Entity\MediaLibrary\MediaPackageFile;
use App\DB\EntityRepository\MediaLibrary\MediaPackageFileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MediaLibraryController extends AbstractController
{
  public function __construct(
    private readonly string $catrobat_mediapackage_path,
    private readonly EntityManagerInterface $entity_manager
  ) {}

  #[Route(path: '/media-library/', name: 'media_library_overview', methods: ['GET'])]
  public function mediaLibraryIndex(): Response
  {
    /** @var MediaPackage $packages */
    $packages = $this->entity_manager->getRepository(MediaPackage::class)->findAll();

    return $this->render('MediaLibrary/media_library_overview.html.twig',
      [
        'packages' => $packages,
      ]
    );
  }

  #[Route(path: '/media-library/{package_name}', name: 'media_library_package', methods: ['GET'])]
  public function mediaLibraryPackage(Request $request, string $package_name, TranslatorInterface $translator): Response
  {
    $flavor = $request->attributes->get('flavor');
    if ('' === $flavor) {
      $flavor = 'pocketcode';
    }

    $package = $this->entity_manager->getRepository(MediaPackage::class)
      ->findOneBy([
        'nameUrl' => $package_name,
      ])
    ;

    if (null === $package) {
      throw $this->createNotFoundException('Unable to find Package entity.');
    }
    // dd($package);
    // dd($package->getCategories());

    $categories = $package->getCategories();
    $categories_sorted = $this->sortCategoriesFlavoredFirst($package->getCategories()->toArray(), $flavor, $translator);
    // dd($categories);

    $files = [];

    // $apiFacade = new MediaLibraryApiFacade();
    // $api = new MediaLibraryApi($apiFacade);

    /*
    foreach ($categories_sorted as $category) {
      //dd($category);
      //$api->mediaPackageNameGet($category['name']);
      dd($this->entity_manager->getRepository(MediaPackageFile::class)->findAll());
      $file = $this->entity_manager->getRepository(MediaPackageFile::class)->findBy(['category' => $category]);
      $this->entity_manager->getRepository(MediaPackageFile::class)->
      dd($file);
    }
    */

    $files_array = $this->entity_manager->getRepository(MediaPackageFile::class)->findAll();
    // dd($files_array);
    foreach ($files_array as $file) {
      if (!$file->getActive()) {
        continue;
      }

      $package_found = false;

      $cat_arr = $file->getCategory()->getPackage()->toArray();
      foreach ($cat_arr as $cat) {
        if ($cat->getNameUrl() === $package->getNameUrl()) {
          $package_found = true;
          break;
        }
      }

      if ($package_found) {
        $download_url = $this->generateUrl('download_media',
          [
            'theme' => $flavor,
            'id' => $file->getId(),
          ],
          UrlGeneratorInterface::ABSOLUTE_URL);

        $url = getFileUrl($file, $this->catrobat_mediapackage_path) ?? $download_url;

        $file_raw = [
          'id' => $file->getId(),
          'name' => $file->getName(),
          'extension' => $file->getExtension(),
          'type' => getFileType($file->getExtension()),
          'category' => $file->getCategory(),
          'flavors' => $file->getFlavors(),
          'url' => $url,
          'download_url' => $download_url,
        ];

        $files[] = $file_raw;
      }
    }

    // dd($files);

    return $this->render('MediaLibrary/media_library_package.html.twig', [
      'flavor' => $flavor,
      'package' => $package_name,
      'categories' => $categories_sorted,
      'mediaDir' => '/'.$this->catrobat_mediapackage_path,
      'files' => $files,
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
  public function mediaPackageSearchAction(?string $q, string $package_name, TranslatorInterface $translator, MediaPackageFileRepository $media_file_repository, UrlGeneratorInterface $url_generator, Request $request): Response
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
      'search' => true,
      'flavor' => $flavor,
      'package' => $package_name,
      'categories' => $categories,
      'mediaDir' => '/'.$this->catrobat_mediapackage_path,
      'foundResults' => ((is_countable($found_media_files) ? count($found_media_files) : 0) ? true : false),
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
  private function sortCategoriesFlavoredFirst(array $unsorted_categories, string $flavor, TranslatorInterface $translator)
  {
    $categories = [];

    if ('pocketcode' !== $flavor) {
      $flavor_name = $translator->trans('flavor.'.$flavor, [], 'catroweb');
      $theme_special_name = $translator->trans('media-packages.theme-special',
        ['%flavor%' => $flavor_name], 'catroweb');

      $categories[] = [
        'id' => -1,
        'displayID' => 'theme-special',
        'name' => $theme_special_name,
        // 'package' => '',
        'priority' => PHP_INT_MAX,
      ];
    }

    /** @var MediaPackageCategory $category */
    foreach ($unsorted_categories as $category) {
      if (str_starts_with((string) $category->getName(), 'ThemeSpecial')) {
        continue;
      }

      $categories[] = [
        'id' => $category->getId(),
        'displayID' => preg_replace('#[^A-Za-z0-9-_:.]#', '', $category->getName()),
        'name' => $category->getName(),
        // 'package' => $category->getPackage(),
        'priority' => $category->getPriority(),
      ];
    }

    usort($categories, fn ($category_a, $category_b) => $category_b['priority'] <=> $category_a['priority']);

    return $categories;
  }
}

function getFileType(string $extension): string
{
  $imageExtensions = [
    'bmp', 'cgm', 'g3', 'gif', 'ief', 'jpeg', 'ktx', 'png', 'btif', 'sgi', 'svg', 'tiff', 'psd', 'uvi', 'sub', 'djvu',
    'dwg', 'dxf', 'fbs', 'fpx', 'fst', 'mmr', 'rlc', 'mdi', 'wdp', 'npx', 'wbmp', 'xif', 'webp', '3ds', 'ras', 'cmx',
    'fh', 'ico', 'sid', 'pcx', 'pic', 'pnm', 'pbm', 'pgm', 'ppm', 'rgb', 'tga', 'xbm', 'xpm', 'xwd',
  ];
  $soundExtensions = [
    'adp', 'au', 'mid', 'mp4a', 'mpga', 'oga', 's3m', 'sil', 'uva', 'eol', 'dra', 'dts', 'dtshd', 'lvp', 'pya',
    'ecelp4800', 'ecelp7470', 'ecelp9600', 'rip', 'weba', 'aac', 'aif', 'caf', 'flac', 'mka', 'm3u', 'wax', 'wma',
    'ram', 'rmp', 'wav', 'xm',
  ];
  $videoExtensions = [
    '3gp', '3g2', 'h261', 'h263', 'h264', 'jpgv', 'jpm', 'mj2', 'mp4', 'mpeg', 'ogv', 'qt', 'uvh', 'uvm', 'uvp',
    'uvs', 'uvv', 'dvb', 'fvt', 'mxu', 'pyv', 'uvu', 'viv', 'webm', 'f4v', 'fli', 'flv', 'm4v', 'mkv', 'mng', 'asf',
    'vob', 'wm', 'wmv', 'wmx', 'wvx', 'avi', 'movie', 'smv',
  ];

  $type = 'other';

  if ('catrobat' === $extension) {
    $type = 'project';
  } elseif (in_array($extension, $imageExtensions, true)) {
    $type = 'image';
  } elseif (in_array($extension, $soundExtensions, true)) {
    $type = 'sound';
  } elseif (in_array($extension, $videoExtensions, true)) {
    $type = 'video';
  }

  return $type;
}

function getFileUrl($file, $assets_dir)
{
  if ('project' === getFileType($file->getExtension())) {
    return '/'.$assets_dir.'thumbs/'.$file->getId().'.png';
  }
  if ('image' === getFileType($file->getExtension())) {
    return '/'.$assets_dir.'thumbs/'.$file->getId().'.'.$file->getExtension();
  }

  return null;
}
