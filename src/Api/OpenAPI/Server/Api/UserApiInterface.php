<?php

/**
 * UserApiInterface.
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

use OpenAPI\Server\Model\RegisterRequest;
use OpenAPI\Server\Model\ResetPasswordRequest;
use OpenAPI\Server\Model\UpdateUserRequest;

/**
 * UserApiInterface Interface Doc Comment.
 *
 * @category Interface
 *
 * @author   OpenAPI Generator team
 *
 * @see     https://github.com/openapitools/openapi-generator
 */
interface UserApiInterface
{
  /**
   * Sets authentication method BearerAuth.
   *
   * @param string|null $value value of the BearerAuth authentication method
   */
  public function setBearerAuth(?string $value): void;

  /**
   * Operation userDelete.
   *
   * Delete user account
   *
   * @param int   &$responseCode   The HTTP Response Code
   * @param array $responseHeaders Additional HTTP headers to return with the response ()
   */
  public function userDelete(
    int &$responseCode,
    array &$responseHeaders,
  ): void;

  /**
   * Operation userGet.
   *
   * Get your private user data
   *
   * @param int   &$responseCode   The HTTP Response Code
   * @param array $responseHeaders Additional HTTP headers to return with the response ()
   */
  public function userGet(
    int &$responseCode,
    array &$responseHeaders,
  ): array|object|null;

  /**
   * Operation userIdGet.
   *
   * Get public user data
   *
   * @param string $id              (required)
   * @param int    &$responseCode   The HTTP Response Code
   * @param array  $responseHeaders Additional HTTP headers to return with the response ()
   */
  public function userIdGet(
    string $id,
    int &$responseCode,
    array &$responseHeaders,
  ): array|object|null;

  /**
   * Operation userPost.
   *
   * Register
   *
   * @param RegisterRequest $register_request (required)
   * @param string          $accept_language  (optional, default to 'en')
   * @param int             &$responseCode    The HTTP Response Code
   * @param array           $responseHeaders  Additional HTTP headers to return with the response ()
   */
  public function userPost(
    RegisterRequest $register_request,
    string $accept_language,
    int &$responseCode,
    array &$responseHeaders,
  ): array|object|null;

  /**
   * Operation userPut.
   *
   * Update User
   *
   * @param UpdateUserRequest $update_user_request (required)
   * @param string            $accept_language     (optional, default to 'en')
   * @param int               &$responseCode       The HTTP Response Code
   * @param array             $responseHeaders     Additional HTTP headers to return with the response ()
   */
  public function userPut(
    UpdateUserRequest $update_user_request,
    string $accept_language,
    int &$responseCode,
    array &$responseHeaders,
  ): array|object|null;

  /**
   * Operation userResetPasswordPost.
   *
   * Request email to reset password
   *
   * @param ResetPasswordRequest $reset_password_request (required)
   * @param string               $accept_language        (optional, default to 'en')
   * @param int                  &$responseCode          The HTTP Response Code
   * @param array                $responseHeaders        Additional HTTP headers to return with the response ()
   */
  public function userResetPasswordPost(
    ResetPasswordRequest $reset_password_request,
    string $accept_language,
    int &$responseCode,
    array &$responseHeaders,
  ): array|object|null;

  /**
   * Operation usersGet.
   *
   * Get users
   *
   * @param string $query           (required)
   * @param int    $limit           (optional, default to 20)
   * @param int    $offset          (optional, default to 0)
   * @param int    &$responseCode   The HTTP Response Code
   * @param array  $responseHeaders Additional HTTP headers to return with the response ()
   */
  public function usersGet(
    string $query,
    int $limit,
    int $offset,
    int &$responseCode,
    array &$responseHeaders,
  ): array|object|null;

  /**
   * Operation usersSearchGet.
   *
   * Search for users
   *
   * @param string $query           (required)
   * @param int    $limit           (optional, default to 20)
   * @param int    $offset          (optional, default to 0)
   * @param string $attributes      (optional, default to '')
   * @param int    &$responseCode   The HTTP Response Code
   * @param array  $responseHeaders Additional HTTP headers to return with the response ()
   */
  public function usersSearchGet(
    string $query,
    int $limit,
    int $offset,
    string $attributes,
    int &$responseCode,
    array &$responseHeaders,
  ): array|object|null;
}
