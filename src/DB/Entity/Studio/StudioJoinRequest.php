<?php

namespace App\DB\Entity\Studio;

use App\DB\Entity\User\User;
use App\DB\EntityRepository\Studios\StudioJoinRequestRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=StudioJoinRequestRepository::class)
 *
 * @ORM\Table(name="studio_join_requests")
 */
class StudioJoinRequest
{
  public const STATUS_PENDING = 'pending';
  public const STATUS_APPROVED = 'approved';
  public const STATUS_DECLINED = 'declined';

  public const STATUS_JOINED = 'joined';

  /**
   * @ORM\Id
   *
   * @ORM\GeneratedValue
   *
   * @ORM\Column(type="integer")
   */
  protected ?int $id = null;

  /**
   * @ORM\ManyToOne(targetEntity=User::class, cascade={"persist"})
   *
   * @ORM\JoinColumn(name="user", referencedColumnName="id", nullable=false, onDelete="CASCADE")
   */
  protected User $user;

  /**
   * @ORM\ManyToOne(targetEntity=Studio::class, cascade={"persist"})
   *
   * @ORM\JoinColumn(name="studio", referencedColumnName="id", nullable=false, onDelete="CASCADE")
   */
  protected Studio $studio;

  /**
   * @ORM\Column(type="string", length=20)
   */
  protected ?string $status;

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getUser(): ?User
  {
    return $this->user;
  }

  public function setUser(?User $user): self
  {
    $this->user = $user;

    return $this;
  }

  public function getStudio(): ?Studio
  {
    return $this->studio;
  }

  public function setStudio(?Studio $studio): self
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
