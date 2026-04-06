<?php

declare(strict_types=1);

namespace App\DB\Entity\User\Notifications;

use App\DB\Entity\User\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class ProjectDeletedNotification extends CatroNotification
{
  public function __construct(
    User $user,
    #[ORM\Column(name: 'deleted_project_name', type: Types::STRING, length: 255, nullable: true)]
    private ?string $project_name = null,
  ) {
    parent::__construct($user, '', '', 'project_deleted');
  }

  public function getProjectName(): ?string
  {
    return $this->project_name;
  }
}
