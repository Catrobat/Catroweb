<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project\CatrobatCode\Parser;

use App\Project\CatrobatCode\Parser\Constants;
use App\Project\CatrobatCode\Parser\FormulaResolver;
use App\System\Testing\PhpUnit\Extension\BootstrapExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(FormulaResolver::class)]
class FormulaResolverTest extends TestCase
{
  #[DataProvider('provideFormulaData')]
  public function testMustResolveAllFormulas(mixed $formula_list_xml_properties, mixed $categories, mixed $expected): void
  {
    $actual = FormulaResolver::resolve($formula_list_xml_properties);
    foreach ($categories as $category) {
      $this->assertEquals($expected[$category], $actual[$category]);
    }
  }

  /**
   * @return array[]
   */
  public static function provideFormulaData(): array
  {
    $data = [];

    $xml_properties = simplexml_load_file(BootstrapExtension::$FIXTURES_DIR.'ValidPrograms/AllFormulaProgram/code.xml');
    $reference_output =
      file(BootstrapExtension::$FIXTURES_DIR.'ValidPrograms/AllFormulaProgram/reference.output', FILE_IGNORE_NEW_LINES);

    $reference_output_index = 0;
    foreach ($xml_properties->xpath('//formulaList') as $formula_list_xml_properties) {
      $categories = [];
      $expected = [];

      foreach ($formula_list_xml_properties->formula as $formula_xml_properties) {
        $category = (string) $formula_xml_properties[Constants::CATEGORY_ATTRIBUTE];

        $categories[] = $category;
        $expected[$category] = $reference_output[$reference_output_index++];
      }

      $data[] = [
        $formula_list_xml_properties,
        $categories,
        $expected,
      ];
    }

    return $data;
  }
}
