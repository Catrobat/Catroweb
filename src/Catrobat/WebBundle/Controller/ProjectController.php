<?php

namespace Catrobat\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Catrobat\CoreBundle\Entity\Project;
use Catrobat\WebBundle\Form\ProjectType;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Templating\EngineInterface;
use Catrobat\CoreBundle\Model\ProjectManager;

/**
 * Project controller.
 *
 * @Route("/projects")
 */
class ProjectController
{
  protected $templating;
  protected $project_manager;
  
  public function __construct(EngineInterface $templating, ProjectManager $project_manager)
  {
    $this->templating = $templating;
    $this->project_manager = $project_manager;
  }
  
  public function indexAction()
  {
    $entities = $this->project_manager->findAll();
    
    return $this->templating->renderResponse('CatrobatWebBundle:Default:index.html.twig');
  }

  public function mostDownloadedAction(Request $request)
  {
    $limit = intval($request->query->get('limit', 9));
    $offset = intval($request->query->get('offset', 0));
    
    $entities = $this->project_manager->findByOrderedByDownloads($limit, $offset);
    
    return $this->templating->renderResponse('CatrobatWebBundle:Project:mostDownloaded.html.twig', array("entities" => $entities));
  }

  public function newestAction(Request $request)
  {
    $limit = intval($request->query->get('limit', 9));
    $offset = intval($request->query->get('offset', 0));
    
    $entities = $this->project_manager->findByOrderedByDate($limit, $offset);
    
    return $this->templating->renderResponse('CatrobatWebBundle:Project:newest.html.twig', array("entities" => $entities));
  }

  public function mostViewedAction(Request $request, $limit = 3)
  {
    $limit = intval($request->query->get('limit', 9));
    $offset = intval($request->query->get('offset', 0));
    
    $entities = $this->project_manager->findByOrderedByDate($limit, $offset);
    
    return $this->templating->renderResponse('CatrobatWebBundle:Project:mostViewed.html.twig', array("entities" => $entities));
  }

  public function showAction($id)
  {
    $entity = $this->project_manager->find($id);
    
    if (! $entity)
    {
      throw $this->createNotFoundException('Unable to find Project entity.');
    }
    return $this->templating->renderResponse('CatrobatWebBundle:Project:show.html.twig', array("entity" => $entity));
  }

}
