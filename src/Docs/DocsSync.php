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
 * Imports the docs repo into doc_page: renders new and changed files, deletes pages whose file is gone, all in one
 * transaction. A file is re-rendered only when it changed, or when it embeds a project (name and picture may change).
 */
class DocsSync
{
  private readonly DocsMarkdown $markdown;

  public function __construct(
    private readonly EntityManagerInterface $entity_manager,
    private readonly DocPageRepository $pages,
    private readonly ProjectRepository $projects,
    private readonly ScreenshotRepository $screenshots,
    #[Autowire(env: 'DOCS_REPOSITORY')]
    public readonly string $repository,
    #[Autowire(env: 'DOCS_REF')]
    public readonly string $ref,
  ) {
    $this->markdown = new DocsMarkdown(
      'https://raw.githubusercontent.com/'.$repository.'/'.$ref.'/',
      'https://github.com/'.$repository.'/blob/'.$ref.'/',
      $this->projectCard(...),
    );
  }

  public function tarballUrl(): string
  {
    return 'https://codeload.github.com/'.$this->repository.'/tar.gz/'.rawurlencode($this->ref);
  }

  /** "Improve this page": the English file in GitHub's editor (translations are generated from it). */
  public function editUrl(DocPage $page): string
  {
    $file = preg_replace('#^[^/]+/#', 'en/', $page->getSourceFile(), 1);

    return 'https://github.com/'.$this->repository.'/edit/'.$this->ref.'/'.(DocPage::GUIDE === $page->getKind() ? $file : $page->getSourceFile());
  }

  /**
   * The Markdown files of a GitHub tarball, keyed by their path in the repo (without the tarball's top folder).
   * Read straight from the archive: nothing is extracted to disk.
   *
   * @return array<string, string>
   */
  public static function readTarball(string $tarball): array
  {
    $files = [];
    $root = 'phar://'.realpath($tarball).'/'; // PharData reports the resolved path (/tmp is a symlink on macOS)
    foreach (new \RecursiveIteratorIterator(new \PharData($tarball)) as $entry) {
      \assert($entry instanceof \SplFileInfo);
      $name = substr($entry->getPathname(), \strlen($root));
      $path = substr($name, (int) strpos($name, '/') + 1);
      if ($entry->isFile() && str_ends_with($path, '.md')) {
        $files[$path] = (string) file_get_contents($entry->getPathname());
      }
    }

    return $files;
  }

  /**
   * The Markdown files of a local checkout, keyed like readTarball().
   *
   * @return array<string, string>
   */
  public static function readDirectory(string $dir): array
  {
    $files = [];
    $dir = rtrim($dir, '/').'/';
    foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)) as $file) {
      \assert($file instanceof \SplFileInfo);
      if ($file->isFile() && 'md' === $file->getExtension()) {
        $files[substr($file->getPathname(), \strlen($dir))] = (string) file_get_contents($file->getPathname());
      }
    }

    return $files;
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
      throw new \RuntimeException('The docs repo has no en/index.md, nothing imported.');
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
