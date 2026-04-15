<?php

declare(strict_types=1);

namespace App\DB\Entity\Project;

use App\DB\EntityRepository\Project\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'tags')]
#[ORM\Index(name: 'internal_title_idx', columns: ['internal_title'])]
#[ORM\Entity(repositoryClass: TagRepository::class)]
class Tag
{
  /**
   * Static Tags - added/updated with UpdateTagsCommand.
   */
  final public const string GAME = 'game';

  final public const string ANIMATION = 'animation';

  final public const string STORY = 'story';

  final public const string MUSIC = 'music';

  final public const string ART = 'art';

  final public const string EXPERIMENTAL = 'experimental';

  final public const string TUTORIAL = 'tutorial';

  final public const string CODING_JAM_09_2021 = 'catrobatfestival2021';

  #[ORM\Id]
  #[ORM\Column(type: Types::INTEGER)]
  #[ORM\GeneratedValue(strategy: 'AUTO')]
  protected ?int $id = null;

  /**
   * @var Collection<int, Project>
   */
  #[ORM\ManyToMany(targetEntity: Project::class, mappedBy: 'tags')]
  protected Collection $projects;

  #[ORM\Column(name: 'internal_title', type: Types::STRING, nullable: false)]
  protected string $internal_title = '';

  #[ORM\Column(name: 'title_ltm_code', type: Types::STRING, nullable: false)]
  protected string $title_ltm_code = '';

  #[ORM\Column(name: 'enabled', type: Types::BOOLEAN, options: ['default' => true])]
  protected bool $enabled = true;

  public function __construct()
  {
    $this->projects = new ArrayCollection();
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getInternalTitle(): string
  {
    return $this->internal_title;
  }

  public function setInternalTitle(string $internal_title): Tag
  {
    $this->internal_title = $internal_title;

    return $this;
  }

  public function getTitleLtmCode(): string
  {
    return $this->title_ltm_code;
  }

  public function setTitleLtmCode(string $title_ltm_code): Tag
  {
    $this->title_ltm_code = $title_ltm_code;

    return $this;
  }

  public function isEnabled(): bool
  {
    return $this->enabled;
  }

  public function setEnabled(bool $enabled): Tag
  {
    $this->enabled = $enabled;

    return $this;
  }

  public function addProject(Project $project): void
  {
    if ($this->projects->contains($project)) {
      return;
    }

    $this->projects->add($project);
  }

  public function getProjectCount(): int
  {
    return count($this->projects);
  }

  public function removeProject(Project $project): void
  {
    $this->projects->removeElement($project);
  }

  public function getProjects(): Collection
  {
    return $this->projects;
  }
}
