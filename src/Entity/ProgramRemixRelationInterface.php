<?php

namespace App\Entity;

/**
 * Interface ProgramRemixRelationInterface.
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
