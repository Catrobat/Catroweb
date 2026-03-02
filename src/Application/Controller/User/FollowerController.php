<?php

declare(strict_types=1);

namespace App\Application\Controller\User;

use App\DB\Entity\User\User;
use App\User\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FollowerController extends AbstractController
{
  public function __construct(private readonly UserManager $user_manager)
  {
  }

  #[Route(path: '/follower', name: 'catrobat_follower', methods: ['GET'])]
  public function follower(string $id = '0'): Response
  {
    /** @var User|null $user */
    $user = $this->getUser();
    if ('0' !== $id && !($user && $user->getId() === $id)) {
      /** @var User|null $user */
      $user = $this->user_manager->find($id);
    }

    if (null === $user) {
      return $this->redirectToRoute('login');
    }

    return $this->render('User/Followers/FollowersPage.html.twig', [
      'user_id' => $user->getId(),
    ]);
  }
}
