<?php

namespace Tests\phpUnit\CatrobatCodeParserTests;

use App\Catrobat\CatrobatCode\Parser\Constants;
use App\Catrobat\CatrobatCode\Parser\FormulaResolver;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers \App\Catrobat\CatrobatCode\Parser\FormulaResolver
 */
class FormulaResolverTest extends TestCase
{
  /**
   * @test
   * @dataProvider formulaDataProvider
   *
   * @param mixed $formula_list_xml_properties
   * @param mixed $categories
   * @param mixed $expected
   */
  public function mustResolveAllFormulas($formula_list_xml_properties, $categories, $expected): void
  {
    $actual = FormulaResolver::resolve($formula_list_xml_properties);

    foreach ($categories as $category)
    {
      $this->assertEquals($expected[$category], $actual[$category]);
    }
  }

  /**
   * @return mixed[][]
   */
  public function formulaDataProvider(): array
  {
    $data = [];

    $xml_properties = simplexml_load_file(__DIR__.'/Resources/ValidPrograms/AllFormulaProgram/code.xml');
    $reference_output =
      file(__DIR__.'/Resources/ValidPrograms/AllFormulaProgram/reference.output', FILE_IGNORE_NEW_LINES);

    $reference_output_index = 0;
    foreach ($xml_properties->xpath('//formulaList') as $formula_list_xml_properties)
    {
      $categories = [];
      $expected = [];

      foreach ($formula_list_xml_properties->formula as $formula_xml_properties)
      {
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
