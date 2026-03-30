<?php

declare(strict_types=1);

namespace App\DB\Entity\Project;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'project_code_statistics')]
#[ORM\Index(name: 'pcs_program_idx', columns: ['program_id'])]
#[ORM\Entity]
class ProjectCodeStatistics
{
  #[ORM\Id]
  #[ORM\Column(type: Types::INTEGER)]
  #[ORM\GeneratedValue(strategy: 'AUTO')]
  private ?int $id = null;

  #[ORM\ManyToOne(targetEntity: Program::class, inversedBy: 'code_statistics')]
  #[ORM\JoinColumn(name: 'program_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
  private Program $program;

  #[ORM\Column(type: Types::DATETIME_MUTABLE)]
  private \DateTimeInterface $created_at;

  #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
  private int $scenes = 0;

  #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
  private int $scripts = 0;

  #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
  private int $bricks = 0;

  #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
  private int $objects = 0;

  #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
  private int $looks = 0;

  #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
  private int $sounds = 0;

  #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
  private int $global_variables = 0;

  #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
  private int $local_variables = 0;

  /** @var array<string, int> */
  #[ORM\Column(type: Types::JSON)]
  private array $script_counts = [];

  /** @var array<string, int> */
  #[ORM\Column(type: Types::JSON)]
  private array $brick_counts = [];

  #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
  private int $score_abstraction = 0;

  #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
  private int $score_parallelism = 0;

  #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
  private int $score_synchronization = 0;

  #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
  private int $score_logical_thinking = 0;

  #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
  private int $score_flow_control = 0;

  #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
  private int $score_user_interactivity = 0;

  #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
  private int $score_data_representation = 0;

  #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
  private int $score_bonus = 0;

  #[ORM\Column(type: Types::STRING, length: 64, options: ['default' => 'rubric_2021_v2'])]
  private string $scoring_version = 'rubric_2021_v2';

  public function __construct()
  {
    $this->created_at = new \DateTime();
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function setId(int $id): self
  {
    $this->id = $id;

    return $this;
  }

  public function getProgram(): Program
  {
    return $this->program;
  }

  public function setProgram(Program $program): self
  {
    $this->program = $program;

    return $this;
  }

  public function getCreatedAt(): \DateTimeInterface
  {
    return $this->created_at;
  }

  public function setCreatedAt(\DateTimeInterface $created_at): self
  {
    $this->created_at = $created_at;

    return $this;
  }

  public function getScenes(): int
  {
    return $this->scenes;
  }

  public function setScenes(int $scenes): self
  {
    $this->scenes = $scenes;

    return $this;
  }

  public function getScripts(): int
  {
    return $this->scripts;
  }

  public function setScripts(int $scripts): self
  {
    $this->scripts = $scripts;

    return $this;
  }

  public function getBricks(): int
  {
    return $this->bricks;
  }

  public function setBricks(int $bricks): self
  {
    $this->bricks = $bricks;

    return $this;
  }

  public function getObjects(): int
  {
    return $this->objects;
  }

  public function setObjects(int $objects): self
  {
    $this->objects = $objects;

    return $this;
  }

  public function getLooks(): int
  {
    return $this->looks;
  }

  public function setLooks(int $looks): self
  {
    $this->looks = $looks;

    return $this;
  }

  public function getSounds(): int
  {
    return $this->sounds;
  }

  public function setSounds(int $sounds): self
  {
    $this->sounds = $sounds;

    return $this;
  }

  public function getGlobalVariables(): int
  {
    return $this->global_variables;
  }

  public function setGlobalVariables(int $global_variables): self
  {
    $this->global_variables = $global_variables;

    return $this;
  }

  public function getLocalVariables(): int
  {
    return $this->local_variables;
  }

  public function setLocalVariables(int $local_variables): self
  {
    $this->local_variables = $local_variables;

    return $this;
  }

  /** @return array<string, int> */
  public function getScriptCounts(): array
  {
    return $this->script_counts;
  }

  /** @param array<string, int> $script_counts */
  public function setScriptCounts(array $script_counts): self
  {
    $this->script_counts = $script_counts;

    return $this;
  }

  /** @return array<string, int> */
  public function getBrickCounts(): array
  {
    return $this->brick_counts;
  }

  /** @param array<string, int> $brick_counts */
  public function setBrickCounts(array $brick_counts): self
  {
    $this->brick_counts = $brick_counts;

    return $this;
  }

  public function getScoreAbstraction(): int
  {
    return $this->score_abstraction;
  }

  public function setScoreAbstraction(int $score_abstraction): self
  {
    $this->score_abstraction = $score_abstraction;

    return $this;
  }

  public function getScoreParallelism(): int
  {
    return $this->score_parallelism;
  }

  public function setScoreParallelism(int $score_parallelism): self
  {
    $this->score_parallelism = $score_parallelism;

    return $this;
  }

  public function getScoreSynchronization(): int
  {
    return $this->score_synchronization;
  }

  public function setScoreSynchronization(int $score_synchronization): self
  {
    $this->score_synchronization = $score_synchronization;

    return $this;
  }

  public function getScoreLogicalThinking(): int
  {
    return $this->score_logical_thinking;
  }

  public function setScoreLogicalThinking(int $score_logical_thinking): self
  {
    $this->score_logical_thinking = $score_logical_thinking;

    return $this;
  }

  public function getScoreFlowControl(): int
  {
    return $this->score_flow_control;
  }

  public function setScoreFlowControl(int $score_flow_control): self
  {
    $this->score_flow_control = $score_flow_control;

    return $this;
  }

  public function getScoreUserInteractivity(): int
  {
    return $this->score_user_interactivity;
  }

  public function setScoreUserInteractivity(int $score_user_interactivity): self
  {
    $this->score_user_interactivity = $score_user_interactivity;

    return $this;
  }

  public function getScoreDataRepresentation(): int
  {
    return $this->score_data_representation;
  }

  public function setScoreDataRepresentation(int $score_data_representation): self
  {
    $this->score_data_representation = $score_data_representation;

    return $this;
  }

  public function getScoreBonus(): int
  {
    return $this->score_bonus;
  }

  public function setScoreBonus(int $score_bonus): self
  {
    $this->score_bonus = $score_bonus;

    return $this;
  }

  public function getScoringVersion(): string
  {
    return $this->scoring_version;
  }

  public function setScoringVersion(string $scoring_version): self
  {
    $this->scoring_version = $scoring_version;

    return $this;
  }

  /**
   * Derived on read so the stored category scores remain the single source of truth.
   */
  public function getScoreTotal(): int
  {
    return $this->score_abstraction
      + $this->score_parallelism
      + $this->score_synchronization
      + $this->score_logical_thinking
      + $this->score_flow_control
      + $this->score_user_interactivity
      + $this->score_data_representation
      + $this->score_bonus;
  }
}
