<?php

namespace App\Catrobat\CatrobatCode\Parser;

use SimpleXMLElement;

class ParsedSimpleProgram extends ParsedObjectsContainer
{
  protected CodeStatistic $code_statistic;

  public function __construct(SimpleXMLElement $program_xml_properties)
  {
    parent::__construct($program_xml_properties);

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
