<?php

declare(strict_types=1);

namespace App\DB\Entity\Project\Special;

use App\DB\Entity\Flavor;
use App\DB\Entity\Project\Program;
use App\DB\Entity\User\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;

class SpecialProgram
{
  public ?File $file = null;

  public ?int $removed_id = null;

  public ?string $old_image_type = null;

  #[ORM\Id]
  #[ORM\Column(type: Types::INTEGER)]
  #[ORM\GeneratedValue(strategy: 'AUTO')]
  protected ?int $id = null;

  #[ORM\Column(type: Types::STRING)]
  protected string $imagetype;

  #[ORM\Column(type: Types::BOOLEAN)]
  protected bool $active = true;

  #[ORM\ManyToOne(targetEntity: Flavor::class, fetch: 'EAGER')]
  protected ?Flavor $flavor = null;

  #[ORM\Column(type: Types::INTEGER)]
  protected int $priority = 0;

  #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
  protected bool $for_ios = false;

  #[ORM\ManyToOne(targetEntity: Program::class, fetch: 'EAGER')]
  protected ?Program $program = null;

  public function getFlavor(): ?Flavor
  {
    return $this->flavor;
  }

  public function setFlavor(Flavor $flavor): void
  {
    $this->flavor = $flavor;
  }

  public function getImageType(): string
  {
    return $this->imagetype;
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getProgram(): ?Program
  {
    return $this->program;
  }

  public function getActive(): bool
  {
    return $this->active;
  }

  public function getPriority(): int
  {
    return $this->priority;
  }

  public function setPriority(int $priority): void
  {
    $this->priority = $priority;
  }

  public function getForIos(): bool
  {
    return $this->for_ios;
  }

  public function setForIos(bool $for_ios): void
  {
    $this->for_ios = $for_ios;
  }

  public function isExample(): bool
  {
    return true;
  }

  public function getName(): string
  {
    return $this->program->getName();
  }

  public function getUser(): ?User
  {
    return $this->getProgram()->getUser();
  }

  public function getNotForKids(): bool
  {
    return false;
  }
}
