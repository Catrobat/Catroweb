<?php

declare(strict_types=1);

namespace App\Api\Exceptions;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::EXCEPTION, method: 'setExceptionResponse')]
class ApiExceptionEventListener
{
  public function __construct(
    private readonly LoggerInterface $logger,
  ) {
  }

  public function setExceptionResponse(ExceptionEvent $event): void
  {
    if (!str_starts_with($event->getRequest()->getPathInfo(), '/api')) {
      return;
    }

    $exception = $event->getThrowable();
    $statusCode = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : Response::HTTP_INTERNAL_SERVER_ERROR;
    $type = ApiErrorResponse::httpStatusToErrorType($statusCode);
    $message = $exception instanceof HttpExceptionInterface ? $exception->getMessage() : 'An unexpected error occurred.';

    if ($statusCode >= 500) {
      $this->logger->error('API {status}: {class}: {error} at {file}:{line}', [
        'status' => $statusCode,
        'class' => $exception::class,
        'error' => $exception->getMessage(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'url' => $event->getRequest()->getUri(),
        'method' => $event->getRequest()->getMethod(),
        'exception' => $exception,
      ]);
    }

    if ('' === $message) {
      $message = Response::$statusTexts[$statusCode] ?? 'An unexpected error occurred.';
    }

    $event->setResponse(ApiErrorResponse::create($statusCode, $type, $message));
  }
}
