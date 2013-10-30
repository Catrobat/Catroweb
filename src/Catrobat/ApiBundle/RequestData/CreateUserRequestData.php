<?php
namespace Catrobat\ApiBundle\RequestData;

use Symfony\Component\Validator\Constraints as Assert;

class CreateUserRequestData
{
  /**
   * @Assert\NotBlank()
   */  
  public $username;

  /**
   * @Assert\NotBlank()
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
