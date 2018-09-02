<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser;


class ParsedSceneProgram
{
  protected $program_xml_properties;
  protected $scenes;
  protected $code_statistic;

  public function __construct(\SimpleXMLElement $program_xml_properties)
  {
    $this->program_xml_properties = $program_xml_properties;
    $this->scenes = [];

    $this->parseScenes();

    $this->code_statistic = new CodeStatistic();
    $this->computeCodeStatistic();
  }

  protected function parseScenes()
  {
    foreach ($this->program_xml_properties->scenes->scene as $scene_xml_properties)
      $this->scenes[] = new ParsedScene($scene_xml_properties);
  }

  protected function computeCodeStatistic()
  {
    foreach ($this->scenes as $scene)
      $this->code_statistic->update($scene);

    $this->code_statistic->computeVariableStatistic($this->program_xml_properties);
  }

  public function hasScenes()
  {
    return true;
  }

  public function getScenes()
  {
    return $this->scenes;
  }

  public function getCodeStatistic()
  {
    return $this->code_statistic;
  }
}