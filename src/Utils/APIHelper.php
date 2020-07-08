<?php

namespace App\Utils;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class APIHelper
{
  /**
   * Wrapper Method for better Error output.
   *
   * @param mixed $value
   *
   * @throws Exception
   */
  public static function getPandaAuth($value): string
  {
    try
    {
      return preg_split('#\s+#', $value)[1];
    }
    catch (Exception $e)
    {
      throw new Exception('The route must be registered under the jwt_token_authenticator! (security.yml)', Response::HTTP_UNAUTHORIZED);
    }
  }

  public static function setDefaultMaxVersionOnNull(?string $max_version): string
  {
    return null === $max_version ? '0' : $max_version;
  }

  public static function setDefaultLimitOnNull(?int $limit): int
  {
    return null === $limit ? 20 : $limit;
  }

  public static function setDefaultOffsetOnNull(?int $offset): int
  {
    return null === $offset ? 0 : $offset;
  }

  public static function setDefaultAcceptLanguageOnNull(?string $accept_language): string
  {
    return null === $accept_language ? 'en' : $accept_language;
  }
}
