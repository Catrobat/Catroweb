<?php

declare(strict_types=1);

namespace App\Application\Controller\Base;

use App\DB\Entity\System\MaintenanceInformation;
use App\DB\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class IndexController extends AbstractController
{
  public function __construct(private readonly EntityManagerInterface $entityManager)
  {
  }

  #[Route(path: '/', name: 'index', methods: ['GET'])]
  public function index(Request $request): Response
  {
    /** @var User|null $user */
    $user = $this->getUser();
    $maintenanceInformation = $this->renderMaintenanceInformation($request);

    return $this->render('Index/IndexPage.html.twig', [
      'is_first_oauth_login' => null !== $user && $user->isOauthUser() && !$user->isOauthPasswordCreated(),
      'maintenanceInformation' => $maintenanceInformation,
    ]);
  }

  public function renderMaintenanceInformation(Request $request): array
  {
    $maintenanceInformationRepository = $this->entityManager->getRepository(MaintenanceInformation::class);
    $maintenanceInformation = $maintenanceInformationRepository->findAll();
    $maintenanceInformationMessages = [];
    foreach ($maintenanceInformation as $info) {
      if ($info->isActive() && !$request->getSession()->has((string) $info->getId())) {
        $parameters = [
          'maintenanceStart' => $info->getLtmMaintenanceStart(),
          'maintenanceEnd' => $info->getLtmMaintenanceEnd(),
          'additionalInfo' => $info->getLtmAdditionalInformation(),
          'code' => $info->getLtmCode(),
          'icon' => $info->getIcon(),
          'featureName' => $info->getInternalTitle(),
          'id' => $info->getId(),
        ];
        $maintenanceInformationMessages[] = $this->renderView('Index/MaintenanceInformation.html.twig', $parameters);
      }
    }

    return $maintenanceInformationMessages;
  }

  #[Route(path: '/maintenance/close/{viewId}', name: 'close_maintenance_view', methods: ['POST'])]
  public function closeMaintenanceView(Request $request, string $viewId): JsonResponse
  {
    try {
      $request->getSession()->set($viewId, true);

      return new JsonResponse(['success' => true]);
    } catch (\Exception $exception) {
      return new JsonResponse(['success' => false, 'error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }
}
