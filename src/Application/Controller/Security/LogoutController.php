<?php

declare(strict_types=1);

namespace App\Application\Controller\Security;

use App\Security\Authentication\CookieService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class LogoutController extends AbstractController
{
  public function __construct(
    protected TokenStorageInterface $token_storage,
    protected CookieService $cookie_service
  ) {
  }

  #[Route(path: '/logout', name: 'logout')]
  public function logout(Request $request): RedirectResponse
  {
    $this->cookie_service->clearCookie('BEARER');
    $this->cookie_service->clearCookie('REFRESH_TOKEN');
    $this->token_storage->setToken(null);
    $request->getSession()->invalidate();

    return $this->redirectToRoute('index');
  }
}
