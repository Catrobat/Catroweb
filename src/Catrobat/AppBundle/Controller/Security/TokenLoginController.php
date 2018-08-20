<?php
namespace Catrobat\AppBundle\Controller\Security;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Assetic\Exception\Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;


class TokenLoginController extends Controller
{

    /**
     * @Route("/tokenlogin", name="token_login")
     * @Method({"GET"})
     */
    public function tokenloginAction(Request $request) {
        $username = $request->query->get('username');
        $token = $request->query->get('token');
        $user = $this->get('usermanager')->findUserByUsername($username);

        if ($user == null) {
            return $this->logout();
        }
        if ($user->getUploadToken() != $token) {
            return $this->logout();
        }
        $token = new UsernamePasswordToken($user, null, "main", $user->getRoles());
        $this->get('security.token_storage')->setToken($token);

        // now dispatch the login event
        $event = new InteractiveLoginEvent($request, $token);
        $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
        return $this->redirect($this->generateUrl('index') . '?login');
    }

    private function logout() {
        $this->get('security.token_storage')->setToken(null);
        return $this->redirect($this->generateUrl('index') . '?redirect');
    }
}