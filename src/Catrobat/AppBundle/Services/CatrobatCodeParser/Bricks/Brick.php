<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

/**
 * Class Brick
 * @package Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks
 */
abstract class Brick
{
  /**
   * @var \SimpleXMLElement
   */
  protected $brick_xml_properties;
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
   * Brick constructor.
   *
   * @param \SimpleXMLElement $brick_xml_properties
   */
  public function __construct(\SimpleXMLElement $brick_xml_properties)
  {
    $this->brick_xml_properties = $brick_xml_properties;
    $this->create();
  }

  /**
   * @return mixed
   */
  abstract protected function create();

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
    if ($this->isCommentedOut() or $this->hasCommentedOutParentScript())
    {
      $this->commentOut();
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
    return ($this->brick_xml_properties->commentedOut != null
      and $this->brick_xml_properties->commentedOut == 'true');
  }

  /**
   * @return bool
   */
  private function hasCommentedOutParentScript()
  {
    $xpath_query_result = $this->brick_xml_properties->xpath('../../commentedOut');

    return ($xpath_query_result != null and $xpath_query_result[0] == 'true');
  }

  /**
   *
   */
  public function commentOut()
  {
    $this->img_file = Constants::UNKNOWN_BRICK_IMG;
  }
}