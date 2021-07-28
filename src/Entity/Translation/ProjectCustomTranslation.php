<?php

namespace App\Entity\Translation;

use App\Entity\Program;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProjectCustomTranslationRepository")
 * @ORM\Table(name="project_custom_translation")
 */
class ProjectCustomTranslation
{
  /**
   * @ORM\Id
   * @ORM\GeneratedValue
   * @ORM\Column(type="integer")
   */
  private ?int $id = null;

  /**
   * @ORM\ManyToOne(
   *     targetEntity="App\Entity\Program",
   *     inversedBy="custom_translations"
   * )
   * @ORM\JoinColumn(name="project_id", referencedColumnName="id")
   */
  private Program $project;

  /**
   * @ORM\Column(type="string", length=5)
   */
  private string $language;

  /**
   * @ORM\Column(type="string", length=300, nullable=true)
   */
  private ?string $name;

  /**
   * @ORM\Column(type="text", nullable=true)
   */
  private ?string $description;

  /**
   * @ORM\Column(type="text", nullable=true)
   */
  private ?string $credits;

  public function __construct(Program $project, string $language)
  {
    $this->project = $project;
    $this->language = $language;
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getProject(): Program
  {
    return $this->project;
  }

  public function getLanguage(): string
  {
    return $this->language;
  }

  public function getName(): ?string
  {
    return $this->name;
  }

  public function setName(?string $name): void
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

  public function getCredits(): ?string
  {
    return $this->credits;
  }

  public function setCredits(?string $credits): void
  {
    $this->credits = $credits;
  }
}
