<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Scripts;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\Bricks\BrickFactory;

/**
 * Class Script
 * @package App\Catrobat\Services\CatrobatCodeParser\Scripts
 */
abstract class Script
{
  /**
   * @var \SimpleXMLElement
   */
  protected $script_xml_properties;
  /**
   * @var
   */
  protected $type;
  /**
   * @var
   */
  protected $caption;
  /**
   * @var
   */
  private $img_file;
  /**
   * @var array
   */
  private $bricks;

  /**
   * Script constructor.
   *
   * @param \SimpleXMLElement $script_xml_properties
   */
  public function __construct(\SimpleXMLElement $script_xml_properties)
  {
    $this->script_xml_properties = $script_xml_properties;
    $this->bricks = [];

    $this->create();

    $this->parseBricks();
  }

  /**
   * @return mixed
   */
  abstract protected function create();

  /**
   *
   */
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

  /**
   * @return mixed
   */
  public function getType()
  {
    return $this->type;
  }

  /**
   * @return mixed
   */
  public function getCaption()
  {
    return $this->caption;
  }

  /**
   * @return mixed
   */
  public function getImgFile()
  {
    return $this->img_file;
  }

  /**
   * @param $img_file
   */
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

  /**
   * @return bool
   */
  private function isCommentedOut()
  {
    return ($this->script_xml_properties->commentedOut != null
      and $this->script_xml_properties->commentedOut == 'true');
  }

  /**
   *
   */
  private function commentOut()
  {
    $this->img_file = Constants::UNKNOWN_SCRIPT_IMG;
  }

  /**
   * @return array
   */
  public function getBricks()
  {
    return $this->bricks;
  }
}