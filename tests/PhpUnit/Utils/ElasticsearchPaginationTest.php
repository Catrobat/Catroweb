<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Utils;

use App\Utils\ElasticsearchPagination;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ElasticsearchPagination::class)]
final class ElasticsearchPaginationTest extends TestCase
{
  #[DataProvider('pages')]
  public function testNeverRequestsHitsBeyondTheResultWindow(?int $limit, int $offset, ?int $expected_size): void
  {
    $requested = null;
    $finder = $this->createStub(TransformedFinder::class);
    $finder->method('find')->willReturnCallback(static function (mixed $query, ?int $size, array $options) use (&$requested): array {
      $requested = [$size, $options['from']];

      return ['hit'];
    });

    $result = ElasticsearchPagination::find($finder, 'query', $limit, $offset);

    if (null === $expected_size) {
      $this->assertSame([], $result);
      $this->assertNull($requested, 'Elasticsearch must not be queried past the window');
    } else {
      $this->assertSame([$expected_size, $offset], $requested);
    }
  }

  /**
   * @return array<string, array{?int, int, ?int}>
   */
  public static function pages(): array
  {
    return [
      'first page' => [20, 0, 20],
      'default size' => [null, 0, 10],
      'page crossing the window is trimmed' => [20, 9995, 5],
      'last full page' => [20, 9980, 20],
      'at the window' => [20, 10000, null],
      'far beyond the window' => [20, PHP_INT_MAX, null],
    ];
  }
}
