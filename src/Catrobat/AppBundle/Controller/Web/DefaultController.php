<?php

namespace Catrobat\AppBundle\Controller\Web;
use Catrobat\AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Catrobat\AppBundle\Services\FeaturedImageRepository;
use Catrobat\AppBundle\Entity\FeaturedRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
  /**
   * @Route("/", name="index")
   * @Method({"GET"})
   */
  public function indexAction(Request $request)
  {
      /**
       * @var $image_repository FeaturedImageRepository
       * @var $repository FeaturedRepository
       * @var $user User
       */
      $image_repository = $this->get('featuredimagerepository');
      $repository = $this->get('featuredrepository');

      $featured_items = $repository->getFeaturedItems($request->getSession()->get('flavor'), 5, 0);

      $featured = array();
      foreach ($featured_items as $item) {
          $info = array();
          if ($item->getProgram() !== null) {
              if ($request->get('flavor')) {
                  $info['url'] = $this->generateUrl('program', array('id' => $item->getProgram()->getId(), 'flavor' => $request->get('flavor')));
              } else {
                  $info['url'] = $this->generateUrl('catrobat_web_program', array('id' => $item->getProgram()->getId()));
              }
          } else {
              $info['url'] = $item->getUrl();
          }
          $info['image'] = $image_repository->getWebPath($item->getId(), $item->getImageType());

          $featured[] = $info;
      }

      $user = $this->getUser();
      $nolb = false;

      if ($user) {
        $nolb = $user->getNolbUser();
      }

      return $this->get('templating')->renderResponse('::index.html.twig', array(
        'featured' => $featured,
        'nolb' => $nolb
      ));
  }

  /**
   * @Route("/termsOfUse", name="termsOfUse")
   * @Method({"GET"})
   */
  public function termsOfUseAction()
  {
      return $this->get('templating')->renderResponse('::termsOfUse.html.twig');
  }

  /**
   * @Route("/licenseToPlay", name="licenseToPlay")
   * @Method({"GET"})
   */
  public function licenseToPlayAction()
  {
      return $this->get('templating')->renderResponse('::licenseToPlay.html.twig');
  }

  /**
   * @Route("/pocket-library/{package_name}", name="pocket_library")
   * @Route("/media-library/{package_name}", name="media_package")
   * @Method({"GET"})
   */
  public function MediaPackageAction($package_name, $flavor = 'pocketcode')
  {
    /**
     * @var $package \Catrobat\AppBundle\Entity\MediaPackage
     * @var $file \Catrobat\AppBundle\Entity\MediaPackageFile
     * @var $category \Catrobat\AppBundle\Entity\MediaPackageCategory
     */
    $em = $this->getDoctrine()->getManager();
    $package = $em->getRepository('\Catrobat\AppBundle\Entity\MediaPackage')
      ->findOneBy(array('name_url' => $package_name));

    if (!$package) {
      throw $this->createNotFoundException('Unable to find Package entity.');
    }

    $categories = array();
    foreach($package->getCategories() as $category) {
      $files = array();
        $files = $this->generateDownloadUrl($flavor, $category, $files);
        $categories[] = array(
        'name' => $category->getName(),
        'files' => $files,
        'priority' => $category->getPriority()
      );
    }

    usort($categories, function($a,$b) {
      if($a['priority'] == $b['priority'])
        return 0;
      return ($a['priority'] > $b['priority']) ? -1 : 1;
    });

    return $this->get('templating')->renderResponse('::mediapackage.html.twig', array(
      'categories' => $categories
    ));
  }

    /**
     * @Route("/click-statistic", name="click_stats")
     * @Method({"POST"})
     */
    public function makeClickStatisticAction(Request $request)
    {
        $type = $_POST['type'];
        $referrer = $request->headers->get('referer');
        $statistics = $this->get('statistics');
        $locale = strtolower($request->getLocale());

        if (in_array($type, ['programs', 'rec_homepage', 'rec_remix_graph', 'rec_remix_notification', 'rec_specific_programs'])) {
            $rec_from_id = $_POST['recFromID'];
            $rec_program_id = $_POST['recID'];
            $is_user_specific_recommendation = isset($_POST['recIsUserSpecific']) ? (bool) $_POST['recIsUserSpecific'] : false;
            $is_recommended_program_a_scratch_program = (($type == 'rec_remix_graph') && isset($_POST['isScratchProgram']))
                                                      ? (bool) $_POST['isScratchProgram']
                                                      : false;

            $statistics->createClickStatistics($request, $type, $rec_from_id, $rec_program_id, null, null,
                $referrer, $locale, $is_recommended_program_a_scratch_program, $is_user_specific_recommendation);
            return new Response('ok');
        } else if ($type == 'tags') {
            $tag_id = $_POST['recID'];
            $statistics->createClickStatistics($request, $type, null, null, $tag_id, null, $referrer, $locale);
            return new Response('ok');
        } else if ($type == 'extensions') {
            $extension_name = $_POST['recID'];
            $statistics->createClickStatistics($request, $type, null, null, null, $extension_name, $referrer, $locale);
            return new Response('ok');
        } else
            return new Response('error');
    }

    /**
     * @Route("/homepage-click-statistic", name="homepage_click_stats")
     * @Method({"POST"})
     */
    public function makeNonRecommendedProgramClickStatisticAction(Request $request)
    {
        $type = $_POST['type'];
        $referrer = $request->headers->get('referer');
        $statistics = $this->get('statistics');
        $locale = strtolower($request->getLocale());

        if (in_array($type, ['featured', 'newest', 'mostDownloaded', 'mostViewed', 'random', 'nolb-example'])) {
            $program_id = $_POST['programID'];
            $statistics->createHomepageProgramClickStatistics($request, $type, $program_id, $referrer, $locale);
            return new Response('ok');
        } else
            return new Response('error');
    }

    /**
     * @param $flavor
     * @param $category
     * @param $files
     * @return array
     */
    private function generateDownloadUrl($flavor, $category, $files) {
        foreach ($category->getFiles() as $file) {
            $flavors_arr = preg_replace("/ /", "", $file->getFlavor());
            $flavors_arr = explode(",", $flavors_arr);
            if (!$file->getActive() || ($file->getFlavor() != null && !in_array($flavor, $flavors_arr))) {
                continue;
            }
            $files[] = array(
                'id' => $file->getId(),
                'data' => $file,
                'downloadUrl' => $this->generateUrl('download_media', array(
                    'id' => $file->getId(),
                    'fname' => $file->getName()
                ))
            );
        }
        return $files;
    }
}