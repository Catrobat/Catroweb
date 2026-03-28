<?php

declare(strict_types=1);

namespace App\Api\Services\Studio;

use App\Api\Services\Base\AbstractResponseManager;
use App\DB\Entity\Studio\Studio;
use App\DB\Entity\Studio\StudioProgram;
use App\DB\Entity\Studio\StudioUser;
use App\DB\Entity\User\Comment\UserComment;
use App\Studio\StudioManager;
use OpenAPI\Server\Model\StudioCommentListResponse;
use OpenAPI\Server\Model\StudioCommentResponse;
use OpenAPI\Server\Model\StudioListResponse;
use OpenAPI\Server\Model\StudioMemberListResponse;
use OpenAPI\Server\Model\StudioMemberResponse;
use OpenAPI\Server\Model\StudioProjectListResponse;
use OpenAPI\Server\Model\StudioProjectResponse;
use OpenAPI\Server\Model\StudioResponse;
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
    ;
  }

  /**
   * @param Studio[] $studios
   */
  public function createStudioListResponse(array $studios, bool $has_more): StudioListResponse
  {
    $data = [];
    foreach ($studios as $studio) {
      $data[] = $this->createStudioResponse($studio);
    }

    return (new StudioListResponse())
      ->setData($data)
      ->setHasMore($has_more)
      ->setNextCursor(null)
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

    return (new StudioProjectResponse())
      ->setId($program->getId())
      ->setName($program->getName())
      ->setAddedBy($studioProject->getUser()->getUsername())
      ->setAddedAt($studioProject->getCreatedOn())
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
      ->setParentId($comment->getParentId() ?: null)
      ->setReplyCount($this->studio_manager->countCommentReplies($comment->getId() ?? 0))
      ->setCreatedAt($comment->getUploadDate())
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
