<?php

declare(strict_types=1);

namespace App\Application\Controller\User;

use App\DB\Entity\User\User;
use App\Project\ProjectManager;
use App\User\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProfileController extends AbstractController
{
  final public const int MIN_PASSWORD_LENGTH = 6;

  final public const int MAX_PASSWORD_LENGTH = 4096;

  public function __construct(
    protected ProjectManager $project_manager,
    protected UserManager $user_manager,
  ) {
  }

  /**
   * Overwrite for FosUser Profile Route (We don't use it!).
   */
  #[Route(path: '/user/{id}', name: 'profile', defaults: ['id' => 0], methods: ['GET'])]
  #[Route(path: '/user/}')]
  public function profile(string $id): Response
  {
    /** @var User|null $user */
    $user = $this->getUser();
    if ('0' === $id || ($user && $user->getId() === $id)) {
      if (is_null($user)) {
        return $this->redirectToRoute('login');
      }

      $project_count = $this->project_manager->countUserProjects($user->getId());
      $view = 'User/Profile/MyProfilePage.html.twig';
    } else {
      /** @var User|null $user */
      $user = $this->user_manager->find($id);
      if (is_null($user)) {
        return $this->redirectToRoute('index');
      }

      if ($user->getProfileHidden()) {
        return $this->redirectToRoute('index');
      }

      $project_count = $this->project_manager->countPublicUserProjects($id);
      $view = 'User/Profile/ProfilePage.html.twig';
    }

    return $this->render($view, [
      'profile' => $user,
      'project_count' => $project_count,
      'firstMail' => $user->getEmail(),
      'minPassLength' => self::MIN_PASSWORD_LENGTH,
      'maxPassLength' => self::MAX_PASSWORD_LENGTH,
      'username' => $user->getUsername(),
    ]);
  }
}
