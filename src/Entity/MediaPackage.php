<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * MediaPackage class. A MediaPackage is part of the Media Library and contains MediaCategories which itself contain
 * MediaFiles.
 *
 *                                          Media Library example:
 *
 *                          Media Package 1                           Media Package 2
 *                       /                 \                                |
 *               Category 1               Category 2                    Category 3
 *              /     |    \              /        \                        |
 *         File 1  File 2  File 3      File 4    File 5                  File 6
 *
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

  public function __construct()
  {
    $this->categories = new ArrayCollection();
  }

  public function __toString()
  {
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
