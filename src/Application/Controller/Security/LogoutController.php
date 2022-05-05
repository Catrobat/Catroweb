<?php

namespace App\Application\Controller\Security;

use App\Security\Authentication\CookieService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class LogoutController extends AbstractController
{
  protected TokenStorageInterface $token_storage;

  public function __construct(TokenStorageInterface $token_storage)
  {
    $this->token_storage = $token_storage;
  }

  /**
   * @Route("/logout", name="logout")
   */
  public function logoutAction(Request $request): RedirectResponse
  {
    CookieService::clearCookie('BEARER');
    CookieService::clearCookie('REFRESH_TOKEN');
    $this->token_storage->setToken();
    $request->getSession()->invalidate();

    return $this->redirect($this->generateUrl('index'));
  }
}
