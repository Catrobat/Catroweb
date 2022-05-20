<?php

namespace Tests\PhpUnit\Api\Services\Base;

use App\Api\Exceptions\ApiException;
use App\Api\Services\Base\PandaAuthenticationTrait;
use App\System\Testing\PhpUnit\DefaultTestCase;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 * @covers \Tests\PhpUnit\Api\Services\Base\PandaAuthenticationTraitTestClass
 */
final class PandaAuthenticationTraitTest extends DefaultTestCase
{
  protected PandaAuthenticationTraitTestClass|MockObject $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockForTrait(PandaAuthenticationTrait::class);
  }

  /**
   * @group integration
   * @small
   */
  public function testTestTraitExists(): void
  {
    $this->assertTrue(trait_exists(PandaAuthenticationTrait::class));
  }

  /**
   * @group unit
   * @small
   * @covers \App\Api\Services\Base\PandaAuthenticationTrait::setPandaAuth
   * @dataProvider dataProviderPandaAuth
   *
   * @param mixed $value
   *
   * @throws Exception
   */
  public function testSetPandaAuth($value, bool $expect_exception, string $expected = ''): void
  {
    if ($expect_exception) {
      $this->expectException(ApiException::class);
    }

    $this->object->setPandaAuth($value);

    $this->assertEquals($expected, $this->object->getAuthenticationToken());
  }

  public function dataProviderPandaAuth(): array
  {
    return [
      'Empty' => [
        'value' => '',
        'expect_exception' => true,
      ],
      'null' => [
        'value' => null,
        'expect_exception' => true,
      ],
      'no token prefix' => [
        'value' => 'myToken',
        'expect_exception' => true,
      ],
      'Valid' => [
        'value' => 'bearer myToken',
        'expect_exception' => false,
        'expected' => 'myToken',
      ],
      'Valid 2' => [
        'value' => 'bearer Abcdefghijklmnopqrstuvwxyz1234567890',
        'expect_exception' => false,
        'expected' => 'Abcdefghijklmnopqrstuvwxyz1234567890',
      ],
    ];
  }
}
