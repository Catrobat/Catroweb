<?php

namespace App\Catrobat\Controller\Resetting;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * Class ResettingController
 * @package App\Catrobat\Controller\Resetting
 */
class ResettingController extends AbstractController
{

  /**
   * @Route("/reset/invalid", name="reset_invalid")
   *
   * @return Response
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
   * @return Response
   */
  public function handlePasswordAlreadyRequested()
  {
    return $this->render('@FOSUser/Resetting/check_email.html.twig', [
      'already_reset' => "",
    ]);
  }
}
