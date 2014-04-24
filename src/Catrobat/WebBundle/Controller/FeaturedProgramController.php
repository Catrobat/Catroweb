<?php

namespace Catrobat\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Catrobat\CoreBundle\Entity\FeaturedProgram;
use Catrobat\WebBundle\Form\FeaturedProgramType;

/**
 * FeaturedProgram controller.
 *
 * @Route("/featured")
 */
class FeaturedProgramController extends Controller
{

    /**
     * Lists all FeaturedProgram entities.
     *
     * @Route("/", name="programs_featured")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('CatrobatCoreBundle:FeaturedProgram')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new FeaturedProgram entity.
     *
     * @Route("/", name="_create")
     * @Method("POST")
     * @Template("CatrobatWebBundle:FeaturedProgram:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity  = new FeaturedProgram();
        $form = $this->createForm(new FeaturedProgramType(), $entity);
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
     * Displays a form to create a new FeaturedProgram entity.
     *
     * @Route("/new", name="_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new FeaturedProgram();
        $form   = $this->createForm(new FeaturedProgramType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a FeaturedProgram entity.
     *
     * @Route("/{id}", name="_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CatrobatCoreBundle:FeaturedProgram')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find FeaturedProgram entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing FeaturedProgram entity.
     *
     * @Route("/{id}/edit", name="_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CatrobatCoreBundle:FeaturedProgram')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find FeaturedProgram entity.');
        }

        $editForm = $this->createForm(new FeaturedProgramType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing FeaturedProgram entity.
     *
     * @Route("/{id}", name="_update")
     * @Method("PUT")
     * @Template("CatrobatWebBundle:FeaturedProgram:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CatrobatCoreBundle:FeaturedProgram')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find FeaturedProgram entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new FeaturedProgramType(), $entity);
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
     * Deletes a FeaturedProgram entity.
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
            $entity = $em->getRepository('CatrobatCoreBundle:FeaturedProgram')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find FeaturedProgram entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('programs_featured'));
    }

    /**
     * Creates a form to delete a FeaturedProgram entity by id.
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
