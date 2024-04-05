<?php

declare(strict_types=1);

namespace App\DB\Entity\MediaLibrary;

use App\DB\Entity\Flavor;
use App\DB\EntityRepository\MediaLibrary\MediaPackageFileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * MediaPackageFile class. A MediaPackageFile is part of a MediaPackageCategory and represents an image, sound etc.
 *
 *                                          Media Library example:
 *
 *                          Media Package 1                           Media Package 2
 *                       /                 \                                |
 *               Category 1               Category 2                    Category 3
 *              /     |    \              /        \                        |
 *         File 1  File 2  File 3      File 4    File 5                  File 6
 */
#[ORM\Table(name: 'media_package_file')]
#[ORM\Entity(repositoryClass: MediaPackageFileRepository::class)]
class MediaPackageFile
{
  public ?File $file = null;

  public ?int $removed_id = null;

  public ?string $old_extension = null;

  #[ORM\Id]
  #[ORM\Column(type: 'integer')]
  #[ORM\GeneratedValue(strategy: 'AUTO')]
  protected ?int $id = null;

  #[ORM\Column(type: 'text', nullable: false)]
  protected string $name = '';

  #[ORM\Column(type: 'string')]
  protected string $extension = '';

  #[ORM\Column(type: 'text', nullable: true)]
  protected ?string $url = null;

  #[ORM\ManyToOne(targetEntity: MediaPackageCategory::class, inversedBy: 'files')]
  protected ?MediaPackageCategory $category = null;

  #[ORM\Column(type: 'boolean')]
  protected bool $active = true;

  #[ORM\Column(type: 'integer')]
  protected int $downloads = 0;

  #[Assert\Count(min: 1)]
  #[ORM\ManyToMany(targetEntity: Flavor::class, inversedBy: 'media_package_files', fetch: 'EXTRA_LAZY')]
  protected Collection $flavors;

  #[ORM\Column(type: 'string', nullable: true)]
  protected ?string $author = null;

  public function __construct()
  {
    $this->flavors = new ArrayCollection();
  }

  public function getActive(): bool
  {
    return $this->active;
  }

  public function setActive(bool $active): MediaPackageFile
  {
    $this->active = $active;

    return $this;
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function setId(int $id): void
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

  public function getUrl(): ?string
  {
    return $this->url;
  }

  public function setUrl(?string $url): void
  {
    $this->url = $url;
  }

  public function getCategory(): ?MediaPackageCategory
  {
    return $this->category;
  }

  public function setCategory(MediaPackageCategory $category): void
  {
    $this->category = $category;
  }

  public function setExtension(string $extension): MediaPackageFile
  {
    $this->extension = $extension;

    return $this;
  }

  public function getExtension(): string
  {
    return $this->extension;
  }

  public function getFile(): ?File
  {
    return $this->file;
  }

  public function setFile(File $file): void
  {
    $this->file = $file;
  }

  public function getRemovedId(): ?int
  {
    return $this->removed_id;
  }

  public function setRemovedId(?int $removed_id): void
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

  public function getDownloads(): int
  {
    return $this->downloads;
  }

  public function setDownloads(int $downloads): void
  {
    $this->downloads = $downloads;
  }

  public function getAuthor(): ?string
  {
    return $this->author;
  }

  public function setAuthor(?string $author): void
  {
    $this->author = $author;
  }

  public function addFlavor(Flavor $flavor): void
  {
    if ($this->flavors->contains($flavor)) {
      return;
    }
    $this->flavors[] = $flavor;
  }

  public function removeFlavor(Flavor $flavor): void
  {
    if (!$this->flavors->contains($flavor)) {
      return;
    }
    $this->flavors->removeElement($flavor);
  }

  public function getFlavors(): Collection
  {
    return $this->flavors;
  }

  /** @deprecated */
  public function getFlavor(): ?string
  {
    if ($this->getFlavors()->isEmpty()) {
      return 'pocketcode';
    }

    return $this->getFlavors()->first()->getName();
  }

  public function getFlavorNames(): array
  {
    $return = [];
    /** @var Flavor $flavor */
    foreach ($this->getFlavors() as $flavor) {
      $return[] = $flavor->getName();
    }

    return $return;
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
}
