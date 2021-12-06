<?php

namespace App\Entity\Translation;

use App\Entity\Program;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;

/**
 * @ORM\Entity
 * @ORM\Table(name="project_machine_translation")
 * @HasLifecycleCallbacks
 */
class ProjectMachineTranslation extends MachineTranslation
{
  /**
   * @ORM\ManyToOne(targetEntity="App\Entity\Program")
   * @ORM\JoinColumn(name="project_id", referencedColumnName="id", onDelete="CASCADE")
   */
  protected Program $project;

  public function __construct(Program $project, string $source_language, string $target_language, string $provider, int $usage_count = 1)
  {
    parent::__construct($source_language, $target_language, $provider, $usage_count);
    $this->project = $project;
  }

  public function getProject(): Program
  {
    return $this->project;
  }
}
