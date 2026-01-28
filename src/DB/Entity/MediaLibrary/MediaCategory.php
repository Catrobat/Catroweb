<?php

declare(strict_types=1);

namespace App\DB\Entity\MediaLibrary;

use App\DB\EntityRepository\MediaLibrary\MediaCategoryRepository;
use App\DB\Generator\MyUuidGenerator;
use App\Utils\TimeUtils;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * MediaCategory class. A category groups media assets (images or sounds) for the media library.
 */
#[ORM\Table(name: 'media_category')]
#[ORM\Index(name: 'priority_idx', columns: ['priority'])]
#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: MediaCategoryRepository::class)]
class MediaCategory implements \Stringable
{
  #[ORM\Id]
  #[ORM\Column(name: 'id', type: Types::GUID)]
  #[ORM\GeneratedValue(strategy: 'CUSTOM')]
  #[ORM\CustomIdGenerator(class: MyUuidGenerator::class)]
  protected string $id;

  #[ORM\Column(type: Types::STRING, length: 255, nullable: false)]
  protected string $name;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  protected ?string $description = null;

  #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
  protected int $priority = 0;

  #[ORM\Column(type: Types::DATETIME_MUTABLE)]
  protected ?\DateTime $created_at = null;

  #[ORM\Column(type: Types::DATETIME_MUTABLE)]
  protected ?\DateTime $updated_at = null;

  /**
   * @var Collection<int, MediaAsset>
   */
  #[ORM\OneToMany(targetEntity: MediaAsset::class, mappedBy: 'category', cascade: ['persist'], fetch: 'EXTRA_LAZY')]
  protected Collection $assets;

  public function __construct()
  {
    $this->assets = new ArrayCollection();
  }

  #[\Override]
  public function __toString(): string
  {
    return $this->name;
  }

  /**
   * @throws \Exception
   */
  #[ORM\PrePersist]
  public function updateTimestamps(): void
  {
    if (!$this->getCreatedAt() instanceof \DateTime) {
      $this->setCreatedAt(TimeUtils::getDateTime());
    }
    $this->setUpdatedAt(TimeUtils::getDateTime());
  }

  /**
   * @throws \Exception
   */
  #[ORM\PreUpdate]
  public function updateUpdatedTimestamp(): void
  {
    $this->setUpdatedAt(TimeUtils::getDateTime());
  }

  public function getId(): string
  {
    return $this->id;
  }

  public function setId(string $id): void
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

  public function getDescription(): ?string
  {
    return $this->description;
  }

  public function setDescription(?string $description): void
  {
    $this->description = $description;
  }

  public function getPriority(): int
  {
    return $this->priority;
  }

  public function setPriority(int $priority): void
  {
    $this->priority = $priority;
  }

  public function getCreatedAt(): ?\DateTime
  {
    return $this->created_at;
  }

  public function setCreatedAt(\DateTime $created_at): void
  {
    $this->created_at = $created_at;
  }

  public function getUpdatedAt(): ?\DateTime
  {
    return $this->updated_at;
  }

  public function setUpdatedAt(\DateTime $updated_at): void
  {
    $this->updated_at = $updated_at;
  }

  public function getAssets(): Collection
  {
    return $this->assets;
  }

  public function addAsset(MediaAsset $asset): void
  {
    if (!$this->assets->contains($asset)) {
      $this->assets->add($asset);
      $asset->setCategory($this);
    }
  }

  public function removeAsset(MediaAsset $asset): void
  {
    if ($this->assets->contains($asset)) {
      $this->assets->removeElement($asset);
    }
  }
}
