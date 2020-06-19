<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="flavor")
 */
class Flavor
{
  /**
   * @ORM\Id
   * @ORM\Column(type="integer")
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  protected ?int $id = null;

  /**
   * @ORM\Column(type="string", length=255, unique=true)
   */
  protected ?string $name = null;

  /**
   * @ORM\ManyToMany(targetEntity="\App\Entity\MediaPackageFile", mappedBy="flavors", fetch="EXTRA_LAZY")
   */
  protected Collection $media_package_files;

  public function __construct()
  {
    $this->media_package_files = new ArrayCollection();
  }

  public function __toString(): string
  {
    return $this->getName() ?? '';
  }

  public function setId(?int $id): void
  {
    $this->id = $id;
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function setName(?string $name): void
  {
    $this->name = $name;
  }

  public function getName(): ?string
  {
    return $this->name;
  }

  public function addMediaPackageFile(MediaPackageFile $media_package_file): void
  {
    if ($this->media_package_files->contains($media_package_file))
    {
      return;
    }
    $this->media_package_files[] = $media_package_file;
  }

  public function removeMediaPackageFile(MediaPackageFile $media_package_file): void
  {
    if (!$this->media_package_files->contains($media_package_file))
    {
      return;
    }
    $this->media_package_files->removeElement($media_package_file);
  }

  public function getMediaPackageFiles(): Collection
  {
    return $this->media_package_files;
  }
}
