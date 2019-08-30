<?php

namespace App\Catrobat\Services\CatrobatCodeParser;

use App\Catrobat\Services\ExtractedCatrobatFile;


/**
 * Class CatrobatCodeParser
 * @package App\Catrobat\Services\CatrobatCodeParser
 */
class CatrobatCodeParser
{
  /**
   * @param ExtractedCatrobatFile $extracted_catrobat_program
   *
   * @return ParsedSceneProgram|ParsedSimpleProgram|null
   */
  public function parse(ExtractedCatrobatFile $extracted_catrobat_program)
  {
    try
    {
      $parsed_program = $this->parseProgram($extracted_catrobat_program);
    } catch (\Exception $e)
    {
      $parsed_program = null;
    }

    return $parsed_program;
  }

  /**
   * @param ExtractedCatrobatFile $extracted_program
   *
   * @return ParsedSceneProgram|ParsedSimpleProgram
   */
  private function parseProgram(ExtractedCatrobatFile $extracted_program)
  {
    $program_xml_properties = $extracted_program->getProgramXmlProperties();

    if ($extracted_program->hasScenes())
    {
      return new ParsedSceneProgram($program_xml_properties);
    }
    else
    {
      return new ParsedSimpleProgram($program_xml_properties);
    }
  }
}