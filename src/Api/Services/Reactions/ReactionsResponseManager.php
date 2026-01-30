<?php

declare(strict_types=1);

namespace App\Api\Services\Reactions;

use App\Api\Services\Base\AbstractResponseManager;
use App\DB\Entity\Project\ProgramLike;
use App\DB\Entity\User\User;
use App\Storage\ImageRepository;
use OpenAPI\Server\Model\ReactionSummaryResponse;
use OpenAPI\Server\Model\ReactionUserEntry;
use OpenAPI\Server\Model\ReactionUserInfo;
use OpenAPI\Server\Model\ReactionUsersResponse;
use OpenAPI\Server\Service\SerializerInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ReactionsResponseManager extends AbstractResponseManager
{
  public function __construct(
    TranslatorInterface $translator,
    SerializerInterface $serializer,
    CacheItemPoolInterface $cache,
    private readonly ImageRepository $image_repository,
  ) {
    parent::__construct($translator, $serializer, $cache);
  }

  /**
   * Create a reaction summary response.
   *
   * @param array{total: int, thumbs_up: int, smile: int, love: int, wow: int, active_types: string[]} $counts
   * @param string[]                                                                                   $user_reactions
   */
  public function createReactionSummaryResponse(array $counts, array $user_reactions = []): ReactionSummaryResponse
  {
    return new ReactionSummaryResponse([
      'total' => $counts['total'],
      'thumbs_up' => $counts['thumbs_up'],
      'smile' => $counts['smile'],
      'love' => $counts['love'],
      'wow' => $counts['wow'],
      'user_reactions' => $user_reactions,
      'active_types' => $counts['active_types'],
    ]);
  }

  /**
   * Create a paginated reaction users response.
   *
   * @param array{data: array, next_cursor: ?string, has_more: bool} $paginated_data
   */
  public function createReactionUsersResponse(array $paginated_data): ReactionUsersResponse
  {
    $entries = [];

    foreach ($paginated_data['data'] as $user_data) {
      $entries[] = $this->createReactionUserEntry($user_data);
    }

    return new ReactionUsersResponse([
      'data' => $entries,
      'next_cursor' => $paginated_data['next_cursor'],
      'has_more' => $paginated_data['has_more'],
    ]);
  }

  /**
   * Create a reaction user entry from user data.
   *
   * @param array{user: User, types: int[], reacted_at: \DateTimeInterface|null} $user_data
   */
  private function createReactionUserEntry(array $user_data): ReactionUserEntry
  {
    $user = $user_data['user'];
    $types = array_filter(array_map(
      static function (int $type_id): ?string {
        return ProgramLike::$TYPE_NAMES[$type_id] ?? null;
      },
      $user_data['types']
    ));

    $avatar = $user->getAvatar();
    $avatar_url = null !== $avatar ? $this->image_repository->getAbsoluteWebPath($avatar, '', true) : '';

    $entry = new ReactionUserEntry();
    $entry->setUser(new ReactionUserInfo([
      'id' => $user->getId() ?? '',
      'username' => $user->getUsername(),
      'avatar' => $avatar_url,
    ]));
    $entry->setTypes(array_values($types));

    $reacted_at = $user_data['reacted_at'];
    if ($reacted_at instanceof \DateTime) {
      $entry->setReactedAt($reacted_at);
    } elseif ($reacted_at instanceof \DateTimeInterface) {
      $entry->setReactedAt(\DateTime::createFromInterface($reacted_at));
    }

    return $entry;
  }
}
