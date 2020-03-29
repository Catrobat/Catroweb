<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="media_package_category")
 */
class MediaPackageCategory
{
  /**
   * @ORM\Id
   * @ORM\Column(type="integer")
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  protected ?int $id = null;

  /**
   * @ORM\Column(type="text", nullable=false)
   */
  protected ?string $name = null;

  /**
   * @ORM\ManyToMany(targetEntity="MediaPackage", inversedBy="categories")
   */
  protected Collection $package;

  /**
   * @ORM\OneToMany(targetEntity="MediaPackageFile", mappedBy="category")
   */
  protected Collection $files;

  /**
   * @ORM\Column(type="integer")
   */
  protected int $priority = 0;

  public function __construct()
  {
    $this->package = new ArrayCollection();
    $this->files = new ArrayCollection();
  }

  public function __toString(): string
  {
    if (count($this->package))
    {
      $string = $this->name.' (';
      $count = count($this->package);

      for ($it = 0; $it < $count; ++$it)
      {
        $string .= $this->package[$it];

        if ($it < ($count - 1))
        {
          $string .= ', ';
        }
      }
      $string .= ')';

      return (string) $string;
    }

    return (string) $this->name;
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

  public function setPackage(Collection $package): void
  {
    $this->package = $package;
  }

  public function getFiles(): Collection
  {
    return $this->files;
  }

  public function setFiles(Collection $files): void
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
