<?php

declare(strict_types=1);

namespace App\DB\Entity\Translation;

use App\DB\Entity\Project\Program;
use App\DB\EntityRepository\Translation\ProjectCustomTranslationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'project_custom_translation')]
#[ORM\Entity(repositoryClass: ProjectCustomTranslationRepository::class)]
class ProjectCustomTranslation
{
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column(type: Types::INTEGER)]
  private ?int $id = null;

  #[ORM\Column(type: Types::STRING, length: 300, nullable: true)]
  private ?string $name = null;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $description = null;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $credits = null;

  public function __construct(
    #[ORM\JoinColumn(name: 'project_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Program::class, inversedBy: 'custom_translations')]
    private Program $project,
    #[ORM\Column(type: Types::STRING, length: 5)]
    private string $language,
  ) {
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function setId(?int $id): ProjectCustomTranslation
  {
    $this->id = $id;

    return $this;
  }

  public function getProject(): Program
  {
    return $this->project;
  }

  public function setProject(Program $program): void
  {
    $this->project = $program;
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
