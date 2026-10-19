<?php

declare(strict_types=1);

namespace App\Utils;

use FOS\ElasticaBundle\Finder\TransformedFinder;

final class ElasticsearchPagination
{
  /** Elasticsearch's default index.max_result_window: it rejects any from + size beyond it. */
  public const int MAX_RESULT_WINDOW = 10000;

  /**
   * Offset-paginated find that never asks for hits past the result window.
   * A cursor pointing beyond the window yields an empty page instead of an Elasticsearch error.
   */
  public static function find(TransformedFinder $finder, mixed $query, ?int $limit, int $offset): array
  {
    $size = min($limit ?? 10, self::MAX_RESULT_WINDOW - $offset);
    if ($size <= 0) {
      return [];
    }

    return $finder->find($query, $size, ['from' => $offset]);
  }
}
