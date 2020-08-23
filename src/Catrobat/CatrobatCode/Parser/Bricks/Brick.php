<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;
use SimpleXMLElement;

abstract class Brick
{
  protected SimpleXMLElement $brick_xml_properties;

  protected string $type;

  protected string $caption;

  private string $img_file;

  public function __construct(SimpleXMLElement $brick_xml_properties)
  {
    $this->brick_xml_properties = $brick_xml_properties;
    $this->create();
  }

  public function getType(): string
  {
    return $this->type;
  }

  public function getCaption(): string
  {
    return $this->caption;
  }

  public function getImgFile(): string
  {
    return $this->img_file;
  }

  public function commentOut(): void
  {
    $this->img_file = Constants::UNKNOWN_BRICK_IMG;
  }

  abstract protected function create(): void;

  protected function setImgFile(string $img_file): void
  {
    if ($this->isCommentedOut() || $this->hasCommentedOutParentScript())
    {
      $this->commentOut();
    }
    else
    {
      $this->img_file = $img_file;
    }
  }

  private function isCommentedOut(): bool
  {
    return null != $this->brick_xml_properties->commentedOut && 'true' == $this->brick_xml_properties->commentedOut;
  }

  private function hasCommentedOutParentScript(): bool
  {
    $xpath_query_result = $this->brick_xml_properties->xpath('../../commentedOut');

    return null != $xpath_query_result && 'true' == $xpath_query_result[0];
  }
}
