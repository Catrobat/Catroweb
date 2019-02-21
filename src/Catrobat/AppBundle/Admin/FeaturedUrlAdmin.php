<?php

namespace Catrobat\AppBundle\Admin;

use Catrobat\AppBundle\Entity\FeaturedProgram;
use Doctrine\DBAL\Types\IntegerType;
use Doctrine\DBAL\Types\StringType;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Catrobat\AppBundle\Forms\FeaturedImageConstraint;
use Sonata\BlockBundle\Meta\Metadata;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;


/**
 * Class FeaturedUrlAdmin
 * @package Catrobat\AppBundle\Admin
 */
class FeaturedUrlAdmin extends AbstractAdmin
{
  /**
   * @var string
   */
  protected $baseRouteName = 'admin_featured_url';

  /**
   * @var string
   */
  protected $baseRoutePattern = 'featured_url';


  /**
   * @param string $context
   *
   * @return QueryBuilder|\Sonata\AdminBundle\Datagrid\ProxyQueryInterface
   */
  public function createQuery($context = 'list')
  {
    /**
     * @var $query QueryBuilder
     */
    $query = parent::createQuery();
    $query->andWhere(
      $query->expr()->isNull($query->getRootAliases()[0] . '.program')
    );

    return $query;
  }


  /**
   * @param FormMapper $formMapper
   *
   * Fields to be shown on create/edit forms
   */
  protected function configureFormFields(FormMapper $formMapper)
  {
    $file_options = [
      'required'    => ($this->getSubject()->getId() === null),
      'constraints' => [
        new FeaturedImageConstraint(),
      ],];
    if ($this->getSubject()->getId() != null)
    {
      $file_options['help'] = '<img src="../' . $this->getFeaturedImageUrl($this->getSubject()) . '">';
    }

    $formMapper
      ->add('file', FileType::class, $file_options)
      ->add('url', UrlType::class)
      ->add('flavor')
      ->add('priority')
      ->add('for_ios', null, ['label' => 'iOS only', 'required' => false,
                              'help' => 'Toggle for iOS featured url api call.'])
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
      ->add('url');
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
      ->add('Featured Image', 'string', ['template' => 'Admin/featured_image.html.twig'])
      ->add('url', UrlType::class)
      ->add('flavor', StringType::class, ['editable' => true])
      ->add('priority', IntegerType::class, ['editable' => true])
      ->add('for_ios', null, ['label' => 'iOS only', 'editable' => true])
      ->add('active', null, ['editable' => true])
      ->add('_action', 'actions', [
        'actions' => [
          'edit'   => [],
          'delete' => [],
        ],
      ]);
  }


  /**
   * @param $object
   *
   * @return string
   */
  public function getFeaturedImageUrl($object)
  {
    /**
     * @var $object FeaturedProgram
     */

    return '../../' . $this->getConfigurationPool()->getContainer()->get('featuredimagerepository')
        ->getWebPath($object->getId(), $object->getImageType());
  }


  /**
   * @param $object
   *
   * @return Metadata
   */
  public function getObjectMetadata($object)
  {
    /**
     * @var $object FeaturedProgram
     */

    return new Metadata($object->getUrl(), '', $this->getFeaturedImageUrl($object));
  }


  /**
   * @param $image FeaturedProgram
   */
  public function preUpdate($image)
  {
    $image->old_image_type = $image->getImageType();
    $image->setImageType(null);
  }
}
