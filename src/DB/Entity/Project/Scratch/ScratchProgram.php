<?php

namespace App\DB\Entity\Project\Scratch;

use App\DB\EntityRepository\Project\ScratchProgramRepository;
use App\Utils\TimeUtils;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'scratch_program')]
#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: ScratchProgramRepository::class)]
class ScratchProgram
{
  #[ORM\Column(type: 'string', length: 300, nullable: true)]
  protected ?string $name = null;

  #[ORM\Column(type: 'text', nullable: true)]
  protected ?string $description = null;

  #[ORM\Column(type: 'text', nullable: true)]
  protected ?string $username = null;

  #[ORM\Column(type: 'datetime')]
  protected ?\DateTime $last_modified_at = null;

  /**
   * ScratchProgram constructor.
   *
   * @throws \Exception
   */
  public function __construct(
    #[ORM\Id]
    #[ORM\Column(type: 'guid', nullable: false)]
    protected string $id)
  {
    $this->updateLastModifiedTimestamp();
  }

  public function setName(?string $name): ScratchProgram
  {
    $this->name = $name;

    return $this;
  }

  public function setDescription(?string $description): ScratchProgram
  {
    $this->description = $description;

    return $this;
  }

  public function setUsername(?string $username): ScratchProgram
  {
    $this->username = $username;

    return $this;
  }

  /**
   * @throws \Exception
   */
  #[ORM\PreUpdate]
  public function updateLastModifiedTimestamp(): void
  {
    $this->setLastModifiedAt(TimeUtils::getDateTime());
  }

  public function getId(): string
  {
    return $this->id;
  }

  public function getName(): ?string
  {
    return $this->name;
  }

  public function getDescription(): ?string
  {
    return $this->description;
  }

  public function getUsername(): ?string
  {
    return $this->username;
  }

  public function setLastModifiedAt(\DateTime $last_modified_at): ScratchProgram
  {
    $this->last_modified_at = $last_modified_at;

    return $this;
  }

  public function getLastModifiedAt(): ?\DateTime
  {
    return $this->last_modified_at;
  }
}
