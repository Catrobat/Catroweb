<?php

declare(strict_types=1);

namespace App\DB\Entity\Project;

use App\DB\Entity\User\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'program_downloads')]
#[ORM\Entity]
class ProgramDownloads
{
  final public const string TYPE_PROJECT = 'project';
  final public const string TYPE_APK = 'apk';

  #[ORM\Column(name: 'id', type: Types::INTEGER)]
  #[ORM\Id]
  #[ORM\GeneratedValue(strategy: 'AUTO')]
  protected ?int $id = null;

  #[ORM\JoinColumn(name: 'program_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
  #[ORM\ManyToOne(targetEntity: Program::class, inversedBy: 'program_downloads')]
  protected Program $program;

  #[ORM\JoinColumn(name: 'user', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
  #[ORM\ManyToOne(targetEntity: User::class)]
  protected ?User $user = null;

  #[ORM\Column(type: Types::DATETIME_MUTABLE)]
  protected ?\DateTime $downloaded_at = null;

  #[ORM\Column(type: Types::STRING, options: ['default' => 'project'])]
  protected ?string $type = self::TYPE_PROJECT;

  public function getId(): ?int
  {
    return $this->id;
  }

  public function setId(int $id): ProgramDownloads
  {
    $this->id = $id;

    return $this;
  }

  public function getProgram(): Program
  {
    return $this->program;
  }

  public function setProgram(Program $program): ProgramDownloads
  {
    $this->program = $program;

    return $this;
  }

  public function getUser(): ?User
  {
    return $this->user;
  }

  public function setUser(?User $user): ProgramDownloads
  {
    $this->user = $user;

    return $this;
  }

  public function getDownloadedAt(): ?\DateTime
  {
    return $this->downloaded_at;
  }

  public function setDownloadedAt(?\DateTime $downloaded_at): ProgramDownloads
  {
    $this->downloaded_at = $downloaded_at;

    return $this;
  }

  public function getType(): ?string
  {
    return $this->type;
  }

  public function setType(?string $type): ProgramDownloads
  {
    $this->type = $type;

    return $this;
  }
}
