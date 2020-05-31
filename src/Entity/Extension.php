<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="extension", indexes={@Index(columns={"name"}, flags={"fulltext"})})
 * @ORM\Entity(repositoryClass="App\Repository\ExtensionRepository")
 */
class Extension
{
  /**
   * @ORM\Id
   * @ORM\Column(type="integer")
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  protected ?int $id = null;

  /**
   * @ORM\Column(type="string", nullable=true)
   */
  protected ?string $name = null;

  /**
   * @ORM\Column(type="string", nullable=true)
   */
  protected ?string $prefix = null;

  /**
   * @ORM\ManyToMany(targetEntity="\App\Entity\Program", mappedBy="extensions")
   */
  protected Collection $programs;

  public function __construct()
  {
    $this->programs = new ArrayCollection();
  }

  public function __toString()
  {
    return $this->name;
  }

  public function addProgram(Program $program): void
  {
    if ($this->programs->contains($program))
    {
      return;
    }
    $this->programs->add($program);
  }

  public function removeProgram(Program $program): void
  {
    $this->programs->removeElement($program);
  }

  public function getPrograms(): Collection
  {
    return $this->programs;
  }

  public function getName(): ?string
  {
    return $this->name;
  }

  public function setName(?string $name): void
  {
    $this->name = $name;
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getPrefix(): ?string
  {
    return $this->prefix;
  }

  public function setPrefix(?string $prefix): void
  {
    $this->prefix = $prefix;
  }

  public function removeAllPrograms(): void
  {
    foreach ($this->programs as $program)
    {
      $this->removeProgram($program);
    }
  }
}
