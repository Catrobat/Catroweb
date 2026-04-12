<?php

declare(strict_types=1);

namespace App\Api\Traits;

trait KeysetCursorTrait
{
  /**
   * Decode a keyset cursor of format: base64("value|id").
   *
   * @return array{value: string, id: string}|null null if cursor is empty/invalid
   */
  protected function decodeKeysetCursor(?string $cursor): ?array
  {
    if (null === $cursor || '' === $cursor) {
      return null;
    }

    $decoded = base64_decode($cursor, true);
    if (false === $decoded) {
      return null;
    }

    $parts = explode('|', $decoded, 2);
    if (2 !== count($parts) || '' === $parts[0] || '' === $parts[1]) {
      return null;
    }

    return ['value' => $parts[0], 'id' => $parts[1]];
  }

  /**
   * Encode a keyset cursor from a sort value and an ID.
   */
  protected function encodeKeysetCursor(string $value, string $id): string
  {
    return base64_encode($value.'|'.$id);
  }

  /**
   * Decode a keyset cursor where the value is a datetime string.
   *
   * @return array{date: \DateTimeImmutable, id: string}|null
   */
  protected function decodeDateKeysetCursor(?string $cursor): ?array
  {
    $data = $this->decodeKeysetCursor($cursor);
    if (null === $data) {
      return null;
    }

    $date = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s.uP', $data['value']);
    if (false === $date) {
      $date = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i:sP', $data['value']);
    }

    if (false === $date) {
      return null;
    }

    return ['date' => $date, 'id' => $data['id']];
  }

  /**
   * Encode a keyset cursor from a datetime and ID.
   */
  protected function encodeDateKeysetCursor(\DateTimeInterface $date, string $id): string
  {
    $utc = \DateTimeImmutable::createFromInterface($date)->setTimezone(new \DateTimeZone('UTC'));

    return $this->encodeKeysetCursor($utc->format('Y-m-d\TH:i:s.uP'), $id);
  }

  /**
   * Decode a keyset cursor where the value is an integer.
   *
   * @return array{value: int, id: string}|null
   */
  protected function decodeIntKeysetCursor(?string $cursor): ?array
  {
    $data = $this->decodeKeysetCursor($cursor);
    if (null === $data) {
      return null;
    }

    if (!ctype_digit($data['value']) && !str_starts_with($data['value'], '-')) {
      return null;
    }

    return ['value' => (int) $data['value'], 'id' => $data['id']];
  }

  /**
   * Encode a keyset cursor from an integer value and ID.
   */
  protected function encodeIntKeysetCursor(int $value, string $id): string
  {
    return $this->encodeKeysetCursor((string) $value, $id);
  }
}
