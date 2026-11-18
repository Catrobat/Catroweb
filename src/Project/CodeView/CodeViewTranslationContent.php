<?php

declare(strict_types=1);

namespace App\Project\CodeView;

final class CodeViewTranslationContent
{
  /** @param array<string, mixed> $segment */
  public static function key(array $segment): ?string
  {
    $source = $segment['source'] ?? null;

    return 'string' === ($segment['kind'] ?? null) && is_string($source) && '' !== trim($source)
      ? hash('sha256', $source)
      : null;
  }

  /**
   * @param array<array-key, mixed> $export
   *
   * @return array<string, string> Translation key to source text, in project order
   */
  public static function texts(array $export): array
  {
    $texts = [];
    foreach (self::segments($export) as $segment) {
      $key = self::key($segment);
      if (null !== $key) {
        $texts[$key] = (string) $segment['source'];
      }
    }

    return $texts;
  }

  /**
   * @param array<array-key, mixed> $node
   *
   * @return \Generator<int, array<string, mixed>>
   */
  private static function segments(array $node): \Generator
  {
    foreach ($node as $key => $value) {
      if (!is_array($value)) {
        continue;
      }
      if ('segments' === $key) {
        foreach ($value as $item) {
          if (is_array($item)) {
            yield $item;
          }
        }
      } else {
        yield from self::segments($value);
      }
    }
  }
}
