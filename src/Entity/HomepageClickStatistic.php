<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="homepage_click_statistics")
 */
class HomepageClickStatistic
{
  /**
   * @ORM\Id
   * @ORM\Column(type="integer")
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  protected ?int $id = null;

  /**
   * @ORM\Column(type="text", options={"default": ""}, nullable=false)
   */
  protected string $type = '';

  /**
   * @ORM\ManyToOne(targetEntity="\App\Entity\Program", inversedBy="program")
   * @ORM\JoinColumn(name="program_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
   */
  protected ?Program $program = null;

  /**
   * @ORM\Column(type="datetime")
   */
  protected ?DateTime $clicked_at = null;

  /**
   * @ORM\Column(type="text", options={"default": ""})
   */
  protected string $ip = '';

  /**
   * @ORM\Column(type="string", nullable=true)
   */
  protected ?string $locale = null;

  /**
   * @ORM\Column(type="string", options={"default": ""}, nullable=true)
   */
  protected ?string $user_agent = null;

  /**
   * @ORM\ManyToOne(targetEntity="\App\Entity\User")
   * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
   */
  protected ?User $user = null;

  /**
   * @ORM\Column(type="string", options={"default": ""}, nullable=true)
   */
  protected ?string $referrer = null;

  public function getProgram(): ?Program
  {
    return $this->program;
  }

  public function setProgram(?Program $program): void
  {
    $this->program = $program;
  }

  public function getIp(): string
  {
    return $this->ip;
  }

  public function setIp(string $ip): void
  {
    $this->ip = $ip;
  }

  public function getLocale(): ?string
  {
    return $this->locale;
  }

  public function setLocale(?string $locale): void
  {
    $this->locale = $locale;
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function setId(int $id): void
  {
    $this->id = $id;
  }

  public function getUserAgent(): ?string
  {
    return $this->user_agent;
  }

  public function setUserAgent(?string $user_agent): void
  {
    $this->user_agent = $user_agent;
  }

  public function getUser(): ?User
  {
    return $this->user;
  }

  public function setUser(?User $user): void
  {
    $this->user = $user;
  }

  public function getReferrer(): ?string
  {
    return $this->referrer;
  }

  public function setReferrer(?string $referrer): void
  {
    $this->referrer = $referrer;
  }

  public function getType(): string
  {
    return $this->type;
  }

  public function setType(string $type): void
  {
    $this->type = $type;
  }

  public function getClickedAt(): ?DateTime
  {
    return $this->clicked_at;
  }

  public function setClickedAt(DateTime $clicked_at): void
  {
    $this->clicked_at = $clicked_at;
  }
}
