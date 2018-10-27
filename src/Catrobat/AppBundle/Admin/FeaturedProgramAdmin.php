<?php

namespace Catrobat\AppBundle\Admin;

use Catrobat\AppBundle\Entity\Program;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Catrobat\AppBundle\Forms\FeaturedImageConstraint;
use Sonata\CoreBundle\Model\Metadata;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FeaturedProgramAdmin extends AbstractAdmin
{
  protected $baseRouteName = 'adminfeatured_program';
  protected $baseRoutePattern = 'featured_program';

  public function createQuery($context = 'list')
  {
    /**
     * @var $query QueryBuilder
     */
    $query = parent::createQuery();
    $query->andWhere(
      $query->expr()->isNotNull($query->getRootAlias() . '.program')
    );

    return $query;
  }

  // Fields to be shown on create/edit forms
  protected function configureFormFields(FormMapper $formMapper)
  {
    $file_options = [
      'required'    => ($this->getSubject()->getId() === null),
      'constraints' => [
        new FeaturedImageConstraint(),
      ],];

    $id_value = '';

    if ($this->getSubject()->getId() != null)
    {
      $file_options['help'] = '<img src="../' . $this->getFeaturedImageUrl($this->getSubject()) . '">';
      $id_value = $this->getSubject()->getProgram()->getId();
    }

    $formMapper
      ->add('file', FileType::class, $file_options)
      ->add('program_id', TextType::class, ['mapped' => false, 'data' => $id_value])
      ->add('flavor')
      ->add('priority')
      ->add('for_ios', null, ['label' => 'iOS only', 'required' => false, 'help' => 'Toggle for iOS featured programs api call.'])
      ->add('active', null, ['required' => false]);
  }

  // Fields to be shown on filter forms
  protected function configureDatagridFilters(DatagridMapper $datagridMapper)
  {
    $datagridMapper
      ->add('program.name');
  }

  // Fields to be shown on lists
  protected function configureListFields(ListMapper $listMapper)
  {
    $listMapper
      ->addIdentifier('id')
      ->add('Featured Image', 'string', ['template' => 'Admin/featured_image.html.twig'])
      ->add('program', EntityType::class, [
        'class'      => Program::class,
        'route'      => ['name' => 'show'],
        'admin_code' => 'catrowebadmin.block.programs.all',
      ])
      ->add('flavor', 'string')
      ->add('priority', 'integer')
      ->add('for_ios', null, ['label' => 'iOS only'])
      ->add('active', null)
      ->add('_action', 'actions', [
        'actions' => [
          'edit'   => [],
          'delete' => [],
        ],
      ]);
  }

  public function getFeaturedImageUrl($object)
  {
    return '../../' . $this->getConfigurationPool()->getContainer()->get('featuredimagerepository')->getWebPath($object->getId(), $object->getImageType());
  }

  public function getObjectMetadata($object)
  {
    return new Metadata($object->getProgram()->getName(), $object->getProgram()->getDescription(), $this->getFeaturedImageUrl($object));
  }

  public function preUpdate($object)
  {
    $object->old_image_type = $object->getImageType();
    $object->setImageType(null);
    $this->checkProgramID($object);

  }

  public function prePersist($object)
  {
    $this->checkProgramID($object);
  }

  private function checkProgramID($object)
  {
    /*
    * @var $program Program
    */

    $id = $this->getForm()->get('program_id')->getData();

    $program_manager = $this->getConfigurationPool()->getContainer()->get('doctrine')->getManager()->getRepository('\Catrobat\AppBundle\Entity\Program');
    $program = $program_manager->find($id);

    if ($program)
    {
      $object->setProgram($program);
    }
    else
    {
      throw new NotFoundHttpException(sprintf('Unable to find program with id : %s', $id));
    }
  }
}
