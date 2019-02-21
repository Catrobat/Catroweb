<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser;


/**
 * Class ParsedSimpleProgram
 * @package Catrobat\AppBundle\Services\CatrobatCodeParser
 */
class ParsedSimpleProgram extends ParsedObjectsContainer
{
  /**
   * @var CodeStatistic
   */
  protected $code_statistic;

  /**
   * ParsedSimpleProgram constructor.
   *
   * @param \SimpleXMLElement $program_xml_properties
   */
  public function __construct(\SimpleXMLElement $program_xml_properties)
  {
    parent::__construct($program_xml_properties);

    $this->code_statistic = new CodeStatistic();
    $this->computeCodeStatistic();
  }

  /**
   *
   */
  protected function computeCodeStatistic()
  {
    $this->code_statistic->update($this);
    $this->code_statistic->computeVariableStatistic($this->xml_properties);
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
}