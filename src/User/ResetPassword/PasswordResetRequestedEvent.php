<?php

namespace App\User\ResetPassword;

use Symfony\Contracts\EventDispatcher\Event;

class PasswordResetRequestedEvent extends Event
{
  public function __construct(protected string $email, protected string $locale)
  {
  }

  public function getEmail(): string
  {
    return $this->email;
  }

  public function getLocale(): string
  {
    return $this->locale;
  }
}
