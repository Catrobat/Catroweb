<?php

declare(strict_types=1);

namespace App\Api_deprecated\OAuth;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

class CreateOAuthUserRequest
{
  #[Assert\NotBlank(message: 'error.username.blank')]
  #[Assert\Regex(pattern: '/^[\w@_\-\.\s]+$/')]
  public string $username;

  #[Assert\NotBlank(message: 'error.email.blank')]
  #[Assert\Email(message: 'error.email.invalid')]
  public string $mail;

  #[Assert\NotBlank(message: 'error.id.blank')]
  public string $id;

  public function __construct(Request $request)
  {
    $this->username = (string) $request->request->get('username');
    $this->id = (string) $request->request->get('id');
    $this->mail = (string) $request->request->get('email');
  }
}
