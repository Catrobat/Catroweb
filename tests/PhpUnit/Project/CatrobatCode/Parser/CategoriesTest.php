<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project\CatrobatCode\Parser;

use App\Project\CatrobatCode\Parser\CodeStatistic;
use App\Project\CatrobatCode\Parser\ParsedScene;
use App\System\Testing\PhpUnit\Extension\BootstrapExtension;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class CategoriesTest extends TestCase
{
  protected function setUp(): void
  {
  }

  public function testMustDetectAllControlBricks(): void
  {
    $category = 'control';
    $stats = $this->loadStatistics($category);

    $this->assertBrickCount($category, 19, 15, $stats);
  }

  public function testMustDetectAllDataBricks(): void
  {
    $category = 'data';
    $stats = $this->loadStatistics($category);

    $this->assertBrickCount($category, 16, 16, $stats);
  }

  public function testMustDetectAllLookBricks(): void
  {
    $category = 'looks';
    $stats = $this->loadStatistics($category);

    $this->assertBrickCount($category, 31, 29, $stats);
  }

  private function loadStatistics(string $category): CodeStatistic
  {
    $xml = simplexml_load_file(BootstrapExtension::$FIXTURES_DIR.'ValidPrograms/CategoryPrograms/'.$category.'.xml');
    $this->assertInstanceOf(\SimpleXMLElement::class, $xml);
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
