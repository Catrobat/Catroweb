<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Scripts;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

class WhenBGChangeScript extends Script
{
  private $look_file_name;

  protected function create()
  {
    $this->type = Constants::WHEN_BG_CHANGE_SCRIPT;
    $this->caption = "When background changes to";

    if (count($this->script_xml_properties->look) != 0)
    {
      if ($this->script_xml_properties->look[Constants::REFERENCE_ATTRIBUTE] != null)
      {
        $this->look_file_name = $this->script_xml_properties->look
          ->xpath($this->script_xml_properties->look[Constants::REFERENCE_ATTRIBUTE])[0]->fileName;
      }
      else
      {
        $this->look_file_name = $this->script_xml_properties->look->fileName;
      }
    }

    $this->setImgFile(Constants::EVENT_SCRIPT_IMG);
  }

  public function getLookFileName()
  {
    return $this->look_file_name;
  }
}