<?php

namespace Catrobat\AppBundle\Admin;

use Catrobat\AppBundle\Entity\Template;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Catrobat\AppBundle\Entity\User;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\CoreBundle\Model\Metadata;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;


/**
 * Class TemplateAdmin
 * @package Catrobat\AppBundle\Admin
 */
class TemplateAdmin extends AbstractAdmin
{

  /**
   * @var string
   */
  protected $baseRouteName = 'admin_catrobat_adminbundle_templateadmin';
  /**
   * @var string
   */
  protected $baseRoutePattern = 'template';

  /**
   * @var array
   */
  protected $datagridValues = [
    '_sort_by'    => 'id',
    '_sort_order' => 'DESC',
  ];


  /**
   * @param FormMapper $formMapper
   *
   * Fields to be shown on create/edit forms
   */
  protected function configureFormFields(FormMapper $formMapper)
  {
    $isNew = $this->getSubject()->getId() == null;
    $formMapper
      ->add('name', TextType::class, ['label' => 'Program name'])
      ->add('landscape_program_file', FileType::class, ['required' => false])
      ->add('portrait_program_file', FileType::class, ['required' => false])
      ->add('thumbnail', FileType::class, ['required' => $isNew])
      ->add('active', null, ['required' => false]);
  }


  /**
   * @param DatagridMapper $datagridMapper
   *
   * Fields to be shown on filter forms
   */
  protected function configureDatagridFilters(DatagridMapper $datagridMapper)
  {
    $datagridMapper
      ->add('id')
      ->add('name');
  }


  /**
   * @return array
   */
  public function getBatchActions()
  {
    $actions = parent::getBatchActions();
    unset($actions['delete']);

    return $actions;
  }


  /**
   * @param ListMapper $listMapper
   *
   * Fields to be shown on lists
   */
  protected function configureListFields(ListMapper $listMapper)
  {
    $listMapper
      ->addIdentifier('id')
      ->add('name')
      ->add('thumbnail', 'string', ['template' => 'Admin/program_thumbnail_image_list.html.twig'])
      ->add('active', 'boolean', ['editable' => true])
      ->add('_action', 'actions', ['actions' => [
        'edit'   => [],
        'delete' => [],
      ]]);
  }


  /**
   * @param RouteCollection $collection
   */
  protected function configureRoutes(RouteCollection $collection)
  {
    $collection->remove('export');
  }


  /**
   * @param $object Template
   *
   * @return string
   */
  public function getThumbnailImageUrl($object)
  {
    return '/' . $this->getConfigurationPool()->getContainer()->get('templatescreenshotrepository')
        ->getThumbnailWebPath($object->getId());
  }

}
