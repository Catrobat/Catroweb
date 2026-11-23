<?php

declare(strict_types=1);

namespace App\DB\Entity\Docs;

use App\DB\EntityRepository\Docs\DocPageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * One page of the user docs in one locale, rendered from the Catrobat/docs repo by DocsSync (catrobat:docs:sync).
 * The table is a cache of the repo, and the seam for a later editor in the database: the API and the pages only
 * read these rows. A brick explanation (`reference/bricks/<type>.md`) is a row of kind `brick`, keyed by brick type.
 */
#[ORM\Table(name: 'doc_page')]
#[ORM\UniqueConstraint(name: 'doc_page_kind_path_locale_idx', columns: ['kind', 'path', 'locale'])]
#[ORM\Entity(repositoryClass: DocPageRepository::class)]
class DocPage
{
  final public const string GUIDE = 'guide';
  final public const string BRICK = 'brick';

  /** English is the source; a translation is current while its source hash matches the English page, stale after. */
  final public const string STATE_SOURCE = 'source';
  final public const string STATE_CURRENT = 'current';
  final public const string STATE_STALE = 'stale';

  #[ORM\Id]
  #[ORM\Column(type: Types::GUID)]
  private string $id;

  #[ORM\Column(type: Types::STRING, length: 255)]
  private string $title = '';

  #[ORM\Column(type: Types::STRING, length: 500, nullable: true)]
  private ?string $description = null;

  #[ORM\Column(type: Types::INTEGER)]
  private int $sort_order = 0;

  /** @var list<string> */
  #[ORM\Column(type: Types::JSON)]
  private array $flavors = [];

  #[ORM\Column(type: Types::TEXT, length: 16_777_215)]
  private string $html = '';

  /** @var list<array{id: string, title: string, level: int}> */
  #[ORM\Column(type: Types::JSON)]
  private array $toc = [];

  /** sha256 of the render version and the file: the page is only rendered again when one of them changes. */
  #[ORM\Column(type: Types::STRING, length: 64)]
  private string $content_hash = '';

  /** English: the hash of this file. Translation: the hash of the English file it was made from (front matter). */
  #[ORM\Column(type: Types::STRING, length: 64, nullable: true)]
  private ?string $source_hash = null;

  #[ORM\Column(type: Types::STRING, length: 10)]
  private string $translation_state = self::STATE_SOURCE;

  #[ORM\Column(type: Types::BOOLEAN)]
  private bool $reviewed = false;

  /** When the sync last saw the file change (a tarball has no commit dates). */
  #[ORM\Column(type: 'utc_datetime')]
  private \DateTime $updated_at;

  public function __construct(
    #[ORM\Column(type: Types::STRING, length: 10)]
    private string $kind,
    /** Page path without locale and `.md`: `tutorials/jump`; a folder's index.md is the folder, the root one is `index`. */
    #[ORM\Column(type: Types::STRING, length: 191)]
    private string $path,
    #[ORM\Column(type: Types::STRING, length: 16)]
    private string $locale,
    /** File in the docs repo, for "Improve this page". */
    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $source_file,
  ) {
    $this->id = Uuid::uuid4()->toString();
    $this->updated_at = new \DateTime();
  }

  public function getId(): string
  {
    return $this->id;
  }

  public function getKind(): string
  {
    return $this->kind;
  }

  public function getPath(): string
  {
    return $this->path;
  }

  public function getLocale(): string
  {
    return $this->locale;
  }

  public function getSourceFile(): string
  {
    return $this->source_file;
  }

  public function setSourceFile(string $source_file): void
  {
    $this->source_file = $source_file;
  }

  public function getTitle(): string
  {
    return $this->title;
  }

  public function getDescription(): ?string
  {
    return $this->description;
  }

  public function getSortOrder(): int
  {
    return $this->sort_order;
  }

  /** @return list<string> */
  public function getFlavors(): array
  {
    return $this->flavors;
  }

  public function getHtml(): string
  {
    return $this->html;
  }

  /** @return list<array{id: string, title: string, level: int}> */
  public function getToc(): array
  {
    return $this->toc;
  }

  public function getSourceHash(): ?string
  {
    return $this->source_hash;
  }

  public function getTranslationState(): string
  {
    return $this->translation_state;
  }

  public function setTranslationState(string $translation_state): void
  {
    $this->translation_state = $translation_state;
  }

  public function isReviewed(): bool
  {
    return $this->reviewed;
  }

  public function getUpdatedAt(): \DateTime
  {
    return $this->updated_at;
  }

  /**
   * @param list<string>                                       $flavors
   * @param list<array{id: string, title: string, level: int}> $toc
   */
  public function update(string $title, ?string $description, int $sort_order, array $flavors, string $html, array $toc, string $content_hash, ?string $source_hash, bool $reviewed): void
  {
    if ($content_hash !== $this->content_hash) {
      $this->updated_at = new \DateTime();
    }
    $this->title = $title;
    $this->description = $description;
    $this->sort_order = $sort_order;
    $this->flavors = $flavors;
    $this->html = $html;
    $this->toc = $toc;
    $this->content_hash = $content_hash;
    $this->source_hash = $source_hash;
    $this->reviewed = $reviewed;
  }
}
