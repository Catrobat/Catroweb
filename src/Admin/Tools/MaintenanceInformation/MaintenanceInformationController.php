<?php

namespace App\Admin\Tools\MaintenanceInformation;

use App\DB\Entity\MaintenanceInformation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\Routing\Annotation\Route;
/**
 * @phpstan-extends CRUDController<object>
 */
class MaintenanceInformationController extends CRUDController
{
  public function __construct(
    private readonly EntityManagerInterface $entityManager,
  ) {}

  public function sendMaintenanceInformation(): array
  {
    $maintenanceInformationRepository = $this->entityManager->getRepository(MaintenanceInformation::class);
    $maintenanceInformation = $maintenanceInformationRepository->findAll();
    $maintenanceInformationMessages = [];
    if (!empty($maintenanceInformation)) {
      foreach ($maintenanceInformation as $info) {
        if ($info->isActive() && !$info->isClosed()) {
          $parameters = [
            'maintenanceStart' => $info->getLtmMaintenanceStart(),
            'maintenanceEnd' => $info->getLtmMaintenanceEnd(),
            'additionalInfo' => $info->getLtmAdditionalInformation(),
            'code' => $info->getLtmCode(),
            'icon' => $info->getIcon(),
            'featureName' => $info->getInternalTitle(),
            'id' => $info->getId(),
          ];

            $maintenanceInformationMessages[] = $this->renderView('/components/maintenaceinformation.html.twig', $parameters);
        }
      }
    }

    return $maintenanceInformationMessages;
  }


    #[Route(path: '/maintenance/close/{viewId}', name: 'close_maintenance_view' , methods: ['POST'])]
    private function closeMaintenanceView(Request $request, int $viewId): JsonResponse
    {

        try {
            $maintenanceInformationRepository = $this->entityManager->getRepository(MaintenanceInformation::class);
            $maintenanceInformation = $maintenanceInformationRepository->find(10);
            if ($maintenanceInformation)
            {
                $maintenanceInformation->setClosed(true);
                $this->entityManager->flush();
            }
            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }


 #[Route(path: '/maintenance/reset-close-state', name: 'reset_close_state' , methods: ['POST'])]
 private function resetCloseState(): void
{
        $maintenanceInformationRepository = $this->entityManager->getRepository(MaintenanceInformation::class);
        $maintenanceInformationList = $maintenanceInformationRepository->findAll();
        foreach ($maintenanceInformationList as $maintenanceInformation) {
            $maintenanceInformation->setClosed(false);
        }
        $this->entityManager->flush();
    }
}



