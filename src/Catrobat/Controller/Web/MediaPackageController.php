<?php

namespace App\Catrobat\Controller\Web;

use App\Catrobat\Services\MediaPackageFileRepository;
use App\Entity\MediaPackage;
use App\Entity\MediaPackageCategory;
use App\Entity\MediaPackageFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MediaPackageController extends AbstractController
{
  /**
   * @Route("/media-library/", name="media_library_overview", methods={"GET"})
   *
   * Legacy route:
   * @Route("/pocket-library/", name="pocket_library_overview", methods={"GET"})
   */
  public function indexAction(): Response
  {
    $em = $this->getDoctrine()->getManager();
    /** @var MediaPackage $packages */
    $packages = $em->getRepository(MediaPackage::class)->findAll();

    return $this->render('MediaLibrary/mediapackageindex.html.twig',
      [
        'packages' => $packages,
        'new_nav' => true,
      ]
    );
  }

  /**
   * @Route("/media-library/{package_name}", name="media_library", methods={"GET"})
   *
   * Legacy route:
   * @Route("/pocket-library/{package_name}", name="pocket_library", methods={"GET"})
   */
  public function mediaPackageAction(string $package_name, TranslatorInterface $translator, string $flavor = 'pocketcode'): Response
  {
    if ('' === $flavor)
    {
      $flavor = 'pocketcode';
    }

    $em = $this->getDoctrine()->getManager();
    $package = $em->getRepository(MediaPackage::class)
      ->findOneBy([
        'nameUrl' => $package_name,
      ])
    ;

    if (null === $package)
    {
      throw $this->createNotFoundException('Unable to find Package entity.');
    }

    $categories = $this->sortCategoriesFlavoredFirst($package->getCategories()->toArray(), $flavor, $translator);

    return $this->render('MediaLibrary/mediapackage.html.twig', [
      'flavor' => $flavor,
      'package' => $package_name,
      'categories' => $categories,
      'new_nav' => true,
      'mediaDir' => '/'.$this->getParameter('catrobat.mediapackage.path'),
    ]);
  }

  /**
   * Searching the whole media library and returning results via twig. If a themed app is used (e.g. luna),
   * MediaPackageFiles of that flavor plus all files of the standard flavor 'pocketcode'
   * are returned. If the standard app is used, only files of the standard flavor 'pocketcode' are returned.
   *
   * @Route("/media-library/{package_name}/search/{q}", name="medialibrary_search", requirements={"q": ".+"}, methods={"GET"})
   * @Route("/media-library/{package_name}/search/", name="medialibrary_empty_search", defaults={"q": null}, methods={"GET"})
   *
   * @Route("/pocket-library/{package_name}/search/{q}", name="pocketlibrary_search", requirements={"q": ".+"}, methods={"GET"})
   * @Route("/pocket-library/{package_name}/search/", name="pocketlibrary_empty_search", defaults={"q": null}, methods={"GET"})
   *
   * @param string $q            Search term
   * @param string $package_name Name of MediaPackage to be searched for files
   * @param string $flavor       The flavor (e.g. pocketcode). Only media files of the specified flavor will be displayed.
   *
   * @return Response the response containing the found media library objects
   */
  public function mediaPackageSearchAction(string $q, string $package_name, TranslatorInterface $translator,
                                           MediaPackageFileRepository $media_file_repository,
                                           UrlGeneratorInterface $url_generator, string $flavor = 'pocketcode'): Response
  {
    $found_media_files = $media_file_repository->search($q, $flavor, $package_name);

    $categories_of_found_files = [];
    /** @var MediaPackageFile $found_media_file */
    foreach ($found_media_files as $found_media_file)
    {
      if (!in_array($found_media_file->getCategory(), $categories_of_found_files, true))
      {
        $categories_of_found_files[] = $found_media_file->getCategory();
      }
    }

    $categories = $this->sortCategoriesFlavoredFirst($categories_of_found_files, $flavor, $translator);

    return $this->render('MediaLibrary/mediapackage.html.twig', [
      'mediasearch' => true,
      'flavor' => $flavor,
      'package' => $package_name,
      'categories' => $categories,
      'new_nav' => true,
      'mediaDir' => '/'.$this->getParameter('catrobat.mediapackage.path'),
      'foundResults' => (count($found_media_files) ? true : false),
      'mediaSearchPath' => $url_generator->generate(
        'open_api_server_mediaLibrary_mediafilessearchget',
        [
          'query' => $q,
          'flavor' => $flavor,
          'package' => $package_name,
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

    if ('pocketcode' !== $flavor)
    {
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
    foreach ($unsorted_categories as $category)
    {
      if (0 === strpos($category->getName(), 'ThemeSpecial'))
      {
        continue;
      }

      $categories[] = [
        'displayID' => preg_replace('#[^A-Za-z0-9-_:.]#', '', $category->getName()),
        'name' => $category->getName(),
        'priority' => $category->getPriority(),
      ];
    }

    usort($categories, function ($category_a, $category_b)
    {
      if ($category_a['priority'] === $category_b['priority'])
      {
        return 0;
      }

      return ($category_a['priority'] > $category_b['priority']) ? -1 : 1;
    });

    return $categories;
  }
}
