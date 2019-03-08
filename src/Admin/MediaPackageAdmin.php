<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;


/**
 * Class MediaPackageAdmin
 * @package App\Admin
 */
class MediaPackageAdmin extends AbstractAdmin
{

  /**
   * @var string
   */
  protected $baseRouteName = 'adminmedia_package_package';

  /**
   * @var string
   */
  protected $baseRoutePattern = 'media_package';


  /**
   * @param FormMapper $formMapper
   *
   * Fields to be shown on create/edit forms
   */
  protected function configureFormFields(FormMapper $formMapper)
  {
    $formMapper
      ->add('name', TextType::class, ['label' => 'Name'])
      ->add('name_url', TextType::class, ['label' => 'Url']);
  }


  /**
   * @param DatagridMapper $datagridMapper
   *
   * Fields to be shown on filter forms
   */
  protected function configureDatagridFilters(DatagridMapper $datagridMapper)
  {
  }


  /**
   * @param ListMapper $listMapper
   *
   * Fields to be shown on lists
   */
  protected function configureListFields(ListMapper $listMapper)
  {
    $listMapper
      ->addIdentifier('name')
      ->add('name_url', null, ["label" => "Url"])
      ->add('_action', 'actions', [
        'actions' => [
          'edit'   => [],
          'delete' => [],
        ],
      ]);
  }
}
