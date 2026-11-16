<?php

declare(strict_types=1);

namespace Tests\PhpUnit\System;

use App\System\Commands\ConsoleApplication;
use Doctrine\Deprecations\Deprecation;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Deprecations from our own code once filled prod.deprecations.log with ~66 MB a day.
 *
 * @internal
 */
#[CoversClass(ConsoleApplication::class)]
final class NoDeprecationsTest extends KernelTestCase
{
  public function testEntityMappingsUseSortDirection(): void
  {
    $entity_manager = self::getContainer()->get(EntityManagerInterface::class);
    \assert($entity_manager instanceof EntityManagerInterface);

    Deprecation::disable(); // the counter is process-global: earlier tests' query-builder deprecations must not count here
    Deprecation::enableTrackingDeprecations();
    Deprecation::withoutDeduplication();
    try {
      $factory = new ClassMetadataFactory(); // uncached, so every mapping is read again
      $factory->setEntityManager($entity_manager);
      $this->assertNotEmpty($factory->getAllMetadata());

      $this->assertSame(0, Deprecation::getTriggeredDeprecations()['https://github.com/doctrine/orm/issues/11313'] ?? 0);
    } finally {
      Deprecation::disable();
    }
  }

  public function testTheConsoleRegistersCommandsWithoutTheDeprecatedAdd(): void
  {
    $deprecations = [];
    set_error_handler(static function (int $level, string $message) use (&$deprecations): bool {
      $deprecations[] = $message;

      return true;
    }, E_USER_DEPRECATED);
    try {
      $this->assertNotEmpty(new ConsoleApplication(self::bootKernel())->all());
    } finally {
      restore_error_handler();
    }

    $this->assertSame([], array_values(array_filter($deprecations, static fn (string $m): bool => str_contains($m, 'Application::add()'))));
  }
}
