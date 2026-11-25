<?php

declare(strict_types=1);

namespace App\Docs;

use App\DB\Entity\Docs\DocPage;
use App\DB\EntityRepository\Docs\DocPageRepository;
use App\DB\EntityRepository\Project\ProjectRepository;
use App\Storage\ScreenshotRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Imports the user docs (`user-docs/` in this repo, the same layout the Catrobat/docs repo had) into doc_page: renders
 * new and changed files, deletes pages whose file is gone, all in one transaction. A file is re-rendered only when it
 * changed, or when it embeds a project (name and picture may change), a brick or a formula element (a new catalog may
 * show it differently), or when a media file it links changed. Media are served by the web server as static files
 * (public/docs-media links to user-docs/media); their URLs carry a content hash, so a replaced file is a new URL.
 */
class DocsSync
{
  /** The docs live in this repo: "Improve this page" and links to other repo files go to GitHub's copy of main. */
  final public const string GITHUB = 'https://github.com/Catrobat/Catroweb/';

  /**
   * The file types allowed in media/ (the writer guide's image types and PDF). No SVG: the web server serves media as
   * static files and cannot sandbox one opened directly.
   */
  final public const array MEDIA_EXTENSIONS = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'pdf'];

  private readonly DocsMarkdown $markdown;

  public function __construct(
    private readonly EntityManagerInterface $entity_manager,
    private readonly DocPageRepository $pages,
    private readonly ProjectRepository $projects,
    private readonly ScreenshotRepository $screenshots,
    #[Autowire('%catrobat.user_docs.dir%')]
    public readonly string $docs_dir,
    DocsCatalog $catalog,
    TranslatorInterface $translator,
  ) {
    $this->markdown = new DocsMarkdown(self::GITHUB.'blob/main/user-docs/', $this->projectCard(...), $catalog, $docs_dir, $translator);
  }

  /** "Improve this page": the English file in GitHub's editor (translations are generated from it). */
  public function editUrl(DocPage $page): string
  {
    $file = DocPage::GUIDE === $page->getKind() ? preg_replace('#^[^/]+/#', 'en/', $page->getSourceFile(), 1) : $page->getSourceFile();

    return self::GITHUB.'edit/main/user-docs/'.$file;
  }

  /**
   * "Improve this page" of a brick: its English explanation in GitHub's editor, or a new file there when it has none
   * (a brick page needs no file to exist).
   */
  public function brickEditUrl(string $type, bool $exists): string
  {
    return $exists
      ? self::GITHUB.'edit/main/user-docs/reference/bricks/'.rawurlencode($type).'.md'
      : self::GITHUB.'new/main/user-docs/reference/bricks?filename='.rawurlencode($type).'.md';
  }

  /**
   * The page's Markdown source with absolute links (DocsMarkdown::markdown), null when its file is gone.
   *
   * @param string $root absolute URL of the theme root, with trailing slash
   */
  public function markdown(DocPage $page, string $root): ?string
  {
    $file = rtrim($this->docs_dir, '/').'/'.$page->getSourceFile();

    return is_file($file) ? $this->markdown->markdown((string) file_get_contents($file), $page->getSourceFile(), $root) : null;
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
   * The media files of a docs folder (path inside media/ => version, DocsMarkdown::mediaVersion). They go live as
   * static files (public/docs-media), so a file of another type (SVG, HTML, ...) or a link fails the sync before
   * anything changes: in a deploy and in the docs publish the sync runs before the new docs go live.
   *
   * @return array<string, string>
   */
  public static function readMedia(string $dir): array
  {
    $root = rtrim($dir, '/').'/media/';
    if (!is_dir($root)) {
      return [];
    }
    $media = [];
    $refused = [];
    foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS)) as $file) {
      \assert($file instanceof \SplFileInfo);
      $path = substr($file->getPathname(), \strlen($root));
      if ($file->isLink() || !$file->isFile() || !\in_array(strtolower($file->getExtension()), self::MEDIA_EXTENSIONS, true)) {
        if ('.gitkeep' !== $path) {
          $refused[] = $path;
        }
        continue;
      }
      $media[$path] = (string) DocsMarkdown::mediaVersion($file->getPathname());
    }
    if ([] !== $refused) {
      sort($refused);

      throw new \RuntimeException('The user docs have media that may not go live (only '.implode(', ', self::MEDIA_EXTENSIONS).', no links): '.implode(', ', $refused));
    }
    ksort($media);

    return $media;
  }

  /**
   * @param array<string, string> $files repo path => content
   * @param array<string, string> $media path inside media/ => version (readMedia)
   *
   * @return array{created: int, updated: int, deleted: int, skipped: list<string>}
   */
  public function import(array $files, array $media = []): array
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
      // The render version is part of the change check, so a release that renders differently re-renders every page,
      // and so are the versions of the media the page names, so a replaced image gets its new URL.
      // ponytail: a media file counts as named when its path inside media/ appears in the text, raw or URL-encoded.
      $versions = '';
      foreach ($media as $path => $version) {
        if (str_contains($content, $path) || str_contains($content, implode('/', array_map(rawurlencode(...), explode('/', $path))))) {
          $versions .= "\n".$path.'='.$version;
        }
      }
      $entries[$key] = ['file' => $file, 'content' => $content, 'hash' => hash('sha256', $content), 'content_hash' => hash('sha256', DocsMarkdown::VERSION."\n".$content.$versions)] + $location;
      if ('en' === $location['locale']) {
        $english_hashes[$location['kind'].'|'.$location['path']] = $entries[$key]['hash'];
      }
    }
    if (!isset($entries[DocPage::GUIDE.'|en|index'])) {
      // A broken download must not wipe the docs.
      throw new \RuntimeException('The user docs have no en/index.md, nothing imported.');
    }

    return $this->entity_manager->wrapInTransaction(function () use ($entries, $english_hashes, $skipped, $media): array {
      $existing = [];
      foreach ($this->pages->createQueryBuilder('p')->select('p.id', 'p.kind', 'p.locale', 'p.path', 'p.content_hash', 'p.source_hash', 'p.reviewed', 'p.translation_state')->getQuery()->getArrayResult() as $row) {
        $existing[$row['kind'].'|'.$row['locale'].'|'.$row['path']] = $row;
      }
      $created = $updated = 0;
      foreach ($entries as $key => $entry) {
        $row = $existing[$key] ?? null;
        unset($existing[$key]);
        $state = 'en' === $entry['locale'] ? DocPage::STATE_SOURCE : null; // translations: known after the front matter is read
        if (null !== $row && $row['content_hash'] === $entry['content_hash'] && 1 !== preg_match('/::(project|brick|formula)\{/', $entry['content'])) {
          $state ??= self::translationState($english_hashes[$entry['kind'].'|'.$entry['path']] ?? null, $row['source_hash'], $row['reviewed']);
          if ($state !== $row['translation_state']) {
            $this->pages->find($row['id'])?->setTranslationState($state);
          }
          continue;
        }
        try {
          $rendered = $this->markdown->convert($entry['content'], $entry['file'], $media);
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
