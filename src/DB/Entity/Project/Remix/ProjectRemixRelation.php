<?php

declare(strict_types=1);

namespace App\DB\Entity\Project\Remix;

use App\DB\Entity\Project\Project;
use App\DB\EntityRepository\Project\ProjectRemixRepository;
use App\Utils\TimeUtils;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'program_remix_relation')]
#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: ProjectRemixRepository::class)]
class ProjectRemixRelation implements ProjectRemixRelationInterface, ProjectCatrobatRemixRelationInterface, \Stringable
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
  protected string $ancestor_id;

  #[ORM\JoinColumn(name: 'ancestor_id', referencedColumnName: 'id')]
  #[ORM\ManyToOne(targetEntity: Project::class, cascade: ['persist'], fetch: 'LAZY', inversedBy: 'catrobat_remix_descendant_relations')]
  protected Project $ancestor;

  #[ORM\Id]
  #[ORM\Column(type: Types::GUID)]
  protected string $descendant_id;

  #[ORM\JoinColumn(name: 'descendant_id', referencedColumnName: 'id')]
  #[ORM\ManyToOne(targetEntity: Project::class, cascade: ['persist'], fetch: 'LAZY', inversedBy: 'catrobat_remix_ancestor_relations')]
  protected Project $descendant;

  #[ORM\Id]
  #[ORM\Column(type: Types::INTEGER, nullable: false, options: ['default' => 0])]
  protected int $depth = 0;

  #[ORM\Column(type: Types::DATETIME_MUTABLE)]
  protected ?\DateTime $created_at = null;

  #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
  protected ?\DateTime $seen_at = null;

  public function __construct(Project $ancestor, Project $descendant, int $depth)
  {
    $this->setAncestor($ancestor);
    $this->setDescendant($descendant);
    $this->setDepth($depth);
  }

  #[\Override]
  public function __toString(): string
  {
    return '(#'.$this->ancestor_id.', #'.$this->descendant_id.', depth: '.$this->depth.')';
  }

  /**
   * @throws \Exception
   */
  #[ORM\PrePersist]
  public function updateTimestamps(): void
  {
    if (null == $this->getCreatedAt()) {
      $this->setCreatedAt(TimeUtils::getDateTime());
    }
  }

  public function setAncestor(Project $ancestor): void
  {
    $this->ancestor = $ancestor;
    $this->ancestor_id = $ancestor->getId() ?? throw new \LogicException('Ancestor program must have an ID.');
  }

  #[\Override]
  public function getAncestor(): Project
  {
    return $this->ancestor;
  }

  public function getAncestorId(): string
  {
    return $this->ancestor_id;
  }

  public function setDescendant(Project $descendant): void
  {
    $this->descendant = $descendant;
    $this->descendant_id = $descendant->getId() ?? throw new \LogicException('Descendant program must have an ID.');
  }

  #[\Override]
  public function getDescendant(): Project
  {
    return $this->descendant;
  }

  public function getDescendantId(): string
  {
    return $this->descendant_id;
  }

  public function setDepth(int $depth): void
  {
    $this->depth = $depth;
  }

  #[\Override]
  public function getDepth(): int
  {
    return $this->depth;
  }

  #[\Override]
  public function getCreatedAt(): ?\DateTime
  {
    return $this->created_at;
  }

  #[\Override]
  public function setCreatedAt(\DateTime $created_at): void
  {
    $this->created_at = $created_at;
  }

  #[\Override]
  public function getSeenAt(): ?\DateTime
  {
    return $this->seen_at;
  }

  #[\Override]
  public function setSeenAt(?\DateTime $seen_at): void
  {
    $this->seen_at = $seen_at;
  }

  #[\Override]
  public function getUniqueKey(): string
  {
    return sprintf('ProjectRemixRelation(%d,%d,%d)', $this->ancestor_id, $this->descendant_id, $this->depth);
  }
}
