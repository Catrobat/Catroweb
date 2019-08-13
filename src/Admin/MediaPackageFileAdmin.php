<?php

namespace App\Admin;

use App\Catrobat\Services\MediaPackageFileRepository;
use App\Entity\MediaPackageCategory;
use App\Entity\MediaPackageFile;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


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
   * @var MediaPackageFileRepository
   */
  private $media_package_file_repository;

  /**
   * @var ParameterBagInterface
   */
  private $parameter_bag;

  /**
   * MediaPackageFileAdmin constructor.
   *
   * @param $code
   * @param $class
   * @param $baseControllerName
   * @param MediaPackageFileRepository $media_package_file_repository
   * @param ParameterBagInterface $parameter_bag
   */
  public function __construct($code, $class, $baseControllerName,
                              MediaPackageFileRepository $media_package_file_repository,
                              ParameterBagInterface $parameter_bag)
  {
    parent::__construct($code, $class, $baseControllerName);
    $this->media_package_file_repository = $media_package_file_repository;
    $this->parameter_bag = $parameter_bag;
  }

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
    $this->checkFlavor();
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
    $this->media_package_file_repository->save($file, $object->getId(), $object->getExtension());
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
    $this->checkFlavor();
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
    $this->media_package_file_repository->save($file, $object->getId(), $object->getExtension());
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
    $this->media_package_file_repository->remove($object->removed_id, $object->getExtension());
  }

  /**
   *
   */
  private function checkFlavor()
  {
    $flavor = $this->getForm()->get('flavor')->getData();
    $flavor_options =  $this->parameter_bag->get('themes');

    if (!in_array($flavor, $flavor_options)) {
      throw new NotFoundHttpException(
        '"' . $flavor . '"Flavor is unknown! Choose either ' . implode(",", $flavor_options)
      );
    }
  }
}
