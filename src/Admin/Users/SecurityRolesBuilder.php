<?php

declare(strict_types=1);

namespace App\Admin\Users;

use Sonata\AdminBundle\SonataConfiguration;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @phpstan-type Role = array{
 *      role: string,
 *      role_translated: string,
 *      is_granted: boolean,
 *      label?: string,
 *      admin_label?: string,
 *      admin_code?: string,
 *      group_label?: string,
 *      group_code?: string
 *  }
 */
final class SecurityRolesBuilder
{
  /**
   * @param array<string, array<string>> $rolesHierarchy
   */
  public function __construct(
    private AuthorizationCheckerInterface $authorizationChecker,
    private SonataConfiguration $configuration,
    private TranslatorInterface $translator,
    private array $rolesHierarchy = []
  ) {
  }

  public function getRoles(?string $domain = null): array
  {
    $securityRoles = [];
    $hierarchy = $this->getHierarchy();

    foreach ($hierarchy as $role => $childRoles) {
      $securityRoles[$role] = $this->getSecurityRole($role, $domain);
      $securityRoles = array_merge(
        $securityRoles,
        $this->getSecurityRoles($hierarchy, $childRoles, $domain)
      );
    }

    return $securityRoles;
  }

  /**
   * @return array<string, array<string>>
   */
  private function getHierarchy(): array
  {
    $roleSuperAdmin = $this->configuration->getOption('role_super_admin');
    \assert(\is_string($roleSuperAdmin));

    $roleAdmin = $this->configuration->getOption('role_admin');
    \assert(\is_string($roleAdmin));

    return array_merge([
      $roleSuperAdmin => [],
      $roleAdmin => [],
    ], $this->rolesHierarchy);
  }

  /**
   * @return array<string, string|bool>
   *
   * @phpstan-return Role
   */
  private function getSecurityRole(string $role, ?string $domain): array
  {
    return [
      'role' => $role,
      'role_translated' => $this->translateRole($role, $domain),
      'is_granted' => $this->authorizationChecker->isGranted($role),
    ];
  }

  /**
   * @param string[][] $hierarchy
   * @param string[]   $roles
   *
   * @return array<string, array<string, string|bool>>
   *
   * @phpstan-return Role[]
   */
  private function getSecurityRoles(array $hierarchy, array $roles, ?string $domain): array
  {
    $securityRoles = [];
    foreach ($roles as $role) {
      if (!\array_key_exists($role, $hierarchy) && !isset($securityRoles[$role])
        && !$this->recursiveArraySearch($role, $securityRoles)) {
        $securityRoles[$role] = $this->getSecurityRole($role, $domain);
      }
    }

    return $securityRoles;
  }

  private function translateRole(string $role, ?string $domain): string
  {
    if (null !== $domain) {
      return $this->translator->trans($role, [], $domain);
    }

    return $role;
  }

  /**
   * @param array<string, array<string, string|bool>>|array<string, string|bool> $roles
   *
   * @phpstan-param Role[]|Role $roles
   */
  private function recursiveArraySearch(string $role, array $roles): bool
  {
    foreach ($roles as $key => $value) {
      if ($role === $key || (\is_array($value) && true === $this->recursiveArraySearch($role, $value))) {
        return true;
      }
    }

    return false;
  }
}
