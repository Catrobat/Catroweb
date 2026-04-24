<?php

declare(strict_types=1);

namespace App\DB\Entity\Studio;

use App\DB\Entity\User\User;
use App\DB\EntityRepository\Studios\StudioJoinRequestRepository;
use App\DB\Generator\MyUuidGenerator;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'studio_join_requests')]
#[ORM\Index(name: 'idx_join_request_studio_status', columns: ['studio', 'status'])]
#[ORM\Entity(repositoryClass: StudioJoinRequestRepository::class)]
class StudioJoinRequest
{
  public const string STATUS_PENDING = 'pending';

  public const string STATUS_APPROVED = 'approved';

  public const string STATUS_DECLINED = 'declined';

  public const string STATUS_JOINED = 'joined';

  #[ORM\Id]
  #[ORM\GeneratedValue(strategy: 'CUSTOM')]
  #[ORM\CustomIdGenerator(class: MyUuidGenerator::class)]
  #[ORM\Column(type: Types::GUID)]
  protected ?string $id = null;

  #[ORM\JoinColumn(name: 'user', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
  #[ORM\ManyToOne(targetEntity: User::class, cascade: ['persist'])]
  protected User $user;

  #[ORM\JoinColumn(name: 'studio', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
  #[ORM\ManyToOne(targetEntity: Studio::class, cascade: ['persist'], inversedBy: 'join_requests')]
  protected Studio $studio;

  #[ORM\Column(type: Types::STRING, length: 20)]
  protected ?string $status = null;

  public function getId(): ?string
  {
    return $this->id;
  }

  public function getUser(): ?User
  {
    return $this->user;
  }

  public function setUser(User $user): self
  {
    $this->user = $user;

    return $this;
  }

  public function getStudio(): ?Studio
  {
    return $this->studio;
  }

  public function setStudio(Studio $studio): self
  {
    $this->studio = $studio;

    return $this;
  }

  public function getStatus(): ?string
  {
    return $this->status;
  }

  public function setStatus(string $status): void
  {
    $this->status = $status;
  }
}
