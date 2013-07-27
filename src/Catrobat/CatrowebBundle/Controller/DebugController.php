<?php

namespace Catrobat\CatrowebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Catrobat\CatrowebBundle\Helper\CatrobatFileExtractor;
use Catrobat\CatrowebBundle\Helper\ScreenshotRepository;
use Catrobat\CatrowebBundle\Helper\ProjectRepository;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Filesystem\Filesystem;

class DebugController extends Controller
{
  
    private $upload_dir = '/../web/uploads/';
    private $screenshot_dir = '/../web/screenshots/';
    private $thumbnail_dir = '/../web/screenshots/thumb/';
    
	/**
	 * @Route("/",name="debug_index") 
	 * @Method({"GET"})
	 */
	public function indexAction()
	{
		return $this->render('CatrowebBundle:Debug:index.html.twig');
	}

	/**
	 * @Route("/upload",name="debug_upload")) 
	 * @Method({"GET", "POST"})
	 */
	public function projectUploadAction(Request $request)
	{
		$form = $this->createFormBuilder(array())
		->add('name','text')
		->add('project','file')
		->getForm();
		
		if ($request->isMethod('POST')) {
			return $this->redirect($this->generateUrl('debug_index'));
		}
		
		return $this->render('CatrowebBundle:Debug:upload.html.twig',array('form' => $form->createView()));
	}

	/**
	 * @Route("/extract",name="debug_extract"))
	 */
	public function extractFile(Request $request)
	{
	  $file = new File($this->get('kernel')->getRootDir() . $this->upload_dir . "scaryghost.catrobat");
	  
	  $extractor = new CatrobatFileExtractor($this->get('kernel')->getRootDir() . $this->upload_dir . "extract/");
	  $screen_repo = new ScreenshotRepository($this->get('kernel')->getRootDir() . $this->screenshot_dir, $this->get('kernel')->getRootDir() . $this->thumbnail_dir);
	  $project_repo = new ProjectRepository($this->get('kernel')->getRootDir() . $this->upload_dir . "projects/");
	   
	  $message = "Done";
	  $message = $extractor->extract($file);
	  
	  $screen_repo->saveProjectAssets($message['dir']."screenshot.png",1);
	  $project_repo->saveProjectfile($file);
	   
	  $filesystem = new Filesystem();
	  $filesystem->remove($message['dir']);
	  
	  return $this->render('CatrowebBundle:Debug:debug.html.twig',array('message' => $message));
	}
	
	
}
