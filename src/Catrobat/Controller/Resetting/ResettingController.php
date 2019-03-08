<?php

namespace App\Catrobat\Controller\Resetting;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;


/**
 * Class ResettingController
 * @package App\Catrobat\Controller\Resetting
 */
class ResettingController extends Controller
{

  /**
   * @Route("/reset/invalid", name="reset_invalid")
   *
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function handleInvalidUsernameOrEmail()
  {
    return $this->render('@FOSUser/Resetting/request.html.twig', [
      'invalid_username' => "",
    ]);
  }


  /**
   * @Route("/reset/already_requested", name="reset_already_requested")
   *
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function handlePasswordAlreadyRequested()
  {
    return $this->render('@FOSUser/Resetting/check_email.html.twig', [
      'already_reset' => "",
    ]);
  }
}
