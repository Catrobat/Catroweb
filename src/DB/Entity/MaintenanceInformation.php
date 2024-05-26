<?php

declare(strict_types=1);

namespace App\DB\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'maintanance_information')]
#[ORM\Entity]
class MaintenanceInformation
{
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column(type: Types::INTEGER)]
  protected int $id;

  #[ORM\Column(type: Types::STRING, length: 255)]
  protected string $internalTitle;

  #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
  protected ?string $icon = null;

  #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
  protected ?string $ltmCode = null;

  #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
  protected ?\DateTime $ltm_maintenanceStart = null;

  #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
  protected ?\DateTime $ltm_maintenanceEnd = null;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  protected ?string $ltm_additionalInformation = null;

  #[ORM\Column(type: Types::BOOLEAN)]
  protected bool $active;

  // Getters and setters
  public function getId(): ?int
  {
    return $this->id;
  }

  public function getInternalTitle(): ?string
  {
    return $this->internalTitle;
  }

  public function setInternalTitle(string $internalTitle): self
  {
    $this->internalTitle = $internalTitle;

    return $this;
  }

  public function getIcon(): ?string
  {
    return $this->icon;
  }

  public function setIcon(?string $icon): self
  {
    $this->icon = $icon;

    return $this;
  }

  public function getLtmCode(): ?string
  {
    return $this->ltmCode;
  }

  public function setLtmCode(?string $ltmCode): self
  {
    $this->ltmCode = $ltmCode;

    return $this;
  }

  public function getLtmMaintenanceStart(): ?\DateTime
  {
    return $this->ltm_maintenanceStart;
  }

  public function setLtmMaintenanceStart(?\DateTime $ltm_maintenanceStart): self
  {
    $this->ltm_maintenanceStart = $ltm_maintenanceStart;

    return $this;
  }

  public function getLtmMaintenanceEnd(): ?\DateTime
  {
    return $this->ltm_maintenanceEnd;
  }

  public function setLtmMaintenanceEnd(?\DateTime $ltm_maintenanceEnd): self
  {
    $this->ltm_maintenanceEnd = $ltm_maintenanceEnd;

    return $this;
  }

  public function getLtmAdditionalInformation(): ?string
  {
    return $this->ltm_additionalInformation;
  }

  public function setLtmAdditionalInformation(?string $ltm_additionalInformation): self
  {
    $this->ltm_additionalInformation = $ltm_additionalInformation;

    return $this;
  }

  public function isActive(): ?bool
  {
    return $this->active;
  }

  public function setActive(bool $active): self
  {
    $this->active = $active;

    return $this;
  }
}
