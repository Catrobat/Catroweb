<?php

declare(strict_types=1);

namespace App\Security\OAuth;

use HWI\Bundle\OAuthBundle\Form\RegistrationFormHandlerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class HwiOauthRegistrationFormHandler implements RegistrationFormHandlerInterface
{
  /**
   * {@inheritdoc}
   */
  public function process(Request $request, FormInterface $form, UserResponseInterface $userInformation)
  {
    return true;
  }
}
