<?php

namespace App\Catrobat\Controller\Web;

use App\Entity\MediaPackage;
use App\Entity\MediaPackageCategory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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
  public function mediaPackageAction(string $package_name, string $flavor, TranslatorInterface $translator): Response
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
    foreach ($package->getCategories() as $category)
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

    usort($categories, fn ($category_a, $category_b): int => $category_a['priority'] <=> $category_b['priority']);

    $mediaDir = $this->getParameter('catrobat.mediapackage.path');

    return $this->render('MediaLibrary/mediapackage.html.twig', [
      'flavor' => $flavor,
      'package' => $package_name,
      'categories' => $categories,
      'new_nav' => true,
      'mediaDir' => '../../'.$mediaDir,
    ]);
  }
}
