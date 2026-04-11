<?php

declare(strict_types=1);

namespace App\Api\Traits;

trait CursorPaginationTrait
{
  protected function decodeCursorToOffset(?string $cursor): int
  {
    if (null === $cursor || '' === $cursor) {
      return 0;
    }

    $decoded = base64_decode($cursor, true);
    if (false === $decoded) {
      return 0;
    }

    return max(0, (int) $decoded);
  }

  protected function encodeCursorFromOffset(int $offset, int $count): ?string
  {
    if ($count <= 0) {
      return null;
    }

    return base64_encode((string) ($offset + $count));
  }
}
