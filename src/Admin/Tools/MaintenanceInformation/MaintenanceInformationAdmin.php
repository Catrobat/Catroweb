<?php

namespace App\Admin\Tools\MaintenanceInformation;
use App\DB\Entity\MaintenanceInformation;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\TemplateType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\Form\Type\DatePickerType;
use Sonata\Form\Type\DateTimePickerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;


class MaintenanceInformationAdmin extends AbstractAdmin
{

    protected $baseRoutePattern = 'maintenanceinformation';
    protected $baseRouteName = 'maintenanceinformation';


    public function __construct(
        private readonly EntityManagerInterface $entityManager,

    ) {}

    // Access the database to retrieve a list of entities
    private function updateLtmCodes():void
    {
        $entities = $this->entityManager->getRepository(MaintenanceInformation::class)->findAll();

        foreach ($entities as $entity) {
            if (null === $entity->getLtmCode()) {
                $entity->setLtmCode('maintenanceinformations.maintenance_information.feature_' . $entity->getId());
            }
        }

        $this->entityManager->flush();
    }

    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
            ->add('internalTitle', null, ['label' => 'Feature Name'])
            ->add('AvailableIcons', TemplateType::class, [
                'template' => 'Admin/maintenance_information_ltmParameters.html.twig',
                'label' => '',
            ])
            ->add('icon', ChoiceType::class, [
                'label' => 'Choice',
                'choices' => [
                    'Error' => 'error',
                    'Warning' => 'warning',
                    'Notifications' => 'notifications',
                    'Outlined Error' => 'error_outline',
                    'Outlined Error 2' => 'error_outline_2',
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

    protected function configureDatagridFilters(DatagridMapper $datagridMapper) : void
    {
        $datagridMapper
            ->add('id')
            ->add('internalTitle');
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->add('internalTitle', null, ['label' => 'Feature Name'])
            ->add('active', 'boolean', ['label' => 'Active'])
            ->add('ltm_code', null, ['label' => 'LTM Code'])
            ->add('ltm_maintenanceStart', null, ['label' => 'Maintenance Start'])
            ->add('ltm_maintenanceEnd', null, ['label' => 'Maintenance End'])
            ->add('ltm_additionalInformation', null, ['label' => 'Additional Information'])
            ->add('icon', 'string',
                [
                    'template' => 'Admin/maintenance_information_icon.html.twig',
                ]
            )
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'edit' => [],
                ],
            ])
        ;
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection
            ->remove('acl');
    }
}