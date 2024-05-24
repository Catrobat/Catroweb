<?php

declare(strict_types=1);

namespace App\DB\Entity\User;

use App\DB\EntityRepository\User\ResetPasswordRequestRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestTrait;

#[ORM\Table(name: 'reset_password_request')]
#[ORM\Entity(repositoryClass: ResetPasswordRequestRepository::class)]
class ResetPasswordRequest implements ResetPasswordRequestInterface
{
  use ResetPasswordRequestTrait;

  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column(type: Types::INTEGER)]
  private ?int $id = null;

  public function __construct(
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'reset_password_requests')]
    private readonly User $user,
    \DateTimeInterface $expiresAt,
    string $selector,
    string $hashedToken
  ) {
    $this->initialize($expiresAt, $selector, $hashedToken);
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  #[\Override]
  public function getUser(): object
  {
    return $this->user;
  }
}
