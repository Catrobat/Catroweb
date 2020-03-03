<?php

namespace App\Catrobat\Services\CatrobatCodeParser;

use SimpleXMLElement;

/**
 * Class ParsedSimpleProgram.
 */
class ParsedSimpleProgram extends ParsedObjectsContainer
{
  /**
   * @var CodeStatistic
   */
  protected $code_statistic;

  /**
   * ParsedSimpleProgram constructor.
   */
  public function __construct(SimpleXMLElement $program_xml_properties)
  {
    parent::__construct($program_xml_properties);

    $this->code_statistic = new CodeStatistic();
    $this->computeCodeStatistic();
  }

  /**
   * @return CodeStatistic
   */
  public function getCodeStatistic()
  {
    return $this->code_statistic;
  }

  /**
   * @return bool
   */
  public function hasScenes()
  {
    return false;
  }

  protected function computeCodeStatistic()
  {
    $this->code_statistic->update($this);
    $this->code_statistic->computeVariableStatistic($this->xml_properties);
  }
}
