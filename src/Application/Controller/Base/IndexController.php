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
use Symfony\Contracts\Translation\TranslatorInterface;

class IndexController extends AbstractController
{
  public function __construct(
    private readonly EntityManagerInterface $entityManager,
    private readonly TranslatorInterface $translator,
  ) {
  }

  #[Route(path: '/', name: 'index', methods: ['GET'])]
  public function index(): Response
  {
    /** @var User|null $user */
    $user = $this->getUser();

    return $this->render('Index/IndexPage.html.twig', [
      'is_first_oauth_login' => null !== $user && $user->isOauthUser() && !$user->isOauthPasswordCreated(),
    ]);
  }

  #[Route(path: '/maintenance/list', name: 'maintenance_list', methods: ['GET'])]
  public function getMaintenanceInformation(Request $request): JsonResponse
  {
    $maintenanceInformationRepository = $this->entityManager->getRepository(MaintenanceInformation::class);
    $maintenanceInformation = $maintenanceInformationRepository->findAll();
    $result = [];
    foreach ($maintenanceInformation as $info) {
      if ($info->isActive() && !$request->getSession()->has((string) $info->getId())) {
        $result[] = [
          'id' => $info->getId(),
          'icon' => $info->getIcon(),
          'message' => $this->translator->trans((string) $info->getLtmCode(), [], 'catroweb'),
          'feature_name' => $info->getInternalTitle(),
          'maintenance_start' => $info->getLtmMaintenanceStart()?->format('Y-m-d'),
          'maintenance_end' => $info->getLtmMaintenanceEnd()?->format('Y-m-d'),
          'additional_info' => $info->getLtmAdditionalInformation(),
        ];
      }
    }

    return new JsonResponse($result);
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
