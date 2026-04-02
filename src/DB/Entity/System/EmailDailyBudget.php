<?php

declare(strict_types=1);

namespace App\DB\Entity\System;

use App\DB\EntityRepository\System\EmailDailyBudgetRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'email_daily_budget')]
#[ORM\Entity(repositoryClass: EmailDailyBudgetRepository::class)]
class EmailDailyBudget
{
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column(type: Types::INTEGER)]
  protected ?int $id = null;

  #[ORM\Column(name: 'date', type: Types::DATE_MUTABLE, unique: true, nullable: false)]
  protected \DateTime $date;

  #[ORM\Column(name: 'total_sent', type: Types::INTEGER, nullable: false, options: ['default' => 0])]
  protected int $total_sent = 0;

  #[ORM\Column(name: 'verification_sent', type: Types::INTEGER, nullable: false, options: ['default' => 0])]
  protected int $verification_sent = 0;

  #[ORM\Column(name: 'reset_sent', type: Types::INTEGER, nullable: false, options: ['default' => 0])]
  protected int $reset_sent = 0;

  #[ORM\Column(name: 'consent_sent', type: Types::INTEGER, nullable: false, options: ['default' => 0])]
  protected int $consent_sent = 0;

  #[ORM\Column(name: 'admin_sent', type: Types::INTEGER, nullable: false, options: ['default' => 0])]
  protected int $admin_sent = 0;

  #[ORM\Column(name: 'management_sent', type: Types::INTEGER, nullable: false, options: ['default' => 0])]
  protected int $management_sent = 0;

  public function __construct()
  {
    $this->date = new \DateTime('today');
  }

  public function getDate(): \DateTime
  {
    return $this->date;
  }

  public function setDate(\DateTime $date): self
  {
    $this->date = $date;

    return $this;
  }

  public function getTotalSent(): int
  {
    return $this->total_sent;
  }

  public function setTotalSent(int $total_sent): self
  {
    $this->total_sent = $total_sent;

    return $this;
  }

  public function getVerificationSent(): int
  {
    return $this->verification_sent;
  }

  public function setVerificationSent(int $verification_sent): self
  {
    $this->verification_sent = $verification_sent;

    return $this;
  }

  public function getResetSent(): int
  {
    return $this->reset_sent;
  }

  public function setResetSent(int $reset_sent): self
  {
    $this->reset_sent = $reset_sent;

    return $this;
  }

  public function getConsentSent(): int
  {
    return $this->consent_sent;
  }

  public function setConsentSent(int $consent_sent): self
  {
    $this->consent_sent = $consent_sent;

    return $this;
  }

  public function getAdminSent(): int
  {
    return $this->admin_sent;
  }

  public function setAdminSent(int $admin_sent): self
  {
    $this->admin_sent = $admin_sent;

    return $this;
  }

  public function getManagementSent(): int
  {
    return $this->management_sent;
  }

  public function setManagementSent(int $management_sent): self
  {
    $this->management_sent = $management_sent;

    return $this;
  }

  public function incrementType(string $type): void
  {
    match ($type) {
      'verification' => $this->verification_sent++,
      'reset' => $this->reset_sent++,
      'consent' => $this->consent_sent++,
      'admin' => $this->admin_sent++,
      'management' => $this->management_sent++,
      default => throw new \InvalidArgumentException(sprintf('Unknown email type: %s', $type)),
    };
    ++$this->total_sent;
  }

  public function getSentByType(string $type): int
  {
    return match ($type) {
      'verification' => $this->verification_sent,
      'reset' => $this->reset_sent,
      'consent' => $this->consent_sent,
      'admin' => $this->admin_sent,
      'management' => $this->management_sent,
      default => throw new \InvalidArgumentException(sprintf('Unknown email type: %s', $type)),
    };
  }
}
