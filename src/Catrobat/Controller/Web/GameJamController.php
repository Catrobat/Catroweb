<?php

namespace App\Catrobat\Controller\Web;

use App\Entity\GameJam;
use App\Repository\GameJamRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GameJamController extends AbstractController
{
  /**
   * @Route("/gamejam/submit-your-own", name="gamejam_submit_own", methods={"GET"})
   *
   * @throws NonUniqueResultException
   */
  public function gameJamSubmitOwnAction(GameJamRepository $game_jam_repository): Response
  {
    $jam = null;

    /** @var GameJam|null $game_jam */
    $game_jam = $game_jam_repository->getCurrentGameJam();

    if (null !== $game_jam)
    {
      $game_jam_flavor = $game_jam->getFlavor();

      if (null != $game_jam_flavor)
      {
        $config = $this->getParameter('gamejam');
        $gamejam_config = $config[$game_jam_flavor];
        $jam = $this->configureSubmitYourOwn($gamejam_config, $game_jam);
      }
    }

    return $this->render('gamejam_submit_own.html.twig', [
      'jam' => $jam,
    ]);
  }

  /**
   * @Route("/gaming-tutorials/{page}", name="catrobat_web_gamejamtutorialcards", defaults={"page": -1},
   * requirements={"page": "\d+"}, methods={"GET"})
   */
  public function tutorialCardsAction(int $page): Response
  {
    $cards_num = 6;

    if ($page > $cards_num)
    {
      throw $this->createNotFoundException('Unable to find tutorialcard.');
    }

    if (-1 == $page)
    {
      return $this->render('help/gamejamtutorialcards.html.twig', ['count' => $cards_num]);
    }

    $blocks = [];
    $blocks[0] = ['image_orientation' => 'landscape', 'image_count' => 2];
    $blocks[1] = ['video_link' => '6ytY_vfsnNU'];
    $blocks[2] = ['program_id' => '0'];

    switch ($page)
    {
      case 1:
        $blocks[0] = ['image_orientation' => 'landscape', 'image_count' => 2];
        $blocks[1] = ['video_link' => 'tGgMFWoJDBU'];
        $blocks[2] = ['download_url' => $this->generateUrl('download', ['id' => 4_799, 'fname' => 'Welcome to Wonderland'])];
        break;
      case 2:
        $blocks[0] = ['image_orientation' => 'potrait', 'image_count' => 2];
        $blocks[1] = ['video_link' => 'mx2DLFIg1Rc'];
        $blocks[2] = ['download_url' => $this->generateUrl('download', ['id' => 4_801, 'fname' => 'Save Alice'])];
        break;
      case 3:
        $blocks[0] = ['image_orientation' => 'landscape', 'image_count' => 2];
        $blocks[1] = ['video_link' => 'HxYr_2HdMsE'];
        $blocks[2] = ['download_url' => $this->generateUrl('download', ['id' => 4_683, 'fname' => 'The Hatter - Hit and Run'])];
        break;
      case 4:
        $blocks[0] = ['image_orientation' => 'landscape', 'image_count' => 2];
        $blocks[1] = ['video_link' => 'yLqhLmX9Mp4'];
        $blocks[2] = ['download_url' => $this->generateUrl('download', ['id' => 4_683, 'fname' => 'The Hatter - Hit and Run'])];
        break;
      case 5:
        $blocks[0] = ['image_orientation' => 'landscape', 'image_count' => 2];
        $blocks[1] = ['video_link' => 'G85_vgb1Ja4'];
        $blocks[2] = ['download_url' => $this->generateUrl('download', ['id' => 4_684, 'fname' => 'Whack a Cheshire Cat'])];
        break;
      case 6:
        $blocks[0] = ['image_orientation' => 'potrait', 'image_count' => 2];
        $blocks[1] = ['video_link' => 'm97g4G49kOg'];
        $blocks[2] = ['download_url' => $this->generateUrl('download', ['id' => 4_682, 'fname' => 'A Rabbits Race'])];
        break;
    }

    return $this->render('help/gamejamtutorialcard.html.twig', [
      'page' => $page,
      'blocks' => $blocks,
    ]);
  }

  private function configureSubmitYourOwn(array $game_jam_config, GameJam $game_jam): ?array
  {
    $jam = null;
    if (null != $game_jam_config)
    {
      $display_name = $game_jam_config['display_name'];
      $mobile_en_image_url = $game_jam_config['mobile_image_url_en'];
      $mobile_de_image_url = $game_jam_config['mobile_image_url_de'];
      $web_en_image_url = $game_jam_config['web_image_url_en'];
      $web_de_image_url = $game_jam_config['web_image_url_de'];
      $gamejam_url = $game_jam_config['gamejam_url'];
      $gamejam_tag = $game_jam->getHashtag();

      $jam = [
        'name' => $display_name,
        'mobile_en_image_url' => $mobile_en_image_url,
        'mobile_de_image_url' => $mobile_de_image_url,
        'web_en_image_url' => $web_en_image_url,
        'web_de_image_url' => $web_de_image_url,
        'gamejam_url' => $gamejam_url,
        'tag' => $gamejam_tag,
      ];
    }

    return $jam;
  }
}
