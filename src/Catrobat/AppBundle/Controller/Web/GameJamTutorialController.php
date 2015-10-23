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
      $blocks[0] = array('block' => 'one', 'images' => 1);
      $blocks[1] = array('block' => 'two', 'images' => 1);

      switch($page) {
        case 2:
        case 3:
        case 10:
          $blocks[2] = array('block' => 'three', 'images' => 1);
          break;

        case 9:
          $blocks[0] = array('block' => 'one', 'images' => 2);
          $blocks[2] = array('block' => 'three', 'images' => 1);
          break;

        case 11:
          $blocks[1] = array('block' => 'two', 'images' => 2);
          break;

        case 12:
          $blocks[1] = array('block' => 'two', 'images' => 3);
          break;
      }

      $example_link = null;
      switch($page) {
        case 1:
          $example_link = $this->generateUrl('program', array('id' => 3983));
          break;
        case 2:
          $example_link = $this->generateUrl('program', array('id' => 3984));
          break;
        case 3:
          $example_link = $this->generateUrl('program', array('id' => 3985));
          break;
        case 4:
          $example_link = $this->generateUrl('program', array('id' => 3986));
          break;
        case 5:
          $example_link = $this->generateUrl('program', array('id' => 3987));
          break;
        case 6:
          $example_link = $this->generateUrl('program', array('id' => 3988));
          break;
        case 7:
          $example_link = $this->generateUrl('program', array('id' => 3990));
          break;
        case 8:
          $example_link = $this->generateUrl('program', array('id' => 3991));
          break;
        case 9:
          $example_link = $this->generateUrl('program', array('id' => 3992));
          break;
        case 10:
          $example_link = $this->generateUrl('program', array('id' => 3979));
          break;
        case 11:
          $example_link = $this->generateUrl('program', array('id' => 3981));
          break;
        case 12:
          $example_link = $this->generateUrl('program', array('id' => 3982));
          break;
      }

      return $this->get('templating')->renderResponse(':help:gamejamtutorialcard.html.twig', array(
        'page' => $page,
        'blocks' => $blocks,
        'example_link' => $example_link
      ));
  }
}
