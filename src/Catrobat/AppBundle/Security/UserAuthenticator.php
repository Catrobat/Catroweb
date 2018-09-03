<?php

namespace Catrobat\AppBundle\Security;

use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserAuthenticator
{
  private $authentication_manager;
  private $user_provider;

  public function __construct(UserProviderInterface $user_provider, AuthenticationManagerInterface $authentication_manager)
  {
    $this->authentication_manager = $authentication_manager;
    $this->user_provider = $user_provider;
  }

  /**
   * @param unknown $username
   * @param unknown $password
   *
   * @return TokenInterface
   */
  public function authenticate($username, $password)
  {
    $user = $this->user_provider->loadUserByUsername($username);

    return $this->authentication_manager->authenticate(new UsernamePasswordToken($user->getUsername(), $password, 'main'));
  }
}