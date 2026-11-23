<?php

declare(strict_types=1);

namespace App\Docs;

use App\DB\Entity\Docs\DocPage;
use App\DB\EntityRepository\Docs\DocPageRepository;
use App\DB\EntityRepository\Project\ProjectRepository;
use App\Storage\ScreenshotRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Imports the user docs (`user-docs/` in this repo, the same layout the Catrobat/docs repo had) into doc_page: renders
 * new and changed files, deletes pages whose file is gone, all in one transaction. A file is re-rendered only when it
 * changed, or when it embeds a project (name and picture may change). Media are served straight from the folder.
 */
class DocsSync
{
  /** The docs live in this repo: "Improve this page" and links to other repo files go to GitHub's copy of main. */
  final public const string GITHUB = 'https://github.com/Catrobat/Catroweb/';

  /** Extension => content type of the media share serves from the docs' media/ folder. */
  final public const array MEDIA_TYPES = [
    'png' => 'image/png',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'gif' => 'image/gif',
    'webp' => 'image/webp',
    'svg' => 'image/svg+xml',
    'pdf' => 'application/pdf',
  ];

  private readonly DocsMarkdown $markdown;

  public function __construct(
    private readonly EntityManagerInterface $entity_manager,
    private readonly DocPageRepository $pages,
    private readonly ProjectRepository $projects,
    private readonly ScreenshotRepository $screenshots,
    #[Autowire('%catrobat.user_docs.dir%')]
    public readonly string $docs_dir,
  ) {
    $this->markdown = new DocsMarkdown(self::GITHUB.'blob/main/user-docs/', $this->projectCard(...));
  }

  /** "Improve this page": the English file in GitHub's editor (translations are generated from it). */
  public function editUrl(DocPage $page): string
  {
    $file = DocPage::GUIDE === $page->getKind() ? preg_replace('#^[^/]+/#', 'en/', $page->getSourceFile(), 1) : $page->getSourceFile();

    return self::GITHUB.'edit/main/user-docs/'.$file;
  }

  /**
   * The Markdown files of a docs folder, keyed by their path in it.
   *
   * @return array<string, string>
   */
  public static function readDirectory(string $dir): array
  {
    $dir = rtrim($dir, '/').'/';
    $pages = [];
    foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)) as $file) {
      \assert($file instanceof \SplFileInfo);
      if ($file->isFile() && 'md' === $file->getExtension()) {
        $pages[substr($file->getPathname(), \strlen($dir))] = (string) file_get_contents($file->getPathname());
      }
    }

    return $pages;
  }

  /**
   * A file of the media/ folder that share serves (the image types the writer guide allows, and PDF): its path inside
   * media/, or null. Every segment must be a plain name, so no URL can reach outside the media folder.
   */
  public static function mediaPath(string $repo_path): ?string
  {
    $pattern = '#^media/((?:[A-Za-z0-9_-][A-Za-z0-9._-]*/)*[A-Za-z0-9_-][A-Za-z0-9._-]*\.([A-Za-z0-9]+))$#';
    if (1 !== preg_match($pattern, $repo_path, $m) || !isset(self::MEDIA_TYPES[strtolower($m[2])]) || str_contains($m[1], '..')) {
      return null;
    }

    return $m[1];
  }

  /** A media file of the docs folder that may be served, or null. Its real path must stay inside media/, so no link (to a file or a folder) leads out. */
  public function mediaFile(string $path): ?string
  {
    $root = realpath(rtrim($this->docs_dir, '/').'/media');
    $file = false === $root || null === self::mediaPath('media/'.$path) ? false : realpath($root.'/'.$path);

    return false !== $file && str_starts_with($file, $root.\DIRECTORY_SEPARATOR) && is_file($file) ? $file : null;
  }

  /**
   * @param array<string, string> $files repo path => content
   *
   * @return array{created: int, updated: int, deleted: int, skipped: list<string>}
   */
  public function import(array $files): array
  {
    $entries = [];
    $skipped = [];
    $english_hashes = [];
    foreach ($files as $file => $content) {
      $location = DocsMarkdown::locate($file);
      if (null === $location) {
        continue;
      }
      $key = $location['kind'].'|'.$location['locale'].'|'.$location['path'];
      if (isset($entries[$key])) {
        $skipped[] = $file.': same page as '.$entries[$key]['file'];
        continue;
      }
      // The render version is part of the change check, so a release that renders differently re-renders every page.
      $entries[$key] = ['file' => $file, 'content' => $content, 'hash' => hash('sha256', $content), 'content_hash' => hash('sha256', DocsMarkdown::VERSION."\n".$content)] + $location;
      if ('en' === $location['locale']) {
        $english_hashes[$location['kind'].'|'.$location['path']] = $entries[$key]['hash'];
      }
    }
    if (!isset($entries[DocPage::GUIDE.'|en|index'])) {
      // A broken download must not wipe the docs.
      throw new \RuntimeException('The user docs have no en/index.md, nothing imported.');
    }

    return $this->entity_manager->wrapInTransaction(function () use ($entries, $english_hashes, $skipped): array {
      $existing = [];
      foreach ($this->pages->createQueryBuilder('p')->select('p.id', 'p.kind', 'p.locale', 'p.path', 'p.content_hash', 'p.source_hash', 'p.reviewed', 'p.translation_state')->getQuery()->getArrayResult() as $row) {
        $existing[$row['kind'].'|'.$row['locale'].'|'.$row['path']] = $row;
      }
      $created = $updated = 0;
      foreach ($entries as $key => $entry) {
        $row = $existing[$key] ?? null;
        unset($existing[$key]);
        $state = 'en' === $entry['locale'] ? DocPage::STATE_SOURCE : null; // translations: known after the front matter is read
        if (null !== $row && $row['content_hash'] === $entry['content_hash'] && !str_contains($entry['content'], '::project{')) {
          $state ??= self::translationState($english_hashes[$entry['kind'].'|'.$entry['path']] ?? null, $row['source_hash'], $row['reviewed']);
          if ($state !== $row['translation_state']) {
            $this->pages->find($row['id'])?->setTranslationState($state);
          }
          continue;
        }
        try {
          $rendered = $this->markdown->convert($entry['content'], $entry['file']);
        } catch (\Throwable $e) {
          $skipped[] = $entry['file'].': '.$e->getMessage();
          continue;
        }
        $front = $rendered['front_matter'];
        $title = \is_scalar($front['title'] ?? null) ? trim((string) $front['title']) : '';
        if (DocPage::GUIDE === $entry['kind'] && '' === $title) {
          $skipped[] = $entry['file'].': front matter has no title';
          continue;
        }
        $page = null === $row ? null : $this->pages->find($row['id']);
        if (null === $page) {
          $page = new DocPage($entry['kind'], $entry['path'], $entry['locale'], $entry['file']);
          $this->entity_manager->persist($page);
          ++$created;
        } else {
          $page->setSourceFile($entry['file']);
          ++$updated;
        }
        $source_hash = 'en' === $entry['locale'] ? $entry['hash'] : (\is_scalar($front['source_hash'] ?? null) ? strtolower((string) $front['source_hash']) : null);
        $description = \is_scalar($front['description'] ?? null) ? trim((string) $front['description']) : '';
        $page->update(
          mb_substr($title, 0, 255),
          '' === $description ? null : mb_substr($description, 0, 500),
          is_numeric($front['order'] ?? null) ? (int) $front['order'] : 1000,
          array_values(array_filter(\is_array($front['flavors'] ?? null) ? $front['flavors'] : [], is_string(...))),
          $rendered['html'],
          $rendered['toc'],
          $entry['content_hash'],
          $source_hash,
          true === ($front['reviewed'] ?? false),
        );
        $page->setTranslationState($state ?? self::translationState($english_hashes[$entry['kind'].'|'.$entry['path']] ?? null, $source_hash, $page->isReviewed()));
      }
      if ([] !== $existing) {
        $this->entity_manager->createQuery('DELETE FROM '.DocPage::class.' p WHERE p.id IN (:ids)')
          ->setParameter('ids', array_column($existing, 'id'))->execute()
        ;
      }
      $this->entity_manager->flush();

      return ['created' => $created, 'updated' => $updated, 'deleted' => \count($existing), 'skipped' => $skipped];
    });
  }

  /**
   * A translation is current while it names the hash (or a prefix of at least 7 characters) of today's English file.
   * A reviewed page without a hash was written by a person (the WordPress import) and counts as current.
   */
  private static function translationState(?string $english_hash, ?string $source_hash, bool $reviewed): string
  {
    if (null === $source_hash) {
      return $reviewed ? DocPage::STATE_CURRENT : DocPage::STATE_STALE;
    }

    return null !== $english_hash && \strlen($source_hash) >= 7 && str_starts_with($english_hash, $source_hash) ? DocPage::STATE_CURRENT : DocPage::STATE_STALE;
  }

  /** @return array{name: string, author: string, thumbnail: string}|null */
  private function projectCard(string $id): ?array
  {
    $project = $this->projects->find($id);
    if (null === $project || !$project->isVisible() || $project->getAutoHidden() || $project->getPrivate()) {
      return null;
    }

    return [
      'name' => $project->getName(),
      'author' => (string) $project->getUser()?->getUsername(),
      'thumbnail' => '/'.$this->screenshots->getThumbnailWebPath($id),
    ];
  }
}
