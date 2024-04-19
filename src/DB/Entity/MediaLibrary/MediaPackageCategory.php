<?php

declare(strict_types=1);

namespace App\DB\Entity\MediaLibrary;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * MediaPackageCategory class. A MediaPackageCategory is part of a MediaPackage and contains MediaPackageFiles.
 *
 *                                          Media Library example:
 *
 *                          Media Package 1                           Media Package 2
 *                       /                 \                                |
 *               Category 1               Category 2                    Category 3
 *              /     |    \              /        \                        |
 *         File 1  File 2  File 3      File 4    File 5                  File 6
 */
#[ORM\Table(name: 'media_package_category')]
#[ORM\Entity]
class MediaPackageCategory implements \Stringable
{
  #[ORM\Id]
  #[ORM\Column(type: 'integer')]
  #[ORM\GeneratedValue(strategy: 'AUTO')]
  protected ?int $id = null;

  #[ORM\Column(type: 'text', nullable: false)]
  protected ?string $name = null;

  #[ORM\ManyToMany(targetEntity: MediaPackage::class, inversedBy: 'categories')]
  protected Collection $package;

  #[ORM\OneToMany(targetEntity: MediaPackageFile::class, mappedBy: 'category')]
  protected Collection $files;

  #[ORM\Column(type: 'integer')]
  protected int $priority = 0;

  public function __construct()
  {
    $this->package = new ArrayCollection();
    $this->files = new ArrayCollection();
  }

  public function __toString(): string
  {
    if (count($this->package)) {
      $string = $this->name.' (';
      $count = count($this->package);

      for ($it = 0; $it < $count; ++$it) {
        $string .= $this->package[$it];

        if ($it < ($count - 1)) {
          $string .= ', ';
        }
      }

      return $string.')';
    }

    return $this->name ?? '';
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function setId(int $id): void
  {
    $this->id = $id;
  }

  public function getName(): ?string
  {
    return $this->name;
  }

  public function setName(string $name): void
  {
    $this->name = $name;
  }

  public function getPackage(): Collection
  {
    return $this->package;
  }

  /**
   * @param ArrayCollection<array-key, MediaPackage> $package
   */
  public function setPackage(ArrayCollection $package): void
  {
    $this->package = $package;
  }

  public function getPackageNames(): array
  {
    $return = [];
    /** @var MediaPackage $media_package */
    foreach ($this->getPackage() as $media_package) {
      $return[] = $media_package->getName();
    }

    return $return;
  }

  public function getFiles(): Collection
  {
    return $this->files;
  }

  public function setFiles(ArrayCollection $files): void
  {
    $this->files = $files;
  }

  public function getPriority(): int
  {
    return $this->priority;
  }

  public function setPriority(int $priority): void
  {
    $this->priority = $priority;
  }
}
