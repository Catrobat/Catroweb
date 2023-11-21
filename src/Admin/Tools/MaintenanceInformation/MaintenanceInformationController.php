<?php

namespace App\Admin\Tools\MaintenanceInformation;
use App\DB\Entity\MaintenanceInformation;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Controller\CRUDController;


class MaintenanceInformationController  extends CRUDController
{


    public function __construct(
        private readonly EntityManagerInterface $entityManager,

    ) {}
    public function sendSnackbarMaintenanceInformation(): array
    {
        $maintenanceInformationRepository = $this->entityManager->getRepository(MaintenanceInformation::class);
        $maintenanceInformation = $maintenanceInformationRepository->findAll();
        $snackbarMessages = [];
        if (!empty($maintenanceInformation))
        {
            foreach ($maintenanceInformation as $info)
            {
                if ($info->isActive())
                {
                    $parameters = [

                        'maintenanceStart'=> $info->getLtmMaintenanceStart(),
                        'maintenanceEnd'=> $info->getLtmMaintenanceEnd(),
                        'additionalInfo' => $info->getLtmAdditionalInformation(),
                        'code' => $info->getLtmCode(),
                        'icon' => $info->getIcon(),
                        'featureName' => $info->getInternalTitle()
                    ];

                    $snackbarMessages[] = $this->renderView('/Default/maintenaceinformation.html.twig', $parameters);
                }
               }
            }
        return $snackbarMessages;
        }
}