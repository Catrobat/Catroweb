<?php

namespace App\DB\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class FeatureFlag
{
  /**
   * @ORM\Id
   *
   * @ORM\GeneratedValue
   *
   * @ORM\Column(type="integer")
   *
   * @ORM\property-read int $id
   */
  private $id;

  /**
   * @ORM\Column(type="string", length=255)
   */
  private $name;

  /**
   * @ORM\Column(type="boolean")
   */
  private $value;

  public function __construct(string $name, bool $value)
  {
    $this->name = $name;
    $this->value = $value;
  }

  public function getId(): ?int
  {
    return $this->id;
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
