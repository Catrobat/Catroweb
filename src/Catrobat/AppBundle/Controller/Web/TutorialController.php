<?php

namespace Catrobat\AppBundle\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;


class TutorialController extends Controller
{
  /**
   * @Route("/help", name="catrobat_web_help")
   * @Method({"GET"})
   */
  public function helpAction()
  {
    return $this->get("templating")->renderResponse(':help:help.html.twig');
  }

  /**
   * @Route("/hour-of-code/{page}", name="catrobat_web_hour_of_code", defaults={"page" = 0}, requirements={"page":"\d+"})
   * @Route("/hourOfCode/{page}", name="catrobat_web_hourOfCode", defaults={"page" = 0}, requirements={"page":"\d+"})
   * @Method({"GET"})
   */
  public function hourOfCodeAction($page)
  {
    $paginator = $this->get('knp_paginator');
    $images = array();

    for ($i = 0; $i < 21; $i++) {
      $images[] = $i;
    }


    $pagination = $paginator->paginate(
      $images,
      $page, //current page
      1/*limit per page*/
    );

    $pagination->setTemplate(':help:paginationStart0.html.twig');

    if($page > 21) {
      throw $this->createNotFoundException('Unable to find step.');
    }

    $containers = 3;
    $class = "col-3";

    if($page == 4 || $page == 7 || $page == 8 || $page == 11 || $page == 14) {
      $containers = 4;
      $class = "col-4";
    }
    else if($page == 21) {
      $containers = 5;
      $class = "col-5";
    }

    return $this->get("templating")->renderResponse(':help:hourOfCode.html.twig', array("page" => $page, "containers" => $containers, "class" => $class, 'pagination' => $pagination));
  }

  /**
   * @Route("/step-by-step/{page}", name="catrobat_web_step_by_step", defaults={"page" = 1}, requirements={"page":"\d+"})
   * @Route("/stepByStep/{page}", name="catrobat_web_stepByStep", defaults={"page" = 1}, requirements={"page":"\d+"})
   * @Method({"GET"})
   */
  public function stepByStepAction($page)
  {
    $paginator = $this->get('knp_paginator');
    $steps = array();

    for ($i = 1; $i < 12; $i++) {
      $steps[] = $i;
    }

    $pagination = $paginator->paginate(
      $steps,
      $page, //current page
      1/*limit per page*/
    );

    $pagination->setTemplate(':help:paginationStart1.html.twig');

    return $this->get("templating")->renderResponse(':help:stepByStep.html.twig', array("page" => $page, 'pagination' => $pagination));
  }

  /**
   * @Route("/tutorials", name="catrobat_web_tutorials")
   * @Method({"GET"})
   */
  public function tutorialsAction()
  {
    return $this->get("templating")->renderResponse(':help:tutorials.html.twig');
  }

  /**
   * @Route("/starter-programs", name="catrobat_web_starter")
   * @Method({"GET"})
   */
  public function starterProgramsAction()
  {
    return $this->get("templating")->renderResponse(':help:starterPrograms.html.twig');
  }
}
