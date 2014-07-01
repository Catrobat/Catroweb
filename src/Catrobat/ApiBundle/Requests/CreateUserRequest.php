<?php
namespace Catrobat\ApiBundle\Requests;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Request;

class CreateUserRequest
{
  public function __construct(Request $request)
  {
    $this->username = $request->request->get('registrationUsername');
    $this->password = $request->request->get('registrationPassword');
    $this->country = strtoupper($request->request->get('registrationCountry'));
    $this->mail = $request->request->get('registrationEmail');
  }

  /**
   * @Assert\NotBlank(message = "error.username.blank")
   * @Assert\Regex(pattern="/^[\w@_\-\.]+$/")
   */  
  public $username;

  /**
   * @Assert\NotBlank(message = "error.email.blank")
   * @Assert\Email(message = "error.email.invalid")
   */
  public $mail;
  
  /**
   * @Assert\NotBlank(message = "error.password.blank")
   * @Assert\Length(min = "6", minMessage = "error.password.short")
   */
  public $password;
  
  /**
   * @Assert\NotBlank(message = "error.country.blank")
   * @Assert\Country(message = "error.country.invalid")
   */
  public $country;
}
