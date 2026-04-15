<?php

declare(strict_types=1);

namespace App\Api;

use App\Api\Services\AuthenticationManager;
use App\Api\Services\Base\AbstractApiController;
use App\DB\Entity\Project\Project;
use App\DB\Entity\User\Comment\UserComment;
use App\DB\Entity\User\Notifications\CommentNotification;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\User\Comment\UserCommentRepository;
use App\Moderation\TextSanitizer;
use App\Project\ProjectManager;
use App\Translation\TranslationDelegate;
use App\Translation\TranslationResult;
use App\User\Notification\NotificationManager;
use App\User\UserAvatarService;
use Doctrine\ORM\EntityManagerInterface;
use OpenAPI\Server\Api\CommentsApiInterface;
use OpenAPI\Server\Model\CommentCreateRequest;
use OpenAPI\Server\Model\CommentListResponse;
use OpenAPI\Server\Model\CommentResponse;
use OpenAPI\Server\Model\CommentTranslationResponse;
use OpenAPI\Server\Model\CommentUserInfo;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CommentsApi extends AbstractApiController implements CommentsApiInterface
{
  use RateLimitTrait;

  private const int DEFAULT_LIMIT = 20;
  private const int MAX_LIMIT = 50;

  public function __construct(
    private readonly AuthenticationManager $authentication_manager,
    private readonly ProjectManager $project_manager,
    private readonly UserCommentRepository $comment_repository,
    private readonly EntityManagerInterface $entity_manager,
    private readonly TranslationDelegate $translation_delegate,
    private readonly NotificationManager $notification_manager,
    private readonly RequestStack $request_stack,
    private readonly AuthorizationCheckerInterface $authorization_checker,
    private readonly RateLimiterFactory $commentBurstLimiter,
    private readonly RateLimiterFactory $commentDailyLimiter,
    private readonly TextSanitizer $textSanitizer,
    private readonly UserAvatarService $user_avatar_service,
  ) {
  }

  #[\Override]
  public function projectsIdCommentsGet(string $id, string $accept_language, int $limit, ?string $cursor, int &$responseCode, array &$responseHeaders): array|object|null
  {
    $project = $this->project_manager->findProjectIfVisibleToCurrentUser($id);
    if (!$project instanceof Project) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $limit = $this->normalizeLimit($limit);
    $cursor_data = $this->decodeCursor($cursor);
    if (null === $cursor_data && null !== $cursor) {
      $responseCode = Response::HTTP_BAD_REQUEST;

      return null;
    }

    $page = $this->comment_repository->getProjectCommentsPageData(
      $project,
      $limit,
      $cursor_data['date'] ?? null,
      $cursor_data['id'] ?? null,
    );

    $responseCode = Response::HTTP_OK;
    $project_id = $project->getId();
    if (null === $project_id) {
      $responseCode = Response::HTTP_INTERNAL_SERVER_ERROR;

      return null;
    }

    return $this->createCommentListResponse($page['comments'], $page['has_more'], $project_id, false);
  }

  #[\Override]
  public function projectsIdCommentsPost(string $id, CommentCreateRequest $comment_create_request, string $accept_language, int &$responseCode, array &$responseHeaders): array|object|null
  {
    $user = $this->authentication_manager->getAuthenticatedUser();
    if (!$user instanceof User) {
      $responseCode = Response::HTTP_UNAUTHORIZED;

      return null;
    }

    if ($user->isMinor()) {
      $responseCode = Response::HTTP_FORBIDDEN;

      return null;
    }

    if (!$this->authorization_checker->isGranted('ROLE_ADMIN')
      && (null === $this->checkUserRateLimit($user, $this->commentBurstLimiter)
        || null === $this->checkUserRateLimit($user, $this->commentDailyLimiter))) {
      $responseCode = Response::HTTP_TOO_MANY_REQUESTS;

      return null;
    }

    $project = $this->project_manager->findProjectIfVisibleToCurrentUser($id);
    if (!$project instanceof Project) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $message = $this->textSanitizer->sanitize(trim((string) $comment_create_request->getMessage())) ?? '';
    if ('' === $message) {
      $responseCode = Response::HTTP_BAD_REQUEST;

      return null;
    }

    $parent_id = $comment_create_request->getParentId();
    $parent_comment = null;
    if (null !== $parent_id) {
      $parent_comment = $this->comment_repository->findOneBy(['id' => $parent_id]);
      if (!$parent_comment instanceof UserComment) {
        $responseCode = Response::HTTP_NOT_FOUND;

        return null;
      }

      if ($parent_comment->getProject()?->getId() !== $project->getId()) {
        $responseCode = Response::HTTP_BAD_REQUEST;

        return null;
      }

      if ($parent_comment->getAutoHidden() && !$this->canAccessHiddenComment($parent_comment)) {
        $responseCode = Response::HTTP_NOT_FOUND;

        return null;
      }
    }

    $comment = new UserComment();
    $comment->setUser($user);
    $comment->setUsername($user->getUsername() ?? '');
    $comment->setText($message);
    $comment->setProject($project);
    $comment->setUploadDate(new \DateTime('now', new \DateTimeZone('UTC')));
    $comment->setIsDeleted(false);
    if (null !== $parent_id) {
      $comment->setParentId($parent_id);
    }

    $this->entity_manager->persist($comment);
    $this->entity_manager->flush();
    $this->entity_manager->refresh($comment);

    $project_owner = $project->getUser();
    if ($project_owner instanceof User && $project_owner->getId() !== $user->getId()) {
      $notification = new CommentNotification($project_owner, $comment);
      $comment->setNotification($notification);
      $this->notification_manager->addNotification($notification);
      $this->entity_manager->persist($comment);
      $this->entity_manager->flush();
    }

    $comment_data = $this->buildCommentDataFromEntity($comment, 0);
    $project_id = $project->getId();
    if (null === $project_id) {
      $responseCode = Response::HTTP_INTERNAL_SERVER_ERROR;

      return null;
    }
    $response = $this->createCommentResponse($comment_data, $project_id, null !== $parent_id);
    $responseCode = Response::HTTP_CREATED;

    return $response;
  }

  #[\Override]
  public function commentsIdDelete(string $id, string $accept_language, int &$responseCode, array &$responseHeaders): void
  {
    if ('' === $id) {
      $responseCode = Response::HTTP_BAD_REQUEST;

      return;
    }

    $user = $this->authentication_manager->getAuthenticatedUser();
    if (!$user instanceof User) {
      $responseCode = Response::HTTP_UNAUTHORIZED;

      return;
    }

    $comment = $this->comment_repository->findOneBy(['id' => $id]);
    if (!$comment instanceof UserComment) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return;
    }

    if (true === $comment->getIsDeleted()) {
      $responseCode = Response::HTTP_BAD_REQUEST;

      return;
    }

    if ($comment->getUser()?->getId() !== $user->getId() && !$this->authorization_checker->isGranted('ROLE_ADMIN')) {
      $responseCode = Response::HTTP_FORBIDDEN;

      return;
    }

    $comment->setIsDeleted(true);
    $this->entity_manager->persist($comment);
    $this->entity_manager->flush();
    $responseCode = Response::HTTP_NO_CONTENT;
  }

  #[\Override]
  public function commentsIdRepliesGet(string $id, string $accept_language, int $limit, ?string $cursor, int &$responseCode, array &$responseHeaders): array|object|null
  {
    $comment = $this->comment_repository->findOneBy(['id' => $id]);
    if (!$comment instanceof UserComment) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    if ($comment->getAutoHidden() && !$this->canAccessHiddenComment($comment)) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $project = $comment->getProject();
    if (!$project instanceof Project || !$this->project_manager->findProjectIfVisibleToCurrentUser($project->getId()) instanceof Project) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $limit = $this->normalizeLimit($limit);
    $cursor_data = $this->decodeCursor($cursor);
    if (null === $cursor_data && null !== $cursor) {
      $responseCode = Response::HTTP_BAD_REQUEST;

      return null;
    }

    $page = $this->comment_repository->getCommentRepliesPageData(
      $id,
      $limit,
      $cursor_data['date'] ?? null,
      $cursor_data['id'] ?? null,
    );

    $responseCode = Response::HTTP_OK;
    $project_id = $project->getId();
    if (null === $project_id) {
      $responseCode = Response::HTTP_INTERNAL_SERVER_ERROR;

      return null;
    }

    return $this->createCommentListResponse($page['comments'], $page['has_more'], $project_id, true);
  }

  #[\Override]
  public function commentsIdTranslationGet(string $id, string $target_language, string $accept_language, ?string $source_language, int &$responseCode, array &$responseHeaders): array|object|null
  {
    $comment = $this->comment_repository->findOneBy(['id' => $id]);
    if (!$comment instanceof UserComment) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    if ($comment->getAutoHidden() && !$this->canAccessHiddenComment($comment)) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    if (true === $comment->getIsDeleted()) {
      $responseCode = Response::HTTP_BAD_REQUEST;

      return null;
    }

    if ($source_language === $target_language) {
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;

      return null;
    }

    $comment_text = $comment->getText();
    if (null === $comment_text) {
      $responseCode = Response::HTTP_BAD_REQUEST;

      return null;
    }

    $etag_value = md5($comment_text).$target_language;
    $etag_header = '"'.$etag_value.'"';
    $responseHeaders['ETag'] = $etag_header;
    $request = $this->request_stack->getCurrentRequest();
    $if_none_match = $request?->headers->get('If-None-Match');
    if (null !== $if_none_match) {
      $candidates = array_map(trim(...), explode(',', $if_none_match));
      foreach ($candidates as $candidate) {
        if (trim($candidate, '"') === $etag_value) {
          $responseCode = Response::HTTP_NOT_MODIFIED;

          return null;
        }
      }
    }

    try {
      $translation_result = $this->translation_delegate->translate($comment_text, $source_language, $target_language);
    } catch (\InvalidArgumentException) {
      $responseCode = Response::HTTP_BAD_REQUEST;

      return null;
    }

    if (!$translation_result instanceof TranslationResult) {
      $responseCode = Response::HTTP_SERVICE_UNAVAILABLE;

      return null;
    }

    $response = new CommentTranslationResponse();
    $response->setId($comment->getId() ?? '');
    $response->setSourceLanguage($source_language ?? $translation_result->detected_source_language);
    $response->setTargetLanguage($target_language);
    $response->setTranslation($translation_result->translation);
    $response->setProvider($translation_result->provider);
    $response->setCache(null !== $translation_result->cache ? true : null);

    $responseCode = Response::HTTP_OK;

    return $response;
  }

  private function createCommentListResponse(array $comments, bool $has_more, string $project_id, bool $are_replies): CommentListResponse
  {
    $response = new CommentListResponse();
    $data = [];

    foreach ($comments as $comment_data) {
      $data[] = $this->createCommentResponse($comment_data, $project_id, $are_replies);
    }

    $next_cursor = null;
    if ($has_more && [] !== $comments) {
      $last = array_last($comments);
      $next_cursor = $this->encodeCursor($last['upload_date'], (string) $last['id']);
    }

    $response->setData($data);
    $response->setHasMore($has_more);
    $response->setNextCursor($next_cursor);

    return $response;
  }

  private function createCommentResponse(array $comment_data, string $project_id, bool $are_replies): CommentResponse
  {
    $response = new CommentResponse();
    $response->setId((string) $comment_data['id']);
    $response->setProjectId($project_id);
    $parent_id = $comment_data['parent_id'] ?? null;
    $response->setParentId(null !== $parent_id ? (string) $parent_id : null);
    $response->setMessage(true === $comment_data['is_deleted'] ? null : (string) $comment_data['text']);
    $response->setCreatedAt($comment_data['upload_date']);
    $response->setReplyCount((int) ($comment_data['number_of_replies'] ?? 0));
    $response->setIsDeleted((bool) $comment_data['is_deleted']);
    $response->setIsReported((bool) $comment_data['is_reported']);

    $user_info = new CommentUserInfo();
    $user_info->setId((string) $comment_data['user_id']);
    $user_info->setUsername((string) $comment_data['username']);
    $user_info->setAvatar($this->user_avatar_service->getVariants($comment_data['user'] ?? null));
    $user_info->setApproved((bool) ($comment_data['user_approved'] ?? false));
    $response->setUser($user_info);

    return $response;
  }

  private function buildCommentDataFromEntity(UserComment $comment, int $reply_count): array
  {
    return [
      'id' => $comment->getId(),
      'username' => $comment->getUsername(),
      'text' => $comment->getText(),
      'is_deleted' => $comment->getIsDeleted(),
      'is_reported' => $comment->getAutoHidden(),
      'upload_date' => $comment->getUploadDate(),
      'user_id' => $comment->getUser()?->getId(),
      'user' => $comment->getUser(),
      'user_approved' => $comment->getUser()?->isApproved() ?? false,
      'parent_id' => $comment->getParentId(),
      'number_of_replies' => $reply_count,
    ];
  }

  private function canAccessHiddenComment(UserComment $comment): bool
  {
    if (!$comment->getAutoHidden()) {
      return true;
    }

    if ($this->authorization_checker->isGranted('ROLE_ADMIN')) {
      return true;
    }

    $current_user = $this->authentication_manager->getAuthenticatedUser();
    if (!$current_user instanceof User) {
      return false;
    }

    return $comment->getUser()?->getId() === $current_user->getId();
  }

  private function normalizeLimit(int $limit): int
  {
    $limit = $limit > 0 ? $limit : self::DEFAULT_LIMIT;

    return min($limit, self::MAX_LIMIT);
  }

  private function decodeCursor(?string $cursor): ?array
  {
    if (null === $cursor || '' === $cursor) {
      return null;
    }

    $decoded = base64_decode($cursor, true);
    if (false === $decoded) {
      return null;
    }

    $parts = explode('|', $decoded);
    if (2 !== count($parts)) {
      return null;
    }

    [$date_string, $id_string] = $parts;
    $date = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s.u\Z', $date_string, new \DateTimeZone('UTC'));
    if (false === $date) {
      $date = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s\Z', $date_string, new \DateTimeZone('UTC'));
    }

    if (false === $date) {
      return null;
    }

    if ('' === $id_string) {
      return null;
    }

    return [
      'date' => $date,
      'id' => $id_string,
    ];
  }

  private function encodeCursor(\DateTimeInterface $date, string $id): string
  {
    $utc_date = \DateTimeImmutable::createFromInterface($date)->setTimezone(new \DateTimeZone('UTC'));
    $value = $utc_date->format('Y-m-d\TH:i:s.u\Z').'|'.$id;

    return base64_encode($value);
  }
}
