<?php

namespace App\Entity;

use App\Utils\TimeUtils;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Exception;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="program_remix_backward_relation")
 * @ORM\Entity(repositoryClass="App\Repository\ProgramRemixBackwardRepository")
 */
class ProgramRemixBackwardRelation implements ProgramRemixRelationInterface, ProgramCatrobatRemixRelationInterface
{
  /**
   * -----------------------------------------------------------------------------------------------------------------
   * NOTE: this entity uses a Doctrine workaround in order to allow using foreign keys as primary keys.
   *
   * @link{http://stackoverflow.com/questions/6383964/primary-key-and-foreign-key-with-doctrine-2-at-the-same-time}
   * -----------------------------------------------------------------------------------------------------------------
   */

  /**
   * @ORM\Id
   * @ORM\Column(type="guid")
   */
  protected string $parent_id;

  /**
   * @ORM\ManyToOne(targetEntity="\App\Entity\Program", inversedBy="catrobat_remix_backward_child_relations",
   * fetch="LAZY")
   * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
   */
  protected Program $parent;

  /**
   * @ORM\Id
   * @ORM\Column(type="guid")
   */
  protected string $child_id;

  /**
   * @ORM\ManyToOne(targetEntity="\App\Entity\Program", inversedBy="catrobat_remix_backward_parent_relations",
   * fetch="LAZY")
   * @ORM\JoinColumn(name="child_id", referencedColumnName="id")
   */
  protected Program $child;

  /**
   * @ORM\Column(type="datetime")
   */
  protected ?DateTime $created_at = null;

  /**
   * @ORM\Column(type="datetime", nullable=true)
   */
  protected ?DateTime $seen_at = null;

  public function __construct(Program $parent, Program $child)
  {
    $this->setParent($parent);
    $this->setChild($child);
  }

  public function __toString(): string
  {
    return '(#'.$this->parent_id.', #'.$this->child_id.')';
  }

  /**
   * @ORM\PrePersist
   *
   * @throws Exception
   */
  public function updateTimestamps(): void
  {
    if (null == $this->getCreatedAt())
    {
      $this->setCreatedAt(TimeUtils::getDateTime());
    }
  }

  public function setParent(Program $parent): ProgramRemixBackwardRelation
  {
    $this->parent = $parent;
    $this->parent_id = $parent->getId();

    return $this;
  }

  public function getParent(): Program
  {
    return $this->parent;
  }

  public function getParentId(): string
  {
    return $this->parent_id;
  }

  public function setChild(Program $child): ProgramRemixBackwardRelation
  {
    $this->child = $child;
    $this->child_id = $child->getId();

    return $this;
  }

  public function getChild(): Program
  {
    return $this->child;
  }

  public function getChildId(): string
  {
    return $this->child_id;
  }

  public function getDepth(): int
  {
    return 1;
  }

  public function getCreatedAt(): ?DateTime
  {
    return $this->created_at;
  }

  public function setCreatedAt(DateTime $created_at): void
  {
    $this->created_at = $created_at;
  }

  public function getSeenAt(): ?DateTime
  {
    return $this->seen_at;
  }

  public function setSeenAt(?DateTime $seen_at): void
  {
    $this->seen_at = $seen_at;
  }

  public function getUniqueKey(): string
  {
    return sprintf('ProgramRemixBackwardRelation(%d,%d)', $this->parent_id, $this->child_id);
  }

  public function getAncestor(): Program
  {
    return $this->parent;
  }

  public function getDescendant(): Program
  {
    return $this->child;
  }
}
