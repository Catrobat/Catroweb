<?php

namespace App\Catrobat\CatrobatCode\Parser;

use App\Catrobat\Services\ExtractedCatrobatFile;
use Exception;

class CatrobatCodeParser
{
  /**
   * @return ParsedSceneProgram|ParsedSimpleProgram|null
   */
  public function parse(ExtractedCatrobatFile $extracted_catrobat_program)
  {
    try
    {
      $parsed_program = $this->parseProgram($extracted_catrobat_program);
    }
    catch (Exception $exception)
    {
      $parsed_program = null;
    }

    return $parsed_program;
  }

  /**
   * @return ParsedSceneProgram|ParsedSimpleProgram
   */
  private function parseProgram(ExtractedCatrobatFile $extracted_program)
  {
    $program_xml_properties = $extracted_program->getProgramXmlProperties();

    if ($extracted_program->hasScenes())
    {
      return new ParsedSceneProgram($program_xml_properties);
    }

    return new ParsedSimpleProgram($program_xml_properties);
  }
}
