<?php

namespace Catrobat\AppBundle\Controller\Web;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;

class TutorialController extends Controller
{
    /**
   * @Route("/help", name="catrobat_web_help")
   * @Method({"GET"})
   */
  public function helpAction()
  {
      return $this->get('templating')->renderResponse(':help:help.html.twig');
  }

  /**
   * @Route("/step-by-step/{page}", name="catrobat_web_step_by_step", defaults={"page" = 1}, requirements={"page":"\d+"})
   * @Route("/stepByStep/{page}", name="catrobat_web_stepByStep", defaults={"page" = 1}, requirements={"page":"\d+"})
   * @Method({"GET"})
   */
  public function stepByStepAction($page)
  {
      $max_page = 11;

      if ($page > $max_page) {
          throw $this->createNotFoundException('Unable to find step.');
      }

      $paginator = $this->get('knp_paginator');
      $steps = array();

      for ($i = 1; $i <= $max_page; ++$i) {
          $steps[] = $i;
      }

      $pagination = $paginator->paginate(
      $steps,
      $page, //current page
      1/*limit per page*/
    );

      $pagination->setTemplate(':help:paginationStart1.html.twig');

      return $this->get('templating')->renderResponse(':help:stepByStep.html.twig', array(
      'page' => $page,
      'pagination' => $pagination,
    ));
  }

  /**
   * @Route("/tutorialcards/{page}", name="catrobat_web_tutorialcards", defaults={"page" = -1}, requirements={"page":"\d+"})
   * @Method({"GET"})
   */
  public function tutorialcardsAction($page)
  {
      $cards_num = 12;

      if ($page > $cards_num) {
          throw $this->createNotFoundException('Unable to find tutorialcard.');
      }

      if ($page == -1) {
          return $this->get('templating')->renderResponse(':help:tutorialcards.html.twig', array('count' => $cards_num));
      }

      $blocks = $this->generateBlocks($page);

      $example_link = $this->setExampleLink($page);

      return $this->get('templating')->renderResponse(':help:tutorialcard.html.twig', array(
        'page' => $page,
        'blocks' => $blocks,
        'example_link' => $example_link
      ));
  }

  /**
   * @Route("/starter-programs", name="catrobat_web_starter")
   * @Method({"GET"})
   */
  public function starterProgramsAction()
  {
      /*
     * @var $categories \Catrobat\AppBundle\Entity\StarterCategory
     */
    $em = $this->getDoctrine()->getManager();

      $categories = $em->getRepository('AppBundle:StarterCategory')->findBy(array(), array('order' => 'asc'));

      $categories_twig = $this->generateCategoryArray($categories);

      return $this->get('templating')->renderResponse(':help:starterPrograms.html.twig', array(
      'categories' => $categories_twig,
    ));
  }

  /**
   * @Route("/category-programs/{id}", name="catrobat_web_category_programs", requirements={"id":"\d+"})
   * @Method({"GET"})
   */
  public function categoryProgramsAction(Request $request, $id)
  {
      /*
     * @var $program \Catrobat\AppBundle\Entity\Program
     */
    $em = $this->getDoctrine()->getManager();
      $programs = $em->getRepository('AppBundle:Program')->findBy(array('category' => $id));

      $screenshot_repository = $this->get('screenshotrepository');

      $retArray = $this->receiveCategoryPrograms($request, $programs, $screenshot_repository);

      $retArray['CatrobatInformation'] = array(
      'BaseUrl' => ($request->isSecure() ? 'https://' : 'http://').$request->getHttpHost().'/',
      'TotalProjects' => count($programs),
      'ProjectsExtension' => '.catrobat',
    );

      return JsonResponse::create($retArray);
  }

  /**
   * @Route("/pocket-game-jam", name="catrobat_web_game_jam")
   * @Method({"GET"})
   */
  public function gameJamAction()
  {
      return $this->get('templating')->renderResponse(':help:gamejam.html.twig');
  }

    /**
     * @param $page
     * @return array
     */
    private function generateBlocks($page) {
        $blocks = array();
        $blocks[0] = array('block' => 'one', 'images' => 1);
        $blocks[1] = array('block' => 'two', 'images' => 1);

        switch ($page) {
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
        return $blocks;
    }

    /**
     * @param $page
     * @return null|string
     */
    private function setExampleLink($page) {
        $example_link = null;
        switch ($page) {
            case 1:
                $example_link = $this->generateUrl('program', array('id' => 3983, 'flavor' => 'pocketcode'));
                break;
            case 2:
                $example_link = $this->generateUrl('program', array('id' => 3984, 'flavor' => 'pocketcode'));
                break;
            case 3:
                $example_link = $this->generateUrl('program', array('id' => 3985, 'flavor' => 'pocketcode'));
                break;
            case 4:
                $example_link = $this->generateUrl('program', array('id' => 3986, 'flavor' => 'pocketcode'));
                break;
            case 5:
                $example_link = $this->generateUrl('program', array('id' => 3987, 'flavor' => 'pocketcode'));
                break;
            case 6:
                $example_link = $this->generateUrl('program', array('id' => 3988, 'flavor' => 'pocketcode'));
                break;
            case 7:
                $example_link = $this->generateUrl('program', array('id' => 3990, 'flavor' => 'pocketcode'));
                break;
            case 8:
                $example_link = $this->generateUrl('program', array('id' => 3991, 'flavor' => 'pocketcode'));
                break;
            case 9:
                $example_link = $this->generateUrl('program', array('id' => 3992, 'flavor' => 'pocketcode'));
                break;
            case 10:
                $example_link = $this->generateUrl('program', array('id' => 3979, 'flavor' => 'pocketcode'));
                break;
            case 11:
                $example_link = $this->generateUrl('program', array('id' => 3981, 'flavor' => 'pocketcode'));
                break;
            case 12:
                $example_link = $this->generateUrl('program', array('id' => 3982, 'flavor' => 'pocketcode'));
                break;
        }
        return $example_link;
    }

    /**
     * @param $categories
     * @return array
     */
    private function generateCategoryArray($categories) {
        $categories_twig = array();

        foreach ($categories as $category) {
            $categories_twig[] = array(
                'id' => $category->getId(),
                'alias' => $category->getAlias(),
            );
        }
        return $categories_twig;
    }

    /**
     * @param Request $request
     * @param $programs
     * @param $screenshot_repository
     * @return array
     */
    private function receiveCategoryPrograms(Request $request, $programs, $screenshot_repository) {
        $retArray = array(
            'CatrobatProjects' => array(),
        );

        foreach ($programs as $program) {
            $retArray['CatrobatProjects'][] = array(
                'ProjectId' => $program->getId(),
                'ProjectName' => $program->getName(),
                'Downloads' => $program->getDownloads(),
                'ScreenshotSmall' => $screenshot_repository->getThumbnailWebPath($program->getId()),
                'ProjectUrl' => ltrim($this->generateUrl('program', array(
                    'flavor' => $request->attributes->get('flavor'),
                    'id' => $program->getId(),
                )), '/'),
            );
        }
        return $retArray;
    }
}
