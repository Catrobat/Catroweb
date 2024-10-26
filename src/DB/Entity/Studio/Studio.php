<?php

declare(strict_types=1);

namespace App\DB\Entity\Studio;

use App\DB\Entity\User\Comment\UserComment;
use App\DB\EntityRepository\Studios\StudioRepository;
use App\DB\Generator\MyUuidGenerator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'studio')]
#[ORM\Entity(repositoryClass: StudioRepository::class)]
class Studio
{
  #[ORM\Id]
  #[ORM\Column(name: 'id', type: Types::GUID)]
  #[ORM\GeneratedValue(strategy: 'CUSTOM')]
  #[ORM\CustomIdGenerator(class: MyUuidGenerator::class)]
  protected ?string $id = null;

  #[ORM\Column(name: 'name', type: Types::STRING, unique: true, nullable: false)]
  protected string $name;

  #[ORM\Column(name: 'description', type: Types::TEXT, length: 3000, nullable: false)]
  protected string $description;

  #[ORM\Column(name: 'is_public', type: Types::BOOLEAN, options: ['default' => true])]
  protected bool $is_public = true;

  #[ORM\Column(name: 'is_enabled', type: Types::BOOLEAN, options: ['default' => true])]
  protected bool $is_enabled = true;

  #[ORM\Column(name: 'allow_comments', type: Types::BOOLEAN, options: ['default' => true])]
  protected bool $allow_comments = true;

  #[ORM\Column(name: 'cover_path', type: Types::STRING, length: 300, nullable: true)]
  protected ?string $cover_path = null;

  #[ORM\Column(name: 'updated_on', type: Types::DATETIME_MUTABLE, nullable: true)]
  protected ?\DateTime $updated_on = null;

  #[ORM\Column(name: 'created_on', type: Types::DATETIME_MUTABLE, nullable: false)]
  protected ?\DateTime $created_on = null;

  /**
   * When this studio is deleted, all its comments should be removed too.
   *
   * @var Collection<int, UserComment>
   */
  #[ORM\OneToMany(targetEntity: UserComment::class, mappedBy: 'studio', cascade: ['remove'], fetch: 'EXTRA_LAZY')]
  protected Collection $user_comments;

  /**
   * @var Collection<int, StudioJoinRequest>
   */
  #[ORM\OneToMany(targetEntity: StudioJoinRequest::class, mappedBy: 'studio', cascade: ['persist', 'remove'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
  protected Collection $join_requests;

  public function __construct()
  {
    $this->user_comments = new ArrayCollection();
    $this->join_requests = new ArrayCollection();
  }

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

  public function getCoverAssetPath(): ?string
  {
    return $this->cover_path;
  }

  public function setCoverAssetPath(?string $cover_path): Studio
  {
    $this->cover_path = $cover_path;

    return $this;
  }

  public function getUpdatedOn(): ?\DateTime
  {
    return $this->updated_on;
  }

  public function setUpdatedOn(?\DateTime $updated_on): Studio
  {
    $this->updated_on = $updated_on;

    return $this;
  }

  public function getCreatedOn(): ?\DateTime
  {
    return $this->created_on;
  }

  public function setCreatedOn(?\DateTime $created_on): Studio
  {
    $this->created_on = $created_on;

    return $this;
  }

  public function getJoinRequests(): Collection
  {
    return $this->join_requests;
  }
}
