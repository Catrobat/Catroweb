<?php

namespace App\Project\CatrobatCode\Parser;

use App\Project\CatrobatFile\ExtractedCatrobatFile;

class CatrobatCodeParser
{
  /**
   * @return ParsedSceneProgram|ParsedSimpleProgram|null
   */
  public function parse(ExtractedCatrobatFile $extracted_catrobat_program)
  {
    try {
      $parsed_program = $this->parseProgram($extracted_catrobat_program);
    } catch (\Throwable) {
      $parsed_program = null;
    }

    return $parsed_program;
  }

  private function parseProgram(ExtractedCatrobatFile $extracted_program): ParsedSceneProgram|ParsedSimpleProgram
  {
    $program_xml_properties = $extracted_program->getProgramXmlProperties();

    if ($extracted_program->hasScenes()) {
      return new ParsedSceneProgram($program_xml_properties);
    }

    return new ParsedSimpleProgram($program_xml_properties);
  }
}
