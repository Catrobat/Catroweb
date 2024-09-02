<?php

declare(strict_types=1);

namespace App\DB\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class FeatureFlag
{
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column(type: Types::INTEGER)]
  private int $id;

  public function __construct(
    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $name,
    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $value,
  ) {
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function setId(int $id): self
  {
    $this->id = $id;

    return $this;
  }

  public function getName(): ?string
  {
    return $this->name;
  }

  public function setName(string $name): self
  {
    $this->name = $name;

    return $this;
  }

  public function getValue(): ?bool
  {
    return $this->value;
  }

  public function setValue(bool $value): self
  {
    $this->value = $value;

    return $this;
  }
}
