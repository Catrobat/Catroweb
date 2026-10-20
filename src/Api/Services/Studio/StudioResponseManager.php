<?php

declare(strict_types=1);

namespace App\Api\Services\Studio;

use App\Api\Services\Base\AbstractResponseManager;
use App\DB\Entity\Studio\Studio;
use App\DB\Entity\Studio\StudioActivity;
use App\DB\Entity\Studio\StudioInvite;
use App\DB\Entity\Studio\StudioJoinRequest;
use App\DB\Entity\Studio\StudioProject;
use App\DB\Entity\Studio\StudioUser;
use App\DB\Entity\User\Comment\UserComment;
use App\DB\Entity\User\User;
use App\Project\ProjectManager;
use App\Studio\StudioManager;
use App\User\UserAvatarService;
use OpenAPI\Server\Model\StudioActivityListResponse;
use OpenAPI\Server\Model\StudioActivityResponse;
use OpenAPI\Server\Model\StudioCommentListResponse;
use OpenAPI\Server\Model\StudioCommentResponse;
use OpenAPI\Server\Model\StudioInviteListResponse;
use OpenAPI\Server\Model\StudioInviteResponse;
use OpenAPI\Server\Model\StudioJoinRequestListResponse;
use OpenAPI\Server\Model\StudioJoinRequestResponse;
use OpenAPI\Server\Model\StudioListResponse;
use OpenAPI\Server\Model\StudioMemberListResponse;
use OpenAPI\Server\Model\StudioMemberResponse;
use OpenAPI\Server\Model\StudioProjectListResponse;
use OpenAPI\Server\Model\StudioProjectResponse;
use OpenAPI\Server\Model\StudioResponse;
use OpenAPI\Server\Model\StudioUserProjectResponse;
use OpenAPI\Server\Model\StudioUserProjectsResponse;
use OpenAPI\Server\Service\SerializerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class StudioResponseManager extends AbstractResponseManager
{
  public function __construct(
    TranslatorInterface $translator,
    SerializerInterface $serializer,
    \Psr\Cache\CacheItemPoolInterface $cache,
    private readonly UrlGeneratorInterface $url_generator,
    private readonly ParameterBagInterface $parameter_bag,
    private readonly StudioManager $studio_manager,
    private readonly ProjectManager $project_manager,
    private readonly UserAvatarService $user_avatar_service,
  ) {
    parent::__construct($translator, $serializer, $cache);
  }

  /**
   * @param array{users: array<string, int>, projects: array<string, int>, activities: array<string, int>, comments: array<string, int>}|null $preloaded_counts
   */
  public function createStudioResponse(Studio $studio, ?array $preloaded_counts = null, ?User $viewer = null): StudioResponse
  {
    $id = $studio->getId() ?? '';

    return (new StudioResponse())
      ->setId($studio->getId())
      ->setName($studio->getName())
      ->setDescription($studio->getDescription())
      ->setIsPublic($studio->isIsPublic())
      ->setEnableComments($studio->isAllowComments())
      ->setCover($this->studio_manager->getCoverVariants($studio))
      ->setMembersCount($preloaded_counts['users'][$id] ?? $this->studio_manager->countStudioUsers($studio))
      ->setProjectsCount($preloaded_counts['projects'][$id] ?? $this->studio_manager->countStudioProjects($studio, $viewer))
      ->setActivitiesCount($preloaded_counts['activities'][$id] ?? $this->studio_manager->countStudioActivities($studio))
      ->setCommentsCount($preloaded_counts['comments'][$id] ?? $this->studio_manager->countStudioComments($studio))
    ;
  }

  /**
   * @param array{users: array<string, int>, projects: array<string, int>, activities: array<string, int>, comments: array<string, int>}|null $preloaded_counts
   */
  public function createStudioResponseWithUserContext(Studio $studio, ?User $user, ?array $preloaded_counts = null): StudioResponse
  {
    $response = $this->createStudioResponse($studio, $preloaded_counts, $user);

    if ($user instanceof User) {
      $studio_user = $this->studio_manager->findStudioUser($user, $studio);
      $is_active = true === $studio_user?->isActive();
      $response->setIsMember($is_active);
      $response->setIsBanned(StudioUser::STATUS_BANNED === $studio_user?->getStatus());

      $userRole = $is_active ? $studio_user->getRole() : null;
      $response->setUserRole($userRole);

      if ('admin' === $userRole) {
        $response->setPendingJoinRequestsCount(
          count($this->studio_manager->findPendingJoinRequests($studio))
        );
      }

      $joinRequest = $this->studio_manager->findJoinRequestByUserAndStudio($user, $studio);
      $response->setJoinRequestStatus($joinRequest?->getStatus());
    }

    return $response;
  }

  /**
   * @param Studio[] $studios
   */
  public function createStudioListResponse(array $studios, bool $has_more, ?User $user = null, ?int $current_offset = null): StudioListResponse
  {
    $studioIds = array_filter(array_map(static fn (Studio $s) => $s->getId(), $studios));
    $preloaded_counts = $this->studio_manager->countStudioStatsBatch($studioIds, $user);

    $data = [];
    foreach ($studios as $studio) {
      $data[] = $this->createStudioResponseWithUserContext($studio, $user, $preloaded_counts);
    }

    $next_cursor = null;
    if ($has_more && [] !== $studios) {
      $offset = ($current_offset ?? 0) + count($studios);
      $next_cursor = base64_encode((string) $offset);
    }

    return (new StudioListResponse())
      ->setData($data)
      ->setHasMore($has_more)
      ->setNextCursor($next_cursor)
    ;
  }

  /**
   * @param StudioUser[] $members
   */
  public function createMemberListResponse(array $members, bool $has_more): StudioMemberListResponse
  {
    $data = [];
    foreach ($members as $member) {
      $data[] = $this->createMemberResponse($member);
    }

    $next_cursor = null;
    if ($has_more && [] !== $members) {
      $last = end($members);
      $next_cursor = base64_encode((string) $last->getId());
    }

    return (new StudioMemberListResponse())
      ->setData($data)
      ->setHasMore($has_more)
      ->setNextCursor($next_cursor)
    ;
  }

  public function createMemberResponse(StudioUser $member): StudioMemberResponse
  {
    $user = $member->getUser();

    return (new StudioMemberResponse())
      ->setId($member->getId())
      ->setUserId($user->getId())
      ->setUsername($user->getUsername())
      ->setAvatar($this->user_avatar_service->getVariants($user))
      ->setRole($member->getRole())
      ->setStatus($member->getStatus())
      ->setJoinedAt($member->getCreatedOn())
      ->setStudioProjectCount($this->studio_manager->countStudioUserProjects($member->getStudio(), $user))
    ;
  }

  /**
   * @param StudioProject[] $projects
   */
  public function createProjectListResponse(array $projects, bool $has_more): StudioProjectListResponse
  {
    $data = [];
    foreach ($projects as $studioProject) {
      $data[] = $this->createProjectResponse($studioProject);
    }

    $next_cursor = null;
    if ($has_more && [] !== $projects) {
      $last = end($projects);
      $next_cursor = base64_encode((string) $last->getId());
    }

    return (new StudioProjectListResponse())
      ->setData($data)
      ->setHasMore($has_more)
      ->setNextCursor($next_cursor)
    ;
  }

  public function createProjectResponse(StudioProject $studioProject): StudioProjectResponse
  {
    $project = $studioProject->getProject();
    $programId = $project->getId();
    $user = $studioProject->getUser();

    return (new StudioProjectResponse())
      ->setId($programId)
      ->setName($project->getName())
      ->setAddedBy($user->getUsername())
      ->setAddedAt($studioProject->getCreatedOn())
      ->setScreenshot(null !== $programId ? $this->project_manager->getScreenshotVariants($programId) : null)
      ->setAuthor($project->getUser()?->getUsername())
      ->setAuthorId($project->getUser()?->getId())
      ->setNotForKids($project->getNotForKids())
    ;
  }

  /**
   * @param UserComment[] $comments
   */
  public function createCommentListResponse(array $comments, bool $has_more): StudioCommentListResponse
  {
    $data = [];
    foreach ($comments as $comment) {
      $data[] = $this->createCommentResponse($comment);
    }

    $next_cursor = null;
    if ($has_more && [] !== $comments) {
      $last = end($comments);
      $next_cursor = base64_encode((string) $last->getId());
    }

    return (new StudioCommentListResponse())
      ->setData($data)
      ->setHasMore($has_more)
      ->setNextCursor($next_cursor)
    ;
  }

  public function createCommentResponse(UserComment $comment): StudioCommentResponse
  {
    $user = $comment->getUser();

    return (new StudioCommentResponse())
      ->setId($comment->getId())
      ->setMessage($comment->getText())
      ->setUsername($comment->getUsername())
      ->setUserId($user?->getId())
      ->setUserAvatar($this->user_avatar_service->getVariants($user))
      ->setParentId($comment->getParentId())
      ->setReplyCount($this->studio_manager->countCommentReplies($comment->getId() ?? ''))
      ->setUserApproved($user?->isApproved() ?? false)
      ->setCreatedAt($comment->getUploadDate())
    ;
  }

  /**
   * @param StudioActivity[] $activities
   */
  public function createActivityListResponse(array $activities, bool $has_more): StudioActivityListResponse
  {
    $data = [];
    foreach ($activities as $activity) {
      $data[] = $this->createActivityResponse($activity);
    }

    $next_cursor = null;
    if ($has_more && [] !== $activities) {
      $last = end($activities);
      $next_cursor = base64_encode((string) $last->getId());
    }

    return (new StudioActivityListResponse())
      ->setData($data)
      ->setHasMore($has_more)
      ->setNextCursor($next_cursor)
    ;
  }

  public function createActivityResponse(StudioActivity $activity): StudioActivityResponse
  {
    $user = $activity->getUser();

    return (new StudioActivityResponse())
      ->setId($activity->getId())
      ->setType($activity->getType())
      ->setUserId($user->getId())
      ->setUsername($user->getUsername())
      ->setCreatedAt($activity->getCreatedOn())
    ;
  }

  /**
   * @param list<array{id: string|null, name: string, in_studio: bool, screenshot_small: string|null}> $projects
   */
  public function createUserProjectsResponse(array $projects): StudioUserProjectsResponse
  {
    $data = [];
    foreach ($projects as $project) {
      $entry = (new StudioUserProjectResponse())
        ->setId($project['id'])
        ->setName($project['name'])
        ->setInStudio($project['in_studio'])
      ;
      if (null !== $project['id']) {
        $entry->setScreenshot($this->project_manager->getScreenshotVariants($project['id']));
      }
      $data[] = $entry;
    }

    return (new StudioUserProjectsResponse())
      ->setProjects($data)
    ;
  }

  /**
   * @param StudioJoinRequest[] $joinRequests
   */
  public function createJoinRequestListResponse(array $joinRequests, bool $has_more): StudioJoinRequestListResponse
  {
    $data = [];
    foreach ($joinRequests as $joinRequest) {
      $data[] = $this->createJoinRequestResponse($joinRequest);
    }

    $next_cursor = null;
    if ($has_more && [] !== $joinRequests) {
      $last = end($joinRequests);
      $next_cursor = base64_encode((string) $last->getId());
    }

    return (new StudioJoinRequestListResponse())
      ->setData($data)
      ->setHasMore($has_more)
      ->setNextCursor($next_cursor)
    ;
  }

  public function createJoinRequestResponse(StudioJoinRequest $joinRequest): StudioJoinRequestResponse
  {
    $user = $joinRequest->getUser();

    return (new StudioJoinRequestResponse())
      ->setId($joinRequest->getId())
      ->setUserId($user?->getId())
      ->setUsername($user?->getUsername())
      ->setAvatar($this->user_avatar_service->getVariants($user))
      ->setStatus($joinRequest->getStatus())
    ;
  }

  /**
   * For 201 Created responses. Emits a `Location` header per HTTP semantics.
   */
  public function addStudioCreatedLocationToHeaders(array &$responseHeaders, Studio $studio): void
  {
    $responseHeaders['Location'] = $this->createStudioLocation($studio);
  }

  /**
   * For 200 OK responses. Emits `Content-Location` (descriptive metadata)
   * instead of `Location` — PHP's CGI/FastCGI SAPI rewrites a response with
   * `200 + Location` into `302 Found`, which breaks client-side fetches that
   * then follow the redirect to an HTML page and fail JSON parsing.
   */
  public function addStudioContentLocationToHeaders(array &$responseHeaders, Studio $studio): void
  {
    $responseHeaders['Content-Location'] = $this->createStudioLocation($studio);
  }

  /**
   * @param StudioInvite[] $invites
   */
  public function createInviteListResponse(array $invites): StudioInviteListResponse
  {
    return (new StudioInviteListResponse())
      ->setData(array_map($this->createInviteResponse(...), $invites))
    ;
  }

  public function createInviteResponse(StudioInvite $invite): StudioInviteResponse
  {
    return (new StudioInviteResponse())
      ->setId($invite->getId())
      ->setCode($invite->getCode())
      ->setUrl($this->url_generator->generate(
        'studio_join_with_code',
        ['theme' => $this->parameter_bag->get('umbrellaTheme'), 'code' => $invite->getCode()],
        UrlGeneratorInterface::ABSOLUTE_URL
      ))
      ->setUses($invite->getUses())
      ->setMaxUses($invite->getMaxUses())
      ->setExpiresAt($invite->getExpiresAt())
      ->setCreatedAt($invite->getCreatedOn())
    ;
  }

  protected function createStudioLocation(Studio $studio): string
  {
    return $this->url_generator->generate(
      'studio_details',
      [
        'theme' => $this->parameter_bag->get('umbrellaTheme'),
        'id' => $studio->getId(),
      ],
      UrlGeneratorInterface::ABSOLUTE_URL
    );
  }
}
