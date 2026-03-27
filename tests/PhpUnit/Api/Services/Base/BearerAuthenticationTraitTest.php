<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api\Services\Base;

use App\Api\Exceptions\ApiException;
use App\Api\Services\Base\BearerAuthenticationTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 */
#[CoversClass(BearerAuthenticationTraitTestClass::class)]
final class BearerAuthenticationTraitTest extends TestCase
{
  protected BearerAuthenticationTraitTestClass $object;

  #[\Override]
  protected function setUp(): void
  {
    $this->object = new BearerAuthenticationTraitTestClass();
  }

  #[\Override]
  protected function tearDown(): void
  {
    $this->object->setCurrentRequest(null);
  }

  #[Group('integration')]
  public function testTestTraitExists(): void
  {
    $this->assertTrue(trait_exists(BearerAuthenticationTrait::class));
  }

  /**
   * @throws \Exception
   */
  #[Group('unit')]
  #[DataProvider('provideBearerAuthData')]
  public function testSetBearerAuth(?string $value, bool $expect_exception, string $expected = ''): void
  {
    if ($expect_exception) {
      $this->expectException(ApiException::class);
    }

    $this->object->setBearerAuth($value);

    $this->assertEquals($expected, $this->object->getAuthenticationToken());
  }

  public static function provideBearerAuthData(): array
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

  /**
   * @throws \Exception
   */
  #[Group('unit')]
  public function testSetBearerAuthFallsBackToBearerCookie(): void
  {
    $request = Request::create('/api/projects', 'GET');
    $request->cookies->set('BEARER', 'cookie-token');
    $this->object->setCurrentRequest($request);

    $this->object->setBearerAuth(null);

    $this->assertSame('cookie-token', $this->object->getAuthenticationToken());
  }
}
