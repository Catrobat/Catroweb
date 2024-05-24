<?php

declare(strict_types=1);

namespace App\DB\Entity\System;

use App\DB\EntityRepository\System\CronJobRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'cronjob')]
#[ORM\Entity(repositoryClass: CronJobRepository::class)]
class CronJob
{
  #[ORM\Column(name: 'name', type: Types::STRING, unique: true, nullable: false)]
  #[ORM\Id]
  protected string $name = '';

  #[ORM\Column(name: 'state', type: Types::STRING, nullable: false, options: ['default' => 'idle'])]
  protected string $state = 'idle';

  #[ORM\Column(name: 'cron_interval', type: Types::STRING, nullable: false, options: ['default' => '1 days'])]
  protected string $cron_interval = '1 days';

  #[ORM\Column(name: 'priority', type: Types::INTEGER, nullable: false, options: ['default' => 0])]
  protected int $priority = 0;

  #[ORM\Column(name: 'start_at', type: Types::DATETIME_MUTABLE, nullable: true)]
  protected ?\DateTime $start_at = null;

  #[ORM\Column(name: 'end_at', type: Types::DATETIME_MUTABLE, nullable: true)]
  protected ?\DateTime $end_at = null;

  #[ORM\Column(name: 'result_code', type: Types::INTEGER, nullable: true)]
  protected ?int $result_code = null;

  public function getName(): string
  {
    return $this->name;
  }

  public function setName(string $name): CronJob
  {
    $this->name = $name;

    return $this;
  }

  public function getState(): string
  {
    return $this->state;
  }

  public function setState(string $state): CronJob
  {
    $this->state = $state;

    return $this;
  }

  public function getCronInterval(): string
  {
    return $this->cron_interval;
  }

  public function setCronInterval(string $cron_interval): CronJob
  {
    $this->cron_interval = $cron_interval;

    return $this;
  }

  public function getPriority(): int
  {
    return $this->priority;
  }

  public function setPriority(int $priority): CronJob
  {
    $this->priority = $priority;

    return $this;
  }

  public function getStartAt(): ?\DateTime
  {
    return $this->start_at;
  }

  public function setStartAt(?\DateTime $start_at): CronJob
  {
    $this->start_at = $start_at;

    return $this;
  }

  public function getEndAt(): ?\DateTime
  {
    return $this->end_at;
  }

  public function setEndAt(?\DateTime $end_at): CronJob
  {
    $this->end_at = $end_at;

    return $this;
  }

  public function getResultCode(): ?int
  {
    return $this->result_code;
  }

  public function setResultCode(?int $result_code): CronJob
  {
    $this->result_code = $result_code;

    return $this;
  }
}
