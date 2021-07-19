<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Studio.
 *
 * @ORM\Entity(repositoryClass="App\Repository\Studios\StudioRepository")
 * @ORM\Table(name="studio")
 */
class Studio
{
  /**
   * @ORM\Id
   * @ORM\Column(name="id", type="guid")
   * @ORM\GeneratedValue(strategy="CUSTOM")
   * @ORM\CustomIdGenerator(class="App\Utils\MyUuidGenerator")
   */
  protected ?string $id = null;

  /**
   * @ORM\Column(name="name", type="string", nullable=false, unique=true)
   */
  protected string $name;

  /**
   * @ORM\Column(name="description", type="text", length=300, nullable=false)
   */
  protected string $description;

  /**
   * @ORM\Column(name="is_public", type="boolean", options={"default": true})
   */
  protected bool $is_public = true;

  /**
   * @ORM\Column(name="is_enabled", type="boolean", options={"default": true})
   */
  protected bool $is_enabled = true;

  /**
   * @ORM\Column(name="allow_comments", type="boolean", options={"default": true})
   */
  protected bool $allow_comments = true;

  /**
   * @ORM\Column(name="cover_path", type="string", length=300, nullable=true)
   */
  protected ?string $cover_path = null;

  /**
   * @ORM\Column(name="updated_on", type="datetime", nullable=true)
   */
  protected ?DateTime $updated_on;

  /**
   * @ORM\Column(name="created_on", type="datetime", nullable=false)
   */
  protected ?DateTime $created_on;

  public function getId(): ?string
  {
    return $this->id;
  }

  public function setId(?string $id): void
  {
    $this->id = $id;
  }

  public function getName(): string
  {
    return $this->name;
  }

  public function setName(string $name): void
  {
    $this->name = $name;
  }

  public function getDescription(): string
  {
    return $this->description;
  }

  public function setDescription(string $description): void
  {
    $this->description = $description;
  }

  public function isPublic(): bool
  {
    return $this->is_public;
  }

  public function setIsPublic(bool $is_public): void
  {
    $this->is_public = $is_public;
  }

  public function isEnabled(): bool
  {
    return $this->is_enabled;
  }

  public function setIsEnabled(bool $is_enabled): void
  {
    $this->is_enabled = $is_enabled;
  }

  public function isAllowComments(): bool
  {
    return $this->allow_comments;
  }

  public function setAllowComments(bool $allow_comments): void
  {
    $this->allow_comments = $allow_comments;
  }

  public function getCoverPath(): ?string
  {
    return $this->cover_path;
  }

  public function setCoverPath(?string $cover_path): void
  {
    $this->cover_path = $cover_path;
  }

  public function getUpdatedOn(): ?DateTime
  {
    return $this->updated_on;
  }

  public function setUpdatedOn(?DateTime $updated_on): void
  {
    $this->updated_on = $updated_on;
  }

  public function getCreatedOn(): ?DateTime
  {
    return $this->created_on;
  }

  public function setCreatedOn(?DateTime $created_on): void
  {
    $this->created_on = $created_on;
  }
}
