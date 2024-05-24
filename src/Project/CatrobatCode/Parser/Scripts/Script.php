<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Scripts;

use App\Project\CatrobatCode\Parser\Bricks\BrickFactory;
use App\Project\CatrobatCode\Parser\Constants;

abstract class Script
{
  protected string $type;

  protected string $caption;

  private string $img_file;

  private array $bricks = [];

  public function __construct(protected \SimpleXMLElement $script_xml_properties)
  {
    $this->create();

    $this->parseBricks();
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

  public function getBricks(): array
  {
    return $this->bricks;
  }

  abstract protected function create(): void;

  protected function setImgFile(string $img_file): void
  {
    if ($this->isCommentedOut()) {
      $this->commentOut();
      foreach ($this->bricks as $brick) {
        $brick->commentOut();
      }
    } else {
      $this->img_file = $img_file;
    }
  }

  /**
   * This function parses the simple_xml bricks and adds them to $this->bricks
   * This has to be done recursive since some bricks contain children bricks (loops, ...).
   */
  private function parseBricks(): void
  {
    $bricks = $this->script_xml_properties->brickList->children();
    $this->parseBricksRecursive($bricks);
  }

  private function parseBricksRecursive(\SimpleXMLElement $bricks_as_xml): void
  {
    foreach ($bricks_as_xml as $brick_as_xml) {
      $this->addBrick($brick_as_xml);
      $this->checkAndParseChildrenBlocks($brick_as_xml);
    }
  }

  /**
   * For Loops and branching statements we need to complete the bricks by their children and end/middle tags.
   * The XML file only contains the beginning brick, end/middle bricks are redundant due the structure.
   */
  private function checkAndParseChildrenBlocks(\SimpleXMLElement $brick_as_xml): void
  {
    if (property_exists($brick_as_xml, 'loopBricks') && null !== $brick_as_xml->loopBricks) {
      // "loop" .. "end of loop" -> auto generate "end of loop" bricks
      $this->parseChildBricks($brick_as_xml->loopBricks);
      $this->addBrickThatIsNotDirectlyMentionedInXml(Constants::LOOP_END_BRICK);
    } elseif (property_exists($brick_as_xml, 'ifBranchBricks') && null !== $brick_as_xml->ifBranchBricks && (!property_exists($brick_as_xml, 'elseBranchBricks') || null === $brick_as_xml->elseBranchBricks)) {
      // "if" .. "end if" -> auto generate "end if" bricks
      $this->parseChildBricks($brick_as_xml->ifBranchBricks);
      $this->addBrickThatIsNotDirectlyMentionedInXml(Constants::ENDIF_BRICK);
    } elseif (property_exists($brick_as_xml, 'ifBranchBricks') && null !== $brick_as_xml->ifBranchBricks && (property_exists($brick_as_xml, 'elseBranchBricks') && null !== $brick_as_xml->elseBranchBricks)) {
      // if .. else .. "end if"-> auto generate "else", "end if" bricks
      $this->parseChildBricks($brick_as_xml->ifBranchBricks);
      $this->addBrickThatIsNotDirectlyMentionedInXml(Constants::ELSE_BRICK);
      $this->parseChildBricks($brick_as_xml->elseBranchBricks);
      $this->addBrickThatIsNotDirectlyMentionedInXml(Constants::ENDIF_BRICK);
    }
  }

  /**
   * @param string $type The brick type as defined in Constants.php
   */
  private function addBrickThatIsNotDirectlyMentionedInXml(string $type): void
  {
    $brick_as_xml = new \SimpleXMLElement('<brick></brick>');
    // @phpstan-ignore-next-line
    $brick_as_xml[Constants::TYPE_ATTRIBUTE] = $type;
    $this->bricks[] = BrickFactory::generate($brick_as_xml);
  }

  private function parseChildBricks(\SimpleXMLElement $brick_as_xml): void
  {
    $bricks_children = $brick_as_xml->children();
    $this->parseBricksRecursive($bricks_children);
  }

  private function addBrick(\SimpleXMLElement $brick_as_xml): void
  {
    if (isset($brick_as_xml[Constants::REFERENCE_ATTRIBUTE])) {
      $this->bricks[] = BrickFactory::generate($brick_as_xml->xpath($brick_as_xml[Constants::REFERENCE_ATTRIBUTE]->__toString())[0]);
    } else {
      $this->bricks[] = BrickFactory::generate($brick_as_xml);
    }
  }

  private function isCommentedOut(): bool
  {
    return null != $this->script_xml_properties->commentedOut && 'true' == $this->script_xml_properties->commentedOut;
  }

  private function commentOut(): void
  {
    $this->img_file = Constants::UNKNOWN_SCRIPT_IMG;
  }
}
