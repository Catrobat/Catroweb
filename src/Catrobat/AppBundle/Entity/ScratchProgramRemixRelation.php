<?php

namespace Catrobat\AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="scratch_program_remix_relation")
 * @ORM\Entity(repositoryClass="Catrobat\AppBundle\Entity\ScratchProgramRemixRepository")
 */
class ScratchProgramRemixRelation implements ProgramRemixRelationInterface
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
    protected $scratch_parent_id;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $catrobat_child_id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="\Catrobat\AppBundle\Entity\Program",
     *     inversedBy="scratch_remix_parent_relations",
     *     fetch="LAZY"
     * )
     * @ORM\JoinColumn(name="catrobat_child_id", referencedColumnName="id")
     * @var \Catrobat\AppBundle\Entity\Program
     */
    protected $catrobat_child;

    /**
     * @param int $scratch_parent_id
     * @param \Catrobat\AppBundle\Entity\Program $catrobat_child
     */
    public function __construct($scratch_parent_id, Program $catrobat_child)
    {
        $this->setScratchParentId($scratch_parent_id);
        $this->setCatrobatChild($catrobat_child);
    }

    /**
     * @param int $scratch_parent_id
     * @return ScratchProgramRemixRelation
     */
    public function setScratchParentId($scratch_parent_id)
    {
        $this->scratch_parent_id = $scratch_parent_id;

        return $this;
    }

    /**
     * @return int
     */
    public function getScratchParentId()
    {
        return $this->scratch_parent_id;
    }

    /**
     * @param Program $catrobat_child
     * @return ScratchProgramRemixRelation
     */
    public function setCatrobatChild(Program $catrobat_child)
    {
        $this->catrobat_child = $catrobat_child;
        $this->catrobat_child_id = $catrobat_child->getId();

        return $this;
    }

    /**
     * @return Program
     */
    public function getCatrobatChild()
    {
        return $this->catrobat_child;
    }

    /**
     * @return int
     */
    public function getCatrobatChildId()
    {
        return $this->catrobat_child_id;
    }

    /**
     * @return int
     */
    public function getDepth()
    {
        return 1;
    }

    public function getUniqueKey()
    {
        return sprintf("ScratchProgramRemixRelation(%d, %d)", $this->scratch_parent_id, $this->catrobat_child_id);
    }

    public function __toString()
    {
        return "(Scratch: #" . $this->scratch_parent_id . ", Catrobat: #" . $this->catrobat_child_id . ")";
    }
}
