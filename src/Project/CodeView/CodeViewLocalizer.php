<?php

declare(strict_types=1);

namespace App\Project\CodeView;

/**
 * Puts Catroid's own translations into the code export's text. The export writes each of Catroid's strings as a
 * marker (player/scripts/export_code_resources.py): \x01 kind ":" name (\x1f argument)* \x02, kind s (string, the
 * arguments filled into its format specifiers), p (plural, the first argument its quantity) or a (array item, the
 * first argument its index). The translations come with the export, in strings/<Android locale>.json.
 */
class CodeViewLocalizer
{
  private const string MARKER = '/\x01([spa]):([^\x01\x02\x1f]*)((?:\x1f[^\x01\x02\x1f]*)*)\x02/';

  private const string FORMAT = '/%%|%n|%(\d+\$)?[-#+ 0,(]*\d*(?:\.\d+)?[a-zA-Z]/';

  /** Share's language codes that Android writes differently. */
  private const array ANDROID_LANGUAGES = ['he' => 'iw', 'id' => 'in', 'nb' => 'no', 'yi' => 'ji'];

  /** @var array<string, array{s: array<string, string>, p: array<string, array<string, string>>, a: array<string, list<string>>}> */
  private array $tables = [];

  public function __construct(private readonly CodeExportRunner $runner)
  {
  }

  public function localize(string $text, string $locale): string
  {
    if (!str_contains($text, "\x01")) {
      return $text;
    }

    $tables = [$this->table($this->androidLocale($locale)), $this->table('en')];
    // Innermost first: a marker's arguments can be markers themselves (a format argument that is a string).
    for ($i = 0; $i < 10 && str_contains($text, "\x01"); ++$i) {
      $text = (string) preg_replace_callback(
        self::MARKER,
        fn (array $m): string => $this->resolve($m[1], $m[2], '' === $m[3] ? [] : explode("\x1f", substr($m[3], 1)), $tables, $locale),
        $text
      );
    }

    return str_replace(["\x01", "\x02", "\x1f"], '', $text);
  }

  /**
   * Share's locale (de_DE, pt-BR, zh_CN, de) as Catroid's values folders name it (de, pt-rBR, zh-rCN).
   */
  public function androidLocale(string $locale): string
  {
    $parts = preg_split('/[-_]/', $locale) ?: [];
    $language = strtolower($parts[0] ?? '');
    $language = self::ANDROID_LANGUAGES[$language] ?? $language;
    $region = strtoupper($parts[1] ?? '');
    if ('' !== $region && $this->hasTable($language.'-r'.$region)) {
      return $language.'-r'.$region;
    }

    return $this->hasTable($language) ? $language : 'en';
  }

  /**
   * @param list<string>                                                                                                   $args
   * @param list<array{s: array<string, string>, p: array<string, array<string, string>>, a: array<string, list<string>>}> $tables
   */
  private function resolve(string $kind, string $name, array $args, array $tables, string $locale): string
  {
    if ('a' === $kind) {
      $index = (int) ($args[0] ?? 0);
      foreach ($tables as $table) {
        if (isset($table['a'][$name][$index])) {
          return $table['a'][$name][$index];
        }
      }

      return '';
    }

    if ('p' === $kind) {
      $quantity = (int) ($args[0] ?? 0);
      $category = $this->pluralCategory($quantity, $locale);
      foreach ($tables as $table) {
        $forms = $table['p'][$name] ?? null;
        if (null !== $forms) {
          return $this->format($forms[$category] ?? $forms['other'] ?? (string) reset($forms), [(string) $quantity]);
        }
      }

      return (string) $quantity;
    }

    foreach ($tables as $table) {
      if (isset($table['s'][$name])) {
        return $this->format($table['s'][$name], $args);
      }
    }

    return $name;
  }

  /**
   * Fills the arguments into a translation's format specifiers, in order or by their position (%2$s).
   *
   * @param list<string> $args
   */
  private function format(string $text, array $args): string
  {
    if ([] === $args) {
      return $text;
    }

    $next = 0;

    return (string) preg_replace_callback(self::FORMAT, static function (array $m) use ($args, &$next): string {
      if ('%%' === $m[0]) {
        return '%';
      }
      if ('%n' === $m[0]) {
        return "\n";
      }
      $index = '' !== ($m[1] ?? '') ? (int) $m[1] - 1 : $next++;

      return $args[$index] ?? $m[0];
    }, $text);
  }

  private function pluralCategory(int $quantity, string $locale): string
  {
    $category = \MessageFormatter::formatMessage(
      str_replace('-', '_', $locale),
      '{n, plural, zero{zero} one{one} two{two} few{few} many{many} other{other}}',
      ['n' => $quantity]
    );

    return is_string($category) ? $category : (1 === $quantity ? 'one' : 'other');
  }

  private function hasTable(string $android_locale): bool
  {
    return 1 === preg_match('/^[a-z]{2,3}(-r[A-Z]{2})?$/', $android_locale)
      && is_file($this->runner->getExportDir().'/strings/'.$android_locale.'.json');
  }

  /**
   * @return array{s: array<string, string>, p: array<string, array<string, string>>, a: array<string, list<string>>}
   */
  private function table(string $android_locale): array
  {
    if (!isset($this->tables[$android_locale])) {
      $table = [];
      if ($this->hasTable($android_locale)) {
        try {
          $table = json_decode((string) file_get_contents($this->runner->getExportDir().'/strings/'.$android_locale.'.json'), true, 16, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
          // an unreadable translation: English instead
        }
      }
      $this->tables[$android_locale] = [
        's' => is_array($table['s'] ?? null) ? $table['s'] : [],
        'p' => is_array($table['p'] ?? null) ? $table['p'] : [],
        'a' => is_array($table['a'] ?? null) ? $table['a'] : [],
      ];
    }

    return $this->tables[$android_locale];
  }
}
