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
  private ?int $id = null;

  /**
   * @ORM\OneToOne(targetEntity="User")
   * @ORM\JoinColumn(name="user", referencedColumnName="id", nullable=false)
   */
  private User $user;

  /**
   * @ORM\Column(name="upload", type="boolean")
   */
  private bool $upload = false;

  /**
   * @ORM\Column(name="report", type="boolean")
   */
  private bool $report = false;

  /**
   * @ORM\Column(name="summary", type="boolean")
   */
  private bool $summary = false;

  public function __toString(): string
  {
    return $this->user->__toString().' notification';
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function Id(User $user): Notification
  {
    $this->user = $user;

    return $this;
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

  public function setSummary(bool $summary): Notification
  {
    $this->summary = $summary;

    return $this;
  }

  public function getSummary(): bool
  {
    return $this->summary;
  }
}
