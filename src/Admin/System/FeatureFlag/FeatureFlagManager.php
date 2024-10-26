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

  public function __construct(
    protected RequestStack $requestStack,
    protected EntityManagerInterface $entityManager,
    #[Autowire('%feature_flags%')]
    protected array $feature_flags,
  ) {
    if ($this->entityManager->getConnection()->isConnected()) {
      $this->defaultFlags = $this->feature_flags;

      $flagMap = [];
      foreach ($this->defaultFlags as $name => $value) {
        $flagMap[$name] = new FeatureFlag($name, $value);
        $flag = $this->entityManager->getRepository(FeatureFlag::class)->findOneBy(['name' => $name]);

        if (null === $flag) {
          $flag = $flagMap[$name];
          $this->entityManager->persist($flag);
        }
      }

      $entityManagerFlags = $this->entityManager->getRepository(FeatureFlag::class)->findAll();
      foreach ($entityManagerFlags as $flag) {
        $flagName = $flag->getName();
        if (!array_key_exists($flagName, $flagMap)) {
          $this->entityManager->remove($flag);
        }
      }

      $this->entityManager->flush();
    }
  }

  public function isEnabled(string $flagName): bool
  {
    return $this->getFlagValue($flagName) ?? false;
  }

  public function setFlagValue(string $flagName, bool $value): void
  {
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
