<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StarterCategoryRepository")
 * @ORM\Table(name="starter_category")
 */
class StarterCategory
{
  /**
   * @ORM\Id
   * @ORM\Column(type="integer")
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  protected ?int $id = null;

  /**
   * @ORM\Column(type="string", length=255)
   */
  protected ?string $name = null;

  /**
   * @ORM\Column(type="string", length=255)
   */
  protected ?string $alias = null;

  /**
   * @ORM\Column(type="integer", name="order_pos")
   */
  protected ?int $order = null;

  /**
   * @ORM\OneToMany(targetEntity="Program", mappedBy="category", fetch="EAGER")
   */
  private Collection $programs;

  public function __construct()
  {
    $this->programs = new ArrayCollection();
  }

  public function __toString(): string
  {
    return (string) $this->alias;
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function setId(int $id): void
  {
    $this->id = $id;
  }

  public function getPrograms(): Collection
  {
    return $this->programs;
  }

  public function setPrograms(Collection $programs): void
  {
    $this->programs = $programs;
  }

  public function addProgram(Program $program): void
  {
    $program->setCategory($this);
  }

  public function removeProgram(Program $program): void
  {
    $program->setCategory(null);
  }

  public function getName(): ?string
  {
    return $this->name;
  }

  public function setName(?string $name): void
  {
    $this->name = $name;
  }

  public function getAlias(): ?string
  {
    return $this->alias;
  }

  public function setAlias(string $alias): void
  {
    $this->alias = $alias;
  }

  public function getOrder(): ?int
  {
    return $this->order;
  }

  public function setOrder(int $order): void
  {
    $this->order = $order;
  }
}
