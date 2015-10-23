<?php

namespace Catrobat\AppBundle\Controller\Web;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;

class GameJamTutorialController extends Controller
{

  /**
   * @Route("/gaming-tutorials/{page}", name="catrobat_web_gamejamtutorialcards", defaults={"page" = -1}, requirements={"page":"\d+"})
   * @Method({"GET"})
   */
  public function tutorialcardsAction($page)
  {
      $cards_num = 5;

      if ($page > $cards_num) {
          throw $this->createNotFoundException('Unable to find tutorialcard.');
      }

      if ($page == -1) {
        return $this->get('templating')->renderResponse(':help:gamejamtutorialcards.html.twig', array('count' => $cards_num));
      }

      $blocks = array();
      $blocks[0] = array('image_orientation' => "landscape", 'image_count' => 2);
      $blocks[1] = array('video_link' => "6ytY_vfsnNU");
      $blocks[2] = array('program_id' => "0");

      switch($page) {
        case 1:
            $blocks[0] = array('image_orientation' => "landscape", 'image_count' => 2);
            $blocks[1] = array('video_link' => "6ytY_vfsnNU");
            $blocks[2] = array('program_id' => "0");
            break;
        case 2:
            $blocks[0] = array('image_orientation' => "potrait", 'image_count' => 2);
            $blocks[1] = array('video_link' => "6ytY_vfsnNU");
            $blocks[2] = array('program_id' => "0");
            break;
        case 3:
            $blocks[0] = array('image_orientation' => "landscape", 'image_count' => 3);
            $blocks[1] = array('video_link' => "6ytY_vfsnNU");
            $blocks[2] = array('program_id' => "0");
            break;
        case 4:
            $blocks[0] = array('image_orientation' => "landscape", 'image_count' => 3);
            $blocks[1] = array('video_link' => "6ytY_vfsnNU");
            $blocks[2] = array('program_id' => "0");
          break;
        case 5:
            $blocks[0] = array('image_orientation' => "potrait", 'image_count' => 2);
            $blocks[1] = array('video_link' => "6ytY_vfsnNU");
            $blocks[2] = array('program_id' => "0");
          break;
//
//        case 9:
//          $blocks[0] = array('block' => 'one', 'images' => 2);
//          $blocks[2] = array('block' => 'three', 'images' => 1);
//          break;
//
//        case 11:
//          $blocks[1] = array('block' => 'two', 'images' => 2);
//          break;
//
//        case 12:
//          $blocks[1] = array('block' => 'two', 'images' => 3);
//          break;
      }


      return $this->get('templating')->renderResponse(':help:gamejamtutorialcard.html.twig', array(
        'page' => $page,
        'blocks' => $blocks,
      ));
  }
}
