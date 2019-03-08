<?php

namespace App\Entity;

/**
 * Interface ProgramCatrobatRemixRelationInterface
 * @package App\Entity
 */
interface ProgramCatrobatRemixRelationInterface
{
  /**
   * @return Program
   */
  public function getAncestor();

  /**
   * @return Program
   */
  public function getDescendant();

  /**
   * @return \DateTime
   */
  public function getCreatedAt();

  /**
   * @param \DateTime $created_at
   *
   * @return mixed
   */
  public function setCreatedAt(\DateTime $created_at);

  /**
   * @return \DateTime
   */
  public function getSeenAt();

  /**
   * @param \DateTime $seen_at
   *
   * @return $this
   */
  public function setSeenAt($seen_at);
}
