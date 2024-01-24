<?php

namespace App\DB\Entity\Translation;

use App\DB\Entity\Project\Project;
use App\DB\EntityRepository\Translation\ProjectCustomTranslationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProjectCustomTranslationRepository::class)
 *
 * @ORM\Table(name="project_custom_translation")
 */
class ProjectCustomTranslation
{
  /**
   * @ORM\Id
   *
   * @ORM\GeneratedValue
   *
   * @ORM\Column(type="integer")
   */
  private ?int $id = null;

  /**
   * @ORM\Column(type="string", length=300, nullable=true)
   */
  private ?string $name = null;

  /**
   * @ORM\Column(type="text", nullable=true)
   */
  private ?string $description = null;

  /**
   * @ORM\Column(type="text", nullable=true)
   */
  private ?string $credits = null;

  public function __construct(
    /**
     * @ORM\ManyToOne(
     *     targetEntity=Project::class,
     *     inversedBy="custom_translations"
     * )
     *
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private Project $project,
    /**
     * @ORM\Column(type="string", length=5)
     */
    private string $language
  ) {
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getProject(): Project
  {
    return $this->project;
  }

  public function setProject(Project $project): void
  {
    $this->project = $project;
  }

  public function getLanguage(): string
  {
    return $this->language;
  }

  public function setLanguage(string $language): void
  {
    $this->language = $language;
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
