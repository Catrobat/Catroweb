<?php

namespace Catrobat\AppBundle\Entity;


/**
 * Interface ProgramRemixRelationInterface
 * @package Catrobat\AppBundle\Entity
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
