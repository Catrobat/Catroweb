<?php

declare(strict_types=1);

namespace App\Application\Controller\Studio;

use App\DB\Entity\User\User;
use App\Studio\StudioManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class StudioController extends AbstractController
{
  public function __construct(
    protected StudioManager $studio_manager,
  ) {
  }

  #[Route(path: '/studios', name: 'studios_overview', methods: ['GET'])]
  public function studiosOverview(): Response
  {
    /** @var User|null $user */
    $user = $this->getUser();

    return $this->render('Studio/Studios.html.twig', [
      'is_logged_in' => null !== $user,
      'user_id' => $user?->getId(),
      'user_name' => $user?->getUsername(),
    ]);
  }

  #[Route(path: '/studio/create', name: 'studio_new', methods: ['GET'])]
  public function studioNew(): Response
  {
    if (!$this->getUser()) {
      return $this->redirectToRoute('login');
    }

    return $this->render('Studio/CreatePage.html.twig');
  }

  /**
   * Entry page for invite codes; an invite link pre-fills the code. Joining itself is a POST to the API,
   * so link previews and prefetchers never add anyone to a studio.
   */
  #[Route(path: '/studio/join/{code}', name: 'studio_join_with_code', requirements: ['code' => '[A-Za-z0-9-]*'], defaults: ['code' => ''], methods: ['GET'])]
  public function joinWithCode(Request $request, string $code): Response
  {
    if (!$this->getUser()) {
      $request->getSession()->set('catroweb_login_redirect', $request->getUri());

      return $this->redirectToRoute('login');
    }

    return $this->render('Studio/JoinWithCodePage.html.twig', ['code' => $code]);
  }

  #[Route(path: '/studio/{id}', name: 'studio_details', methods: ['GET'])]
  public function studioDetails(string $id): Response
  {
    $studio = $this->studio_manager->findStudioById($id);
    if (null === $studio || $studio->getAutoHidden() || !$studio->isIsEnabled()) {
      throw $this->createNotFoundException();
    }

    /** @var User|null $user */
    $user = $this->getUser();
    $userRole = null;
    $userName = null;
    if (null !== $user) {
      $userName = $user->getUsername();
      $userRole = $this->studio_manager->getStudioUserRole($user, $studio);
    }

    if (!$studio->isIsPublic() && null === $user) {
      return $this->redirectToRoute('login');
    }

    return $this->render('Studio/DetailsPage.html.twig', [
      'studio' => $studio,
      'user_role' => $userRole,
      'user_name' => $userName,
    ]);
  }
}
