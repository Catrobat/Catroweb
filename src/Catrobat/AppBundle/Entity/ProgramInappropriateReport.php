<?php

namespace Catrobat\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProgramInappropriateReport.
 *
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Catrobat\AppBundle\Entity\ProgramInappropriateReportRepository")
 */
class ProgramInappropriateReport
{
  const STATUS_NEW = 1;
  const STATUS_REJECTED = 2;
  const STATUS_ACCEPTED = 3;

  /**
   * @var int
   *
   * @ORM\Column(name="id", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private $id;

  /**
   * @var \Catrobat\AppBundle\Entity\User
   *
   * @ORM\ManyToOne(targetEntity="\Catrobat\AppBundle\Entity\User")
   * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
   */
  private $reportingUser;

  /**
   * @var string
   *
   * @ORM\Column(name="category", type="text", length=256)
   */
  private $category;

  /**
   * @var string
   *
   * @ORM\Column(name="note", type="text")
   */
  private $note;

  /**
   * @var \DateTime
   *
   * @ORM\Column(name="time", type="datetime")
   */
  private $time;

  /**
   * @var int
   *
   * @ORM\Column(type="integer")
   */
  private $state;

  /**
   * @var \Catrobat\AppBundle\Entity\Program
   *
   * @ORM\ManyToOne(targetEntity="\Catrobat\AppBundle\Entity\Program")
   * @ORM\JoinColumn(name="program_id", referencedColumnName="id", onDelete="SET NULL")
   */
  private $program;

  /**
   * @var int
   *
   * @ORM\Column(name="projectVersion", type="integer")
   */
  private $projectVersion;

  /**
   * @ORM\PrePersist
   *
   * @throws \Exception
   */
  public function updateTimestamps()
  {
    if ($this->getTime() == null)
    {
      $this->setTime(new \DateTime());
    }
  }

  /**
   * @ORM\PrePersist
   */
  public function updateState()
  {
    if ($this->getState() == null)
    {
      $this->setState(self::STATUS_NEW);
    }
  }

  /**
   * @ORM\PrePersist
   */
  public function updateProgramVersion()
  {
    $this->setProjectVersion($this->getProgram()->getVersion());
  }

  /**
   * Get id.
   *
   * @return int
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * Set reportingUser.
   *
   * @param \Catrobat\AppBundle\Entity\User $reportingUser
   *
   * @return ProgramInappropriateReport
   */
  public function setReportingUser($reportingUser)
  {
    $this->reportingUser = $reportingUser;

    return $this;
  }

  /**
   * Get reportingUser.
   *
   * @return \Catrobat\AppBundle\Entity\User
   */
  public function getReportingUser()
  {
    return $this->reportingUser;
  }

  /**
   * Set category.
   *
   * @param string $category
   *
   * @return ProgramInappropriateReport
   */
  public function setCategory($category)
  {
    $this->category = $category;

    return $this;
  }

  /**
   * Get category.
   *
   * @return string
   */
  public function getCategory()
  {
    return $this->category;
  }

  /**
   * Set note.
   *
   * @param string $note
   *
   * @return ProgramInappropriateReport
   */
  public function setNote($note)
  {
    $this->note = $note;

    return $this;
  }

  /**
   * Get note.
   *
   * @return string
   */
  public function getNote()
  {
    return $this->note;
  }

  /**
   * Set time.
   *
   * @param \DateTime $time
   *
   * @return ProgramInappropriateReport
   */
  public function setTime($time)
  {
    $this->time = $time;

    return $this;
  }

  /**
   * Get time.
   *
   * @return \DateTime
   */
  public function getTime()
  {
    return $this->time;
  }

  /**
   * Set state.
   *
   * @param int $state
   *
   * @return ProgramInappropriateReport
   *
   * @throws \InvalidArgumentException
   */
  public function setState($state)
  {
    if (!in_array($state, [self::STATUS_NEW, self::STATUS_ACCEPTED, self::STATUS_REJECTED]))
    {
      throw new \InvalidArgumentException('Invalid state');
    }
    $this->state = $state;

    return $this;
  }

  /**
   * Get state.
   *
   * @return int
   */
  public function getState()
  {
    return $this->state;
  }

  /**
   * Set project.
   *
   * @param Program $program
   *
   * @return ProgramInappropriateReport
   */
  public function setProgram($program)
  {
    $this->program = $program;

    return $this;
  }

  /**
   * Get program.
   *
   * @return \Catrobat\AppBundle\Entity\Program
   */
  public function getProgram()
  {
    return $this->program;
  }

  /**
   * Set projectVersion.
   *
   * @param int $projectVersion
   *
   * @return ProgramInappropriateReport
   */
  public function setProjectVersion($projectVersion)
  {
    $this->projectVersion = $projectVersion;

    return $this;
  }

  /**
   * Get projectVersion.
   *
   * @return int
   */
  public function getProjectVersion()
  {
    return $this->projectVersion;
  }
}
