<?php

namespace App\Admin;

use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use App\Entity\Program;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


/**
 * Class PendingApkRequestsAdmin
 * @package App\Admin
 */
class PendingApkRequestsAdmin extends AbstractAdmin
{

  /**
   * @var string
   */
  protected $baseRouteName = 'admin_catrobat_apk_pending_requests';

  /**
   * @var string
   */
  protected $baseRoutePattern = 'apk_pending_requests';

  /**
   * @var array
   */
  protected $datagridValues = [
    '_sort_by' => 'apk_request_time',
  ];


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
      $query->expr()->eq($query->getRootAliases()[0] . '.apk_status', ':apk_status')
    );
    $query->setParameter('apk_status', Program::APK_PENDING);

    return $query;
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
      ->add('name')
      ->add('user.username')
      ->add('apk_request_time');
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
      ->add('user', null, [
        'route' => [
          'name' => 'show',
        ],
      ])
      ->add('name')
      ->add('apk_request_time')
      ->add('thumbnail', 'string', ['template' => 'Admin/program_thumbnail_image_list.html.twig'])
      ->add('apk_status', ChoiceType::class, [
        'choices' => [
          Program::APK_NONE    => 'none',
          Program::APK_PENDING => 'pending',
          Program::APK_READY   => 'ready',
        ],])
      ->add('_action', 'actions', [
        'actions' => [
          'Reset'   => [
            'template' => 'Admin/CRUD/list__action_reset_status.html.twig',
          ],
          'Rebuild' => [
            'template' => 'Admin/CRUD/list__action_rebuild_apk.html.twig',
          ],
        ],
      ]);
  }


  /**
   * @param RouteCollection $collection
   *
   *
   */
  protected function configureRoutes(RouteCollection $collection)
  {
    $collection->clearExcept(['list']);
    $collection->add('resetStatus', $this->getRouterIdParameter() . '/resetStatus');
    $collection->add('rebuildApk', $this->getRouterIdParameter() . '/rebuildApk');
    $collection->add('deleteAllApk');
    $collection->add('rebuildAllApk');
    $collection->add('resetAllApk');
  }


  /**
   * @param $object Program
   *
   * @return string
   */
  public function getThumbnailImageUrl($object)
  {
    return '/' . $this->getConfigurationPool()->getContainer()->get('screenshotrepository')
      ->getThumbnailWebPath($object->getId());
  }
}
