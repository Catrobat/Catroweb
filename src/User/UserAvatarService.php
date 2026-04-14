<?php

declare(strict_types=1);

namespace App\User;

use App\DB\Entity\User\User;
use App\Storage\Images\ImageVariantGenerator;
use App\Storage\Images\ImageVariantUrlBuilder;
use Doctrine\ORM\EntityManagerInterface;
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
    private readonly ImageVariantGenerator $generator,
    private readonly EntityManagerInterface $entity_manager,
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

  public function getVariantsById(?string $user_id): ?ImageVariants
  {
    if (null === $user_id || '' === $user_id) {
      return null;
    }

    $user = $this->entity_manager->find(User::class, $user_id);

    return $this->getVariants($user);
  }

  /**
   * Decodes a validated+resized avatar data URI and writes the full variant
   * set under {@see self::getStorageDir()}. Stamps the basename key on the
   * user and deletes the previous set (if any). Shared between upload-time
   * writes ({@see \App\Api\Services\User\UserApiProcessor}) and the
   * backfill command.
   *
   * Returns the basename key, or null if the input is not a base64 data URI
   * or cannot be decoded (callers should log/skip).
   */
  public function storeFromDataUri(User $user, string $data_uri): ?string
  {
    if (!preg_match('#^data:([^;]+);base64,(.*)$#s', $data_uri, $matches)) {
      return null;
    }

    $decoded = base64_decode($matches[2], true);
    if (false === $decoded) {
      return null;
    }

    $previous_key = $user->getAvatarKey();
    if (null !== $previous_key && '' !== $previous_key) {
      $this->generator->remove($this->storage_dir, $previous_key);
    }

    $temp_source = tempnam(sys_get_temp_dir(), 'catroweb-avatar-');
    if (false === $temp_source) {
      return null;
    }

    try {
      if (false === file_put_contents($temp_source, $decoded)) {
        return null;
      }

      $key = (string) $user->getId().'-'.dechex(random_int(0, 0xFFFFFFFF));
      $this->generator->generate($temp_source, $this->storage_dir, $key);
      $user->setAvatarKey($key);

      return $key;
    } finally {
      @unlink($temp_source);
    }
  }

  public function clearStoredAvatar(User $user): void
  {
    $key = $user->getAvatarKey();
    if (null !== $key && '' !== $key) {
      $this->generator->remove($this->storage_dir, $key);
    }
    $user->setAvatarKey(null);
  }
}
