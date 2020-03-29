<?php

namespace App\Catrobat\Controller\Security;

use App\Entity\User;
use App\Entity\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class TokenLoginController extends AbstractController
{
  /**
   * @Route("/tokenlogin", name="token_login", methods={"GET"})
   */
  public function tokenLoginAction(Request $request, UserManager $user_manager): RedirectResponse
  {
    $username = $request->query->get('username');
    $token = $request->query->get('token');

    /** @var User|null $user */
    $user = $user_manager->findUserByUsername($username);

    if (null === $user)
    {
      return $this->logout();
    }
    if ($user->getUploadToken() !== $token)
    {
      return $this->logout();
    }
    $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
    $this->get('security.token_storage')->setToken($token);

    // now dispatch the login event
    $event = new InteractiveLoginEvent($request, $token);
    $this->get('event_dispatcher')->dispatch($event);

    return $this->redirect($this->generateUrl('index').'?login');
  }

  private function logout(): RedirectResponse
  {
    $this->get('security.token_storage')->setToken(null);

    return $this->redirect($this->generateUrl('index').'?redirect');
  }
}
