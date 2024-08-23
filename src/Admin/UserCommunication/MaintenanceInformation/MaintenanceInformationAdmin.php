<?php

declare(strict_types=1);

namespace App\Admin\UserCommunication\MaintenanceInformation;

use App\DB\Entity\MaintenanceInformation;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\TemplateType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * @phpstan-extends AbstractAdmin<\stdClass>
 */
class MaintenanceInformationAdmin extends AbstractAdmin
{
  #[\Override]
  protected function generateBaseRouteName(bool $isChildAdmin = false): string
  {
    return 'admin_maintenance_info';
  }

  #[\Override]
  protected function generateBaseRoutePattern(bool $isChildAdmin = false): string
  {
    return 'user-communication/maintenance-info';
  }

  public function __construct(
    private readonly EntityManagerInterface $entityManager,
  ) {
  }

  // Access the database to retrieve a list of entities
  private function updateLtmCodes(): void
  {
    $entities = $this->entityManager->getRepository(MaintenanceInformation::class)->findAll();

    foreach ($entities as $entity) {
      if (null === $entity->getLtmCode()) {
        $entity->setLtmCode('maintenanceinformations.maintenance_information.feature_'.$entity->getId());
      }
    }

    $this->entityManager->flush();
  }

  #[\Override]
  protected function configureFormFields(FormMapper $form): void
  {
    $form
      ->add('internalTitle', null, ['label' => 'Feature Name'])
      ->add('AvailableIcons', TemplateType::class, [
        'template' => 'Admin/UserCommunication/MaintenanceInformation/ltm_parameters.html.twig',
        'label' => '',
      ])
      ->add('icon', ChoiceType::class, [
        'label' => 'Choice',
        'choices' => [
          'Error' => 'error',
          'Warning' => 'warning',
          'Notifications' => 'notifications',
          'Outlined Error' => 'error_outline',
          'Construction and Maintenance' => 'build',
          'Adjusting Settings' => 'settings',
          'Fine-Tuning Equipment' => 'tune',
          'Renewal and Updating' => 'autorenew',
          'Data Caching' => 'cached',
          'Bug Reporting and Fixing' => 'bug_report',
          'Tracking Changes' => 'track_changes',
          'Security Key Management' => 'vpn_key',
          'Maintenance Schedule' => 'timeline',
          'Information' => 'info',
          'Outlined Information' => 'info_outline',
          'Announcement' => 'announcement',
        ],
      ])
      ->add('active', ChoiceType::class, [
        'choices' => [
          'Active' => true,
          'Inactive' => false,
        ],
        'expanded' => true, // Display as radio buttons
        'label' => 'Status',
      ])
      ->add('ltm_maintenanceStart', null, [
        'label' => 'Maintenance Start',
        'required' => false,
        'empty_data' => null,
      ])
      ->add('ltm_maintenanceEnd', null, [
        'label' => 'Maintenance End',
        'required' => false,
        'empty_data' => null,
      ])
      ->add('ltm_additionalInformation', TextType::class, [
        'label' => 'Additional Information',
        'required' => false,
      ])
    ;
    $this->updateLtmCodes();
  }

  #[\Override]
  protected function configureDatagridFilters(DatagridMapper $filter): void
  {
    $filter
      ->add('id')
      ->add('internalTitle')
    ;
  }

  #[\Override]
  protected function configureListFields(ListMapper $list): void
  {
    $list
      ->add('internalTitle', null, ['label' => 'Feature Name'])
      ->add('active', 'boolean', ['label' => 'Active'])
      ->add('ltm_code', null, ['label' => 'LTM Code'])
      ->add('ltm_maintenanceStart', null, ['label' => 'Maintenance Start'])
      ->add('ltm_maintenanceEnd', null, ['label' => 'Maintenance End'])
      ->add('ltm_additionalInformation', null, ['label' => 'Additional Information'])
      ->add('icon', 'string',
        [
          'template' => 'Admin/UserCommunication/MaintenanceInformation/icon.html.twig',
        ]
      )
      ->add(ListMapper::NAME_ACTIONS, null, [
        'actions' => [
          'edit' => [],
        ],
      ])
    ;
  }

  #[\Override]
  protected function configureRoutes(RouteCollectionInterface $collection): void
  {
    $collection
      ->remove('acl')
    ;
  }
}
