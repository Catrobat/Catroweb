<?php

declare(strict_types=1);

namespace App\Project\CodeView;

final class CodeViewTranslatedNames
{
  /**
   * Reserve original names, including names outside this job's budget. A project-wide name is visible in every
   * sprite. Names local to different sprites can have the same translation.
   *
   * @param array<string, mixed>  $export
   * @param array<string, string> $updates Segment key to translated text
   *
   * @return list<array{key: string, text: string}>
   */
  public static function updates(array $export, array $updates): array
  {
    $symbols = [];
    $originals = [];
    $aliases = [];
    $declarations = $export['symbols'] ?? [];
    foreach (is_array($declarations) ? $declarations : [] as $symbol) {
      if (!is_array($symbol) || !is_string($symbol['id'] ?? null) || !is_string($symbol['scope'] ?? null)
        || !is_string($symbol['name'] ?? null) || !in_array($symbol['kind'] ?? null, ['variable', 'list'], true)) {
        continue;
      }

      ['id' => $id, 'scope' => $scope, 'kind' => $kind, 'name' => $name] = $symbol;
      $symbols[$id] = $symbol;
      $originals[self::nameKey($kind, $name)][$scope][$id] = true;
      if (isset($updates[$id])) {
        $alias = trim($updates[$id]);
        if ('' === $alias || $alias === $name || preg_match('/[\r\n]/', $alias)) {
          unset($updates[$id]);
        } else {
          $updates[$id] = $alias;
          $aliases[self::nameKey($kind, $alias)][$scope][$id] = true;
        }
      }
    }

    $result = [];
    foreach ($updates as $id => $text) {
      if (isset($symbols[$id])) {
        $symbol = $symbols[$id];
        $name = self::nameKey($symbol['kind'], $text);
        if (self::collides($originals[$name] ?? [], $symbol['scope'], $id)
          || self::collides($aliases[$name] ?? [], $symbol['scope'], $id)) {
          continue;
        }
      } elseif (str_starts_with($id, 'name-')) {
        continue; // A reference without a declaration cannot be shown safely as an alias.
      }
      $result[] = ['key' => $id, 'text' => $text];
    }

    return $result;
  }

  private static function nameKey(string $kind, string $name): string
  {
    return $kind.':'.mb_strtolower(\Normalizer::normalize(trim($name)) ?: trim($name));
  }

  /** @param array<string, array<string, true>> $owners */
  private static function collides(array $owners, string $owner, string $id): bool
  {
    $local = $owners[$owner] ?? [];
    if (count($local) > (isset($local[$id]) ? 1 : 0)) {
      return true;
    }

    return 'project' === $owner
      ? count($owners) > (isset($owners['project']) ? 1 : 0)
      : isset($owners['project']);
  }
}
