<?php

namespace Catrobat\AppBundle\Admin;

use Catrobat\AppBundle\Entity\ProgramManager;
use Catrobat\AppBundle\Services\ExtractedFileRepository;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Catrobat\AppBundle\Entity\User;
use Sonata\AdminBundle\Route\RouteCollection;

class ApproveProgramsAdmin extends Admin
{
    protected $baseRouteName = 'admin_approve_programs';
    protected $baseRoutePattern = 'approve';
    
    private $extractedProgram = null;

    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);
        $query->andWhere(
            $query->expr()->eq($query->getRootAlias().'.approved', $query->expr()->literal(false))
        );
        $query->andWhere(
            $query->expr()->eq($query->getRootAlias().'.visible', $query->expr()->literal(true))
        );

        return $query;
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        // Here we set the fields of the ShowMapper variable, $showMapper (but this can be called anything)
      $showMapper

          /*
           * The default option is to just display the value as text (for boolean this will be 1 or 0)
           */
          ->add('Thumbnail', null, array('template' => ':Admin:program_thumbnail_image.html.twig'))
          ->add('id')
          ->add('Name')
          ->add('Description')
          ->add('version')
          ->add('user', 'entity', array('class' => 'Catrobat\AppBundle\Entity\User'))
          ->add('upload_ip')
          ->add('visible', 'boolean')
          ->add('Images', null, array('template' => ':Admin:program_containing_image.html.twig'))
          ->add('Sounds', null, array('template' => ':Admin:program_containing_sound.html.twig'))
          ->add('Strings', null, array('template' => ':Admin:program_containing_strings.html.twig'))
          ->add('Objects', null, array('template' => ':Admin:program_containing_code_objects.html.twig'))
          ->add('', null, array('template' => ':Admin:program_approve_action.html.twig'))
      ;
    }

    public function preUpdate($program)
    {
        $old_program = $this->getModelManager()->getEntityManager($this->getClass())->getUnitOfWork()->getOriginalEntityData($program);

        if ($old_program['approved'] == false && $program->getApproved() == true) {
            $program->setApprovedByUser($this->getConfigurationPool()->getContainer()->get('security.context')->getToken()->getUser());
            $this->getModelManager()->update($program);
        } elseif ($old_program['approved'] == true && $program->getApproved() == false) {
            $program->setApprovedByUser(null);
            $this->getModelManager()->update($program);
        }
    }

    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('name', 'text', array('label' => 'Program name'))
            ->add('user', 'entity', array('class' => 'Catrobat\AppBundle\Entity\User'))
        ;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
            ->add('user.username')
        ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add('user')
            ->add('name')
            ->add('description')
            ->add('visible', 'boolean', array('editable' => true))
            ->add('approved', 'boolean', array('editable' => true))
            ->add('_action', 'actions', array('actions' => array('show' => array())))
        ;
    }

    public function getThumbnailImageUrl($object)
    {
        return '/'.$this->getConfigurationPool()->getContainer()->get('screenshotrepository')->getThumbnailWebPath($object->getId());
    }

    public function getContainingImageUrls($object)
    {

      /* @var $extractedFileRepository ExtractedFileRepository */
      /* @var $progManager ProgramManager */

      if ($this->extractedProgram == null) {
          $extractedFileRepository = $this->getConfigurationPool()->getContainer()->get('extractedfilerepository');
          $progManager = $this->getConfigurationPool()->getContainer()->get('programmanager');
          $this->extractedProgram = $extractedFileRepository->loadProgramExtractedFile($progManager->find($object->getId()));
      }

        return $this->extractedProgram->getContainingImagePaths();
    }

    public function getContainingSoundUrls($object)
    {
        /* @var $extractedFileRepository ExtractedFileRepository */
    /* @var $progManager ProgramManager */

    if ($this->extractedProgram == null) {
        $extractedFileRepository = $this->getConfigurationPool()->getContainer()->get('extractedfilerepository');
        $progManager = $this->getConfigurationPool()->getContainer()->get('programmanager');
        $this->extractedProgram = $extractedFileRepository->loadProgramExtractedFile($progManager->find($object->getId()));
    }

        return $this->extractedProgram->getContainingSoundPaths();
    }

    public function getContainingStrings($object)
    {
        if ($this->extractedProgram == null) {
            $extractedFileRepository = $this->getConfigurationPool()->getContainer()->get('extractedfilerepository');
            $progManager = $this->getConfigurationPool()->getContainer()->get('programmanager');
            $this->extractedProgram = $extractedFileRepository->loadProgramExtractedFile($progManager->find($object->getId()));
        }

        return $this->extractedProgram->getContainingStrings();
    }

    public function getContainingCodeObjects($object){
        if ($this->extractedProgram == null) {
            $extractedFileRepository = $this->getConfigurationPool()->getContainer()->get('extractedfilerepository');
            $progManager = $this->getConfigurationPool()->getContainer()->get('programmanager');
            $this->extractedProgram = $extractedFileRepository->loadProgramExtractedFile($progManager->find($object->getId()));
        }

        if ($this->extractedProgram->hasScenes()) {
            return array();
        } else {
            return $this->extractedProgram->getContainingCodeObjects();
        }
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create')->remove('delete')->remove('edit');
        $collection
            ->add('approve', $this->getRouterIdParameter().'/approve')
            ->add('invisible', $this->getRouterIdParameter().'/invisible')
            ->add('skip', $this->getRouterIdParameter().'/skip')
      ;
    }
}
