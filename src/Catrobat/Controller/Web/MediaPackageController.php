<?php

namespace App\Catrobat\Controller\Web;

use App\Entity\MediaPackage;
use App\Entity\MediaPackageCategory;
use App\Entity\MediaPackageFile;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Translation\TranslatorInterface;
use Twig\Error\Error;


/**
 * Class MediaPackageController
 * @package App\Catrobat\Controller\Web
 */
class MediaPackageController extends AbstractController
{

  /**
   * @Route("/media-library/", name="media_library_overview", methods={"GET"})
   * @Route("/pocket-library/", name="pocket_library_overview", methods={"GET"})
   *
   * @return Response
   * @throws Error
   */
  public function indexAction()
  {
    /**
     * @var $em         EntityManager
     * @var $packages   MediaPackage
     */
    $em = $this->getDoctrine()->getManager();
    $packages = $em->getRepository(MediaPackage::class)->findAll();

    return $this->get('templating')->renderResponse('MediaLibrary/mediapackageindex.html.twig',
      [
        'packages' => $packages,
        'new_nav'  => true,
      ]
    );
  }


  /**
   * @Route("/pocket-library/{package_name}", name="pocket_library", methods={"GET"})
   * @Route("/media-library/{package_name}", name="media_package", methods={"GET"})
   *
   * @param Request $request
   * @param         $package_name
   * @param string  $flavor
   * @param TranslatorInterface  $translator
   *
   * @return Response
   * @throws Error
   */
  public function mediaPackageAction(Request $request, $package_name, $flavor, TranslatorInterface $translator)
  {
    /**
     * @var $package  MediaPackage
     * @var $file     MediaPackageFile
     * @var $category MediaPackageCategory
     * @var $user     User
     * @var $token    UsernamePasswordToken
     * @var $event    InteractiveLoginEvent
     * @var TranslatorInterface $translator
     */

    if (!isset($flavor)) {
      $flavor = 'pocketcode';
    }

    $em = $this->getDoctrine()->getManager();
    $package = $em->getRepository(MediaPackage::class)
      ->findOneBy([
        'nameUrl' => $package_name,
      ]);

    if (!$package)
    {
      throw $this->createNotFoundException('Unable to find Package entity.');
    }

    $categories = [];

    if ($flavor !== "pocketcode")
    {
      $flavor_name = $translator->trans("flavor." . $flavor, [], "catroweb");
      $theme_special_name = $translator->trans("media-packages.theme-special",
        ["%flavor%" => $flavor_name], "catroweb");

      $categories[] = [
        'displayID' => 'theme-special',
        'name'      => $theme_special_name,
        'priority'  => PHP_INT_MAX,
      ];
    }

    foreach ($package->getCategories() as $category)
    {
      if (strpos($category->getName(), "ThemeSpecial") === 0)
      {
        continue;
      }

      $categories[] = [
        'displayID' => preg_replace("/[^A-Za-z0-9-_:.]/", '', $category->getName()),
        'name'      => $category->getName(),
        'priority'  => $category->getPriority(),
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

    $mediaDir = $this->getParameter('catrobat.mediapackage.path');

    return $this->get('templating')->renderResponse('MediaLibrary/mediapackage.html.twig', [
      'flavor'     => $flavor,
      'package'    => $package_name,
      'categories' => $categories,
      'new_nav'    => true,
      'mediaDir'   => '../../' . $mediaDir,
    ]);
  }


  /**
   * @param $flavor
   * @param $category MediaPackageCategory
   * @param $files
   *
   * @return array
   */
  private function generateDownloadUrl($flavor, $category, $files)
  {
    /**
     * @var $file MediaPackageFile
     */

    foreach ($category->getFiles() as $file)
    {
      $flavors_arr = preg_replace("/ /", "", $file->getFlavor());
      $flavors_arr = explode(",", $flavors_arr);
      if (!$file->getActive() || ($file->getFlavor() !== null && !in_array($flavor, $flavors_arr)))
      {
        continue;
      }
      $files[] = [
        'id'          => $file->getId(),
        'data'        => $file,
        'downloadUrl' => $this->generateUrl('download_media', [
            'id'    => $file->getId(),
            'fname' => $file->getName()]
        ),
      ];
    }

    return $files;
  }
}