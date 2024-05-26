<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api\Services\Base;

use App\Api\Services\Base\TranslatorAwareTrait;
use App\System\Testing\PhpUnit\DefaultTestCase;
use Behat\Behat\Definition\Translator\Translator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 *
 * @covers \Tests\PhpUnit\Api\Services\Base\TranslatorAwareTraitTestClass
 */
final class TranslatorAwareTraitTest extends DefaultTestCase
{
  protected MockObject|TranslatorAwareTraitTestClass $object;

  #[\Override]
  protected function setUp(): void
  {
    $this->object = $this->getMockForAbstractClass(TranslatorAwareTraitTestClass::class);
  }

  /**
   * @group integration
   *
   * @small
   */
  public function testTestTraitExists(): void
  {
    $this->assertTrue(trait_exists(TranslatorAwareTrait::class));
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers       \App\Api\Services\Base\TranslatorAwareTrait::__
   */
  public function test(): void
  {
    $this->object = $this->getMockBuilder(TranslatorAwareTraitTestClass::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['trans'])
      ->getMockForAbstractClass()
    ;

    $this->object->expects($this->once())->method('trans');
    $this->object->__('id');
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers       \App\Api\Services\Base\TranslatorAwareTrait::trans
   */
  public function testTransSuccess(): void
  {
    $translator = $this->createMock(Translator::class);
    $translator->expects($this->once())->method('trans')->willReturn('en');
    $this->object = $this->getMockBuilder(TranslatorAwareTraitTestClass::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['sanitizeLocale'])
      ->getMockForAbstractClass()
    ;
    $this->object->expects($this->once())->method('sanitizeLocale');
    $this->object->initTranslator($translator);
    $this->object->trans('id');
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers       \App\Api\Services\Base\TranslatorAwareTrait::trans
   */
  public function testTransFailureHandling(): void
  {
    $translator = $this->createMock(Translator::class);
    $translator->expects($this->exactly(2))->method('trans')->will(
      $this->onConsecutiveCalls(
        $this->throwException(new \Exception()),
        'en',
      )
    );
    $this->object = $this->getMockBuilder(TranslatorAwareTraitTestClass::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['sanitizeLocale', 'getLocaleFallback'])
      ->getMockForAbstractClass()
    ;
    $this->object->expects($this->once())->method('sanitizeLocale');
    $this->object->expects($this->once())->method('getLocaleFallback');
    $this->object->initTranslator($translator);
    $this->object->trans('id');
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers       \App\Api\Services\Base\TranslatorAwareTrait::sanitizeLocale
   */
  #[DataProvider('provideSanitizeLocaleData')]
  public function testSanitizeLocale(?string $input, string $expected): void
  {
    $this->assertSame(
      $expected,
      $this->object->sanitizeLocale($input)
    );
  }

  public static function provideSanitizeLocaleData(): array
  {
    return [
      [null, 'en'],
      ['', 'en'],
      ['en_UK', 'en_UK'],
      ['en-UK', 'en_UK'],
      ['en-UK noise123424', 'en_UK'],
      ['de', 'de_DE'],
      ['de', 'de_DE'],
      ['de_DE-DE', 'de_DE'],
      ['de_De', 'de_DE'],
      ['de_AT', 'de_DE'],
      ['DE', 'de_DE'],
    ];
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers       \App\Api\Services\Base\TranslatorAwareTrait::isLocaleAValidLocaleWithUnderscore
   */
  #[DataProvider('provideIsLocaleAValidLocaleWithUnderscoreData')]
  public function testIsLocaleAValidLocaleWithUnderscore(string $input, bool $expected): void
  {
    $this->assertSame(
      $expected,
      $this->object->isLocaleAValidLocaleWithUnderscore($input)
    );
  }

  public static function provideIsLocaleAValidLocaleWithUnderscoreData(): array
  {
    return [
      ['de_DE', true],
      ['kab_KAB', true],
      ['en-UK', false],
      ['', false],
      ['en_DE_DE', false],
      ['en_uk', true],
      ['en_', false],
      ['en', false],
    ];
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers       \App\Api\Services\Base\TranslatorAwareTrait::isLocaleAValidTwoLetterLocale
   */
  #[DataProvider('provideIsLocaleAValidTwoLetterLocaleData')]
  public function testIsLocaleAValidTwoLetterLocale(string $input, bool $expected): void
  {
    $this->assertSame(
      $expected,
      $this->object->isLocaleAValidTwoLetterLocale($input)
    );
  }

  public static function provideIsLocaleAValidTwoLetterLocaleData(): array
  {
    return [
      ['en-UK', false],
      ['', false],
      ['de_DE', false],
      ['EN', false],
      ['en1', false],
      ['en', true],
      ['kab', true],
    ];
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers       \App\Api\Services\Base\TranslatorAwareTrait::normalizeLocaleFormatToLocaleWithUnderscore
   */
  #[DataProvider('provideMapLocaleToLocaleWithUnderscoreData')]
  public function testMapLocaleToLocaleWithUnderscore(string $input, string $expected): void
  {
    $this->assertSame(
      $expected,
      $this->object->normalizeLocaleFormatToLocaleWithUnderscore($input)
    );
  }

  public static function provideMapLocaleToLocaleWithUnderscoreData(): array
  {
    return [
      'simple' => ['en-UK', 'en_UK'],
      'simple (2)' => ['de-AT', 'de_AT'],
    ];
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers       \App\Api\Services\Base\TranslatorAwareTrait::mapLocaleWithUnderscoreToTwoLetterCode
   */
  #[DataProvider('provideMapLocaleWithUnderscoreToTwoLetterCodeData')]
  public function testMapLocaleWithUnderscoreToTwoLetterCode(string $input, string $expected): void
  {
    $this->assertSame(
      $expected,
      $this->object->mapLocaleWithUnderscoreToTwoLetterCode($input)
    );
  }

  public static function provideMapLocaleWithUnderscoreToTwoLetterCodeData(): array
  {
    return [
      'simple' => ['en_UK', 'en'],
      'simple (2)' => ['de_AT', 'de'],
    ];
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers       \App\Api\Services\Base\TranslatorAwareTrait::mapTwoLetterCodeToLocaleWithUnderscore
   */
  #[DataProvider('provideMapTwoLetterCodeToLocaleWithUnderscoreData')]
  public function testMapTwoLetterCodeToLocaleWithUnderscore(string $input, string $expected): void
  {
    $this->assertSame(
      $expected,
      $this->object->mapTwoLetterCodeToLocaleWithUnderscore($input)
    );
  }

  public static function provideMapTwoLetterCodeToLocaleWithUnderscoreData(): array
  {
    return [
      'empty' => ['', 'en'],
      'invalid' => ['invalid', 'en'],
      'default' => ['de', 'de_DE'],
      'custom' => ['en', 'en_UK'],
      'custom (2)' => ['pt', 'pt_BR'],
    ];
  }
}
