<?php

declare(strict_types=1);

namespace App\Application\EventListener;

use App\DB\Entity\Project\Project;
use App\DB\Entity\User\User;
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
  ) {
  }

  public function populate(SitemapPopulateEvent $event): void
  {
    $this->addStaticPages($event->getUrlContainer());
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

  private function addProjects(UrlContainerInterface $urls): void
  {
    $qb = $this->entityManager->createQueryBuilder()
      ->select('p.id', 'p.last_modified_at')
      ->from(Project::class, 'p')
      ->where('p.visible = true')
      ->andWhere('p.auto_hidden = false')
      ->andWhere('p.private = false')
      ->andWhere('p.debug_build = false')
      ->orderBy('p.id', 'ASC')
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
      ->orderBy('u.id', 'ASC')
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
