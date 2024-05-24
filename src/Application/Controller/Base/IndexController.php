<?php

declare(strict_types=1);

namespace App\Application\Controller\Base;

use App\DB\Entity\Flavor;
use App\DB\Entity\MaintenanceInformation;
use App\DB\Entity\Project\Special\FeaturedProgram;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\Project\Special\FeaturedRepository;
use App\Storage\ImageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class IndexController extends AbstractController
{
  public function __construct(protected ImageRepository $image_repository, protected FeaturedRepository $featured_repository, private readonly EntityManagerInterface $entityManager)
  {
  }

  #[Route(path: '/', name: 'index', methods: ['GET'])]
  public function index(Request $request): Response
  {
    $flavor = $request->attributes->get('flavor');
    /** @var User|null $user */
    $user = $this->getUser();
    $maintenanceInformation = $this->sendMaintenanceInformation($request);

    return $this->render('Index/index.html.twig', [
      'featured' => $this->getFeaturedSliderData($flavor),
      'is_first_oauth_login' => null !== $user && $user->isOauthUser() && !$user->isOauthPasswordCreated(),
      'maintenanceInformation' => $maintenanceInformation,
    ]);
  }

  protected function getFeaturedSliderData(string $flavor): array
  {
    if (Flavor::PHIROCODE === $flavor) {
      $featured_items = $this->featured_repository->getFeaturedItems(Flavor::POCKETCODE, 10, 0);
    } else {
      $featured_items = $this->featured_repository->getFeaturedItems($flavor, 10, 0);
    }

    $featuredData = [];
    /** @var FeaturedProgram $item */
    foreach ($featured_items as $item) {
      $info = [];
      if (null !== $item->getProgram()) {
        if ($flavor) {
          $info['url'] = $this->generateUrl('program',
            ['id' => $item->getProgram()->getId(), 'theme' => $flavor]);
        } else {
          $info['url'] = $this->generateUrl('program', ['id' => $item->getProgram()->getId()]);
        }
      } else {
        $info['url'] = $item->getUrl();
      }
      $info['image'] = $this->image_repository->getWebPath($item->getId(), $item->getImageType(), true);

      $featuredData[] = $info;
    }

    return $featuredData;
  }

  public function sendMaintenanceInformation(Request $request): array
  {
    $maintenanceInformationRepository = $this->entityManager->getRepository(MaintenanceInformation::class);
    $maintenanceInformation = $maintenanceInformationRepository->findAll();
    $maintenanceInformationMessages = [];
    if (!empty($maintenanceInformation)) {
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
          $maintenanceInformationMessages[] = $this->renderView('/components/maintenaceinformation.html.twig', $parameters);
        }
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
    } catch (\Exception $e) {
      return new JsonResponse(['success' => false, 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }
}
