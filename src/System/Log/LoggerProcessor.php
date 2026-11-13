<?php

declare(strict_types=1);

namespace App\System\Log;

use App\DB\Entity\User\User;
use Monolog\LogRecord;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

#[Autoconfigure(tags: ['monolog.processor'])]
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
    $request_id = $this->getRequestId($request);
    if (null !== $request_id) {
      $record->extra['request_id'] = $request_id;
    }

    return $record;
  }

  /**
   * The reverse proxy sets X-Request-Id; only accept a plain token so a client cannot inject junk into log lines.
   */
  private function getRequestId(Request $request): ?string
  {
    $id = $request->headers->get('X-Request-Id');

    return null !== $id && 1 === preg_match('/^[\w.-]{1,128}$/', $id) ? $id : null;
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
      $pos = strpos((string) $ip, ',');

      return false !== $pos ? substr((string) $ip, 0, $pos) : $ip;
    }

    return $ip;
  }
}
