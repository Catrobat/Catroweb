<?php

namespace Catrobat\CatrowebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Catrobat\CatrowebBundle\Entity\Project;
use Catrobat\CatrowebBundle\Form\ProjectType;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Catrobat\CatrowebBundle\Helper\CatrobatFileExtractor;
use Catrobat\CatrowebBundle\Helper\ScreenshotRepository;
use Catrobat\CatrowebBundle\Helper\ProjectRepository;
use Symfony\Component\Filesystem\Filesystem;
use Catrobat\CatrowebBundle\Helper\ProjectDirectoryValidator;

/**
 * Project controller.
 *
 * @Route("/projects")
 */
class ProjectController extends Controller
{
    /**
     * Lists all Project entities.
     *
     * @Route("/", name="project")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('CatrowebBundle:Project')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    
    /**
     * Lists all Project entities.
     *
     * @Route("/most-downloaded.{_format}", name="most-downloaded")
     * @Route("/most-downloaded", name="most-downloaded-default")
     * @Method("GET")
     * @Template(template="")
     */
    public function mostDownloadedAction(Request $request)
    {
      $limit = intval($request->query->get('limit',9));
      $offset = intval($request->query->get('offset',0));
      
      $em = $this->getDoctrine()->getManager();
    
      $entities = $em->getRepository('CatrowebBundle:Project')->findByOrderedByDownloads($limit,$offset);
    
      return array(
          'entities' => $entities
      );
    }

    /**
     * Lists all Project entities.
     * @Template()
     */
    public function newestAction(Request $request)
    {
    	$limit = intval($request->query->get('limit',9));
    	$offset = intval($request->query->get('offset',0));
    
    	$em = $this->getDoctrine()->getManager();
    
    	$entities = $em->getRepository('CatrowebBundle:Project')->findByOrderedByDownloads($limit,$offset);
    
    	return array(
    			'entities' => $entities
    	);
    }
    
    
    /**
     * Lists all Project entities.
     *
     * @Route("/most-viewed", name="most-viewed")
     * @Method("GET")
     * @Template()
     */
    public function mostViewedAction(Request $request, $limit = 3)
    {
      $limit = intval($request->query->get('limit',9));
      $offset = intval($request->query->get('offset',0));
      
//       $error = $this->get('validator')->validateValue($limit, new Range(array("min"=>0, "max"=>20)));
      
//       if (count($error) > 0)
//         throw new \Exception();
      
      $em = $this->getDoctrine()->getManager();
    
      $entities = $em->getRepository('CatrowebBundle:Project')->findByOrderedByDate($limit,$offset);
    
      foreach ($entities as $entity)
      {
        $entity->setThumbnail($this->get('screenshotrepository')->getThumbnailWebPath($entity->getId()));
      }
      return array(
          'entities' => $entities
      );
    }
    
    /**
     * Creates a new Project entity.
     *
     * @Route("/", name="project_create")
     * @Method("POST")
     * @Template("CatrowebBundle:Project:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity  = new Project();
        $form = $this->createForm(new ProjectType(), $entity);
        $form->submit($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('project_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to create a new Project entity.
     *
     * @Route("/new", name="project_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Project();
        $form   = $this->createForm(new ProjectType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Project entity.
     *
     * @Route("/{id}", name="project_show", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CatrowebBundle:Project')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Project entity.
     *
     * @Route("/{id}/edit", name="project_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CatrowebBundle:Project')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }

        $editForm = $this->createForm(new ProjectType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Project entity.
     *
     * @Route("/{id}", name="project_update", requirements={"id" = "\d+"})
     * @Method("PUT")
     * @Template("CatrowebBundle:Project:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CatrowebBundle:Project')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new ProjectType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('project_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Project entity.
     *
     * @Route("/{id}", name="project_delete", requirements={"id" = "\d+"})
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('CatrowebBundle:Project')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Project entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('project'));
    }

    /**
     * Creates a form to delete a Project entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
    
    /**
     * @Template
     * @Route("/upload", name="project_upload_form")
     * @Method("GET")
	 */
    public function uploadFormAction()
    {
      if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
        throw new AccessDeniedException();
      }
      
      $upload_form = $this->container->get('form.factory')->createNamedBuilder('','form') 
      ->add('xfile','file', array('label' => "File to upload: "))
      ->getForm();
      return array(
      	'upload_form' => $upload_form->createView()
      );
    }

    /**
     * @Template
     * @Route("/upload", name="project_upload")
     * @Method("POST")
     */
    public function uploadAction(Request $request)
    {
      if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
        throw new AccessDeniedException();
      }
    
      $file = $request->files->get('xfile');
      
      $extractor = $this->get('extractor');
      $screen_repo = $this->get('screenshotrepository');
      $project_repo = $this->get('projectrepository'); 
      $validator = new ProjectDirectoryValidator();
      
      $extract_dir = $extractor->extract($file);
       
      $info = $validator->getProjectInfo($extract_dir);

      $project = new Project();
      $project->setName($info['name']);
      $project->setDescription($info['description']);
      $project->setFilename($file->getFilename());
      $project->setThumbnail("");
      $project->setScreenshot("");
      $project->setUser($this->getUser());
      

      $em = $this->getDoctrine()->getManager();
      $em->persist($project);
      $em->flush();

      
      $screen_repo->saveProjectAssets($info['screenshot'], $project->getId());

    //  $filesystem = new Filesystem();
    //  $filesystem->remove($extract_dir);
      
      return array('mfile' => $file);
    }
    
}
