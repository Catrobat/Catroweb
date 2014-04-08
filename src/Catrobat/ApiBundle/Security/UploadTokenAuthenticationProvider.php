<?php

namespace Catrobat\ApiBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;

class UploadTokenAuthenticationProvider implements AuthenticationProviderInterface
{
  protected $user_provider;
  
  public function __construct(\FOS\UserBundle\Security\UserProvider $user_provider)
  {
    $this->user_provider = $user_provider;
  }
  
  public function authenticate(TokenInterface $token)
  {
    $user = $this->user_provider->loadUserByUsername($token->getUsername());
    if ($token->getCredentials() === $user->getUploadToken())
    {
      $authenticated_token = new PreAuthenticatedToken($user, $token->getCredentials(), $token->getProviderKey(), $user->getRoles());
      $authenticated_token->setAuthenticated(true);
      return $authenticated_token;
    }
    else
    {
      throw new AuthenticationException('Upload Token auth failed.');
    }
  }
  
  public function supports(TokenInterface $token)
  {
    return $token instanceof PreAuthenticatedToken;
  }

}
