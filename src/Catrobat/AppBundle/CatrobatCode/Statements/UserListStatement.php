<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

/**
 * Class UserListStatement
 * @package Catrobat\AppBundle\CatrobatCode\Statements
 */
class UserListStatement extends Statement
{

  /**
   * UserListStatement constructor.
   *
   * @param $statementFactory
   * @param $xmlTree
   * @param $spaces
   * @param $value
   */
  public function __construct($statementFactory, $xmlTree, $spaces, $value)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces,
      $value,
      "");
  }

}
