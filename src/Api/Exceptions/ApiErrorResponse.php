<?php

declare(strict_types=1);

namespace App\Api\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * Utility class for creating standardized JSON error responses across the API.
 *
 * This is intentionally NOT in the generated OpenAPI Server directory, because
 * code generation overwrites files there. All non-generated API code that needs
 * structured error responses should use this class.
 */
final class ApiErrorResponse
{
  /**
   * Creates a standardized JSON error response.
   *
   * @param int                                          $code    HTTP status code
   * @param string                                       $type    Machine-readable error type
   * @param string                                       $message Human-readable error summary
   * @param array<array{field: string, message: string}> $details Optional field-level error details
   * @param array<string, string>                        $headers Optional additional headers
   */
  public static function create(int $code, string $type, string $message, array $details = [], array $headers = []): Response
  {
    $body = [
      'error' => [
        'code' => $code,
        'type' => $type,
        'message' => $message,
      ],
    ];

    if ([] !== $details) {
      $body['error']['details'] = $details;
    }

    $headers = array_merge($headers, ['Content-Type' => 'application/json']);

    return new Response(json_encode($body, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES), $code, $headers);
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
