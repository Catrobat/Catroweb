<?php

namespace App\Catrobat\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class LogoutController extends AbstractController
{
  /**
   * @Route("/logout", name="logout", defaults={"_format": "json"}, methods={"GET"})
   */
  public function logoutAction(): RedirectResponse
  {
    $this->get('security.token_storage')->setToken(null);

    return $this->redirect($this->generateUrl('index'));
  }
}
