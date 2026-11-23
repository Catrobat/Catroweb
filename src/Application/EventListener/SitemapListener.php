<?php

declare(strict_types=1);

namespace App\Application\EventListener;

use App\DB\Entity\Project\Project;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\Docs\DocPageRepository;
use App\Docs\DocsCatalog;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Presta\SitemapBundle\Event\SitemapPopulateEvent;
use Presta\SitemapBundle\Service\UrlContainerInterface;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsEventListener(event: SitemapPopulateEvent::class, method: 'populate')]
class SitemapListener
{
  private const int BATCH_SIZE = 500;

  public function __construct(
    private readonly EntityManagerInterface $entityManager,
    private readonly UrlGeneratorInterface $urlGenerator,
    private readonly DocPageRepository $docPages,
    private readonly DocsCatalog $docsCatalog,
  ) {
  }

  public function populate(SitemapPopulateEvent $event): void
  {
    $this->addStaticPages($event->getUrlContainer());
    $this->addDocs($event->getUrlContainer());
    $this->addProjects($event->getUrlContainer());
    $this->addUsers($event->getUrlContainer());
  }

  private function addStaticPages(UrlContainerInterface $urls): void
  {
    $urls->addUrl(
      new UrlConcrete(
        $this->urlGenerator->generate('index', ['theme' => 'app'], UrlGeneratorInterface::ABSOLUTE_URL),
        null,
        UrlConcrete::CHANGEFREQ_DAILY,
        1.0,
      ),
      'default',
    );
  }

  /** Every guide in every language it exists in; a translation is its own URL (?locale=). */
  private function addDocs(UrlContainerInterface $urls): void
  {
    /** @var list<array{path: string, locale: string, title: string, sort_order: int, flavors: list<string>, updated_at: \DateTime}> $pages */
    $pages = $this->docPages->guideSummaries();
    foreach ($pages as $page) {
      $params = ['theme' => 'app', 'path' => 'index' === $page['path'] ? '' : $page['path']] + ('en' === $page['locale'] ? [] : ['locale' => $page['locale']]);
      $urls->addUrl(
        new UrlConcrete($this->urlGenerator->generate('docs', $params, UrlGeneratorInterface::ABSOLUTE_URL), $page['updated_at'], UrlConcrete::CHANGEFREQ_WEEKLY, 0.7),
        'docs',
      );
    }

    // The brick reference in English; its pages name their translations (hreflang).
    $reference = [['docs_reference', []]];
    foreach ($this->docsCatalog->types() as $type) {
      $reference[] = ['docs_brick', ['type' => $type]];
    }
    foreach ($this->docsCatalog->catalog('en')['formula'] as $elements) {
      foreach ($elements as $element) {
        $reference[] = ['docs_formula', ['name' => $element['name']]];
      }
    }
    foreach ($reference as [$route, $params]) {
      $urls->addUrl(
        new UrlConcrete($this->urlGenerator->generate($route, ['theme' => 'app'] + $params, UrlGeneratorInterface::ABSOLUTE_URL), null, UrlConcrete::CHANGEFREQ_WEEKLY, 0.6),
        'docs',
      );
    }
  }

  private function addProjects(UrlContainerInterface $urls): void
  {
    $qb = $this->entityManager->createQueryBuilder()
      ->select('p.id', 'p.last_modified_at')
      ->from(Project::class, 'p')
      ->where('p.visible = true')
      ->andWhere('p.auto_hidden = false')
      ->andWhere('p.private = false')
      ->andWhere('p.has_missing_files = false')
      ->andWhere('p.debug_build = false')
      ->orderBy('p.id', \SortDirection::Ascending)
      ->setMaxResults(self::BATCH_SIZE)
    ;

    $this->processBatches($qb, 'p', function (array $row) use ($urls): void {
      $url = $this->urlGenerator->generate(
        'project',
        ['theme' => 'app', 'id' => $row['id']],
        UrlGeneratorInterface::ABSOLUTE_URL,
      );

      $lastmod = $row['last_modified_at'] instanceof \DateTimeInterface ? $row['last_modified_at'] : null;

      $urls->addUrl(
        new UrlConcrete($url, $lastmod, UrlConcrete::CHANGEFREQ_WEEKLY, 0.8),
        'projects',
      );
    });
  }

  private function addUsers(UrlContainerInterface $urls): void
  {
    $qb = $this->entityManager->createQueryBuilder()
      ->select('u.id')
      ->from(User::class, 'u')
      ->where('u.enabled = true')
      ->andWhere('u.profile_hidden = false')
      ->orderBy('u.id', \SortDirection::Ascending)
      ->setMaxResults(self::BATCH_SIZE)
    ;

    $this->processBatches($qb, 'u', function (array $row) use ($urls): void {
      $url = $this->urlGenerator->generate(
        'profile',
        ['theme' => 'app', 'id' => $row['id']],
        UrlGeneratorInterface::ABSOLUTE_URL,
      );

      $urls->addUrl(
        new UrlConcrete($url, null, UrlConcrete::CHANGEFREQ_MONTHLY, 0.5),
        'users',
      );
    });
  }

  private function processBatches(QueryBuilder $qb, string $alias, callable $callback): void
  {
    $lastId = '';

    do {
      $query = (clone $qb);
      if ('' !== $lastId) {
        $query->andWhere("{$alias}.id > :lastId")->setParameter('lastId', $lastId);
      }

      $results = $query->getQuery()->getArrayResult();

      foreach ($results as $row) {
        $callback($row);
        $lastId = $row['id'];
      }

      $this->entityManager->clear();
    } while (self::BATCH_SIZE === \count($results));
  }
}
