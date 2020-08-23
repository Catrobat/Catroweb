<?php

namespace Tests\phpUnit\Catrobat\Services;

use App\Catrobat\Services\TokenGenerator;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers  \App\Catrobat\Services\TokenGenerator
 */
class TokenGeneratorTest extends TestCase
{
  private TokenGenerator $token_generator;

  protected function setUp(): void
  {
    $this->token_generator = new TokenGenerator();
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(TokenGenerator::class, $this->token_generator);
  }

  public function testGeneratesAString(): void
  {
    $generated_token = $this->token_generator->generateToken();
    $this->assertIsString($generated_token);
  }

  public function testGeneratesADifferentTokenEachTime(): void
  {
    $generated_tokens = [];
    for ($i = 0; $i < 100; ++$i)
    {
      $generated_token = $this->token_generator->generateToken();
      $generated_tokens[] = $generated_token;
    }
    Assert::assertCount(100, array_unique($generated_tokens));
  }

  public function testGeneratesATokenWithALengthOf32(): void
  {
    $generated_token = $this->token_generator->generateToken();
    Assert::assertEquals(32, strlen($generated_token));
  }

  public function getMatchers(): array
  {
    return [
      'haveLength' => function ($subject, $key)
      {
        return strlen($subject) === $key;
      },
    ];
  }
}
