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
  }

  public function getFlagValue(string $flagName): ?bool
  {
    $this->synchronizeDefaults();

    $request = $this->requestStack->getCurrentRequest();

    if ($request && $request->headers->has('X-Feature-Flag-'.$flagName)) {
      return (bool) $request->headers->get('X-Feature-Flag-'.$flagName);
    }

    $flag = $this->entityManager->getRepository(FeatureFlag::class)->findOneBy(['name' => $flagName]);

    if (null !== $flag) {
      return $flag->getValue();
    }

    return $this->defaultFlags[$flagName] ?? null;
  }
}
