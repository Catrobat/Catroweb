<?php

namespace App\Logger;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class LoggerProcessor
{
  private RequestStack $request_stack;
  private TokenStorageInterface $security_token_storage;

  public function __construct(RequestStack $request_stack, TokenStorageInterface $security_token_storage)
  {
    $this->request_stack = $request_stack;
    $this->security_token_storage = $security_token_storage;
  }

  public function __invoke(array $record)
  {
    if (!$this->request_stack->getCurrentRequest()) {
      return $record;
    }

    $request = $this->request_stack->getCurrentRequest();

    $record['extra']['client_ip'] = $this->getOriginalClientIp($request);
    $record['extra']['user_agent'] = $this->getUserAgent($request);
    $record['extra']['session_user'] = $this->getSessionUser();

    return $record;
  }

  private function getSessionUser()
  {
    $token = $this->security_token_storage->getToken();
    $session_user = null;
    if (null !== $token) {
      $session_user = $this->security_token_storage->getToken()->getUser();
    }
    return ($session_user instanceof User) ? $session_user->getUsername() : 'anonymous';
  }

  private function getUserAgent(Request $request): ?string
  {
    return $request->headers->get('User-Agent');
  }

  private function getOriginalClientIp(Request $request): ?string
  {
    $ip = $request->getClientIp();

    if (false !== strpos($ip, ','))
    {
      $ip = substr($ip, 0, strpos($ip, ','));
    }

    return $ip;
  }
}
