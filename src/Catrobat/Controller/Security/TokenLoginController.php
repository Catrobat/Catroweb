<?php

namespace App\Catrobat\Controller\Security;

use App\Entity\User;
use App\Entity\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;


/**
 * Class TokenLoginController
 * @package App\Catrobat\Controller\Security
 */
class TokenLoginController extends AbstractController
{

  /**
   * @Route("/tokenlogin", name="token_login", methods={"GET"})
   *
   * @param Request $request
   * @param UserManager $user_manager
   *
   * @return RedirectResponse
   */
  public function tokenloginAction(Request $request, UserManager $user_manager)
  {
    /**
     * @var $user User
     */
    $username = $request->query->get('username');
    $token = $request->query->get('token');
    $user = $user_manager->findUserByUsername($username);

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
   * @return RedirectResponse
   */
  private function logout()
  {
    $this->get('security.token_storage')->setToken(null);

    return $this->redirect($this->generateUrl('index') . '?redirect');
  }
}