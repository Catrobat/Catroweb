<?php

declare(strict_types=1);

namespace App\DB\Entity;

use App\DB\Entity\Project\Program;
use App\DB\Entity\Studio\Studio;
use App\DB\EntityRepository\FeaturedBannerRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;

#[ORM\Table(name: 'featured_banner')]
#[ORM\Entity(repositoryClass: FeaturedBannerRepository::class)]
class FeaturedBanner
{
  public ?File $file = null;

  public ?string $old_image_type = null;

  public ?int $removed_id = null;

  public function __construct()
  {
    $this->created_on = new \DateTime();
  }

  #[ORM\Id]
  #[ORM\Column(type: Types::INTEGER)]
  #[ORM\GeneratedValue(strategy: 'AUTO')]
  protected ?int $id = null;

  #[ORM\Column(type: Types::STRING, length: 20)]
  protected string $type = 'project';

  #[ORM\ManyToOne(targetEntity: Program::class, fetch: 'EAGER')]
  #[ORM\JoinColumn(name: 'program_id', referencedColumnName: 'id', nullable: true)]
  protected ?Program $program = null;

  #[ORM\ManyToOne(targetEntity: Studio::class, fetch: 'EAGER')]
  #[ORM\JoinColumn(name: 'studio_id', referencedColumnName: 'id', nullable: true)]
  protected ?Studio $studio = null;

  #[ORM\Column(type: Types::STRING, nullable: true)]
  protected ?string $url = null;

  #[ORM\Column(type: Types::STRING)]
  protected string $image_type = '';

  #[ORM\Column(type: Types::STRING, nullable: true)]
  protected ?string $title = null;

  #[ORM\Column(type: Types::BOOLEAN)]
  protected bool $active = true;

  #[ORM\Column(type: Types::INTEGER)]
  protected int $priority = 0;

  #[ORM\Column(type: Types::DATETIME_MUTABLE)]
  protected \DateTime $created_on;

  #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
  protected ?\DateTime $updated_on = null;

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getType(): string
  {
    return $this->type;
  }

  public function setType(string $type): self
  {
    $this->type = $type;

    return $this;
  }

  public function getProgram(): ?Program
  {
    return $this->program;
  }

  public function setProgram(?Program $program): self
  {
    $this->program = $program;

    return $this;
  }

  public function getStudio(): ?Studio
  {
    return $this->studio;
  }

  public function setStudio(?Studio $studio): self
  {
    $this->studio = $studio;

    return $this;
  }

  public function getUrl(): ?string
  {
    return $this->url;
  }

  public function setUrl(?string $url): self
  {
    $this->url = $url;

    return $this;
  }

  public function getImageType(): string
  {
    return $this->image_type;
  }

  public function setImageType(string $image_type): self
  {
    $this->image_type = $image_type;

    return $this;
  }

  public function getTitle(): ?string
  {
    return $this->title;
  }

  public function setTitle(?string $title): self
  {
    $this->title = $title;

    return $this;
  }

  public function getActive(): bool
  {
    return $this->active;
  }

  public function setActive(bool $active): self
  {
    $this->active = $active;

    return $this;
  }

  public function getPriority(): int
  {
    return $this->priority;
  }

  public function setPriority(int $priority): self
  {
    $this->priority = $priority;

    return $this;
  }

  public function getCreatedOn(): \DateTime
  {
    return $this->created_on;
  }

  public function setCreatedOn(\DateTime $created_on): self
  {
    $this->created_on = $created_on;

    return $this;
  }

  public function getUpdatedOn(): ?\DateTime
  {
    return $this->updated_on;
  }

  public function setUpdatedOn(?\DateTime $updated_on): self
  {
    $this->updated_on = $updated_on;

    return $this;
  }

  public function setNewFeaturedImage(File $file): void
  {
    $this->file = $file;
  }
}
