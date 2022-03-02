<?php

namespace App\Application\Controller\Security;

use App\Security\Authentication\CookieService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LogoutController extends AbstractController
{
  /**
   * @Route("/logout", name="logout", methods={"GET"})
   */
  public function logoutAction(Request $request): RedirectResponse
  {
    CookieService::clearCookie('LOGGED_IN');
    CookieService::clearCookie('BEARER');
    CookieService::clearCookie('REFRESH_TOKEN');
    $this->get('security.token_storage')->setToken();
    $request->getSession()->invalidate();

    return $this->redirect($this->generateUrl('index'));
  }
}
