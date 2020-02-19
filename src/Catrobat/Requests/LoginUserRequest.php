<?php

namespace App\Catrobat\Requests;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class LoginUserRequest.
 */
class LoginUserRequest
{
  /**
   * @Assert\NotBlank(message="errors.username.blank")
   * @Assert\Regex(pattern="/^[\w@_\-\.]+$/")
   */
  public $username;

  /**
   * @Assert\NotBlank(message="errors.password.blank")
   * @Assert\Length(min="6", minMessage="errors.password.short")
   */
  public $password;

  /**
   * LoginUserRequest constructor.
   */
  public function __construct(Request $request)
  {
    $this->username = $request->request->get('registrationUsername');
    $this->password = $request->request->get('registrationPassword');
  }
}
