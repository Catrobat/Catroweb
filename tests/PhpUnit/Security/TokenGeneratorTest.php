<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Security;

use App\Security\TokenGenerator;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(TokenGenerator::class)]
class TokenGeneratorTest extends TestCase
{
  private TokenGenerator $token_generator;

  #[\Override]
  protected function setUp(): void
  {
    $this->token_generator = new TokenGenerator();
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(TokenGenerator::class, $this->token_generator);
  }

  /**
   * @throws \Exception
   */
  public function testGeneratesADifferentTokenEachTime(): void
  {
    $generated_tokens = [];
    for ($i = 0; $i < 100; ++$i) {
      $generated_token = $this->token_generator->generateToken();
      $generated_tokens[] = $generated_token;
    }

    Assert::assertCount(100, array_unique($generated_tokens));
  }

  /**
   * @throws \Exception
   */
  public function testGeneratesATokenWithALengthOf32(): void
  {
    $generated_token = $this->token_generator->generateToken();
    Assert::assertEquals(32, strlen($generated_token));
  }

  public function getMatchers(): array
  {
    return [
      'haveLength' => static fn ($subject, $key): bool => strlen((string) $subject) === $key,
    ];
  }
}
