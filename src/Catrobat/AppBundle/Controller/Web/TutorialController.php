<?php

namespace Catrobat\AppBundle\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;


class TutorialController extends Controller
{
  /**
   * @Route("/tutorial", name="catrobat_web_tutorial")
   * @Method({"GET"})
   */
  public function tutorialAction()
  {
    return $this->get("templating")->renderResponse(':tutorials:tutorial.html.twig');
  }

  /**
   * @Route("/tutorial/tutorials", name="catrobat_web_tutorials")
   * @Method({"GET"})
   */
  public function tutorialsAction()
  {
    return $this->get("templating")->renderResponse(':tutorials:tutorials.html.twig');
  }

  /**
   * @Route("/tutorial/stepByStep", name="catrobat_web_stepByStep")
   * @Method({"GET"})
   */
  public function stepByStepAction()
  {
    return $this->get("templating")->renderResponse(':tutorials:stepByStep.html.twig');
  }

  /**
   * @Route("/tutorial/starterPrograms", name="catrobat_web_starter")
   * @Method({"GET"})
   */
  public function starterProgramsAction()
  {
    return $this->get("templating")->renderResponse(':tutorials:starterPrograms.html.twig');
  }
}
