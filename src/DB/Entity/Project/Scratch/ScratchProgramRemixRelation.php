<?php

declare(strict_types=1);

namespace App\DB\Entity\Project\Scratch;

use App\DB\Entity\Project\Program;
use App\DB\Entity\Project\Remix\ProgramRemixRelationInterface;
use App\DB\EntityRepository\Project\ScratchProgramRemixRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'scratch_program_remix_relation')]
#[ORM\Entity(repositoryClass: ScratchProgramRemixRepository::class)]
class ScratchProgramRemixRelation implements ProgramRemixRelationInterface, \Stringable
{
  /**
   * -----------------------------------------------------------------------------------------------------------------
   * NOTE: this entity uses a Doctrine workaround in order to allow using foreign keys as primary keys.
   *
   * @see{http://stackoverflow.com/questions/6383964/primary-key-and-foreign-key-with-doctrine-2-at-the-same-time}
   * -----------------------------------------------------------------------------------------------------------------
   */
  #[ORM\Id]
  #[ORM\Column(type: Types::GUID)]
  protected string $scratch_parent_id;

  #[ORM\Id]
  #[ORM\Column(type: Types::GUID)]
  protected string $catrobat_child_id;

  #[ORM\JoinColumn(name: 'catrobat_child_id', referencedColumnName: 'id')]
  #[ORM\ManyToOne(targetEntity: Program::class, fetch: 'LAZY', inversedBy: 'scratch_remix_parent_relations')]
  protected Program $catrobat_child;

  public function __construct(string $scratch_parent_id, Program $catrobat_child)
  {
    $this->setScratchParentId($scratch_parent_id);
    $this->setCatrobatChild($catrobat_child);
  }

  #[\Override]
  public function __toString(): string
  {
    return '(Scratch: #'.$this->scratch_parent_id.', Catrobat: #'.$this->catrobat_child_id.')';
  }

  public function setScratchParentId(string $scratch_parent_id): ScratchProgramRemixRelation
  {
    $this->scratch_parent_id = $scratch_parent_id;

    return $this;
  }

  public function getScratchParentId(): string
  {
    return $this->scratch_parent_id;
  }

  public function setCatrobatChild(Program $catrobat_child): ScratchProgramRemixRelation
  {
    $this->catrobat_child = $catrobat_child;
    $this->catrobat_child_id = $catrobat_child->getId();

    return $this;
  }

  public function getCatrobatChild(): Program
  {
    return $this->catrobat_child;
  }

  public function getCatrobatChildId(): string
  {
    return $this->catrobat_child_id;
  }

  #[\Override]
  public function getDepth(): int
  {
    return 1;
  }

  #[\Override]
  public function getUniqueKey(): string
  {
    return sprintf('ScratchProgramRemixRelation(%d, %d)', $this->scratch_parent_id, $this->catrobat_child_id);
  }
}
