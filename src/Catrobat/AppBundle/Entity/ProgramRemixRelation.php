<?php

namespace Catrobat\AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="program_remix_relation")
 * @ORM\Entity(repositoryClass="Catrobat\AppBundle\Entity\ProgramRemixRepository")
 */
class ProgramRemixRelation implements ProgramRemixRelationInterface, ProgramCatrobatRemixRelationInterface
{
  /**
   * -----------------------------------------------------------------------------------------------------------------
   * NOTE: this entity uses a Doctrine workaround in order to allow using foreign keys as primary keys
   * @link{http://stackoverflow.com/questions/6383964/primary-key-and-foreign-key-with-doctrine-2-at-the-same-time}
   * -----------------------------------------------------------------------------------------------------------------
   */

  /**
   * @ORM\Id
   * @ORM\Column(type="integer", nullable=false)
   */
  protected $ancestor_id;

  /**
   * @ORM\ManyToOne(targetEntity="\Catrobat\AppBundle\Entity\Program", inversedBy="catrobat_remix_descendant_relations",
   *                                                                   fetch="LAZY")
   * @ORM\JoinColumn(name="ancestor_id", referencedColumnName="id")
   * @var \Catrobat\AppBundle\Entity\Program
   */
  protected $ancestor;

  /**
   * @ORM\Id
   * @ORM\Column(type="integer", nullable=false)
   */
  protected $descendant_id;

  /**
   * @ORM\ManyToOne(targetEntity="\Catrobat\AppBundle\Entity\Program", inversedBy="catrobat_remix_ancestor_relations",
   *                                                                   fetch="LAZY")
   * @ORM\JoinColumn(name="descendant_id", referencedColumnName="id")
   * @var \Catrobat\AppBundle\Entity\Program
   */
  protected $descendant;

  /**
   * @ORM\Id
   * @ORM\Column(type="integer", nullable=false, options={"default" = 0})
   */
  protected $depth = 0;

  /**
   * @ORM\Column(type="datetime")
   */
  protected $created_at;

  /**
   * @var \DateTime
   * @ORM\Column(type="datetime", nullable=true)
   */
  protected $seen_at;

  /**
   * @param \Catrobat\AppBundle\Entity\Program $ancestor
   * @param \Catrobat\AppBundle\Entity\Program $descendant
   * @param int                                $depth
   */
  public function __construct(Program $ancestor, Program $descendant, $depth)
  {
    $this->setAncestor($ancestor);
    $this->setDescendant($descendant);
    $this->setDepth($depth);
    $this->created_at = null;
    $this->seen_at = null;
  }

  /**
   * @ORM\PrePersist
   */
  public function updateTimestamps()
  {
    if ($this->getCreatedAt() == null)
    {
      $this->setCreatedAt(new \DateTime());
    }
  }

  /**
   * @param \Catrobat\AppBundle\Entity\Program $ancestor
   *
   * @return ProgramRemixRelation
   */
  public function setAncestor(Program $ancestor)
  {
    $this->ancestor = $ancestor;
    $this->ancestor_id = $ancestor->getId();

    return $this;
  }

  /**
   * @return Program
   */
  public function getAncestor()
  {
    return $this->ancestor;
  }

  /**
   * @return int
   */
  public function getAncestorId()
  {
    return $this->ancestor_id;
  }

  /**
   * @param \Catrobat\AppBundle\Entity\Program $descendant
   *
   * @return ProgramRemixRelation
   */
  public function setDescendant(Program $descendant)
  {
    $this->descendant = $descendant;
    $this->descendant_id = $descendant->getId();

    return $this;
  }

  /**
   * @return Program
   */
  public function getDescendant()
  {
    return $this->descendant;
  }

  /**
   * @return int
   */
  public function getDescendantId()
  {
    return $this->descendant_id;
  }

  /**
   * @param int $depth
   *
   * @return ProgramRemixRelation
   */
  public function setDepth($depth)
  {
    $this->depth = (int)$depth;

    return $this;
  }

  /**
   * @return int
   */
  public function getDepth()
  {
    return $this->depth;
  }

  /**
   * @return \DateTime
   */
  public function getCreatedAt()
  {
    return $this->created_at;
  }

  /**
   * @param \DateTime $created_at
   *
   * @return $this
   */
  public function setCreatedAt(\DateTime $created_at)
  {
    $this->created_at = $created_at;

    return $this;
  }

  /**
   * @return \DateTime
   */
  public function getSeenAt()
  {
    return $this->seen_at;
  }

  /**
   * @param \DateTime $seen_at
   *
   * @return $this
   */
  public function setSeenAt($seen_at)
  {
    $this->seen_at = $seen_at;

    return $this;
  }

  public function getUniqueKey()
  {
    return sprintf("ProgramRemixRelation(%d,%d,%d)", $this->ancestor_id, $this->descendant_id, $this->depth);
  }

  public function __toString()
  {
    return "(#" . $this->ancestor_id . ", #" . $this->descendant_id . ", depth: " . $this->depth . ")";
  }

}
