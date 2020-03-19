<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Notification.
 *
 * @ORM\Table
 * @ORM\Entity(repositoryClass="App\Repository\NotificationRepository")
 */
class Notification
{
  /**
   * @ORM\Column(name="id", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private int $id;

  /**
   * @ORM\OneToOne(targetEntity="User")
   * @ORM\JoinColumn(name="user", referencedColumnName="id", nullable=false)
   */
  private User $user;

  /**
   * @ORM\Column(name="upload", type="boolean")
   */
  private bool $upload;

  /**
   * @ORM\Column(name="report", type="boolean")
   */
  private bool $report;

  /**
   * @ORM\Column(name="summary", type="boolean")
   */
  private bool $summary;

  public function __toString(): string
  {
    if (is_object($this->user))
    {
      return $this->user->__toString().' notification';
    }

    return 'notification';
  }

  public function getId(): int
  {
    return $this->id;
  }

  public function setUser(User $user): Notification
  {
    $this->user = $user;

    return $this;
  }

  public function getUser(): User
  {
    return $this->user;
  }

  public function setUpload(bool $upload): Notification
  {
    $this->upload = $upload;

    return $this;
  }

  public function getUpload(): bool
  {
    return $this->upload;
  }

  public function setReport(bool $report): Notification
  {
    $this->report = $report;

    return $this;
  }

  public function getReport(): bool
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
