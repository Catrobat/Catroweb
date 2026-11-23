<?php

declare(strict_types=1);

namespace App\Docs;

use App\DB\Entity\Docs\DocPage;
use App\DB\EntityRepository\Docs\DocPageRepository;

/** Reads the synced docs for the API and the pages: navigation, a page with English fallback, its translations. */
class DocsLibrary
{
  public function __construct(private readonly DocPageRepository $pages)
  {
  }

  /**
   * The docs locales to try, best first: `de_DE` → de_DE, de-DE, de, en.
   *
   * @return list<string>
   */
  public static function locales(string $locale): array
  {
    $language = strtolower((string) preg_replace('/[_-].*$/', '', $locale));

    return array_values(array_unique([$locale, str_replace('_', '-', $locale), $language, 'en']));
  }

  public function page(string $path, string $locale): ?DocPage
  {
    return $this->pages->findGuide($path, self::locales($locale));
  }

  /** The English page stands in for a translation that does not exist yet. */
  public static function isFallback(DocPage $page, string $requested_locale): bool
  {
    return 'en' === $page->getLocale() && 1 !== preg_match('/^en([_-]|$)/i', $requested_locale);
  }

  /**
   * Every guide as a tree, titled in the best available locale. A page's parent is its nearest ancestor folder that
   * has a page. Pages for the given flavour come first on each level, then by `order` and title.
   *
   * @return list<array{path: string, title: string, children: array<mixed>}>
   */
  public function navigation(string $locale, ?string $flavor = null): array
  {
    $locales = self::locales($locale);
    $best = [];
    /** @var list<array{path: string, locale: string, title: string, sort_order: int, flavors: list<string>, updated_at: \DateTime}> $rows */
    $rows = $this->pages->guideSummaries($locales);
    foreach ($rows as $row) {
      $current = $best[$row['path']] ?? null;
      if (null === $current || array_search($row['locale'], $locales, true) < array_search($current['locale'], $locales, true)) {
        $best[$row['path']] = $row;
      }
    }

    $children = [];
    foreach (array_keys($best) as $path) {
      $parent = '';
      for ($ancestor = $path; str_contains($ancestor, '/');) {
        $ancestor = substr($ancestor, 0, (int) strrpos($ancestor, '/'));
        if (isset($best[$ancestor])) {
          $parent = $ancestor;
          break;
        }
      }
      $children[$parent][] = (string) $path;
    }

    $key = static fn (array $row): array => [!\in_array($flavor, $row['flavors'], true), $row['sort_order'], mb_strtolower($row['title'])];
    $build = static function (string $parent) use (&$build, $children, $best, $key): array {
      $paths = $children[$parent] ?? [];
      usort($paths, static fn (string $a, string $b): int => $key($best[$a]) <=> $key($best[$b]));

      return array_map(static fn (string $path): array => ['path' => $path, 'title' => $best[$path]['title'], 'children' => $build($path)], $paths);
    };

    return $build('');
  }

  /**
   * The navigation in reading order, for previous and next links.
   *
   * @param list<array{path: string, title: string, children: array<mixed>}> $navigation
   *
   * @return list<array{path: string, title: string}>
   */
  public static function flatten(array $navigation): array
  {
    $flat = [];
    foreach ($navigation as $item) {
      $flat[] = ['path' => $item['path'], 'title' => $item['title']];
      /** @var list<array{path: string, title: string, children: array<mixed>}> $children */
      $children = $item['children'];
      array_push($flat, ...self::flatten($children));
    }

    return $flat;
  }

  /**
   * Locales the guide exists in.
   *
   * @return list<string>
   */
  public function translations(string $path): array
  {
    /** @var list<array{locale: string}> $rows */
    $rows = $this->pages->createQueryBuilder('p')->select('p.locale')
      ->where('p.kind = :kind')->andWhere('p.path = :path')
      ->setParameter('kind', DocPage::GUIDE)->setParameter('path', $path)
      ->orderBy('p.locale')->getQuery()->getArrayResult()
    ;

    return array_column($rows, 'locale');
  }
}
