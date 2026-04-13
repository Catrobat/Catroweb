<?php

declare(strict_types=1);

namespace App\User;

use App\DB\Entity\User\User;
use App\Storage\Images\ImageVariantUrlBuilder;
use OpenAPI\Server\Model\ImageVariants;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Builds the {@see ImageVariants} payload exposed via the API for a given user.
 *
 * Avatars live under `public/resources/images/users/{avatar_key}-{variant}@{dpr}x.{format}`.
 * The legacy base64 `avatar` column on the User entity is retained for read-through
 * compatibility until the migration command (`catro:migrate:avatars`) lands; until
 * an avatar key is set on the entity this service returns `null`, which the
 * frontend treats as "no avatar — render the default placeholder".
 */
class UserAvatarService
{
  private readonly string $storage_dir;

  private readonly string $public_path;

  public function __construct(
    private readonly ImageVariantUrlBuilder $url_builder,
    ParameterBagInterface $parameter_bag,
  ) {
    /** @var string $resources_dir */
    $resources_dir = $parameter_bag->get('catrobat.resources.dir');
    $this->storage_dir = rtrim($resources_dir, '/').'/images/users/';
    $this->public_path = 'resources/images/users/';
  }

  public function getStorageDir(): string
  {
    return $this->storage_dir;
  }

  public function getPublicPath(): string
  {
    return $this->public_path;
  }

  /**
   * Returns the responsive avatar variants for a user, or `null` if no
   * filesystem-backed avatar is available yet (legacy base64-only users
   * fall through here until they are migrated).
   */
  public function getVariants(?User $user): ?ImageVariants
  {
    if (!$user instanceof User) {
      return null;
    }

    $key = $user->getAvatarKey();
    if (null === $key || '' === $key) {
      return null;
    }

    return $this->url_builder->build($this->storage_dir, $this->public_path, $key);
  }
}
