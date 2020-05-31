<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="tags", indexes={@Index(columns={"en", "de", "it", "fr"}, flags={"fulltext"})})
 * @ORM\Entity(repositoryClass="App\Repository\TagRepository")
 */
class Tag
{
  /**
   * @ORM\Id
   * @ORM\Column(type="integer")
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  protected ?int $id = null;

  /**
   * @ORM\ManyToMany(targetEntity="\App\Entity\Program", mappedBy="tags")
   */
  protected Collection $programs;

  /**
   * @ORM\Column(type="string", nullable=true)
   */
  protected ?string $en = null;

  /**
   * @ORM\Column(type="string", nullable=true)
   */
  protected ?string $de = null;

  /**
   * @ORM\Column(type="string", nullable=true)
   */
  protected ?string $it = null;

  /**
   * @ORM\Column(type="string", nullable=true)
   */
  protected ?string $fr = null;

  public function __construct()
  {
    $this->programs = new ArrayCollection();
  }

  public function __toString()
  {
    return $this->id.'';
  }

  public function getId(): ?int
  {
    return $this->id;
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

  public function getEn(): ?string
  {
    return $this->en;
  }

  public function setEn(?string $en): void
  {
    $this->en = $en;
  }

  public function getDe(): ?string
  {
    return $this->de;
  }

  public function setDe(?string $de): void
  {
    $this->de = $de;
  }

  public function getIt(): ?string
  {
    return $this->it;
  }

  public function setIt(?string $it): void
  {
    $this->it = $it;
  }

  public function getFr(): ?string
  {
    return $this->fr;
  }

  public function setFr(?string $fr): void
  {
    $this->fr = $fr;
  }
}
