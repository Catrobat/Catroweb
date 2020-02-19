<?php

namespace App\Catrobat\Requests;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class CreateOAuthUserRequest.
 */
class CreateOAuthUserRequest
{
  /**
   * @Assert\NotBlank(message="error.username.blank")
   * @Assert\Regex(pattern="/^[\w@_\-\.\s]+$/")
   */
  public $username;

  /**
   * @Assert\NotBlank(message="error.email.blank")
   * @Assert\Email(message="error.email.invalid")
   */
  public $mail;

  /**
   * @Assert\NotBlank(message="error.id.blank")
   */
  public $id;

  /**
   * CreateOAuthUserRequest constructor.
   */
  public function __construct(Request $request)
  {
    $this->username = $request->request->get('username');
    $this->id = $request->request->get('id');
    $this->mail = $request->request->get('email');
  }
}
