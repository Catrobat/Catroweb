<?php

namespace Catrobat\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="program_like")
 * @ORM\Entity(repositoryClass="Catrobat\AppBundle\Entity\ProgramLikeRepository")
 */
class ProgramLike
{
    const TYPE_NONE = 0;
    const TYPE_THUMBS_UP = 1;
    const TYPE_SMILE = 2;
    const TYPE_LOVE = 3;
    const TYPE_WOW = 4;
    // -> new types go here...

    public static $VALID_TYPES = [
        self::TYPE_THUMBS_UP,
        self::TYPE_SMILE,
        self::TYPE_LOVE,
        self::TYPE_WOW,
        // -> ... and here
    ];

    static public function isValidType($type)
    {
        return in_array($type, self::$VALID_TYPES);
    }

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
    protected $program_id;

    /**
     * @ORM\ManyToOne(targetEntity="\Catrobat\AppBundle\Entity\Program", inversedBy="likes", fetch="LAZY")
     * @ORM\JoinColumn(name="program_id", referencedColumnName="id")
     * @var \Catrobat\AppBundle\Entity\Program
     */
    protected $program;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $user_id;

    /**
     * @ORM\ManyToOne(targetEntity="\Catrobat\AppBundle\Entity\User", inversedBy="likes", fetch="LAZY")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * @var \Catrobat\AppBundle\Entity\User
     */
    protected $user;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", nullable=false, options={"default" = 0})
     */
    protected $type = self::TYPE_THUMBS_UP;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created_at;

    /**
     * @param \Catrobat\AppBundle\Entity\Program $program
     * @param \Catrobat\AppBundle\Entity\User $user
     * @param int $type
     */
    public function __construct(Program $program, User $user, $type)
    {
        $this->setProgram($program);
        $this->setUser($user);
        $this->setType($type);
        $this->created_at = null;
        $this->seen_at = null;
    }

    /**
     * @ORM\PrePersist
     */
    public function updateTimestamps()
    {
        if ($this->getCreatedAt() == null) {
            $this->setCreatedAt(new \DateTime());
        }
    }

    /**
     * @param \Catrobat\AppBundle\Entity\Program $program
     * @return ProgramLike
     */
    public function setProgram(Program $program)
    {
        $this->program = $program;
        $this->program_id = $program->getId();

        return $this;
    }

    /**
     * @return Program
     */
    public function getProgram()
    {
        return $this->program;
    }

    /**
     * @return int
     */
    public function getProgramId()
    {
        return $this->program_id;
    }

    /**
     * @param \Catrobat\AppBundle\Entity\User $user
     * @return ProgramLike
     */
    public function setUser(User $user)
    {
        $this->user = $user;
        $this->user_id = $user->getId();

        return $this;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @param int $type
     * @return ProgramLike
     */
    public function setType($type)
    {
        $this->type = (int) $type;

        return $this;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
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
     * @return $this
     */
    public function setCreatedAt(\DateTime $created_at)
    {
        $this->created_at = $created_at;

        return $this;
    }

}
