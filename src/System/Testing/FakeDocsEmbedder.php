<?php

declare(strict_types=1);

namespace App\System\Testing;

use App\Docs\Search\DocsEmbedder;

/**
 * Deterministic stand-in for the embedding model, so the tests prove the hybrid search without it: words of one
 * meaning (jump, springen, y, ...) share a dimension, every other word gets a small hashed one. "make my cat jump"
 * is thus close to a brick that "moves the object up", which has no word in common with it.
 */
class FakeDocsEmbedder extends DocsEmbedder
{
  private const array MEANINGS = [
    ['jump', 'jumps', 'jumping', 'springen', 'springt', 'hop', 'hüpfen', 'y'],
  ];

  /** Texts sent to the "model" so far, so a test can see which were embedded again. */
  public int $embedded = 0;

  #[\Override]
  public function isEnabled(): bool
  {
    return true;
  }

  #[\Override]
  protected function embed(array $inputs, float $timeout): array
  {
    $this->embedded += \count($inputs);

    return array_map(self::vector(...), $inputs);
  }

  /** @return list<float> */
  private static function vector(string $text): array
  {
    $vector = array_fill(0, self::DIMS, 0.0);
    $vector[self::DIMS - 1] = 0.05; // never all zero: ES refuses a zero vector for cosine
    preg_match_all('/\p{L}+/u', mb_strtolower(preg_replace('/^(query|passage): /', '', $text) ?? ''), $words);
    foreach ($words[0] as $word) {
      $meaning = null;
      foreach (self::MEANINGS as $i => $synonyms) {
        $meaning = \in_array($word, $synonyms, true) ? $i : $meaning;
      }
      if (null === $meaning) {
        $vector[\count(self::MEANINGS) + crc32($word) % (self::DIMS - \count(self::MEANINGS) - 1)] += 0.1;
      } else {
        $vector[$meaning] += 1.0;
      }
    }
    $norm = sqrt(array_sum(array_map(static fn (float $x): float => $x * $x, $vector)));

    return array_values(array_map(static fn (float $x): float => $x / $norm, $vector));
  }
}
