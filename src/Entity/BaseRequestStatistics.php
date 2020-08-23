<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

trait BaseRequestStatistics
{
  /**
   * @ORM\Id
   * @ORM\Column(type="integer")
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  protected ?int $id = null;

  /**
   * @ORM\ManyToOne(targetEntity="\App\Entity\User")
   * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
   */
  protected ?User $user = null;

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
   * @ORM\Column(type="string", options={"default": ""}, nullable=true)
   */
  protected ?string $referrer = null;

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

  public function setReferrer(?string $referrer): void
  {
    $this->referrer = $referrer;
  }
}
