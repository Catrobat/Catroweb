<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Translation;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * Every locale must translate every English key, with the same placeholders.
 * A missing key silently falls back to English, which is how ~94 keys went untranslated in all locales.
 * See docs/contributor-guide/Translations.md.
 *
 * @internal
 */
#[CoversNothing]
class TranslationFilesTest extends TestCase
{
  private const string DIR = __DIR__.'/../../../translations/';

  /**
   * @return array<string, array{string}>
   */
  public static function locales(): array
  {
    $locales = [];
    foreach (glob(self::DIR.'catroweb.*.yaml') ?: [] as $file) {
      $locale = substr(basename($file, '.yaml'), strlen('catroweb.'));
      if ('en' !== $locale) {
        $locales[$locale] = [$locale];
      }
    }

    return $locales;
  }

  #[DataProvider('locales')]
  public function testLocaleHasExactlyTheEnglishKeys(string $locale): void
  {
    $english = self::messages('en');
    $translated = self::messages($locale);

    $this->assertSame([], array_keys(array_diff_key($english, $translated)), "Keys missing in catroweb.{$locale}.yaml (add them, AI translation is fine)");
    $this->assertSame([], array_keys(array_diff_key($translated, $english)), "Keys in catroweb.{$locale}.yaml that catroweb.en.yaml no longer has");
  }

  #[DataProvider('locales')]
  public function testLocaleKeepsThePlaceholders(string $locale): void
  {
    $translated = self::messages($locale);
    $wrong = [];
    foreach (self::messages('en') as $key => $english) {
      if (isset($translated[$key]) && self::placeholders($english) !== self::placeholders($translated[$key])) {
        $wrong[] = $key;
      }
    }

    $this->assertSame([], $wrong, "Placeholders like %name% differ from English in catroweb.{$locale}.yaml");
  }

  /**
   * @return array<string, string>
   */
  private static function messages(string $locale): array
  {
    return self::flatten((array) Yaml::parseFile(self::DIR."catroweb.{$locale}.yaml"));
  }

  /**
   * @param array<array-key, mixed> $messages
   *
   * @return array<string, string>
   */
  private static function flatten(array $messages, string $prefix = ''): array
  {
    $flat = [];
    foreach ($messages as $key => $value) {
      $key = '' === $prefix ? (string) $key : $prefix.'.'.$key;
      if (is_array($value)) {
        $flat += self::flatten($value, $key);
      } else {
        $flat[$key] = is_scalar($value) ? (string) $value : '';
      }
    }

    return $flat;
  }

  /**
   * @return list<string>
   */
  private static function placeholders(string $message): array
  {
    preg_match_all('/%[A-Za-z_]+%/', $message, $matches);
    $placeholders = array_values(array_unique($matches[0]));
    sort($placeholders);

    return $placeholders;
  }
}
