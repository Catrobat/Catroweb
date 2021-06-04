<?php

namespace Tests\phpUnit\CatrobatCodeParserTests;

use App\Catrobat\CatrobatCode\Parser\CodeStatistic;
use App\Catrobat\CatrobatCode\Parser\ParsedScene;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class CategoriesTest extends TestCase
{
  protected function setUp(): void
  {
  }

  /**
   * @test
   */
  public function mustDetectAllControlBricks(): void
  {
    $category = 'control';
    $stats = $this->loadStatistics($category);

    $this->assertBrickCount($category, 19, 15, $stats);
  }

  /**
   * @test
   */
  public function mustDetectAllDataBricks(): void
  {
    $category = 'data';
    $stats = $this->loadStatistics($category);

    $this->assertBrickCount($category, 16, 16, $stats);
  }

  /**
   * @test
   */
  public function mustDetectAllLookBricks(): void
  {
    $category = 'looks';
    $stats = $this->loadStatistics($category);

    $this->assertBrickCount($category, 31, 29, $stats);
  }

  private function loadStatistics(string $category): CodeStatistic
  {
    $xml = simplexml_load_file(__DIR__.'/Resources/ValidPrograms/CategoryPrograms/'.$category.'.xml');
    self::assertNotFalse($xml);
    $code_statistic = new CodeStatistic();
    $code_statistic->update(new ParsedScene($xml->xpath('//scene')[0]));

    return $code_statistic;
  }

  private function assertBrickCount(string $category, int $expected_count, int $expected_different_count, CodeStatistic $code_statistic): void
  {
    $brick_statistic = $code_statistic->getBrickTypeStatistic()[$category.'Bricks'];
    self::assertEquals($expected_count, $brick_statistic['numTotal']);
    self::assertEquals($expected_different_count, $brick_statistic['different']['numDifferent']);
    self::assertEquals($expected_count + $code_statistic->getScriptStatistic(), $code_statistic->getBrickStatistic());
  }
}
