<?php

namespace Catrobat\WebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Templating\EngineInterface;


class TutorialController extends Controller
{
  protected $templating;
  
  public function __construct(EngineInterface $templating)
  {
    $this->templating = $templating;
  }

  public function tutorialAction()
  {
    return $this->templating->renderResponse('CatrobatWebBundle:tutorials:tutorial.html.twig');
  }

  public function tutorialsAction()
  {
    return $this->templating->renderResponse('CatrobatWebBundle:tutorials:tutorials.html.twig');
  }

  public function stepByStepAction()
  {
    return $this->templating->renderResponse('CatrobatWebBundle:tutorials:stepByStep.html.twig');
  }

  public function starterProgramsAction()
  {
    return $this->templating->renderResponse('CatrobatWebBundle:tutorials:starterPrograms.html.twig');
  }
}
