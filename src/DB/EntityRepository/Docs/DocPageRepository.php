<?php

declare(strict_types=1);

namespace App\DB\EntityRepository\Docs;

use App\DB\Entity\Docs\DocPage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DocPage>
 */
class DocPageRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, DocPage::class);
  }

  /**
   * The guide in the first of the locales that has it.
   *
   * @param list<string> $locales in order of preference
   */
  public function findGuide(string $path, array $locales): ?DocPage
  {
    /** @var list<DocPage> $pages */
    $pages = $this->findBy(['kind' => DocPage::GUIDE, 'path' => $path, 'locale' => $locales]);
    usort($pages, static fn (DocPage $a, DocPage $b): int => array_search($a->getLocale(), $locales, true) <=> array_search($b->getLocale(), $locales, true));

    return $pages[0] ?? null;
  }

  /**
   * Guides without their HTML, for navigation and the sitemap.
   *
   * @param list<string>|null $locales null for all
   *
   * @return array<mixed> rows of path, locale, title, sort_order, flavors, updated_at
   */
  public function guideSummaries(?array $locales = null): array
  {
    $qb = $this->createQueryBuilder('p')
      ->select('p.path', 'p.locale', 'p.title', 'p.sort_order', 'p.flavors', 'p.updated_at')
      ->where('p.kind = :kind')->setParameter('kind', DocPage::GUIDE)
      ->orderBy('p.path')->addOrderBy('p.locale')
    ;
    if (null !== $locales) {
      $qb->andWhere('p.locale IN (:locales)')->setParameter('locales', $locales);
    }

    return $qb->getQuery()->getArrayResult();
  }
}
