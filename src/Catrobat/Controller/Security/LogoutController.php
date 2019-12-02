<?php

namespace App\Catrobat\Controller\Security;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;


/**
 * Class LogoutController
 * @package App\Catrobat\Controller\Security
 */
class LogoutController extends AbstractController
{

  /**
   * @return RedirectResponse
   */
  public function logoutAction()
  {
    $this->get('security.token_storage')->setToken(null);

    return $this->redirect($this->generateUrl('index'));
  }
}
