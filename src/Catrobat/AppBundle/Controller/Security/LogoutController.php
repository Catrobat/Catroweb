<?php

namespace Catrobat\AppBundle\Controller\Security;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class LogoutController extends Controller
{
    public function logoutAction() {
        $this->get('security.context')->setToken(null);

        return $this->redirect($this->generateUrl('index'));
    }
}
