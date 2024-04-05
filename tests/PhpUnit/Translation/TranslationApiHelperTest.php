<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Translation;

use App\Translation\TranslationApiHelper;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \App\Translation\TranslationApiHelper
 */
class TranslationApiHelperTest extends TestCase
{
  private const LONG_LANGUAGE_CODE = [
    'aa-bb',
    'cc-dd',
  ];

  private TranslationApiHelper $helper;

  protected function setUp(): void
  {
    $this->helper = new TranslationApiHelper(self::LONG_LANGUAGE_CODE);
  }

  public function testDetectLanguageCode(): void
  {
    $this->assertnull($this->helper->transformLanguageCode(null));
  }

  public function testShortenLanguageCode(): void
  {
    $this->assertEquals('zz', $this->helper->transformLanguageCode('zz-zz'));
  }

  public function testShortLanguageCode(): void
  {
    $this->assertEquals('zz', $this->helper->transformLanguageCode('zz'));
  }

  public function testLongLanguageCode(): void
  {
    $this->assertEquals('aa-bb', $this->helper->transformLanguageCode('aa-bb'));
    $this->assertEquals('cc-dd', $this->helper->transformLanguageCode('cc-dd'));
  }
}
