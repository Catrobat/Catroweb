<?php

namespace App\Catrobat\Controller\Security;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


/**
 * Class LogoutController
 * @package App\Catrobat\Controller\Security
 */
class LogoutController extends Controller
{

  /**
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function logoutAction()
  {
    $this->get('security.token_storage')->setToken(null);

    return $this->redirect($this->generateUrl('index'));
  }
}
