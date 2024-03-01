<?php

namespace App\Project\CatrobatCode\Parser;

class ParsedSimpleProject extends ParsedObjectsContainer
{
  protected CodeStatistic $code_statistic;

  public function __construct(\SimpleXMLElement $project_xml_properties)
  {
    parent::__construct($project_xml_properties);

    $this->code_statistic = new CodeStatistic();
    $this->computeCodeStatistic();
  }

  public function getCodeStatistic(): CodeStatistic
  {
    return $this->code_statistic;
  }

  public function hasScenes(): bool
  {
    return false;
  }

  protected function computeCodeStatistic(): void
  {
    $this->code_statistic->update($this);
    $this->code_statistic->computeVariableStatistic($this->xml_properties);
  }
}
