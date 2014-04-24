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
 * @Route("/programs")
 */
class ProgramController
{
  protected $templating;
  protected $program_manager;
  
  public function __construct(EngineInterface $templating, ProgramManager $program_manager)
  {
    $this->templating = $templating;
    $this->program_manager = $program_manager;
  }
  
  public function indexAction()
  {
    $entities = $this->program_manager->findAll();
    return $this->templating->renderResponse('CatrobatWebBundle::index.html.twig');
  }

  public function showAction($id)
  {
    $entity = $this->program_manager->find($id);

    if (! $entity)
    {
      throw $this->createNotFoundException('Unable to find Project entity.');
    }
    return $this->templating->renderResponse('CatrobatWebBundle::detail.html.twig', array("entity" => $entity));
  }


}
