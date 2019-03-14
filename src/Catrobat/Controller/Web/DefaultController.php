<?php

namespace App\Catrobat\Controller\Web;

use App\Entity\MediaPackage;
use App\Entity\MediaPackageCategory;
use App\Entity\MediaPackageFile;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use App\Catrobat\Services\FeaturedImageRepository;
use App\Entity\FeaturedProgram;
use App\Repository\FeaturedRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Class DefaultController
 * @package App\Catrobat\Controller\Web
 */
class DefaultController extends Controller
{

  /**
   * @Route("/", name="index", methods={"GET"})
   *
   * @param Request $request
   *
   * @return Response
   * @throws \Twig\Error\Error
   */
  public function indexAction(Request $request)
  {
    /**
     * @var $image_repository FeaturedImageRepository
     * @var $repository       FeaturedRepository
     * @var $user             User
     * @var $item             FeaturedProgram
     */

    $image_repository = $this->get('featuredimagerepository');
    $repository = $this->get('featuredrepository');

    $flavor = $request->get('flavor');

    if ($flavor === 'phirocode')
    {
      $featured_items = $repository->getFeaturedItems('pocketcode', 10, 0);
    }
    else
    {
      $featured_items = $repository->getFeaturedItems($flavor, 10, 0);
    }

    $featured = [];
    foreach ($featured_items as $item)
    {
      $info = [];
      if ($item->getProgram() !== null)
      {
        if ($flavor)
        {
          $info['url'] = $this->generateUrl('program', ['id' => $item->getProgram()->getId(), 'flavor' => $flavor]);
        }
        else
        {
          $info['url'] = $this->generateUrl('program', ['id' => $item->getProgram()->getId()]);
        }
      }
      else
      {
        $info['url'] = $item->getUrl();
      }
      $info['image'] = $image_repository->getWebPath($item->getId(), $item->getImageType());

      $featured[] = $info;
    }

    return $this->get('templating')->renderResponse('Index/index.html.twig', [
      'featured' => $featured,
    ]);
  }


  /**
   * @Route("/termsOfUse", name="termsOfUse", methods={"GET"})
   *
   * @return Response
   * @throws \Twig\Error\Error
   */
  public function termsOfUseAction()
  {
    return $this->get('templating')->renderResponse('PrivacyAndTerms/termsOfUse.html.twig');
  }


  /**
   * @Route("/licenseToPlay", name="licenseToPlay", methods={"GET"})
   *
   * @return Response
   * @throws \Twig\Error\Error
   */
  public function licenseToPlayAction()
  {
    return $this->get('templating')->renderResponse('PrivacyAndTerms/licenseToPlay.html.twig');
  }


  /**
   * @Route("/pocket-library/{package_name}", name="pocket_library", methods={"GET"})
   * @Route("/media-library/{package_name}", name="media_package", methods={"GET"})
   *
   * @param Request $request
   * @param         $package_name
   * @param string  $flavor
   *
   * @return Response
   * @throws \Twig\Error\Error
   */
  public function MediaPackageAction(Request $request, $package_name, $flavor = 'pocketcode')
  {
    /**
     * @var $package  MediaPackage
     * @var $file     MediaPackageFile
     * @var $category MediaPackageCategory
     * @var $user     User
     */
    if ($request->query->get('username') && $request->query->get('token'))
    {
      $username = $request->query->get('username');
      $user = $this->get('usermanager')->findUserByUsername($username);
      $token_check = $request->query->get('token');
      if ($user->getUploadToken() == $token_check)
      {
        $user = $this->get('usermanager')->findUserByUsername($username);
        $token = new UsernamePasswordToken($user, null, "main", $user->getRoles());
        $this->get('security.token_storage')->setToken($token);
        // now dispatch the login event
        $event = new InteractiveLoginEvent($request, $token);
        $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
      }
    }
    $em = $this->getDoctrine()->getManager();
    $package = $em->getRepository('\App\Entity\MediaPackage')
      ->findOneBy(['name_url' => $package_name]);

    if (!$package)
    {
      throw $this->createNotFoundException('Unable to find Package entity.');
    }

    $categories = [];
    foreach ($package->getCategories() as $category)
    {
      $files = [];
      $files = $this->generateDownloadUrl($flavor, $category, $files);
      $categories[] = [
        'name'     => $category->getName(),
        'files'    => $files,
        'priority' => $category->getPriority(),
      ];
    }

    usort($categories, function ($a, $b) {
      if ($a['priority'] == $b['priority'])
      {
        return 0;
      }

      return ($a['priority'] > $b['priority']) ? -1 : 1;
    });

    return $this->get('templating')->renderResponse('MediaLibrary/mediapackage.html.twig', [
      'categories' => $categories,
    ]);
  }

  /**
   * @Route("/click-statistic", name="click_stats", methods={"POST"})
   *
   * @param Request $request
   *
   * @return Response
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   * @throws \Geocoder\Exception\Exception
   */
  public function makeClickStatisticAction(Request $request)
  {
    $type = $_POST['type'];
    $referrer = $request->headers->get('referer');
    $statistics = $this->get('statistics');
    $locale = strtolower($request->getLocale());

    if (in_array($type, ['programs', 'rec_homepage', 'rec_remix_graph', 'rec_remix_notification', 'rec_specific_programs']))
    {
      $rec_from_id = $_POST['recFromID'];
      $rec_program_id = $_POST['recID'];
      $is_user_specific_recommendation = isset($_POST['recIsUserSpecific']) ? (bool)$_POST['recIsUserSpecific'] : false;
      $is_recommended_program_a_scratch_program = (($type == 'rec_remix_graph') && isset($_POST['isScratchProgram']))
        ? (bool)$_POST['isScratchProgram']
        : false;

      $statistics->createClickStatistics($request, $type, $rec_from_id, $rec_program_id, null, null,
        $referrer, $locale, $is_recommended_program_a_scratch_program, $is_user_specific_recommendation);

      return new Response('ok');
    }
    else
    {
      if ($type == 'tags')
      {
        $tag_id = $_POST['recID'];
        $statistics->createClickStatistics($request, $type, null, null, $tag_id, null, $referrer, $locale);

        return new Response('ok');
      }
      else
      {
        if ($type == 'extensions')
        {
          $extension_name = $_POST['recID'];
          $statistics->createClickStatistics($request, $type, null, null, null, $extension_name, $referrer, $locale);

          return new Response('ok');
        }
        else
        {
          return new Response('error');
        }
      }
    }
  }


  /**
   * @Route("/homepage-click-statistic", name="homepage_click_stats", methods={"POST"})
   *
   * @param Request $request
   *
   * @return Response
   * @throws \Exception
   */
  public function makeNonRecommendedProgramClickStatisticAction(Request $request)
  {
    $type = $_POST['type'];
    $referrer = $request->headers->get('referer');
    $statistics = $this->get('statistics');
    $locale = strtolower($request->getLocale());

    if (in_array($type, ['featured', 'newest', 'mostDownloaded', 'mostViewed', 'random']))
    {
      $program_id = $_POST['programID'];
      $statistics->createHomepageProgramClickStatistics($request, $type, $program_id, $referrer, $locale);

      return new Response('ok');
    }
    else
    {
      return new Response('error');
    }
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
      if (!$file->getActive() || ($file->getFlavor() != null && !in_array($flavor, $flavors_arr)))
      {
        continue;
      }
      $files[] = [
        'id'          => $file->getId(),
        'data'        => $file,
        'downloadUrl' => $this->generateUrl('download_media', [
            'id'    => $file->getId(),
            'fname' => $file->getName()
          ]
        ),
      ];
    }
    return $files;
  }
}