<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project\Scratch;

use App\Project\Scratch\Converter\AssetNormalizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/**
 * @internal
 */
#[CoversClass(AssetNormalizer::class)]
final class AssetNormalizerTest extends TestCase
{
  public function testLongSoundsBecomeMp3AndShortOnesStayWav(): void
  {
    $sox = new Process(['sox', '-h']);
    $sox->run();
    if (!str_contains($sox->getOutput(), ' mp3 ')) {
      $this->markTestSkipped('sox has no MP3 support (libsox-fmt-mp3)');
    }

    $normalizer = new AssetNormalizer();
    $deadline = microtime(true) + 30.0;

    [$long, $extension] = $normalizer->playableSound(self::pcmWav(4), ['dataFormat' => 'wav'], $deadline);
    $this->assertSame('mp3', $extension);
    $this->assertLessThan(\strlen(self::pcmWav(4)) / 4, \strlen($long));

    $short = self::pcmWav(1);
    $this->assertSame([$short, 'wav'], $normalizer->playableSound($short, ['dataFormat' => 'wav'], $deadline));
  }

  /** A 440 Hz tone as 16-bit mono PCM WAV at 44.1 kHz. */
  private static function pcmWav(int $seconds): string
  {
    $samples = '';
    for ($i = 0; $i < 44_100 * $seconds; ++$i) {
      $samples .= pack('v', (int) (8000.0 * sin(2.0 * M_PI * 440.0 * (float) $i / 44_100.0)) & 0xFFFF);
    }

    return 'RIFF'.pack('V', 36 + \strlen($samples)).'WAVEfmt '.pack('VvvVVvv', 16, 1, 1, 44_100, 88_200, 2, 16).'data'.pack('V', \strlen($samples)).$samples;
  }
}
