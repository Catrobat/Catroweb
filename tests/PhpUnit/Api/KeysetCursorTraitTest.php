<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api;

use App\Api\Traits\KeysetCursorTrait;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \App\Api\Traits\KeysetCursorTrait
 *
 * @internal
 */
class KeysetCursorTraitTest extends TestCase
{
  use KeysetCursorTrait;

  public function testDecodeKeysetCursorNull(): void
  {
    $this->assertNull($this->decodeKeysetCursor(null));
  }

  public function testDecodeKeysetCursorEmpty(): void
  {
    $this->assertNull($this->decodeKeysetCursor(''));
  }

  public function testDecodeKeysetCursorInvalidBase64(): void
  {
    $this->assertNull($this->decodeKeysetCursor('!!!'));
  }

  public function testDecodeKeysetCursorMissingPipe(): void
  {
    $this->assertNull($this->decodeKeysetCursor(base64_encode('nopipe')));
  }

  public function testDecodeKeysetCursorEmptyParts(): void
  {
    $this->assertNull($this->decodeKeysetCursor(base64_encode('|id')));
    $this->assertNull($this->decodeKeysetCursor(base64_encode('value|')));
  }

  public function testDecodeKeysetCursorValid(): void
  {
    $cursor = base64_encode('somevalue|abc123');
    $result = $this->decodeKeysetCursor($cursor);
    $this->assertSame(['value' => 'somevalue', 'id' => 'abc123'], $result);
  }

  public function testEncodeKeysetCursor(): void
  {
    $cursor = $this->encodeKeysetCursor('myval', 'myid');
    $this->assertSame(base64_encode('myval|myid'), $cursor);
  }

  public function testEncodeDecodeRoundtrip(): void
  {
    $cursor = $this->encodeKeysetCursor('value123', 'id456');
    $decoded = $this->decodeKeysetCursor($cursor);
    $this->assertNotNull($decoded);
    $this->assertSame('value123', $decoded['value']);
    $this->assertSame('id456', $decoded['id']);
  }

  public function testDecodeDateKeysetCursorNull(): void
  {
    $this->assertNull($this->decodeDateKeysetCursor(null));
  }

  public function testDecodeDateKeysetCursorInvalid(): void
  {
    $this->assertNull($this->decodeDateKeysetCursor(base64_encode('notadate|id')));
  }

  public function testDecodeDateKeysetCursorValid(): void
  {
    $date = new \DateTimeImmutable('2024-06-15T10:30:00.000000+00:00');
    $cursor = $this->encodeDateKeysetCursor($date, 'proj-123');
    $result = $this->decodeDateKeysetCursor($cursor);

    $this->assertNotNull($result);
    $this->assertSame('proj-123', $result['id']);
    $this->assertSame('2024-06-15', $result['date']->format('Y-m-d'));
    $this->assertSame('10:30:00', $result['date']->format('H:i:s'));
  }

  public function testDecodeDateKeysetCursorWithTimezone(): void
  {
    $date = new \DateTimeImmutable('2024-06-15T12:30:00', new \DateTimeZone('Europe/Berlin'));
    $cursor = $this->encodeDateKeysetCursor($date, 'id-1');
    $result = $this->decodeDateKeysetCursor($cursor);

    $this->assertNotNull($result);
    // Should be converted to UTC
    $this->assertSame('10:30:00', $result['date']->format('H:i:s'));
  }

  public function testDecodeIntKeysetCursorNull(): void
  {
    $this->assertNull($this->decodeIntKeysetCursor(null));
  }

  public function testDecodeIntKeysetCursorInvalidValue(): void
  {
    $this->assertNull($this->decodeIntKeysetCursor(base64_encode('notanint|id')));
  }

  public function testDecodeIntKeysetCursorValid(): void
  {
    $cursor = $this->encodeIntKeysetCursor(42, 'feat-5');
    $result = $this->decodeIntKeysetCursor($cursor);

    $this->assertNotNull($result);
    $this->assertSame(42, $result['value']);
    $this->assertSame('feat-5', $result['id']);
  }

  public function testDecodeIntKeysetCursorZero(): void
  {
    $cursor = $this->encodeIntKeysetCursor(0, 'id-0');
    $result = $this->decodeIntKeysetCursor($cursor);

    $this->assertNotNull($result);
    $this->assertSame(0, $result['value']);
    $this->assertSame('id-0', $result['id']);
  }

  public function testDecodeKeysetCursorPreservesSpecialChars(): void
  {
    // Test that pipe in ID is handled correctly (only first pipe splits)
    $cursor = base64_encode('value|id|with|pipes');
    $result = $this->decodeKeysetCursor($cursor);
    $this->assertNotNull($result);
    $this->assertSame('value', $result['value']);
    $this->assertSame('id|with|pipes', $result['id']);
  }
}
