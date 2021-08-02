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
   * @ORM\Column(name="description", type="text", length=3000, nullable=false)
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

  public function setId(?string $id): Studio
  {
    $this->id = $id;

    return $this;
  }

  public function getName(): string
  {
    return $this->name;
  }

  public function setName(string $name): Studio
  {
    $this->name = $name;

    return $this;
  }

  public function getDescription(): string
  {
    return $this->description;
  }

  public function setDescription(string $description): Studio
  {
    $this->description = $description;

    return $this;
  }

  public function isIsPublic(): bool
  {
    return $this->is_public;
  }

  public function setIsPublic(bool $is_public): Studio
  {
    $this->is_public = $is_public;

    return $this;
  }

  public function isIsEnabled(): bool
  {
    return $this->is_enabled;
  }

  public function setIsEnabled(bool $is_enabled): Studio
  {
    $this->is_enabled = $is_enabled;

    return $this;
  }

  public function isAllowComments(): bool
  {
    return $this->allow_comments;
  }

  public function setAllowComments(bool $allow_comments): Studio
  {
    $this->allow_comments = $allow_comments;

    return $this;
  }

  public function getCoverPath(): ?string
  {
    return $this->cover_path;
  }

  public function setCoverPath(?string $cover_path): Studio
  {
    $this->cover_path = $cover_path;

    return $this;
  }

  public function getUpdatedOn(): ?DateTime
  {
    return $this->updated_on;
  }

  public function setUpdatedOn(?DateTime $updated_on): Studio
  {
    $this->updated_on = $updated_on;

    return $this;
  }

  public function getCreatedOn(): ?DateTime
  {
    return $this->created_on;
  }

  public function setCreatedOn(?DateTime $created_on): Studio
  {
    $this->created_on = $created_on;

    return $this;
  }
}
