<?php

declare(strict_types=1);

namespace App\Api\Services\Base;

use Symfony\Contracts\Translation\TranslatorInterface;

trait TranslatorAwareTrait
{
  private TranslatorInterface $translator;

  private ?string $locale = null;

  public function initTranslator(TranslatorInterface $translator): void
  {
    $this->translator = $translator;
  }

  public function __(string $id, array $parameter = [], ?string $locale = null): string
  {
    return $this->trans($id, $parameter, $locale);
  }

  public function trans(string $id, array $parameter = [], ?string $locale = null): string
  {
    $domain = 'catroweb';
    $locale_with_underscore = $this->sanitizeLocale($locale);
    $this->locale = $locale_with_underscore;

    try {
      return $this->translator->trans($id, $parameter, $domain, $locale_with_underscore);
    } catch (\Exception) {
      $this->locale = $this->getLocaleFallback();

      return $this->translator->trans($id, $parameter, $domain, $this->locale);
    }
  }

  public function sanitizeLocale(?string $locale = null): string
  {
    $locale = $this->removeTrailingNoiseOfLocale($locale);
    if ('' === $locale) {
      return $this->getLocaleFallback();
    }

    $locale = $this->normalizeLocaleFormatToLocaleWithUnderscore($locale);

    if ($this->isLocaleAValidLocaleWithUnderscore($locale)) {
      if (in_array($locale, $this->getSupportedLanguageCodes(), true)) {
        return $locale;
      }

      // Locale format is correct but the locale is not yet supported. Let's try without the regional code
      $locale = $this->mapLocaleWithUnderscoreToTwoLetterCode($locale);
    }

    if ($this->isLocaleAValidTwoLetterLocale($locale)) {
      // Two letter codes are not supported natively and must be mapped to an existing regional code

      return $this->mapTwoLetterCodeToLocaleWithUnderscore($locale);
    }

    // Format is definitely invalid; However we can just try the first two letter; Maybe we are lucky ;)
    $locale = strtolower(substr($locale, 0, 2));

    return $this->mapTwoLetterCodeToLocaleWithUnderscore($locale);
  }

  private function removeTrailingNoiseOfLocale(?string $locale): string
  {
    return explode(' ', trim($locale ?? ''))[0];
  }

  public function isLocaleAValidLocaleWithUnderscore(string $locale): bool
  {
    return 1 === preg_match('/^([a-z]{2,3})(_[a-z,A-Z]+)$/', $locale);
  }

  public function isLocaleAValidTwoLetterLocale(string $locale): bool
  {
    return 1 === preg_match('/^([a-z]{2,3})$/', $locale);
  }

  public function normalizeLocaleFormatToLocaleWithUnderscore(string $locale): string
  {
    return str_replace('-', '_', $locale);
  }

  public function mapLocaleWithUnderscoreToTwoLetterCode(string $locale_with_underscore): string
  {
    return explode('_', $locale_with_underscore)[0];
  }

  /**
   * There is no need to generate the mapping on every request.
   * In case new locales are added this mapping should be updated manually.
   *
   * Python code:
   * ```
   * import os
   * my_dir = os.listdir(r"PATH_TO_DIR_REPLACE!")
   * my_dir.sort()
   * for item in my_dir:
   *     locale_with_underscore = (item.split('.'))[1]
   *     two_letter_code = (locale_with_underscore.split('_'))[0]
   *     print("case '" + two_letter_code + "':")
   *     print("  return '" + locale_with_underscore + "';")
   * ```
   *
   * @return string locale_with_underscore
   */
  public function mapTwoLetterCodeToLocaleWithUnderscore(string $two_letter_code): string
  {
    return match ($two_letter_code) {
      'en' => 'en_UK',
      'pt' => 'pt_BR',
      'af' => 'af_ZA',
      'ar' => 'ar_SA',
      'az' => 'az_AZ',
      'bg' => 'bg_BG',
      'bn' => 'bn_BD',
      'bs' => 'bs_BA',
      'ca' => 'ca_ES',
      'chr' => 'chr_US',
      'cs' => 'cs_CZ',
      'da' => 'da_DK',
      'de' => 'de_DE',
      'el' => 'el_GR',
      'es' => 'es_ES',
      'fa' => 'fa_AF',
      'fi' => 'fi_FI',
      'fr' => 'fr_FR',
      'gl' => 'gl_ES',
      'gu' => 'gu_IN',
      'ha' => 'ha_HG',
      'he' => 'he_IL',
      'hi' => 'hi_IN',
      'hr' => 'hr_HR',
      'hu' => 'hu_HU',
      'id' => 'id_ID',
      'ig' => 'ig_NG',
      'it' => 'it_IT',
      'ja' => 'ja_JP',
      'ka' => 'ka_GE',
      'kab' => 'kab_KAB',
      'kk' => 'kk_KZ',
      'kn' => 'kn_IN',
      'ko' => 'ko_KR',
      'lt' => 'lt_LT',
      'mk' => 'mk_MK',
      'ml' => 'ml_IN',
      'ms' => 'ms_MY',
      'nl' => 'nl_NL',
      'no' => 'no_NO',
      'pl' => 'pl_PL',
      'ps' => 'ps_AF',
      'ro' => 'ro_RO',
      'ru' => 'ru_RU',
      'sd' => 'sd_PK',
      'si' => 'si_LK',
      'sk' => 'sk_SK',
      'sl' => 'sl_SI',
      'sq' => 'sq_AL',
      'sr' => 'sr_Latn',
      'sv' => 'sv_SE',
      'sw' => 'sw_KE',
      'ta' => 'ta_IN',
      'te' => 'te_IN',
      'th' => 'th_TH',
      'tl' => 'tl_PH',
      'tr' => 'tr_TR',
      'tw' => 'tw_TW',
      'uk' => 'uk_UA',
      'ur' => 'ur_PK',
      'uz' => 'uz_UZ',
      'vi' => 'vi_VN',
      'zh' => 'zh_CN',
      default => $this->getLocaleFallback(),
    };
  }

  /**
   * There is no need to query all Languages on every request. They change rarely.
   * In case new locales are added this mapping should be updated here.
   *
   * Python code:
   * ```
   * import os
   * my_dir = os.listdir(r"PATH_TO_DIR_REPLACE!")
   * my_dir.sort()
   * for item in my_dir:
   *     locale_with_underscore = (item.split('.'))[1]
   *     print("'" + locale_with_underscore + "',")
   * ```
   */
  public function getSupportedLanguageCodes(): array
  {
    return [
      'af_ZA',
      'ar_SA',
      'az_AZ',
      'bg_BG',
      'bn_BD',
      'bs_BA',
      'ca_ES',
      'chr_US',
      'cs_CZ',
      'da_DK',
      'de_DE',
      'el_GR',
      'en',
      'en_AU',
      'en_CA',
      'en_GB',
      'es_ES',
      'fa_AF',
      'fa_IR',
      'fi_FI',
      'fr_FR',
      'gl_ES',
      'gu_IN',
      'ha_HG',
      'he_IL',
      'hi_IN',
      'hr_HR',
      'hu_HU',
      'id_ID',
      'ig_NG',
      'it_IT',
      'ja_JP',
      'ka_GE',
      'kab_KAB',
      'kk_KZ',
      'kn_IN',
      'ko_KR',
      'lt_LT',
      'mk_MK',
      'ml_IN',
      'ms_MY',
      'nl_NL',
      'no_NO',
      'pl_PL',
      'ps_AF',
      'pt_BR',
      'pt_PT',
      'ro_RO',
      'ru_RU',
      'sd_PK',
      'si_LK',
      'sk_SK',
      'sl_SI',
      'sq_AL',
      'sr_Latn',
      'sr_SP',
      'sv_SE',
      'sw_KE',
      'ta_IN',
      'te_IN',
      'th_TH',
      'tl_PH',
      'tr_TR',
      'tw_TW',
      'uk_UA',
      'ur_PK',
      'uz_UZ',
      'vi_VN',
      'zh_CN',
      'zh_TW',
    ];
  }

  public function getLocale(): string
  {
    return $this->locale ?? $this->getLocaleFallback();
  }

  public function getLocaleFallback(): string
  {
    return 'en';
  }
}
