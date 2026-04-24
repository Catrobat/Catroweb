<?php

declare(strict_types=1);

namespace App\Admin\System\FeatureFlag;

use App\DB\Entity\System\FeatureFlag;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;

class FeatureFlagManager
{
  private array $defaultFlags;
  private bool $defaultsSynchronized = false;

  /** @var array<string, bool|null> */
  private array $flagCache = [];

  public function __construct(
    protected RequestStack $requestStack,
    protected EntityManagerInterface $entityManager,
    #[Autowire('%feature_flags%')]
    protected array $feature_flags,
  ) {
    $this->defaultFlags = $this->feature_flags;
  }

  public function isEnabled(string $flagName): bool
  {
    return $this->getFlagValue($flagName) ?? false;
  }

  public function synchronizeDefaults(): void
  {
    if ($this->defaultsSynchronized) {
      return;
    }

    if (!$this->entityManager->isOpen()) {
      return;
    }

    try {
      $flagRepository = $this->entityManager->getRepository(FeatureFlag::class);

      $existingFlags = [];
      foreach ($flagRepository->findAll() as $flag) {
        $existingFlags[(string) $flag->getName()] = $flag;
      }

      foreach ($this->defaultFlags as $name => $value) {
        if (!isset($existingFlags[$name])) {
          $this->entityManager->persist(new FeatureFlag($name, $value));
        }
      }

      foreach ($existingFlags as $name => $flag) {
        if (!array_key_exists($name, $this->defaultFlags)) {
          $this->entityManager->remove($flag);
        }
      }

      $this->entityManager->flush();
      $this->defaultsSynchronized = true;
    } catch (\Throwable) {
      // Prevent cascade failures when called during error page rendering
    }
  }

  public function setFlagValue(string $flagName, bool $value): void
  {
    $this->synchronizeDefaults();

    $flag = $this->entityManager->getRepository(FeatureFlag::class)->findOneBy(['name' => $flagName]);

    if (null === $flag) {
      $flag = new FeatureFlag($flagName, $value);
      $this->entityManager->persist($flag);
    } else {
      $flag->setValue($value);
    }

    $this->entityManager->flush();
    $this->flagCache[$flagName] = $value;
  }

  public function getFlagValue(string $flagName): ?bool
  {
    if (array_key_exists($flagName, $this->flagCache)) {
      return $this->flagCache[$flagName];
    }

    $this->synchronizeDefaults();

    $request = $this->requestStack->getCurrentRequest();

    if ($request && $request->headers->has('X-Feature-Flag-'.$flagName)) {
      $value = (bool) $request->headers->get('X-Feature-Flag-'.$flagName);
      $this->flagCache[$flagName] = $value;

      return $value;
    }

    $flag = $this->entityManager->getRepository(FeatureFlag::class)->findOneBy(['name' => $flagName]);

    if (null !== $flag) {
      $this->flagCache[$flagName] = $flag->getValue();

      return $flag->getValue();
    }

    $value = $this->defaultFlags[$flagName] ?? null;
    $this->flagCache[$flagName] = $value;

    return $value;
  }
}
