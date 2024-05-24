<?php

declare(strict_types=1);

namespace App\DB\Entity;

use App\DB\Entity\MediaLibrary\MediaPackageFile;
use App\DB\EntityRepository\FlavorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'flavor')]
#[ORM\Entity(repositoryClass: FlavorRepository::class)]
class Flavor implements \Stringable
{
  final public const string POCKETCODE = 'pocketcode';
  final public const string POCKETALICE = 'pocketalice';
  final public const string POCKETGALAXY = 'pocketgalaxy';
  final public const string PHIROCODE = 'phirocode';
  final public const string LUNA = 'luna';
  final public const string CREATE_AT_SCHOOL = 'create@school';
  final public const string EMBROIDERY = 'embroidery';
  final public const string ARDUINO = 'arduino';
  final public const string MINDSTORMS = 'mindstorms';
  final public const array ALL = [
    self::POCKETCODE,
    self::POCKETALICE,
    self::POCKETGALAXY,
    self::PHIROCODE,
    self::LUNA,
    self::CREATE_AT_SCHOOL,
    self::EMBROIDERY,
    self::ARDUINO,
    self::MINDSTORMS,
  ];

  #[ORM\Id]
  #[ORM\Column(type: Types::INTEGER)]
  #[ORM\GeneratedValue(strategy: 'AUTO')]
  protected ?int $id = null;

  #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
  protected ?string $name = null;

  /**
   * @var Collection<int, MediaPackageFile>
   */
  #[ORM\ManyToMany(targetEntity: MediaPackageFile::class, mappedBy: 'flavors', fetch: 'EXTRA_LAZY')]
  protected Collection $media_package_files;

  public function __construct()
  {
    $this->media_package_files = new ArrayCollection();
  }

  public function __toString(): string
  {
    return $this->getName() ?? '';
  }

  public function setId(?int $id): self
  {
    $this->id = $id;

    return $this;
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function setName(?string $name): self
  {
    $this->name = $name;

    return $this;
  }

  public function getName(): ?string
  {
    return $this->name;
  }

  public function addMediaPackageFile(MediaPackageFile $media_package_file): void
  {
    if ($this->media_package_files->contains($media_package_file)) {
      return;
    }
    $this->media_package_files[] = $media_package_file;
  }

  public function removeMediaPackageFile(MediaPackageFile $media_package_file): void
  {
    if (!$this->media_package_files->contains($media_package_file)) {
      return;
    }
    $this->media_package_files->removeElement($media_package_file);
  }

  public function getMediaPackageFiles(): Collection
  {
    return $this->media_package_files;
  }
}
