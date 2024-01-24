<?php

namespace App\DB\Entity\Project\Scratch;

use App\DB\Entity\Project\Project;
use App\DB\Entity\Project\Remix\ProjectRemixRelationInterface;
use App\DB\EntityRepository\Project\ScratchProjectRemixRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="scratch_project_remix_relation")
 *
 * @ORM\Entity(repositoryClass=ScratchProjectRemixRepository::class)
 */
class ScratchProjectRemixRelation implements ProjectRemixRelationInterface, \Stringable
{
  /**
   * -----------------------------------------------------------------------------------------------------------------
   * NOTE: this entity uses a Doctrine workaround in order to allow using foreign keys as primary keys.
   *
   * @see{http://stackoverflow.com/questions/6383964/primary-key-and-foreign-key-with-doctrine-2-at-the-same-time}
   * -----------------------------------------------------------------------------------------------------------------
   */

  /**
   * @ORM\Id
   *
   * @ORM\Column(type="guid")
   */
  protected string $scratch_parent_id;

  /**
   * @ORM\Id
   *
   * @ORM\Column(type="guid")
   */
  protected string $catrobat_child_id;

  /**
   * @ORM\ManyToOne(
   *     targetEntity=Project::class,
   *     inversedBy="scratch_remix_parent_relations",
   *     fetch="LAZY"
   * )
   *
   * @ORM\JoinColumn(name="catrobat_child_id", referencedColumnName="id")
   */
  protected Project $catrobat_child;

  public function __construct(string $scratch_parent_id, Project $catrobat_child)
  {
    $this->setScratchParentId($scratch_parent_id);
    $this->setCatrobatChild($catrobat_child);
  }

  public function __toString(): string
  {
    return '(Scratch: #'.$this->scratch_parent_id.', Catrobat: #'.$this->catrobat_child_id.')';
  }

  public function setScratchParentId(string $scratch_parent_id): ScratchProjectRemixRelation
  {
    $this->scratch_parent_id = $scratch_parent_id;

    return $this;
  }

  public function getScratchParentId(): string
  {
    return $this->scratch_parent_id;
  }

  public function setCatrobatChild(Project $catrobat_child): ScratchProjectRemixRelation
  {
    $this->catrobat_child = $catrobat_child;
    $this->catrobat_child_id = $catrobat_child->getId();

    return $this;
  }

  public function getCatrobatChild(): Project
  {
    return $this->catrobat_child;
  }

  public function getCatrobatChildId(): string
  {
    return $this->catrobat_child_id;
  }

  public function getDepth(): int
  {
    return 1;
  }

  public function getUniqueKey(): string
  {
    return sprintf('ScratchProjectRemixRelation(%d, %d)', $this->scratch_parent_id, $this->catrobat_child_id);
  }
}
