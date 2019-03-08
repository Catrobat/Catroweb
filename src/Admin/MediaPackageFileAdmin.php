<?php

namespace App\Admin;

use App\Entity\MediaPackageCategory;
use App\Entity\MediaPackageFile;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\File;


/**
 * Class MediaPackageFileAdmin
 * @package App\Admin
 */
class MediaPackageFileAdmin extends AbstractAdmin
{

  /**
   * @var string
   */
  protected $baseRouteName = 'adminmedia_package_file';

  /**
   * @var string
   */
  protected $baseRoutePattern = 'media_package_file';


  /**
   * @param FormMapper $formMapper
   *
   * Fields to be shown on create/edit forms
   */
  protected function configureFormFields(FormMapper $formMapper)
  {
    $file_options = [
      'required' => ($this->getSubject()->getId() === null),];

    $formMapper
      ->add('name', TextType::class, ['label' => 'Name'])
      ->add('file', FileType::class, $file_options)
      ->add('category', EntityType::class, [
        'class'    => MediaPackageCategory::class,
        'required' => true])
      ->add('flavor', TextType::class, ['required' => false])
      ->add('author', TextType::class, ['label' => 'Author', 'required' => false])
      ->add('active', null, ['required' => false]);
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
      ->add("name")
      ->add('file', 'string', ['template' => 'Admin/mediapackage_file.html.twig'])
      ->add('category', EntityType::class, ['class' => MediaPackageCategory::class])
      ->add('author', null, ['editable' => true])
      ->add('flavor', null, ['editable' => true])
      ->add('downloads')
      ->add('active', null, ['editable' => true])
      ->add('_action', 'actions', [
        'actions' => [
          'edit'   => [],
          'delete' => [],
        ],
      ]);
  }


  /**
   * @param $object MediaPackageFile
   */
  public function prePersist($object)
  {
    /**
     * @var $file File
     */

    $file = $object->file;
    if ($file == null)
    {
      return;
    }
    $object->setExtension($file->guessExtension());
  }


  /**
   * @param $object MediaPackageFile
   *
   * @throws \ImagickException
   */
  public function postPersist($object)
  {
    /**
     * @var $file File
     */

    $file = $object->file;
    if ($file == null)
    {
      return;
    }
    $this->getConfigurationPool()->getContainer()->get('mediapackagefilerepository')
      ->save($file, $object->getId(), $object->getExtension());
  }


  /**
   * @param $object MediaPackageFile
   */
  public function preUpdate($object)
  {
    /**
     * @var $file File
     */

    $object->old_extension = $object->getExtension();
    $object->setExtension(null);

    $file = $object->file;
    if ($file == null)
    {
      $object->setExtension($object->old_extension);

      return;
    }
    $object->setExtension($file->guessExtension());
  }


  /**
   * @param $object MediaPackageFile
   *
   * @throws \ImagickException
   */
  public function postUpdate($object)
  {
    $file = $object->file;
    if ($file == null)
    {
      return;
    }
    $this->getConfigurationPool()->getContainer()->get('mediapackagefilerepository')
      ->save($file, $object->getId(), $object->getExtension());
  }


  /**
   * @param $object MediaPackageFile
   */
  public function preRemove($object)
  {
    $object->removed_id = $object->getId();
  }


  /**
   * @param $object MediaPackageFile
   */
  public function postRemove($object)
  {
    $this->getConfigurationPool()->getContainer()->get('mediapackagefilerepository')
      ->remove($object->removed_id, $object->getExtension());
  }
}
