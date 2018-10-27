<?php

namespace Catrobat\AppBundle\Controller\Web;

use Catrobat\AppBundle\Entity\MediaPackage;
use Catrobat\AppBundle\Entity\MediaPackageCategory;
use Catrobat\AppBundle\Entity\MediaPackageFile;
use Catrobat\AppBundle\Entity\User;
use phpDocumentor\Reflection\Types\Integer;
use Psr\Log\LoggerInterface;
use Psr\Log\Test\LoggerInterfaceTest;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class MediaPackageController extends Controller
{
  /**
   * @Route("/media-library", name="media_library_overview", methods={"GET"})
   * @Route("/pocket-library", name="pocket_library_overview", methods={"GET"})
   */
  public function indexAction(LoggerInterface $logger)
  {
    /**
     * @var $user       \Catrobat\AppBundle\Entity\User
     * @var $em         \Doctrine\ORM\EntityManager
     * @var $packages   MediaPackage
     * @var $package    MediaPackage
     * @var $categories MediaPackageCategory
     * @var $category   MediaPackageCategory
     * @var $file       MediaPackageFile
     */
    $em = $this->getDoctrine()->getManager();
    $packages = $em->getRepository(MediaPackage::class)->findAll();

    return $this->get('templating')->renderResponse('mediapackageindex.html.twig',
      [
        'packages' => $packages,
      ]
    );
  }

  /**
   *
   * @Route("/pocket-library/{package_name}", name="pocket_library", methods={"GET"})
   * @Route("/media-library/{package_name}", name="media_package", methods={"GET"})
   *
   * @param Request $request
   * @param         $package_name
   * @param string  $flavor
   *
   * @return mixed
   */
  public function MediaPackageAction(Request $request, $package_name, $flavor = 'pocketcode')
  {
    /**
     * @var $package  MediaPackage
     * @var $file     MediaPackageFile
     * @var $category MediaPackageCategory
     * @var $user     User
     * @var $token    UsernamePasswordToken
     * @var $event    InteractiveLoginEvent
     */
//    if($request->query->get('username') && $request->query->get('token'))
//    {
//      $username = $request->query->get('username');
//      $user = $this->get('usermanager')->findUserByUsername($username);
//      $token_check = $request->query->get('token');
//      if($user->getUploadToken() === $token_check)
//      {
//        $user = $this->get('usermanager')->findUserByUsername($username);
//        $token = new UsernamePasswordToken($user, null, "main", $user->getRoles());
//        $this->get('security.token_storage')->setToken($token);
//        // now dispatch the login event
//
//        $request = $this->get("request");
//        $event = new InteractiveLoginEvent($request, $token);
//        $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
//      }
//    }


    $em = $this->getDoctrine()->getManager();
    $package = $em->getRepository(MediaPackage::class)
      ->findOneBy([
        'nameUrl' => $package_name,
      ]);

    if (!$package)
    {
      throw $this->createNotFoundException('Unable to find Package entity.');
    }

    $user = $this->getUser();
    $nolb = false;
    if ($user)
    {
      $nolb = $user->getNolbUser();
    }

    $categories = [];

    if ($flavor !== "pocketcode")
    {
      $categories[] = [
        'displayID' => str_replace(' ', '', $flavor),
        'name'      => $flavor . " Theme Special",
        'files'     => [],
        'priority'  => PHP_INT_MAX,
      ];
    }

    foreach ($package->getCategories() as $category)
    {
      if (!$nolb && strpos($category->getName(), 'Nolb') === false)
      {
        $categories[] = [
          'displayID' => str_replace(' ', '', $category->getName()),
          'name'     => $category->getName(),
          'priority' => $category->getPriority(),
        ];
      }
    }

    usort($categories, function($a, $b) {
      if ($a['priority'] == $b['priority'])
      {
        return 0;
      }

      return ($a['priority'] > $b['priority']) ? -1 : 1;
    });

    return $this->get('templating')->renderResponse('mediapackage.html.twig', [
      'flavor'     => $flavor,
      'categories' => $categories,
    ]);
  }

  /**
   * @Route("/api/mediapackage", name="api_search_programs", defaults={"_format": "json"},
   *                                     methods={"GET"})
   * @param $flavor
   * @param $category
   * @param $files
   *
   * @return array
   */
  private function generateDownloadUrl($flavor, $category, $files)
  {
    foreach ($category->getFiles() as $file)
    {
      $flavors_arr = preg_replace("/ /", "", $file->getFlavor());
      $flavors_arr = explode(",", $flavors_arr);
      if (!$file->getActive() || ($file->getFlavor() != null && !in_array($flavor, $flavors_arr)))
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