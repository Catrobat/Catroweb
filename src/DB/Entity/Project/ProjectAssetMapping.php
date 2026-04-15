<?php

declare(strict_types=1);

namespace App\DB\Entity\Project;

use App\DB\EntityRepository\Project\ProjectAssetMappingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'project_asset_mapping')]
#[ORM\UniqueConstraint(name: 'project_path_unique', columns: ['project_id', 'path_in_zip'])]
#[ORM\Index(name: 'mapping_project_idx', columns: ['project_id'])]
#[ORM\Index(name: 'mapping_asset_idx', columns: ['asset_hash'])]
#[ORM\Entity(repositoryClass: ProjectAssetMappingRepository::class)]
class ProjectAssetMapping
{
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column(type: Types::INTEGER)]
  private ?int $id = null; /* @phpstan-ignore-line (Doctrine hydrates the int value) */

  #[ORM\ManyToOne(targetEntity: Project::class)]
  #[ORM\JoinColumn(name: 'project_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
  private Project $project;

  #[ORM\ManyToOne(targetEntity: ProjectAsset::class)]
  #[ORM\JoinColumn(name: 'asset_hash', referencedColumnName: 'hash', nullable: false)]
  private ProjectAsset $asset;

  #[ORM\Column(name: 'original_filename', type: Types::STRING, length: 255)]
  private string $originalFilename;

  #[ORM\Column(name: 'path_in_zip', type: Types::STRING, length: 512)]
  private string $pathInZip;

  public function __construct(Project $project, ProjectAsset $asset, string $originalFilename, string $pathInZip)
  {
    $this->project = $project;
    $this->asset = $asset;
    $this->originalFilename = $originalFilename;
    $this->pathInZip = $pathInZip;
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getProject(): Project
  {
    return $this->project;
  }

  public function getAsset(): ProjectAsset
  {
    return $this->asset;
  }

  public function getOriginalFilename(): string
  {
    return $this->originalFilename;
  }

  public function getPathInZip(): string
  {
    return $this->pathInZip;
  }
}
