<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Admin;

use PHPUnit\Framework\Attributes\CoversNothing;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Search\SearchHandler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * The admin dashboard's global search runs every admin's searchable filters; one filter on a field the entity
 * does not have (ExampleProject once filtered on "program.name") broke the search block with a DQL error.
 *
 * @internal
 */
#[CoversNothing]
final class AdminGlobalSearchTest extends KernelTestCase
{
  public function testEveryAdminCanBeSearched(): void
  {
    $pool = self::getContainer()->get('sonata.admin.pool');
    \assert($pool instanceof Pool);
    $handler = self::getContainer()->get('sonata.admin.search.handler');
    \assert($handler instanceof SearchHandler);

    $searched = 0;
    foreach ($pool->getAdminServiceCodes() as $code) {
      $pager = $handler->search($pool->getInstance($code), 'catrobat', 1, 1);
      if (null !== $pager) {
        $pager->getCurrentPageResults();
        ++$searched;
      }
    }

    $this->assertGreaterThan(0, $searched);
  }
}
