<?php

namespace App\Catrobat\Controller\Security;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;

class LogoutController extends AbstractController
{
  public function logoutAction(): RedirectResponse
  {
    $this->get('security.token_storage')->setToken(null);

    return $this->redirect($this->generateUrl('index'));
  }
}
