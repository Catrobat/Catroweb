<?php

declare(strict_types=1);

namespace App\Api_deprecated\Requests;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @deprecated
 */
class CreateUserRequest
{
  #[Assert\NotBlank(message: 'errors.username.blank')]
  #[Assert\Regex(pattern: '/^[\w_\-\.]{3,180}$/')]
  public string $username;

  #[Assert\NotBlank(message: 'errors.email.blank')]
  #[Assert\Email(message: 'errors.email.invalid')]
  public string $mail;

  #[Assert\NotBlank(message: 'errors.password.blank')]
  #[Assert\Length(min: 6, minMessage: 'errors.password.short')]
  public string $password;

  public function __construct(Request $request)
  {
    $this->username = strval($request->request->get('registrationUsername'));
    $this->password = strval($request->request->get('registrationPassword'));
    $this->mail = strval($request->request->get('registrationEmail'));
  }
}
