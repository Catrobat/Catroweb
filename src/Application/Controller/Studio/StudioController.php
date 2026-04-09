<?php

declare(strict_types=1);

namespace App\Application\Controller\Studio;

use App\DB\Entity\User\User;
use App\Studio\StudioManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

  #[Route(path: '/studio/{id}', name: 'studio_details', methods: ['GET'])]
  public function studioDetails(string $id): Response
  {
    $studio = $this->studio_manager->findStudioById($id);
    if (null === $studio || $studio->getAutoHidden() || !$studio->isIsEnabled()) {
      throw $this->createNotFoundException();
    }

    /** @var User|null $user */
    $user = $this->getUser();
    $user_role = null;
    $user_name = null;
    $is_public = $studio->isIsPublic();
    $is_member = false;
    $join_request_status = null;

    if (null !== $user) {
      $user_name = $user->getUsername();
      $user_role = $this->studio_manager->getStudioUserRole($user, $studio);
      $is_member = null !== $user_role;

      if (!$is_public && !$is_member) {
        $joinRequest = $this->studio_manager->findJoinRequestByUserAndStudio($user, $studio);
        $join_request_status = $joinRequest?->getStatus();
      }
    }

    // Block anonymous users from viewing private studios
    if (!$is_public && null === $user) {
      throw $this->createNotFoundException();
    }

    $pending_join_requests_count = 0;
    if ('admin' === $user_role) {
      $pending_join_requests_count = count($this->studio_manager->findPendingJoinRequests($studio));
    }

    return $this->render('Studio/DetailsPage.html.twig', [
      'studio' => $studio,
      'user_role' => $user_role,
      'user_name' => $user_name,
      'is_public' => $is_public,
      'is_member' => $is_member,
      'join_request_status' => $join_request_status,
      'members_count' => $this->studio_manager->countStudioUsers($studio),
      'activities_count' => $this->studio_manager->countStudioActivities($studio),
      'projects_count' => $this->studio_manager->countStudioProjects($studio),
      'comments_count' => $this->studio_manager->countStudioComments($studio),
      'pending_join_requests_count' => $pending_join_requests_count,
    ]);
  }
}
