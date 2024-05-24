<?php

declare(strict_types=1);

namespace App\System\Log;

use App\DB\Entity\User\User;
use Monolog\LogRecord;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class LoggerProcessor
{
  private const string ANON_USER = 'anonymous';

  public function __construct(private readonly RequestStack $request_stack, private readonly TokenStorageInterface $security_token_storage)
  {
  }

  public function __invoke(LogRecord $record): LogRecord
  {
    if (!$this->request_stack->getCurrentRequest() instanceof Request) {
      return $record;
    }

    $request = $this->request_stack->getCurrentRequest();

    $record->extra['client_ip'] = $this->getOriginalClientIp($request);
    $record->extra['user_agent'] = $this->getUserAgent($request);
    $record->extra['session_user'] = $this->getSessionUser();

    return $record;
  }

  private function getSessionUser(): string
  {
    $token = $this->security_token_storage->getToken();
    $session_user = null;
    if ($token instanceof TokenInterface) {
      $session_user = $this->security_token_storage->getToken()->getUser();
    }

    return ($session_user instanceof User) ? ($session_user->getUsername() ?? self::ANON_USER) : self::ANON_USER;
  }

  private function getUserAgent(Request $request): ?string
  {
    return $request->headers->get('User-Agent');
  }

  private function getOriginalClientIp(Request $request): ?string
  {
    $ip = $request->getClientIp();

    if (str_contains((string) $ip, ',')) {
      return substr((string) $ip, 0, strpos((string) $ip, ','));
    }

    return $ip;
  }
}
