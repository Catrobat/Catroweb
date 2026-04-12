<?php

declare(strict_types=1);

namespace App\Api\Exceptions;

use OpenAPI\Server\Model\ErrorResponse;
use OpenAPI\Server\Model\ErrorResponseError;
use OpenAPI\Server\Model\ErrorResponseErrorDetailsInner;
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

  /**
   * Creates an ErrorResponse model object from validation errors.
   *
   * Use this when returning error responses from API handler methods
   * (which return model objects serialized by the framework).
   *
   * @param int                   $code    HTTP status code
   * @param string                $type    Machine-readable error type
   * @param string                $message Human-readable error summary
   * @param array<string, string> $errors  Field-name => error-message map from ValidationWrapper::getErrors()
   */
  public static function createModel(int $code, string $type, string $message, array $errors = []): ErrorResponse
  {
    $details = [];
    foreach ($errors as $field => $fieldMessage) {
      $detail = new ErrorResponseErrorDetailsInner();
      $detail->setField($field);
      $detail->setMessage($fieldMessage);
      $details[] = $detail;
    }

    $error = new ErrorResponseError();
    $error->setCode($code);
    $error->setType($type);
    $error->setMessage($message);
    if ([] !== $details) {
      $error->setDetails($details);
    }

    $response = new ErrorResponse();
    $response->setError($error);

    return $response;
  }

  /**
   * Creates a validation ErrorResponse model from ValidationWrapper errors.
   *
   * Convenience wrapper for the common 422 validation error case.
   *
   * @param array<string, string> $errors Field-name => error-message map from ValidationWrapper::getErrors()
   */
  public static function createValidationModel(array $errors): ErrorResponse
  {
    return self::createModel(422, 'validation_error', 'Validation failed', $errors);
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
