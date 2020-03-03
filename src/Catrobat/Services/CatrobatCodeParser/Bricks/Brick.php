<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use SimpleXMLElement;

/**
 * Class Brick.
 */
abstract class Brick
{
  /**
   * @var SimpleXMLElement
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
   */
  public function __construct(SimpleXMLElement $brick_xml_properties)
  {
    $this->brick_xml_properties = $brick_xml_properties;
    $this->create();
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

  public function commentOut()
  {
    $this->img_file = Constants::UNKNOWN_BRICK_IMG;
  }

  /**
   * @return mixed
   */
  abstract protected function create();

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
    return null != $this->brick_xml_properties->commentedOut
      and 'true' == $this->brick_xml_properties->commentedOut;
  }

  /**
   * @return bool
   */
  private function hasCommentedOutParentScript()
  {
    $xpath_query_result = $this->brick_xml_properties->xpath('../../commentedOut');

    return null != $xpath_query_result and 'true' == $xpath_query_result[0];
  }
}
