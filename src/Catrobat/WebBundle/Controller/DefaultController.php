<?php

namespace Catrobat\WebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Catrobat\CoreBundle\Model\ProgramManager;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class DefaultController extends Controller
{
    protected $templating;
    protected $program_manager;

    public function __construct(EngineInterface $templating, ProgramManager $program_manager)
    {
        $this->templating = $templating;
        $this->program_manager = $program_manager;
    }

    public function headerAction()
    {
        return $this->templating->renderResponse('CatrobatWebBundle:Default:header.html.twig');
    }

    public function footerAction()
    {
        return $this->templating->renderResponse('CatrobatWebBundle:Default:footer.html.twig');
    }

    public function indexAction()
    {
        return $this->templating->renderResponse('CatrobatWebBundle::index.html.twig');
    }

    public function programAction($id)
    {
        $entity = $this->program_manager->find($id);

        if (! $entity)
        {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }
        return $this->templating->renderResponse('CatrobatWebBundle::detail.html.twig', array("entity" => $entity));
    }

    public function profileAction($id)
    {
        $entity = $id;

        return $this->templating->renderResponse('CatrobatWebBundle::profile.html.twig', array("entity" => $entity));
    }

    public function termsOfUseAction()
    {
        return $this->templating->renderResponse('CatrobatWebBundle::termsOfUse.html.twig');
    }

    public function licenseToPlayAction()
    {
        return $this->templating->renderResponse('CatrobatWebBundle::licenseToPlay.html.twig');
    }
}
