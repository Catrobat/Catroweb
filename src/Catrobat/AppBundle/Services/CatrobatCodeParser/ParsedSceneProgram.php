<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser;

/**
 * Class ParsedSceneProgram
 * @package Catrobat\AppBundle\Services\CatrobatCodeParser
 */
class ParsedSceneProgram
{
  /**
   * @var \SimpleXMLElement
   */
  protected $program_xml_properties;

  /**
   * @var ParsedScene
   */
  protected $scenes;

  /**
   * @var CodeStatistic
   */
  protected $code_statistic;

  /**
   * ParsedSceneProgram constructor.
   *
   * @param \SimpleXMLElement $program_xml_properties
   */
  public function __construct(\SimpleXMLElement $program_xml_properties)
  {
    $this->program_xml_properties = $program_xml_properties;
    $this->scenes = [];

    $this->parseScenes();

    $this->code_statistic = new CodeStatistic();
    $this->computeCodeStatistic();
  }

  /**
   *
   */
  protected function parseScenes()
  {
    foreach ($this->program_xml_properties->scenes->scene as $scene_xml_properties)
      $this->scenes[] = new ParsedScene($scene_xml_properties);
  }

  /**
   *
   */
  protected function computeCodeStatistic()
  {
    foreach ($this->scenes as $scene)
      $this->code_statistic->update($scene);

    $this->code_statistic->computeVariableStatistic($this->program_xml_properties);
  }

  /**
   * @return bool
   */
  public function hasScenes()
  {
    return true;
  }

  /**
   * @return array|ParsedScene
   */
  public function getScenes()
  {
    return $this->scenes;
  }

  /**
   * @return CodeStatistic
   */
  public function getCodeStatistic()
  {
    return $this->code_statistic;
  }
}