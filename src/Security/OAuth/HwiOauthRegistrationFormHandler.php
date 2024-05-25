<?php

declare(strict_types=1);

namespace App\Security\OAuth;

use HWI\Bundle\OAuthBundle\Form\RegistrationFormHandlerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class HwiOauthRegistrationFormHandler implements RegistrationFormHandlerInterface
{
  #[\Override]
  public function process(Request $request, FormInterface $form, UserResponseInterface $userInformation): bool
  {
    return true;
  }
}
