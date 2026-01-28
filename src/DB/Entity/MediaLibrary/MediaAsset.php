<?php

declare(strict_types=1);

namespace App\DB\Entity\MediaLibrary;

use App\DB\Entity\Flavor;
use App\DB\EntityRepository\MediaLibrary\MediaAssetRepository;
use App\DB\Generator\MyUuidGenerator;
use App\Utils\TimeUtils;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;

/**
 * MediaAsset class. Represents a single media file (image or sound) in the media library.
 */
#[ORM\Table(name: 'media_asset')]
#[ORM\Index(name: 'name_idx', columns: ['name'])]
#[ORM\Index(name: 'file_type_idx', columns: ['file_type'])]
#[ORM\Index(name: 'active_idx', columns: ['active'])]
#[ORM\Index(name: 'downloads_idx', columns: ['downloads'])]
#[ORM\Index(name: 'created_at_idx', columns: ['created_at'])]
#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: MediaAssetRepository::class)]
class MediaAsset implements \Stringable
{
  public ?File $file = null;

  public ?string $removed_id = null;

  public ?string $old_extension = null;

  #[ORM\Id]
  #[ORM\Column(name: 'id', type: Types::GUID)]
  #[ORM\GeneratedValue(strategy: 'CUSTOM')]
  #[ORM\CustomIdGenerator(class: MyUuidGenerator::class)]
  protected string $id;

  #[ORM\Column(type: Types::STRING, length: 300, nullable: false)]
  protected string $name = '';

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  protected ?string $description = null;

  #[ORM\Column(type: Types::STRING, length: 20, nullable: false, enumType: MediaFileType::class)]
  protected MediaFileType $file_type = MediaFileType::IMAGE;

  #[ORM\Column(type: Types::STRING, length: 10)]
  protected string $extension = '';

  #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
  protected ?string $author = null;

  #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
  protected int $downloads = 0;

  #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
  protected bool $active = true;

  #[ORM\Column(type: Types::DATETIME_MUTABLE)]
  protected ?\DateTime $created_at = null;

  #[ORM\Column(type: Types::DATETIME_MUTABLE)]
  protected ?\DateTime $updated_at = null;

  #[ORM\ManyToOne(targetEntity: MediaCategory::class, inversedBy: 'assets')]
  #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
  protected ?MediaCategory $category = null;

  /**
   * @var Collection<int, Flavor>
   */
  #[ORM\ManyToMany(targetEntity: Flavor::class, fetch: 'EXTRA_LAZY')]
  #[ORM\JoinTable(name: 'media_asset_flavor')]
  protected Collection $flavors;

  public function __construct()
  {
    $this->flavors = new ArrayCollection();
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

  public function getFileType(): MediaFileType
  {
    return $this->file_type;
  }

  public function setFileType(MediaFileType $file_type): void
  {
    $this->file_type = $file_type;
  }

  public function getExtension(): string
  {
    return $this->extension;
  }

  public function setExtension(string $extension): void
  {
    $this->extension = $extension;
  }

  public function getAuthor(): ?string
  {
    return $this->author;
  }

  public function setAuthor(?string $author): void
  {
    $this->author = $author;
  }

  public function getDownloads(): int
  {
    return $this->downloads;
  }

  public function setDownloads(int $downloads): void
  {
    $this->downloads = $downloads;
  }

  public function incrementDownloads(): void
  {
    ++$this->downloads;
  }

  public function getActive(): bool
  {
    return $this->active;
  }

  public function setActive(bool $active): void
  {
    $this->active = $active;
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

  public function getCategory(): ?MediaCategory
  {
    return $this->category;
  }

  public function setCategory(MediaCategory $category): void
  {
    $this->category = $category;
  }

  public function getFile(): ?File
  {
    return $this->file;
  }

  public function setFile(File $file): void
  {
    $this->file = $file;
  }

  public function getRemovedId(): ?string
  {
    return $this->removed_id;
  }

  public function setRemovedId(?string $removed_id): void
  {
    $this->removed_id = $removed_id;
  }

  public function getOldExtension(): ?string
  {
    return $this->old_extension;
  }

  public function setOldExtension(string $old_extension): void
  {
    $this->old_extension = $old_extension;
  }

  public function getFlavors(): Collection
  {
    return $this->flavors;
  }

  public function addFlavor(Flavor $flavor): void
  {
    if (!$this->flavors->contains($flavor)) {
      $this->flavors->add($flavor);
    }
  }

  public function removeFlavor(Flavor $flavor): void
  {
    if ($this->flavors->contains($flavor)) {
      $this->flavors->removeElement($flavor);
    }
  }

  public function clearFlavors(): void
  {
    $this->flavors->clear();
  }

  public function setFlavors(?iterable $flavors): void
  {
    $this->clearFlavors();
    if (null !== $flavors) {
      foreach ($flavors as $flavor) {
        $this->addFlavor($flavor);
      }
    }
  }

  /**
   * @return array<string>
   */
  public function getFlavorNames(): array
  {
    $return = [];
    /** @var Flavor $flavor */
    foreach ($this->getFlavors() as $flavor) {
      $return[] = $flavor->getName() ?? '';
    }

    return $return;
  }

  public function isImage(): bool
  {
    return MediaFileType::IMAGE === $this->file_type;
  }

  public function isSound(): bool
  {
    return MediaFileType::SOUND === $this->file_type;
  }
}
