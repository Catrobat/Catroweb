<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Moderation;

use App\Moderation\TextSanitizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(TextSanitizer::class)]
final class TextSanitizerTest extends TestCase
{
  #[Group('unit')]
  public function testSanitizeMasksHighConfidenceTermsAndInvisibleObfuscation(): void
  {
    $input = "f\u{200B}uck and nigga";

    $this->assertSame('**** and *****', TextSanitizer::sanitize($input));
  }

  #[Group('unit')]
  public function testSanitizeRedactsContactVectors(): void
  {
    $input = 'Mail me at kid@example.com or join discord.gg/catroweb';

    $this->assertSame(
      'Mail me at [contact removed] or join [contact removed]',
      TextSanitizer::sanitize($input)
    );
  }

  #[Group('unit')]
  public function testSanitizeAvoidsClassicFalsePositives(): void
  {
    $this->assertSame('I live in Scunthorpe.', TextSanitizer::sanitize('I live in Scunthorpe.'));
  }
}
