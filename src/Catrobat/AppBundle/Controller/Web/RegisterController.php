<?php
namespace Catrobat\AppBundle\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;

class RegisterController extends Controller
{
  /**
   * @Route("/register", name="register_check")
   * @Method({"POST"})
   */
  public function checkRegistration(Request $request)
  {
    $route = 'fos_user_registration_register';
    $error = false;

    $username = $request->request->get('sonata_user_registration_form')['username'];
    if (strpos($username, "@") !== false)
    {
      $this->addFlash(
        'catroweb_error_message',
        "errors.username.not_email"
      );
      $error = true;
    }

    $password = $request->request->get('sonata_user_registration_form')['plainPassword'];
    if ($password['first'] !== $password['second'])
    {
      $this->addFlash(
        'catroweb_error_message',
        "passwordsNoMatch"
      );
      $error = true;
    }

    if ($error)
    {
      return $this->redirectToRoute('register_form');
    }
    
    $response = $this->forward('SonataUserBundle:RegistrationFOSUser1:register');
    return $response;
  }

  /**
   * @Route("/register", name="register_form")
   * @Method({"GET"})
   */
  public function showRegistrationForm(Request $request)
  {
    $response = $this->forward('FOSUserBundle:Registration:registerAction');
    return $response;
  }
}

