<?php

namespace Catrobat\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Notification.
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Catrobat\AppBundle\Entity\NotificationRepository")
 */
class Notification
{
  /**
   * @var int
   *
   * @ORM\Column(name="id", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private $id;

  /**
   * @var \stdClass
   *
   * @ORM\OneToOne(targetEntity="\Catrobat\AppBundle\Entity\User")
   * @ORM\JoinColumn(name="user", referencedColumnName="id", nullable=false)
   */
  private $user;

  /**
   * @var bool
   *
   * @ORM\Column(name="upload", type="boolean")
   */
  private $upload;

  /**
   * @var bool
   *
   * @ORM\Column(name="report", type="boolean")
   */
  private $report;

  /**
   * @var bool
   *
   * @ORM\Column(name="summary", type="boolean")
   */
  private $summary;

  public function __toString()
  {
    if (is_object($this->user))
    {
      return $this->user->__toString() . ' notification';
    }

    return 'notification';
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
   * Set user.
   *
   * @param \Catrobat\AppBundle\Entity\User $user
   *
   * @return Notification
   */
  public function setUser($user)
  {
    $this->user = $user;

    return $this;
  }

  /**
   * Get user.
   *
   * @return \Catrobat\AppBundle\Entity\User
   */
  public function getUser()
  {
    return $this->user;
  }

  /**
   * Set upload.
   *
   * @param bool $upload
   *
   * @return Notification
   */
  public function setUpload($upload)
  {
    $this->upload = $upload;

    return $this;
  }

  /**
   * Get upload.
   *
   * @return bool
   */
  public function getUpload()
  {
    return $this->upload;
  }

  /**
   * Set report.
   *
   * @param bool $report
   *
   * @return Notification
   */
  public function setReport($report)
  {
    $this->report = $report;

    return $this;
  }

  /**
   * Get report.
   *
   * @return bool
   */
  public function getReport()
  {
    return $this->report;
  }

  /**
   * Set summary.
   *
   * @param bool $summary
   *
   * @return Notification
   */
  public function setSummary($summary)
  {
    $this->summary = $summary;

    return $this;
  }

  /**
   * Get summary.
   *
   * @return bool
   */
  public function getSummary()
  {
    return $this->summary;
  }
}
