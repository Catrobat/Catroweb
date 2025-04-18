<?php

/**
 * NotificationsApiInterface.
 *
 * PHP version 8.1.1
 *
 * @category Class
 *
 * @author   OpenAPI Generator team
 *
 * @see     https://github.com/openapitools/openapi-generator
 */

/**
 * Catroweb API.
 *
 * API for the Catrobat Share Platform
 *
 * The version of the OpenAPI document: v1.6.0
 * Contact: webmaster@catrobat.org
 * Generated by: https://github.com/openapitools/openapi-generator.git
 */

/**
 * NOTE: This class is auto generated by the openapi generator program.
 * https://github.com/openapitools/openapi-generator
 * Do not edit the class manually.
 */

namespace OpenAPI\Server\Api;

/**
 * NotificationsApiInterface Interface Doc Comment.
 *
 * @category Interface
 *
 * @author   OpenAPI Generator team
 *
 * @see     https://github.com/openapitools/openapi-generator
 */
interface NotificationsApiInterface
{
  /**
   * Sets authentication method BearerAuth.
   *
   * @param string|null $value value of the BearerAuth authentication method
   */
  public function setBearerAuth(?string $value): void;

  /**
   * Operation notificationIdReadPut.
   *
   * Mark specified notification as read
   *
   * @param int    $id              (required)
   * @param string $accept_language (optional, default to 'en')
   * @param int    &$responseCode   The HTTP Response Code
   * @param array  $responseHeaders Additional HTTP headers to return with the response ()
   */
  public function notificationIdReadPut(
    int $id,
    string $accept_language,
    int &$responseCode,
    array &$responseHeaders,
  ): void;

  /**
   * Operation notificationsCountGet.
   *
   * Count the number of unseen notifications
   *
   * @param int   &$responseCode   The HTTP Response Code
   * @param array $responseHeaders Additional HTTP headers to return with the response ()
   */
  public function notificationsCountGet(
    int &$responseCode,
    array &$responseHeaders,
  ): array|object|null;

  /**
   * Operation notificationsGet.
   *
   * Get user notifications -- StatusCode: 501 - Not yet implemented
   *
   * @param string $accept_language (optional, default to 'en')
   * @param int    $limit           (optional, default to 20)
   * @param int    $offset          (optional, default to 0)
   * @param string $attributes      (optional, default to '')
   * @param string $type            (optional, default to 'all')
   * @param int    &$responseCode   The HTTP Response Code
   * @param array  $responseHeaders Additional HTTP headers to return with the response ()
   */
  public function notificationsGet(
    string $accept_language,
    int $limit,
    int $offset,
    string $attributes,
    string $type,
    int &$responseCode,
    array &$responseHeaders,
  ): array|object|null;

  /**
   * Operation notificationsReadPut.
   *
   * Mark all notifications as read
   *
   * @param int   &$responseCode   The HTTP Response Code
   * @param array $responseHeaders Additional HTTP headers to return with the response ()
   */
  public function notificationsReadPut(
    int &$responseCode,
    array &$responseHeaders,
  ): void;
}
