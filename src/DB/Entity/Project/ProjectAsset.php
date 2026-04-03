<?php

declare(strict_types=1);

namespace App\DB\Entity\Project;

use App\DB\EntityRepository\Project\ProjectAssetRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'project_asset')]
#[ORM\Entity(repositoryClass: ProjectAssetRepository::class)]
class ProjectAsset
{
  /**
   * SHA-256 hex hash (64 chars). Primary key — natural key, no surrogate.
   */
  #[ORM\Id]
  #[ORM\Column(type: Types::STRING, length: 64)]
  private string $hash;

  #[ORM\Column(type: Types::BIGINT)]
  private int $size;

  #[ORM\Column(name: 'mime_type', type: Types::STRING, length: 127)]
  private string $mimeType;

  #[ORM\Column(name: 'reference_count', type: Types::INTEGER, options: ['default' => 0])]
  private int $referenceCount = 0;

  #[ORM\Column(name: 'storage_path', type: Types::STRING, length: 255)]
  private string $storagePath;

  #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
  private \DateTimeImmutable $createdAt;

  public function __construct(string $hash, int $size, string $mimeType, string $storagePath)
  {
    $this->hash = $hash;
    $this->size = $size;
    $this->mimeType = $mimeType;
    $this->storagePath = $storagePath;
    $this->createdAt = new \DateTimeImmutable();
  }

  public function getHash(): string
  {
    return $this->hash;
  }

  public function getSize(): int
  {
    return $this->size;
  }

  public function getMimeType(): string
  {
    return $this->mimeType;
  }

  public function getReferenceCount(): int
  {
    return $this->referenceCount;
  }

  public function getStoragePath(): string
  {
    return $this->storagePath;
  }

  public function getCreatedAt(): \DateTimeImmutable
  {
    return $this->createdAt;
  }

  public function incrementReferenceCount(): void
  {
    ++$this->referenceCount;
  }

  public function decrementReferenceCount(): void
  {
    $this->referenceCount = max(0, $this->referenceCount - 1);
  }
}
