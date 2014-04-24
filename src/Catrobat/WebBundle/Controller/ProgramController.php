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
    
    return $this->templating->renderResponse('CatrobatWebBundle:Default:index.html.twig');
  }

  public function mostDownloadedAction(Request $request)
  {
    $limit = intval($request->query->get('limit', 9));
    $offset = intval($request->query->get('offset', 0));
    
    $entities = $this->program_manager->findByOrderedByDownloads($limit, $offset);
    
    return $this->templating->renderResponse('CatrobatWebBundle:Program:mostDownloaded.html.twig', array("entities" => $entities));
  }

  public function newestAction(Request $request)
  {
    $limit = intval($request->query->get('limit', 9));
    $offset = intval($request->query->get('offset', 0));
    
    $entities = $this->program_manager->findByOrderedByDate($limit, $offset);
    
    return $this->templating->renderResponse('CatrobatWebBundle:Program:newest.html.twig', array("entities" => $entities));
  }

  public function mostViewedAction(Request $request, $limit = 3)
  {
    $limit = intval($request->query->get('limit', 9));
    $offset = intval($request->query->get('offset', 0));
    
    $entities = $this->program_manager->findByOrderedByDate($limit, $offset);
    
    return $this->templating->renderResponse('CatrobatWebBundle:Program:mostViewed.html.twig', array("entities" => $entities));
  }

  public function showAction($id)
  {
    $entity = $this->program_manager->find($id);
    
    if (! $entity)
    {
      throw $this->createNotFoundException('Unable to find Program entity.');
    }
    return $this->templating->renderResponse('CatrobatWebBundle:Program:show.html.twig', array("entity" => $entity));
  }

}
