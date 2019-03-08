<?php

namespace App\Entity;


/**
 * Interface ProgramRemixRelationInterface
 * @package App\Entity
 */
interface ProgramRemixRelationInterface
{
  /**
   * @return string
   */
  public function getUniqueKey();

  /**
   * @return int
   */
  public function getDepth();
}
