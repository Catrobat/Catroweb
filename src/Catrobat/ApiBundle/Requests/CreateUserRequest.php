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
    $this->country = $request->request->get('registrationCountry');
    $this->mail = $request->request->get('registrationEmail');
  }

  /**
   * @Assert\NotBlank(message = "Username must not be blank")
   */  
  public $username;

  /**
   * @Assert\NotBlank(message = "email must not be blank")
   * @Assert\Email(message = "Your email seems to be invalid")
   */
  public $mail;
  
  /**
   * @Assert\NotBlank(message = "The password is missing.")
   * @Assert\Length(min = "6", minMessage = "Your password must have at least 6 characters.")
   */
  public $password;
  
  /**
   * @Assert\NotBlank(message = "The country is missing.")
   */
  public $country;
}
