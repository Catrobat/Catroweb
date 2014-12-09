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
    return $this->get("templating")->renderResponse(':help:hourOfCode.html.twig');
  }

    /**
   * @Route("/step-by-step", name="catrobat_web_stepByStep")
   * @Method({"GET"})
   */
  public function stepByStepAction()
  {
    return $this->get("templating")->renderResponse(':help:stepByStep.html.twig');
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
