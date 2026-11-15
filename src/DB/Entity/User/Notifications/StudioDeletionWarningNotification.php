<?php

declare(strict_types=1);

namespace App\DB\Entity\User\Notifications;

use App\DB\Entity\Studio\Studio;
use App\DB\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * Tells a studio admin that their empty, inactive studio will be deleted. The studio name is kept in the inherited
 * message column, so the notice still reads right after the studio is gone.
 */
#[ORM\Entity]
class StudioDeletionWarningNotification extends CatroNotification
{
  public const int DAYS_UNTIL_DELETION = 30;

  public function __construct(
    User $user,
    #[ORM\JoinColumn(name: 'studio_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Studio::class)]
    private ?Studio $studio = null,
  ) {
    parent::__construct($user, '', $studio?->getName() ?? '', 'studio_deletion_warning');
  }

  public function getStudio(): ?Studio
  {
    return $this->studio;
  }

  public function getStudioName(): string
  {
    return $this->getMessage();
  }
}
