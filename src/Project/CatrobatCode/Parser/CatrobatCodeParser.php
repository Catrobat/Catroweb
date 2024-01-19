<?php

namespace App\Project\CatrobatCode\Parser;

use App\Project\CatrobatFile\ExtractedCatrobatFile;

class CatrobatCodeParser
{
  /**
   * @return ParsedSceneProject|ParsedSimpleProject|null
   */
  public function parse(ExtractedCatrobatFile $extracted_catrobat_project)
  {
    try {
      $parsed_project = $this->parseProject($extracted_catrobat_project);
    } catch (\Throwable) {
      $parsed_project = null;
    }

    return $parsed_project;
  }

  private function parseProject(ExtractedCatrobatFile $extracted_project): ParsedSceneProject|ParsedSimpleProject
  {
    $project_xml_properties = $extracted_project->getProjectXmlProperties();

    if ($extracted_project->hasScenes()) {
      return new ParsedSceneProject($project_xml_properties);
    }

    return new ParsedSimpleProject($project_xml_properties);
  }
}
