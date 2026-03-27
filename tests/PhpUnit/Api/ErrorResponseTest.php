<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api;

use App\Api\Exceptions\ApiExceptionEventListener;
use OpenAPI\Server\Controller\Controller;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @internal
 *
 * @coversNothing
 */
class ErrorResponseTest extends TestCase
{
  public function testCreateStructuredErrorResponseReturnsValidJson(): void
  {
    $response = Controller::createStructuredErrorResponse(400, 'bad_request', 'Invalid input');

    self::assertSame(400, $response->getStatusCode());
    self::assertSame('application/json', $response->headers->get('Content-Type'));

    $content = $response->getContent();
    self::assertIsString($content);
    $body = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
    self::assertArrayHasKey('error', $body);
    self::assertSame(400, $body['error']['code']);
    self::assertSame('bad_request', $body['error']['type']);
    self::assertSame('Invalid input', $body['error']['message']);
    self::assertArrayNotHasKey('details', $body['error']);
  }

  public function testCreateStructuredErrorResponseWithDetails(): void
  {
    $details = [
      ['field' => 'email', 'message' => 'Email is required'],
      ['field' => 'username', 'message' => 'Username too short'],
    ];

    $response = Controller::createStructuredErrorResponse(422, 'validation_error', 'Validation failed', $details);

    $content = $response->getContent();
    self::assertIsString($content);
    $body = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
    self::assertSame(422, $body['error']['code']);
    self::assertSame('validation_error', $body['error']['type']);
    self::assertCount(2, $body['error']['details']);
    self::assertSame('email', $body['error']['details'][0]['field']);
    self::assertSame('Email is required', $body['error']['details'][0]['message']);
  }

  public function testHttpStatusToErrorType(): void
  {
    self::assertSame('bad_request', Controller::httpStatusToErrorType(400));
    self::assertSame('unauthorized', Controller::httpStatusToErrorType(401));
    self::assertSame('forbidden', Controller::httpStatusToErrorType(403));
    self::assertSame('not_found', Controller::httpStatusToErrorType(404));
    self::assertSame('not_acceptable', Controller::httpStatusToErrorType(406));
    self::assertSame('unsupported_media_type', Controller::httpStatusToErrorType(415));
    self::assertSame('validation_error', Controller::httpStatusToErrorType(422));
    self::assertSame('too_many_requests', Controller::httpStatusToErrorType(429));
    self::assertSame('internal_error', Controller::httpStatusToErrorType(500));
    self::assertSame('internal_error', Controller::httpStatusToErrorType(503));
  }

  public function testApiExceptionEventListenerReturnsStructuredJsonForHttpException(): void
  {
    $listener = new ApiExceptionEventListener();
    $kernel = $this->createStub(HttpKernelInterface::class);
    $request = Request::create('/api/test');
    $exception = new NotFoundHttpException('Resource not found');

    $event = new ExceptionEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $exception);
    $listener->setExceptionResponse($event);

    $response = $event->getResponse();
    self::assertNotNull($response);
    self::assertSame(404, $response->getStatusCode());

    $content = $response->getContent();
    self::assertIsString($content);
    $body = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
    self::assertSame(404, $body['error']['code']);
    self::assertSame('not_found', $body['error']['type']);
    self::assertSame('Resource not found', $body['error']['message']);
  }

  public function testApiExceptionEventListenerReturns500ForGenericException(): void
  {
    $listener = new ApiExceptionEventListener();
    $kernel = $this->createStub(HttpKernelInterface::class);
    $request = Request::create('/api/test');
    $exception = new \RuntimeException('Something broke');

    $event = new ExceptionEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $exception);
    $listener->setExceptionResponse($event);

    $response = $event->getResponse();
    self::assertNotNull($response);
    self::assertSame(500, $response->getStatusCode());

    $content = $response->getContent();
    self::assertIsString($content);
    $body = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
    self::assertSame(500, $body['error']['code']);
    self::assertSame('internal_error', $body['error']['type']);
    self::assertSame('An unexpected error occurred.', $body['error']['message']);
  }

  public function testApiExceptionEventListenerIgnoresNonApiRequests(): void
  {
    $listener = new ApiExceptionEventListener();
    $kernel = $this->createStub(HttpKernelInterface::class);
    $request = Request::create('/some/web/page');
    $exception = new \RuntimeException('Something broke');

    $event = new ExceptionEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $exception);
    $listener->setExceptionResponse($event);

    self::assertNull($event->getResponse());
  }

  public function testApiExceptionEventListenerUsesStatusTextForEmptyMessage(): void
  {
    $listener = new ApiExceptionEventListener();
    $kernel = $this->createStub(HttpKernelInterface::class);
    $request = Request::create('/api/test');
    $exception = new BadRequestHttpException('');

    $event = new ExceptionEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $exception);
    $listener->setExceptionResponse($event);

    $response = $event->getResponse();
    self::assertNotNull($response);
    $content = $response->getContent();
    self::assertIsString($content);
    $body = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
    self::assertSame('Bad Request', $body['error']['message']);
  }
}
