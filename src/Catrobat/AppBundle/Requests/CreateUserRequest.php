<?php

namespace Catrobat\AppBundle\Requests;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Request;

class CreateUserRequest
{
    public function __construct(Request $request)
    {
        $this->username = $request->request->get('registrationUsername');
        $this->password = $request->request->get('registrationPassword');
        $this->mail = $request->request->get('registrationEmail');
    }

  /**
   * @Assert\NotBlank(message = "errors.username.blank")
   * @Assert\Regex(pattern="/^[\w\-\.]+$/", message = "errors.username.invalid")
   */
  public $username;

  /**
   * @Assert\NotBlank(message = "errors.email.blank")
   * @Assert\Email(message = "errors.email.invalid")
   */
  public $mail;

  /**
   * @Assert\NotBlank(message = "errors.password.blank")
   * @Assert\Length(min = "6", minMessage = "errors.password.short")
   */
  public $password;
}
