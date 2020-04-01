<?php

namespace App\Catrobat\Controller\Resetting;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ResettingController extends AbstractController
{
  /**
   * @Route("/reset/invalid", name="reset_invalid")
   */
  public function handleInvalidUsernameOrEmail(): Response
  {
    return $this->render('@FOSUser/Resetting/request.html.twig', [
      'invalid_username' => '',
    ]);
  }

  /**
   * @Route("/reset/already_requested", name="reset_already_requested")
   */
  public function handlePasswordAlreadyRequested(): Response
  {
    return $this->render('@FOSUser/Resetting/check_email.html.twig', [
      'already_reset' => '',
    ]);
  }
}
