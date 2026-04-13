<?php

declare(strict_types=1);

namespace App\Api\Services\Studio;

use App\Api\Services\Base\AbstractResponseManager;
use App\DB\Entity\Studio\Studio;
use App\DB\Entity\Studio\StudioActivity;
use App\DB\Entity\Studio\StudioJoinRequest;
use App\DB\Entity\Studio\StudioProgram;
use App\DB\Entity\Studio\StudioUser;
use App\DB\Entity\User\Comment\UserComment;
use App\DB\Entity\User\User;
use App\Project\ProjectManager;
use App\Studio\StudioManager;
use OpenAPI\Server\Model\StudioActivityListResponse;
use OpenAPI\Server\Model\StudioActivityResponse;
use OpenAPI\Server\Model\StudioCommentListResponse;
use OpenAPI\Server\Model\StudioCommentResponse;
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
use Symfony\Component\HttpFoundation\RequestStack;
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
    private readonly RequestStack $request_stack,
    private readonly StudioManager $studio_manager,
    private readonly ProjectManager $project_manager,
  ) {
    parent::__construct($translator, $serializer, $cache);
  }

  public function createStudioResponse(Studio $studio): StudioResponse
  {
    return (new StudioResponse())
      ->setId($studio->getId())
      ->setName($studio->getName())
      ->setDescription($studio->getDescription())
      ->setIsPublic($studio->isIsPublic())
      ->setEnableComments($studio->isAllowComments())
      ->setImagePath($this->generateImagePath($studio))
      ->setMembersCount($this->studio_manager->countStudioUsers($studio))
      ->setProjectsCount($this->studio_manager->countStudioProjects($studio))
      ->setActivitiesCount($this->studio_manager->countStudioActivities($studio))
      ->setCommentsCount($this->studio_manager->countStudioComments($studio))
    ;
  }

  public function createStudioResponseWithUserContext(Studio $studio, ?User $user): StudioResponse
  {
    $response = $this->createStudioResponse($studio);

    if ($user instanceof User) {
      $studioUser = $this->studio_manager->findStudioUser($user, $studio);
      $response->setIsMember($studioUser instanceof StudioUser);

      $userRole = $this->studio_manager->getStudioUserRole($user, $studio);
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
    $data = [];
    foreach ($studios as $studio) {
      $data[] = $this->createStudioResponseWithUserContext($studio, $user);
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
      ->setAvatar($user->getAvatar())
      ->setRole($member->getRole())
      ->setStatus($member->getStatus())
      ->setJoinedAt($member->getCreatedOn())
      ->setStudioProjectCount($this->studio_manager->countStudioUserProjects($member->getStudio(), $user))
    ;
  }

  /**
   * @param StudioProgram[] $projects
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

  public function createProjectResponse(StudioProgram $studioProject): StudioProjectResponse
  {
    $program = $studioProject->getProgram();
    $programId = $program->getId();
    $user = $studioProject->getUser();

    return (new StudioProjectResponse())
      ->setId($programId)
      ->setName($program->getName())
      ->setAddedBy($user->getUsername())
      ->setAddedAt($studioProject->getCreatedOn())
      ->setScreenshotSmall(null !== $programId ? $this->project_manager->getScreenshotSmall($programId) : null)
      ->setAuthor($program->getUser()?->getUsername())
      ->setAuthorId($program->getUser()?->getId())
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
      ->setUserAvatar($user?->getAvatar())
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
      $data[] = (new StudioUserProjectResponse())
        ->setId($project['id'])
        ->setName($project['name'])
        ->setInStudio($project['in_studio'])
        ->setScreenshotSmall($project['screenshot_small'])
      ;
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
      ->setAvatar($user?->getAvatar())
      ->setStatus($joinRequest->getStatus())
    ;
  }

  public function addStudioLocationToHeaders(array &$responseHeaders, Studio $studio): void
  {
    $responseHeaders['Location'] = $this->createStudioLocation($studio);
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

  protected function generateImagePath(Studio $studio): string
  {
    $assetPath = $studio->getCoverAssetPath();
    if (in_array($assetPath, [null, '', '0'], true)) {
      return '';
    }

    $baseUrl = $this->request_stack->getCurrentRequest()?->getSchemeAndHttpHost() ?? '';

    return $baseUrl.'/'.$assetPath;
  }
}
