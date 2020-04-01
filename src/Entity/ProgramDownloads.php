<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="program_downloads")
 */
class ProgramDownloads
{
  /**
   * @ORM\Id
   * @ORM\Column(type="integer")
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  protected ?int $id = null;

  /**
   * @ORM\ManyToOne(targetEntity="\App\Entity\Program", inversedBy="program_downloads")
   * @ORM\JoinColumn(name="program_id", referencedColumnName="id", nullable=false)
   */
  protected Program $program;

  /**
   * @ORM\Column(type="integer", nullable=true)
   */
  protected ?int $recommended_by_page_id = null;

  /**
   * @ORM\ManyToOne(targetEntity="\App\Entity\Program")
   * @ORM\JoinColumn(name="recommended_by_program_id", referencedColumnName="id", nullable=true)
   */
  protected ?Program $recommended_by_program = null;

  /**
   * @ORM\Column(type="boolean", options={"default": false}, nullable=true)
   */
  protected bool $user_specific_recommendation = false;

  /**
   * @ORM\ManyToOne(targetEntity="\App\Entity\Program")
   * @ORM\JoinColumn(name="rec_from_program_id", referencedColumnName="id", nullable=true)
   */
  protected ?Program $recommended_from_program_via_tag = null;

  /**
   * @ORM\Column(type="datetime")
   */
  protected ?DateTime $downloaded_at = null;

  /**
   * @ORM\Column(type="text", options={"default": ""})
   */
  protected string $ip = '';

  /**
   * @ORM\Column(type="text", options={"default": ""}, nullable=true)
   */
  protected ?string $country_code = '';

  /**
   * @ORM\Column(type="text", options={"default": ""}, nullable=true)
   */
  protected ?string $country_name = '';

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

  public function getProgram(): Program
  {
    return $this->program;
  }

  public function setProgram(Program $program): void
  {
    $this->program = $program;
  }

  public function getRecommendedFromProgramViaTag(): ?Program
  {
    return $this->recommended_from_program_via_tag;
  }

  public function setRecommendedFromProgramViaTag(?Program $recommended_from_program_via_tag): void
  {
    $this->recommended_from_program_via_tag = $recommended_from_program_via_tag;
  }

  public function getDownloadedAt(): ?DateTime
  {
    return $this->downloaded_at;
  }

  public function setDownloadedAt(?DateTime $downloaded_at): void
  {
    $this->downloaded_at = $downloaded_at;
  }

  public function getIp(): string
  {
    return $this->ip;
  }

  public function setIp(string $ip): void
  {
    $this->ip = $ip;
  }

  public function getCountryCode(): ?string
  {
    return $this->country_code;
  }

  public function setCountryCode(?string $country_code): void
  {
    $this->country_code = $country_code;
  }

  public function getCountryName(): ?string
  {
    return $this->country_name;
  }

  public function setCountryName(?string $country_name): void
  {
    $this->country_name = $country_name;
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

  public function getRecommendedByPageId(): ?int
  {
    return $this->recommended_by_page_id;
  }

  public function setRecommendedByPageId(int $recommended_by_page_id): void
  {
    $this->recommended_by_page_id = $recommended_by_page_id;
  }

  public function getRecommendedByProgram(): ?Program
  {
    return $this->recommended_by_program;
  }

  public function setRecommendedByProgram(Program $recommended_by_program): void
  {
    $this->recommended_by_program = $recommended_by_program;
  }

  public function getUserSpecificRecommendation(): bool
  {
    return $this->user_specific_recommendation;
  }

  public function setUserSpecificRecommendation(bool $is_user_specific_recommendation): void
  {
    $this->user_specific_recommendation = $is_user_specific_recommendation;
  }

  public function setReferrer(?string $referrer): void
  {
    $this->referrer = $referrer;
  }
}
