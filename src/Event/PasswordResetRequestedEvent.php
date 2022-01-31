<?php

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class PasswordResetRequestedEvent extends Event
{
  protected string $email;
  protected string $locale;

  public function __construct(string $email, string $locale)
  {
    $this->email = $email;
    $this->locale = $locale;
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
