<?php

namespace Catrobat\AppBundle\Controller\Security;

use Catrobat\AppBundle\Entity\User;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


/**
 * Class TokenLoginController
 * @package Catrobat\AppBundle\Controller\Security
 */
class TokenLoginController extends Controller
{

  /**
   * @Route("/tokenlogin", name="token_login", methods={"GET"})
   *
   * @param Request $request
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function tokenloginAction(Request $request)
  {
    /**
     * @var $user User
     */
    $username = $request->query->get('username');
    $token = $request->query->get('token');
    $user = $this->get('usermanager')->findUserByUsername($username);

    if ($user == null)
    {
      return $this->logout();
    }
    if ($user->getUploadToken() != $token)
    {
      return $this->logout();
    }
    $token = new UsernamePasswordToken($user, null, "main", $user->getRoles());
    $this->get('security.token_storage')->setToken($token);

    // now dispatch the login event
    $event = new InteractiveLoginEvent($request, $token);
    $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);

    return $this->redirect($this->generateUrl('index') . '?login');
  }


  /**
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  private function logout()
  {
    $this->get('security.token_storage')->setToken(null);

    return $this->redirect($this->generateUrl('index') . '?redirect');
  }
}