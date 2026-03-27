<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Security\Authentication;

use Symfony\Component\Security\Core\User\UserInterface;

final readonly class TestUser implements UserInterface
{
  /**
   * @param non-empty-string $identifier
   * @param list<string>     $roles
   */
  public function __construct(
    /** @var non-empty-string */
    private string $identifier,
    /** @var list<string> */
    private array $roles,
  ) {
  }

  #[\Override]
  public function getRoles(): array
  {
    return $this->roles;
  }

  #[\Override]
  public function eraseCredentials(): void
  {
  }

  /**
   * @return non-empty-string
   */
  #[\Override]
  public function getUserIdentifier(): string
  {
    return $this->identifier;
  }
}
