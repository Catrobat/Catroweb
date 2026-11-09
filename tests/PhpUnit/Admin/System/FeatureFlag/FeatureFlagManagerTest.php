<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Admin\System\FeatureFlag;

use App\Admin\System\FeatureFlag\FeatureFlagManager;
use App\DB\Entity\System\FeatureFlag;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @internal
 */
#[CoversClass(FeatureFlagManager::class)]
final class FeatureFlagManagerTest extends TestCase
{
  public function testSynchronizeDefaultsPersistsMissingFlagsAndRemovesStaleFlags(): void
  {
    $repository = $this->createMock(EntityRepository::class);
    $entityManager = $this->createMock(EntityManagerInterface::class);

    $existingFlag = new FeatureFlag('GET_projects_elastica', false);
    $staleFlag = new FeatureFlag('Deprecated-Flag', true);

    $entityManager->method('isOpen')->willReturn(true);

    $entityManager
      ->expects($this->once())
      ->method('getRepository')
      ->with(FeatureFlag::class)
      ->willReturn($repository)
    ;

    $repository
      ->expects($this->once())
      ->method('findAll')
      ->willReturn([$existingFlag, $staleFlag])
    ;

    $entityManager
      ->expects($this->once())
      ->method('persist')
      ->with($this->callback(static fn ($flag): bool => $flag instanceof FeatureFlag && 'Test-Flag' === $flag->getName()))
    ;

    $entityManager
      ->expects($this->once())
      ->method('remove')
      ->with($staleFlag)
    ;

    $entityManager
      ->expects($this->once())
      ->method('flush')
    ;

    $manager = new FeatureFlagManager(
      $this->createStub(RequestStack::class),
      $entityManager,
      [
        'Test-Flag' => false,
        'GET_projects_elastica' => false,
      ],
    );

    $manager->synchronizeDefaults();
  }

  public function testRequestHeadersOverrideFlagsOutsideProdOnly(): void
  {
    $request_stack = new RequestStack();
    $request_stack->push(Request::create('/', server: ['HTTP_X_FEATURE_FLAG_TEST_FLAG' => '1']));
    $entity_manager = $this->createStub(EntityManagerInterface::class);
    $entity_manager->method('isOpen')->willReturn(false);
    $entity_manager->method('getRepository')->willReturn($this->createStub(EntityRepository::class));

    $this->assertTrue((new FeatureFlagManager($request_stack, $entity_manager, ['Test-Flag' => false], 'test'))->isEnabled('Test-Flag'));
    $this->assertFalse((new FeatureFlagManager($request_stack, $entity_manager, ['Test-Flag' => false], 'prod'))->isEnabled('Test-Flag'));
  }
}
