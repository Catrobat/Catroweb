<?php

declare(strict_types=1);

namespace App\Api\Exceptions;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::EXCEPTION, method: 'setExceptionResponse')]
class ApiExceptionEventListener
{
  public function setExceptionResponse(ExceptionEvent $event): void
  {
    if (!str_starts_with($event->getRequest()->getPathInfo(), '/api')) {
      return;
    }

    $exception = $event->getThrowable();
    $statusCode = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : Response::HTTP_INTERNAL_SERVER_ERROR;
    $response = new Response(null, $statusCode);
    $event->setResponse($response);
  }
}
