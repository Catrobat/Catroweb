<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Scripts;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks\BrickFactory;

abstract class Script
{
  protected $script_xml_properties;
  protected $type;
  protected $caption;
  private $img_file;
  private $bricks;

  public function __construct(\SimpleXMLElement $script_xml_properties)
  {
    $this->script_xml_properties = $script_xml_properties;
    $this->bricks = [];

    $this->create();

    $this->parseBricks();
  }

  abstract protected function create();

  private function parseBricks()
  {
    foreach ($this->script_xml_properties->brickList->children() as $brick_xml_properties)
    {
      if ($brick_xml_properties[Constants::REFERENCE_ATTRIBUTE] != null)
      {
        $this->bricks[] = BrickFactory::generate($brick_xml_properties
          ->xpath($brick_xml_properties[Constants::REFERENCE_ATTRIBUTE])[0]);
      }
      else
      {
        $this->bricks[] = BrickFactory::generate($brick_xml_properties);
      }
    }
  }

  public function getType()
  {
    return $this->type;
  }

  public function getCaption()
  {
    return $this->caption;
  }

  public function getImgFile()
  {
    return $this->img_file;
  }

  protected function setImgFile($img_file)
  {
    if ($this->isCommentedOut())
    {
      $this->commentOut();
      foreach ($this->bricks as $brick)
        $brick->commentOut();
    }
    else
    {
      $this->img_file = $img_file;
    }
  }

  private function isCommentedOut()
  {
    return ($this->script_xml_properties->commentedOut != null
      and $this->script_xml_properties->commentedOut == 'true');
  }

  private function commentOut()
  {
    $this->img_file = Constants::UNKNOWN_SCRIPT_IMG;
  }

  public function getBricks()
  {
    return $this->bricks;
  }
}