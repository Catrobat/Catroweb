<?php

namespace App\Admin\Tools\FeatureFlag;

use App\DB\Entity\FeatureFlag;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class FeatureFlagManager
{
  private array $defaultFlags;

  public function __construct(
    protected RequestStack $requestStack,
    protected EntityManagerInterface $entityManager,
    protected ParameterBagInterface $parameter_bag
  ) {
    if($this->entityManager->getConnection()->isConnected()) {
      $this->defaultFlags = include $parameter_bag->get('features');

      $flagMap = [];
      foreach ($this->defaultFlags as $name => $value) {
        $flagMap[$name] = new FeatureFlag($name, $value);
        $flag = $this->entityManager->getRepository(FeatureFlag::class)->findOneBy(['name' => $name]);

        if (!$flag) {
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

  public function isEnabled(string $flagName): ?bool
  {
    $flagValue = $this->getFlagValue($flagName);

    return null !== $flagValue && $flagValue;
  }

  public function setFlagValue(string $flagName, bool $value): void
  {
    $flag = $this->entityManager->getRepository(FeatureFlag::class)->findOneBy(['name' => $flagName]);

    if (!$flag) {
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

    if ($flag) {
      return $flag->getValue();
    }

    return $this->defaultFlags[$flagName] ?? null;
  }
}
