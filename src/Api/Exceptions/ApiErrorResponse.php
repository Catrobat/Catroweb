<?php

declare(strict_types=1);

namespace App\Api\Exceptions;

use Symfony\Component\HttpFoundation\Response;

final class ApiErrorResponse
{
  public static function create(int $code, string $type, string $message): Response
  {
    $body = [
      'error' => [
        'code' => $code,
        'type' => $type,
        'message' => $message,
      ],
    ];

    return new Response(
      json_encode($body, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES),
      $code,
      ['Content-Type' => 'application/json']
    );
  }

  public static function httpStatusToErrorType(int $statusCode): string
  {
    return match ($statusCode) {
      400 => 'bad_request',
      401 => 'unauthorized',
      403 => 'forbidden',
      404 => 'not_found',
      406 => 'not_acceptable',
      415 => 'unsupported_media_type',
      422 => 'validation_error',
      429 => 'too_many_requests',
      default => 'internal_error',
    };
  }
}
