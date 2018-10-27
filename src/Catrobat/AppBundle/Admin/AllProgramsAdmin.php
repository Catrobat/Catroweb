<?php

namespace Catrobat\AppBundle\Admin;

use Catrobat\AppBundle\Entity\Program;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Catrobat\AppBundle\Entity\User;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\CoreBundle\Model\Metadata;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class AllProgramsAdmin extends AbstractAdmin
{
  protected $baseRouteName = 'admin_catrobat_adminbundle_allprogramsadmin';
  protected $baseRoutePattern = 'all_programs';

  protected $datagridValues = [
    '_sort_by'    => 'id',
    '_sort_order' => 'DESC',
  ];

  // Fields to be shown on create/edit forms
  protected function configureFormFields(FormMapper $formMapper)
  {
    $formMapper
      ->add('name', TextType::class, ['label' => 'Program name'])
      ->add('description')
      ->add('user', EntityType::class, ['class' => User::class])
      ->add('downloads')
      ->add('views')
      ->add('flavor')
      ->add('visible', null, ['required' => false])
      ->add('approved', null, ['required' => false]);
  }

  // Fields to be shown on filter forms
  protected function configureDatagridFilters(DatagridMapper $datagridMapper)
  {
    $datagridMapper
      ->add('id')
      ->add('name')
      ->add('downloads')
      ->add('user.username');
  }

  public function preUpdate($program)
  {
    /**
     * @var $program Program
     */
    $old_program = $this->getModelManager()->getEntityManager($this->getClass())->getUnitOfWork()->getOriginalEntityData($program);

    if ($old_program['approved'] == false && $program->getApproved() == true)
    {
      $program->setApprovedByUser($this->getConfigurationPool()->getContainer()->get('security.token_storage')->getToken()->getUser());
      $this->getModelManager()->update($program);
    }
    elseif ($old_program['approved'] == true && $program->getApproved() == false)
    {
      $program->setApprovedByUser(null);
      $this->getModelManager()->update($program);
    }
  }

  // Fields to be shown on lists
  protected function configureListFields(ListMapper $listMapper)
  {
    $listMapper
      ->addIdentifier('id')
      ->add('user')
      ->add('name')
      ->add('description')
      ->add('flavor', 'string', ['editable' => true])
      ->add('views')
      ->add('downloads')
      ->add('thumbnail', 'string', ['template' => 'Admin/program_thumbnail_image_list.html.twig'])
      ->add('approved', null, ['editable' => true])
      ->add('visible', null, ['editable' => true])
      ->add('_action', 'actions', ['actions' => [
        'show' => ['template' => 'CRUD/list__action_show_program_details.html.twig'],
        'edit' => [],
      ]]);
  }

  public function getObjectMetadata($object)
  {
    /**
     * @var $object object
     */
    return new Metadata($object->getName(), $object->getDescription(), $this->getThumbnailImageUrl($object));
  }

  protected function configureRoutes(RouteCollection $collection)
  {
    $collection->remove('create')->remove('delete')->remove('export');
  }

  public function getThumbnailImageUrl($object)
  {
    /**
     * @var $object object
     */
    return '/' . $this->getConfigurationPool()->getContainer()->get('screenshotrepository')->getThumbnailWebPath($object->getId());
  }
}
