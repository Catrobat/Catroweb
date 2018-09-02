<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser;


class ParsedSimpleProgram extends ParsedObjectsContainer
{
  protected $code_statistic;

  public function __construct(\SimpleXMLElement $program_xml_properties)
  {
    parent::__construct($program_xml_properties);

    $this->code_statistic = new CodeStatistic();
    $this->computeCodeStatistic();
  }

  protected function computeCodeStatistic()
  {
    $this->code_statistic->update($this);
    $this->code_statistic->computeVariableStatistic($this->xml_properties);
  }

  public function getCodeStatistic()
  {
    return $this->code_statistic;
  }

  public function hasScenes()
  {
    return false;
  }
}