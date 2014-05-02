<?php

namespace Catrobat\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Catrobat\CoreBundle\Entity\Program;
use Catrobat\WebBundle\Form\ProgramType;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Templating\EngineInterface;
use Catrobat\CoreBundle\Model\ProgramManager;

/**
 * Program controller.
 *
 * @Route("/tutorial")
 */
class TutorialController
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
