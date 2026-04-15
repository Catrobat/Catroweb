<?php

declare(strict_types=1);

namespace App\DB\Entity\User\Notifications;

use App\DB\Entity\Project\Project;
use App\DB\Entity\User\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class ProjectExpiringNotification extends CatroNotification
{
  public function __construct(
    User $user,
    #[ORM\JoinColumn(name: 'program_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Project::class)]
    private ?Project $project = null,
    #[ORM\Column(name: 'expiry_days', type: Types::INTEGER, nullable: true)]
    private ?int $expiry_days = null,
  ) {
    parent::__construct($user, '', '', 'project_expiring');
  }

  public function getProject(): ?Project
  {
    return $this->project;
  }

  public function getExpiryDays(): ?int
  {
    return $this->expiry_days;
  }
}
