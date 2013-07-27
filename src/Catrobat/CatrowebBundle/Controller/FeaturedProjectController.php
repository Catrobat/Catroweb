<?php

namespace Catrobat\CatrowebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Catrobat\CatrowebBundle\Entity\FeaturedProject;
use Catrobat\CatrowebBundle\Form\FeaturedProjectType;

/**
 * FeaturedProject controller.
 *
 * @Route("/featured")
 */
class FeaturedProjectController extends Controller
{

    /**
     * Lists all FeaturedProject entities.
     *
     * @Route("/", name="projects_featured")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('CatrowebBundle:FeaturedProject')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new FeaturedProject entity.
     *
     * @Route("/", name="_create")
     * @Method("POST")
     * @Template("CatrowebBundle:FeaturedProject:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity  = new FeaturedProject();
        $form = $this->createForm(new FeaturedProjectType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to create a new FeaturedProject entity.
     *
     * @Route("/new", name="_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new FeaturedProject();
        $form   = $this->createForm(new FeaturedProjectType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a FeaturedProject entity.
     *
     * @Route("/{id}", name="_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CatrowebBundle:FeaturedProject')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find FeaturedProject entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing FeaturedProject entity.
     *
     * @Route("/{id}/edit", name="_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CatrowebBundle:FeaturedProject')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find FeaturedProject entity.');
        }

        $editForm = $this->createForm(new FeaturedProjectType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing FeaturedProject entity.
     *
     * @Route("/{id}", name="_update")
     * @Method("PUT")
     * @Template("CatrowebBundle:FeaturedProject:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CatrowebBundle:FeaturedProject')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find FeaturedProject entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new FeaturedProjectType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a FeaturedProject entity.
     *
     * @Route("/{id}", name="_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('CatrowebBundle:FeaturedProject')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find FeaturedProject entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl(''));
    }

    /**
     * Creates a form to delete a FeaturedProject entity by id.
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
}
