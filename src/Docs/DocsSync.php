<?php

declare(strict_types=1);

namespace App\Docs;

use App\DB\Entity\Docs\DocPage;
use App\DB\EntityRepository\Docs\DocPageRepository;
use App\DB\EntityRepository\Project\ProjectRepository;
use App\Storage\ScreenshotRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * Imports the docs repo into doc_page: renders new and changed files, deletes pages whose file is gone, all in one
 * transaction. A file is re-rendered only when it changed, or when it embeds a project (name and picture may change).
 */
class DocsSync
{
  /** Extension => content type of the media share serves from the docs repo. */
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
    #[Autowire(env: 'DOCS_REPOSITORY')]
    public readonly string $repository,
    #[Autowire(env: 'DOCS_REF')]
    public readonly string $ref,
    #[Autowire('%catrobat.docs_media.dir%')]
    private readonly string $media_dir,
    /** Limits against a hostile or broken archive: the download, and everything it unpacks to. */
    #[Autowire(env: 'int:DOCS_MAX_DOWNLOAD_BYTES')]
    public readonly int $max_download_bytes,
    #[Autowire(env: 'int:DOCS_MAX_UNPACKED_BYTES')]
    public readonly int $max_unpacked_bytes,
  ) {
    $this->markdown = new DocsMarkdown('https://github.com/'.$repository.'/blob/'.$ref.'/', $this->projectCard(...));
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
   * Unpacks a GitHub tarball into $into (an empty folder the caller removes) and returns the repo's top folder.
   * The system tar, because PharData skips entries with long (pax) names, such as some brick screenshots. tar
   * refuses `..` and absolute names; symlinks it creates are skipped by readDirectory().
   */
  public static function extractTarball(string $tarball, string $into, int $max_bytes): string
  {
    // The unpacked size is at least the tar stream's: measure that before anything lands on disk.
    $gz = gzopen($tarball, 'rb');
    if (false === $gz) {
      throw new \RuntimeException('Cannot read the docs tarball.');
    }
    $size = 0;
    while (!gzeof($gz) && $size <= $max_bytes) {
      $size += \strlen((string) gzread($gz, 1 << 20));
    }
    gzclose($gz);
    if ($size > $max_bytes) {
      throw new \RuntimeException('The docs tarball unpacks to more than '.$max_bytes.' bytes.');
    }
    (new Process(['tar', '-xzf', $tarball, '-C', $into, '--no-same-owner', '--no-same-permissions']))->setTimeout(300)->mustRun();
    $top = glob(rtrim($into, '/').'/*', GLOB_ONLYDIR) ?: [];
    if (1 !== \count($top)) {
      throw new \RuntimeException('The docs tarball has no single top folder.');
    }

    return $top[0];
  }

  /**
   * The Markdown files (content) and media files (source path to copy from) of a checkout, keyed by their path in
   * the repo.
   *
   * @return array{pages: array<string, string>, media: array<string, string>}
   */
  public static function readDirectory(string $dir): array
  {
    $dir = rtrim($dir, '/').'/';
    $files = [];
    foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)) as $file) {
      \assert($file instanceof \SplFileInfo);
      if ($file->isFile() && !$file->isLink()) { // a link could point anywhere on the server
        $files[substr($file->getPathname(), \strlen($dir))] = $file->getPathname();
      }
    }

    return self::split($files);
  }

  /**
   * A file of the repo's media/ folder that share serves (CONTRIBUTING's image types and PDF): its path inside media/,
   * or null. Every segment must be a plain name, so nothing from an archive can point outside the media folder.
   */
  public static function mediaPath(string $repo_path): ?string
  {
    $pattern = '#^media/((?:[A-Za-z0-9_-][A-Za-z0-9._-]*/)*[A-Za-z0-9_-][A-Za-z0-9._-]*\.([A-Za-z0-9]+))$#';
    if (1 !== preg_match($pattern, $repo_path, $m) || !isset(self::MEDIA_TYPES[strtolower($m[2])]) || str_contains($m[1], '..')) {
      return null;
    }

    return $m[1];
  }

  /** The synced copy of a media file, or null. */
  public function mediaFile(string $path): ?string
  {
    $file = $this->media_dir.'files/'.$path;

    return null !== self::mediaPath('media/'.$path) && is_file($file) ? $file : null;
  }

  /**
   * Replaces the synced media with the given files. Written next to the old copy and swapped in, so pages never see a
   * half-copied folder. The swap happens below media_dir, which may be a symlink to storage that survives deploys.
   *
   * @param array<string, string> $media path inside media/ => file to copy from
   */
  public function importMedia(array $media): int
  {
    $filesystem = new Filesystem();
    $current = $this->media_dir.'files';
    $next = $current.'.new';
    if ([] === $media && [] !== (glob($current.'/*') ?: [])) {
      throw new \RuntimeException('The docs repo has no media any more, the synced copy is kept.'); // more likely a broken archive
    }
    $filesystem->remove([$next, $current.'.old']);
    $filesystem->mkdir($next);
    foreach ($media as $path => $source) {
      if (null === self::mediaPath('media/'.$path)) {
        throw new \InvalidArgumentException('Not a docs media file: '.$path);
      }
      $filesystem->mkdir(\dirname($next.'/'.$path));
      if (!copy($source, $next.'/'.$path)) {
        throw new \RuntimeException('Could not copy docs media '.$path);
      }
    }
    if (is_dir($current)) {
      $filesystem->rename($current, $current.'.old');
    }
    $filesystem->rename($next, $current);
    $filesystem->remove($current.'.old');

    return \count($media);
  }

  /**
   * @param array<string, string> $files repo path => file
   *
   * @return array{pages: array<string, string>, media: array<string, string>}
   */
  private static function split(array $files): array
  {
    $pages = $media = [];
    foreach ($files as $path => $file) {
      if (str_ends_with($path, '.md')) {
        $pages[$path] = (string) file_get_contents($file);
      } elseif (null !== ($media_path = self::mediaPath($path))) {
        $media[$media_path] = $file;
      }
    }

    return ['pages' => $pages, 'media' => $media];
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
