<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="media_package")
 */
class MediaPackage
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
   * @ORM\Column(type="text", nullable=false)
   */
  protected ?string $nameUrl = null;

  /**
   * @ORM\ManyToMany(targetEntity="MediaPackageCategory", mappedBy="package")
   */
  protected Collection $categories;

  protected array $flavors = [];

  public function __construct()
  {
    $this->categories = new ArrayCollection();
  }

  public function __toString()
  {
    return (string) $this->name;
  }

  public function getFlavors(): array
  {
    return $this->flavors;
  }

  public function setFlavors(array $flavors): void
  {
    $this->flavors = $flavors;
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

  public function getNameUrl(): ?string
  {
    return $this->nameUrl;
  }

  public function setNameUrl(string $name_url): void
  {
    $this->nameUrl = $name_url;
  }

  public function getCategories(): Collection
  {
    return $this->categories;
  }

  public function setCategories(Collection $categories): void
  {
    $this->categories = $categories;
  }
}
