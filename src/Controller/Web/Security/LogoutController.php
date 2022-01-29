<?php

namespace App\Controller\Web\Security;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LogoutController extends AbstractController
{
  /**
   * @Route("/logout", name="logout", defaults={"_format": "json"}, methods={"GET"})
   */
  public function logoutAction(Request $request): RedirectResponse
  {
    setcookie('LOGGED_IN', '', time() - 3600);
    setcookie('BEARER', '', time() - 3600);
    setcookie('REFRESH_TOKEN', '', time() - 3600);
    $this->get('security.token_storage')->setToken();
    $request->getSession()->invalidate();

    return $this->redirect($this->generateUrl('index'));
  }
}
