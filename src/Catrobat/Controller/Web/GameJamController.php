<?php

namespace App\Catrobat\Controller\Web;

use App\Entity\GameJam;
use App\Repository\GameJamRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Error\Error;

/**
 * Class GameJamController
 * @package App\Catrobat\Controller\Web
 */
class GameJamController extends AbstractController
{

  /**
   * @Route("/gamejame/submit-your-own", name="gamejam_submit_own", methods={"GET"})
   *
   * @param GameJamRepository $game_jam_repository
   *
   * @return Response
   * @throws NonUniqueResultException
   * @throws Error
   */
  public function gamejamSubmitOwnAction(GameJamRepository $game_jam_repository)
  {
    /**
     * @var $gamejam GameJam
     */
    $jam = null;
    $gamejam = $game_jam_repository->getCurrentGameJam();

    if ($gamejam)
    {
      $gamejam_flavor = $gamejam->getFlavor();

      if ($gamejam_flavor != null)
      {
        $config = $this->getParameter('gamejam');
        $gamejam_config = $config[$gamejam_flavor];
        $jam = $this->configureSubmitYourOwn($gamejam_config, $gamejam);
      }
    }

    return $this->get('templating')->renderResponse('gamejam_submit_own.html.twig', [
      'jam' => $jam,
    ]);
  }


  /**
   * @Route("/gaming-tutorials/{page}", name="catrobat_web_gamejamtutorialcards", defaults={"page" = -1},
   *                                    requirements={"page":"\d+"}, methods={"GET"})
   *
   * @param $page
   *
   * @return Response
   * @throws Error
   */
  public function tutorialcardsAction($page)
  {
    $cards_num = 6;

    if ($page > $cards_num)
    {
      throw $this->createNotFoundException('Unable to find tutorialcard.');
    }

    if ($page == -1)
    {
      return $this->get('templating')->renderResponse('help/gamejamtutorialcards.html.twig', ['count' => $cards_num]);
    }

    $blocks = [];
    $blocks[0] = ['image_orientation' => "landscape", 'image_count' => 2];
    $blocks[1] = ['video_link' => "6ytY_vfsnNU"];
    $blocks[2] = ['program_id' => "0"];

    switch ($page)
    {
      case 1:
        $blocks[0] = ['image_orientation' => "landscape", 'image_count' => 2];
        $blocks[1] = ['video_link' => "tGgMFWoJDBU"];
        $blocks[2] = ['download_url' => $this->generateUrl('download', ['id' => 4799, 'fname' => "Welcome to Wonderland"])];
        break;
      case 2:
        $blocks[0] = ['image_orientation' => "potrait", 'image_count' => 2];
        $blocks[1] = ['video_link' => "mx2DLFIg1Rc"];
        $blocks[2] = ['download_url' => $this->generateUrl('download', ['id' => 4801, 'fname' => "Save Alice"])];
        break;
      case 3:
        $blocks[0] = ['image_orientation' => "landscape", 'image_count' => 2];
        $blocks[1] = ['video_link' => "HxYr_2HdMsE"];
        $blocks[2] = ['download_url' => $this->generateUrl('download', ['id' => 4683, 'fname' => "The Hatter - Hit and Run"])];
        break;
      case 4:
        $blocks[0] = ['image_orientation' => "landscape", 'image_count' => 2];
        $blocks[1] = ['video_link' => "yLqhLmX9Mp4"];
        $blocks[2] = ['download_url' => $this->generateUrl('download', ['id' => 4683, 'fname' => "The Hatter - Hit and Run"])];
        break;
      case 5:
        $blocks[0] = ['image_orientation' => "landscape", 'image_count' => 2];
        $blocks[1] = ['video_link' => "G85_vgb1Ja4"];
        $blocks[2] = ['download_url' => $this->generateUrl('download', ['id' => 4684, 'fname' => "Whack a Cheshire Cat"])];
        break;
      case 6:
        $blocks[0] = ['image_orientation' => "potrait", 'image_count' => 2];
        $blocks[1] = ['video_link' => "m97g4G49kOg"];
        $blocks[2] = ['download_url' => $this->generateUrl('download', ['id' => 4682, 'fname' => "A Rabbits Race"])];
        break;
    }

    return $this->get('templating')->renderResponse('help/gamejamtutorialcard.html.twig', [
      'page'   => $page,
      'blocks' => $blocks,
    ]);
  }


  /**
   * @param $gamejam_config
   * @param $gamejam GameJam
   *
   * @return array
   */
  private function configureSubmitYourOwn($gamejam_config, $gamejam)
  {
    $jam = null;
    if ($gamejam_config != null)
    {
      $display_name = $gamejam_config['display_name'];
      $mobile_en_image_url = $gamejam_config['mobile_image_url_en'];
      $mobile_de_image_url = $gamejam_config['mobile_image_url_de'];
      $web_en_image_url = $gamejam_config['web_image_url_en'];
      $web_de_image_url = $gamejam_config['web_image_url_de'];
      $gamejam_url = $gamejam_config['gamejam_url'];
      $gamejam_tag = $gamejam->getHashtag();

      $jam = [
        'name'                => $display_name,
        'mobile_en_image_url' => $mobile_en_image_url,
        'mobile_de_image_url' => $mobile_de_image_url,
        'web_en_image_url'    => $web_en_image_url,
        'web_de_image_url'    => $web_de_image_url,
        'gamejam_url'         => $gamejam_url,
        'tag'                 => $gamejam_tag,
      ];
    }

    return $jam;
  }
}
