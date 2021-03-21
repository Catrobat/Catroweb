<?php

namespace App\Catrobat\Services;

use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class APIHelper
{
  private TranslatorInterface $translator;

  /**
   * APIHelper constructor.
   */
  public function __construct(TranslatorInterface $translator)
  {
    $this->translator = $translator;
  }

  /**
   * Wrapper Method for better Error output.
   *
   * @param mixed $value
   *
   * @throws Exception
   */
  public static function getPandaAuth($value): string
  {
    try {
      return preg_split('#\s+#', $value)[1];
    } catch (Exception $e) {
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

  public function setDefaultAcceptLanguageOnNull(?string $accept_language): string
  {
    $accept_language = null === $accept_language ? 'en' : $accept_language;
//    try {
//      $this->translator->trans('category.recent', [], 'catroweb', 'en');
//    }
//    catch (Exception $e)
//    {
//      throw new Exception('Something went very wrong with translations!', Response::HTTP_INTERNAL_SERVER_ERROR);
//    }
    try {
      $this->translator->trans('category.recent', [], 'catroweb', $accept_language);
    } catch (Exception $e) {
      $accept_language = 'en';
    }

    return $accept_language;
  }

  public static function setDefaultFlavorOnNull(?string $flavor): string
  {
    return null === $flavor ? 'pocketcode' : $flavor;
  }
}
