<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

/**
 * Class SoundListStatement
 * @package Catrobat\AppBundle\CatrobatCode\Statements
 */
class SoundListStatement extends BaseListStatement
{
  const BEGIN_STRING = "used sounds: <br/>";

  /**
   * SoundListStatement constructor.
   *
   * @param $statementFactory
   * @param $xmlTree
   * @param $spaces
   */
  public function __construct($statementFactory, $xmlTree, $spaces)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces,
      self::BEGIN_STRING,
      "");
  }

}