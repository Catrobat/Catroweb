<?php

declare(strict_types=1);

namespace App\DB\Entity\Project;

use App\DB\Entity\User\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'program_downloads')]
#[ORM\Index(name: 'pd_program_idx', columns: ['program_id'])]
#[ORM\Index(name: 'pd_downloaded_at_idx', columns: ['downloaded_at'])]
#[ORM\Index(name: 'pd_user_idx', columns: ['user'])]
#[ORM\Entity]
class ProjectDownloads
{
  final public const string TYPE_PROJECT = 'project';

  #[ORM\Column(name: 'id', type: Types::INTEGER)]
  #[ORM\Id]
  #[ORM\GeneratedValue(strategy: 'AUTO')]
  protected ?int $id = null;

  #[ORM\JoinColumn(name: 'program_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
  #[ORM\ManyToOne(targetEntity: Project::class, inversedBy: 'program_downloads')]
  protected Project $project;

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

  public function setId(int $id): ProjectDownloads
  {
    $this->id = $id;

    return $this;
  }

  public function getProject(): Project
  {
    return $this->project;
  }

  public function setProject(Project $project): ProjectDownloads
  {
    $this->project = $project;

    return $this;
  }

  public function getUser(): ?User
  {
    return $this->user;
  }

  public function setUser(?User $user): ProjectDownloads
  {
    $this->user = $user;

    return $this;
  }

  public function getDownloadedAt(): ?\DateTime
  {
    return $this->downloaded_at;
  }

  public function setDownloadedAt(?\DateTime $downloaded_at): ProjectDownloads
  {
    $this->downloaded_at = $downloaded_at;

    return $this;
  }

  public function getType(): ?string
  {
    return $this->type;
  }

  public function setType(?string $type): ProjectDownloads
  {
    $this->type = $type;

    return $this;
  }
}
