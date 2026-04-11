<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api;

use App\Api\CommentsApi;
use App\Api\Services\AuthenticationManager;
use App\DB\Entity\Project\Program;
use App\DB\Entity\User\Comment\UserComment;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\User\Comment\UserCommentRepository;
use App\Moderation\TextSanitizer;
use App\Project\ProjectManager;
use App\Translation\TranslationDelegate;
use App\Translation\TranslationResult;
use App\User\Notification\NotificationManager;
use Doctrine\ORM\EntityManagerInterface;
use OpenAPI\Server\Model\CommentCreateRequest;
use OpenAPI\Server\Model\CommentTranslationResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @internal
 */
#[CoversClass(CommentsApi::class)]
final class CommentsApiTest extends TestCase
{
  /**
   * @throws Exception
   */
  private function buildApi(
    ?AuthenticationManager $authentication_manager = null,
    ?ProjectManager $project_manager = null,
    ?UserCommentRepository $comment_repository = null,
    ?EntityManagerInterface $entity_manager = null,
    ?TranslationDelegate $translation_delegate = null,
    ?NotificationManager $notification_manager = null,
    ?RequestStack $request_stack = null,
    ?AuthorizationCheckerInterface $authorization_checker = null,
    ?RateLimiterFactory $comment_burst_limiter = null,
    ?RateLimiterFactory $comment_daily_limiter = null,
    ?TextSanitizer $text_sanitizer = null,
  ): CommentsApi {
    return new CommentsApi(
      $authentication_manager ?? $this->createStub(AuthenticationManager::class),
      $project_manager ?? $this->createStub(ProjectManager::class),
      $comment_repository ?? $this->createStub(UserCommentRepository::class),
      $entity_manager ?? $this->createStub(EntityManagerInterface::class),
      $translation_delegate ?? $this->createStub(TranslationDelegate::class),
      $notification_manager ?? $this->createStub(NotificationManager::class),
      $request_stack ?? new RequestStack(),
      $authorization_checker ?? $this->createStub(AuthorizationCheckerInterface::class),
      $comment_burst_limiter ?? $this->createNoLimitRateLimiterFactory('phpunit_comments_burst'),
      $comment_daily_limiter ?? $this->createNoLimitRateLimiterFactory('phpunit_comments_daily'),
      $text_sanitizer ?? $this->createPassthroughTextSanitizer(),
    );
  }

  private function createNoLimitRateLimiterFactory(string $id): RateLimiterFactory
  {
    return new RateLimiterFactory(
      [
        'id' => $id,
        'policy' => 'no_limit',
      ],
      new InMemoryStorage(),
    );
  }

  private function createPassthroughTextSanitizer(): TextSanitizer
  {
    $stub = $this->createStub(TextSanitizer::class);
    $stub->method('sanitize')->willReturnArgument(0);
    $stub->method('sanitizeWithLocale')->willReturnArgument(0);

    return $stub;
  }

  // ==================== projectIdCommentsGet ====================

  #[Group('unit')]
  public function testProjectIdCommentsGetReturnsNotFoundWhenProjectInvisible(): void
  {
    $project_manager = $this->createStub(ProjectManager::class);
    $project_manager->method('findProjectIfVisibleToCurrentUser')->willReturn(null);

    $api = $this->buildApi(project_manager: $project_manager);

    $response_code = 200;
    $response_headers = [];

    $result = $api->projectIdCommentsGet('1', 'en', 20, null, $response_code, $response_headers);

    $this->assertSame(Response::HTTP_NOT_FOUND, $response_code);
    $this->assertNull($result);
  }

  #[Group('unit')]
  public function testProjectIdCommentsGetReturnsBadRequestOnInvalidCursor(): void
  {
    $project_manager = $this->createStub(ProjectManager::class);
    $project_manager->method('findProjectIfVisibleToCurrentUser')->willReturn($this->createStub(Program::class));

    $api = $this->buildApi(project_manager: $project_manager);

    $response_code = 200;
    $response_headers = [];

    $result = $api->projectIdCommentsGet('1', 'en', 20, 'not-valid-base64!!', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_BAD_REQUEST, $response_code);
    $this->assertNull($result);
  }

  // ==================== projectIdCommentsPost ====================

  #[Group('unit')]
  public function testProjectIdCommentsPostRequiresAuthentication(): void
  {
    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn(null);

    $api = $this->buildApi(authentication_manager: $authentication_manager);

    $response_code = 200;
    $response_headers = [];
    $request = new CommentCreateRequest();
    $request->setMessage('hello');

    $result = $api->projectIdCommentsPost('1', $request, 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_UNAUTHORIZED, $response_code);
    $this->assertNull($result);
  }

  #[Group('unit')]
  public function testProjectIdCommentsPostReturnsNotFoundWhenProjectInvisible(): void
  {
    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($this->createStub(User::class));

    $project_manager = $this->createStub(ProjectManager::class);
    $project_manager->method('findProjectIfVisibleToCurrentUser')->willReturn(null);

    $api = $this->buildApi(
      authentication_manager: $authentication_manager,
      project_manager: $project_manager,
    );

    $response_code = 200;
    $response_headers = [];
    $request = new CommentCreateRequest();
    $request->setMessage('hello');

    $result = $api->projectIdCommentsPost('1', $request, 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_NOT_FOUND, $response_code);
    $this->assertNull($result);
  }

  #[Group('unit')]
  public function testProjectIdCommentsPostReturnsBadRequestOnEmptyMessage(): void
  {
    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($this->createStub(User::class));

    $project_manager = $this->createStub(ProjectManager::class);
    $project_manager->method('findProjectIfVisibleToCurrentUser')->willReturn($this->createStub(Program::class));

    $api = $this->buildApi(
      authentication_manager: $authentication_manager,
      project_manager: $project_manager,
    );

    $response_code = 200;
    $response_headers = [];
    $request = new CommentCreateRequest();
    $request->setMessage('   ');

    $result = $api->projectIdCommentsPost('1', $request, 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_BAD_REQUEST, $response_code);
    $this->assertNull($result);
  }

  #[Group('unit')]
  public function testProjectIdCommentsPostReturnsNotFoundWhenParentCommentMissing(): void
  {
    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($this->createStub(User::class));

    $project_manager = $this->createStub(ProjectManager::class);
    $project_manager->method('findProjectIfVisibleToCurrentUser')->willReturn($this->createStub(Program::class));

    $comment_repository = $this->createStub(UserCommentRepository::class);
    $comment_repository->method('findOneBy')->willReturn(null);

    $api = $this->buildApi(
      authentication_manager: $authentication_manager,
      project_manager: $project_manager,
      comment_repository: $comment_repository,
    );

    $response_code = 200;
    $response_headers = [];
    $request = new CommentCreateRequest();
    $request->setMessage('reply text');
    $request->setParentId(999);

    $result = $api->projectIdCommentsPost('1', $request, 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_NOT_FOUND, $response_code);
    $this->assertNull($result);
  }

  #[Group('unit')]
  public function testProjectIdCommentsPostReturnsBadRequestWhenParentBelongsToDifferentProject(): void
  {
    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($this->createStub(User::class));

    $project = $this->createStub(Program::class);
    $project->method('getId')->willReturn('project-1');

    $project_manager = $this->createStub(ProjectManager::class);
    $project_manager->method('findProjectIfVisibleToCurrentUser')->willReturn($project);

    $other_project = $this->createStub(Program::class);
    $other_project->method('getId')->willReturn('project-2');

    $parent_comment = $this->createStub(UserComment::class);
    $parent_comment->method('getProgram')->willReturn($other_project);

    $comment_repository = $this->createStub(UserCommentRepository::class);
    $comment_repository->method('findOneBy')->willReturn($parent_comment);

    $api = $this->buildApi(
      authentication_manager: $authentication_manager,
      project_manager: $project_manager,
      comment_repository: $comment_repository,
    );

    $response_code = 200;
    $response_headers = [];
    $request = new CommentCreateRequest();
    $request->setMessage('reply text');
    $request->setParentId(42);

    $result = $api->projectIdCommentsPost('project-1', $request, 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_BAD_REQUEST, $response_code);
    $this->assertNull($result);
  }

  // ==================== commentsIdDelete ====================

  #[Group('unit')]
  public function testCommentsIdDeleteRejectsInvalidId(): void
  {
    $api = $this->buildApi();

    $response_code = 200;
    $response_headers = [];

    $api->commentsIdDelete(0, 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_BAD_REQUEST, $response_code);
  }

  #[Group('unit')]
  public function testCommentsIdDeleteRequiresAuthentication(): void
  {
    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn(null);

    $api = $this->buildApi(authentication_manager: $authentication_manager);

    $response_code = 200;
    $response_headers = [];

    $api->commentsIdDelete(1, 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_UNAUTHORIZED, $response_code);
  }

  #[Group('unit')]
  public function testCommentsIdDeleteReturnsNotFoundForMissingComment(): void
  {
    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($this->createStub(User::class));

    $comment_repository = $this->createStub(UserCommentRepository::class);
    $comment_repository->method('findOneBy')->willReturn(null);

    $api = $this->buildApi(
      authentication_manager: $authentication_manager,
      comment_repository: $comment_repository,
    );

    $response_code = 200;
    $response_headers = [];

    $api->commentsIdDelete(1, 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_NOT_FOUND, $response_code);
  }

  #[Group('unit')]
  public function testCommentsIdDeleteReturnsBadRequestWhenAlreadyDeleted(): void
  {
    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($this->createStub(User::class));

    $comment = $this->createStub(UserComment::class);
    $comment->method('getIsDeleted')->willReturn(true);

    $comment_repository = $this->createStub(UserCommentRepository::class);
    $comment_repository->method('findOneBy')->willReturn($comment);

    $api = $this->buildApi(
      authentication_manager: $authentication_manager,
      comment_repository: $comment_repository,
    );

    $response_code = 200;
    $response_headers = [];

    $api->commentsIdDelete(1, 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_BAD_REQUEST, $response_code);
  }

  #[Group('unit')]
  public function testCommentsIdDeleteReturnsNoContentWhenOwnerDeletes(): void
  {
    $user = $this->createStub(User::class);
    $user->method('getId')->willReturn('user-1');

    $authentication_manager = $this->createStub(AuthenticationManager::class);
    $authentication_manager->method('getAuthenticatedUser')->willReturn($user);

    $comment_user = $this->createStub(User::class);
    $comment_user->method('getId')->willReturn('user-1');

    $comment = $this->createStub(UserComment::class);
    $comment->method('getIsDeleted')->willReturn(false);
    $comment->method('getUser')->willReturn($comment_user);

    $comment_repository = $this->createStub(UserCommentRepository::class);
    $comment_repository->method('findOneBy')->willReturn($comment);

    $api = $this->buildApi(
      authentication_manager: $authentication_manager,
      comment_repository: $comment_repository,
    );

    $response_code = 200;
    $response_headers = [];

    $api->commentsIdDelete(1, 'en', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_NO_CONTENT, $response_code);
  }

  // ==================== commentsIdRepliesGet ====================

  #[Group('unit')]
  public function testCommentsIdRepliesGetReturnsNotFoundForMissingComment(): void
  {
    $comment_repository = $this->createStub(UserCommentRepository::class);
    $comment_repository->method('findOneBy')->willReturn(null);

    $api = $this->buildApi(comment_repository: $comment_repository);

    $response_code = 200;
    $response_headers = [];

    $result = $api->commentsIdRepliesGet(1, 'en', 20, null, $response_code, $response_headers);

    $this->assertSame(Response::HTTP_NOT_FOUND, $response_code);
    $this->assertNull($result);
  }

  #[Group('unit')]
  public function testCommentsIdRepliesGetReturnsNotFoundWhenProjectNotVisible(): void
  {
    $project = $this->createStub(Program::class);
    $project->method('getId')->willReturn('project-1');

    $comment = $this->createStub(UserComment::class);
    $comment->method('getProgram')->willReturn($project);

    $comment_repository = $this->createStub(UserCommentRepository::class);
    $comment_repository->method('findOneBy')->willReturn($comment);

    $project_manager = $this->createStub(ProjectManager::class);
    $project_manager->method('findProjectIfVisibleToCurrentUser')->willReturn(null);

    $api = $this->buildApi(
      project_manager: $project_manager,
      comment_repository: $comment_repository,
    );

    $response_code = 200;
    $response_headers = [];

    $result = $api->commentsIdRepliesGet(1, 'en', 20, null, $response_code, $response_headers);

    $this->assertSame(Response::HTTP_NOT_FOUND, $response_code);
    $this->assertNull($result);
  }

  #[Group('unit')]
  public function testCommentsIdRepliesGetReturnsBadRequestOnInvalidCursor(): void
  {
    $project = $this->createStub(Program::class);
    $project->method('getId')->willReturn('project-1');

    $comment = $this->createStub(UserComment::class);
    $comment->method('getProgram')->willReturn($project);

    $comment_repository = $this->createStub(UserCommentRepository::class);
    $comment_repository->method('findOneBy')->willReturn($comment);

    $project_manager = $this->createStub(ProjectManager::class);
    $project_manager->method('findProjectIfVisibleToCurrentUser')->willReturn($project);

    $api = $this->buildApi(
      project_manager: $project_manager,
      comment_repository: $comment_repository,
    );

    $response_code = 200;
    $response_headers = [];

    $result = $api->commentsIdRepliesGet(1, 'en', 20, 'not-valid-base64!!', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_BAD_REQUEST, $response_code);
    $this->assertNull($result);
  }

  // ==================== commentsIdTranslationGet ====================

  #[Group('unit')]
  public function testCommentsIdTranslationGetReturnsNotModifiedOnMatchingEtag(): void
  {
    $comment = $this->createStub(UserComment::class);
    $comment->method('getIsDeleted')->willReturn(false);
    $comment->method('getText')->willReturn('test');

    $comment_repository = $this->createStub(UserCommentRepository::class);
    $comment_repository->method('findOneBy')->willReturn($comment);

    $etag = '"'.md5('test').'fr"';
    $request = Request::create('/api/comments/1/translation', 'GET', [], [], [], [
      'HTTP_IF_NONE_MATCH' => $etag,
    ]);
    $request_stack = new RequestStack();
    $request_stack->push($request);

    $api = $this->buildApi(
      comment_repository: $comment_repository,
      request_stack: $request_stack,
    );

    $response_code = 200;
    $response_headers = [];

    $result = $api->commentsIdTranslationGet(1, 'fr', 'en', null, $response_code, $response_headers);

    $this->assertSame(Response::HTTP_NOT_MODIFIED, $response_code);
    $this->assertNull($result);
    $this->assertSame($etag, $response_headers['ETag']);
  }

  #[Group('unit')]
  public function testCommentsIdTranslationGetReturnsNotFoundForMissingComment(): void
  {
    $comment_repository = $this->createStub(UserCommentRepository::class);
    $comment_repository->method('findOneBy')->willReturn(null);

    $api = $this->buildApi(comment_repository: $comment_repository);

    $response_code = 200;
    $response_headers = [];

    $result = $api->commentsIdTranslationGet(1, 'fr', 'en', null, $response_code, $response_headers);

    $this->assertSame(Response::HTTP_NOT_FOUND, $response_code);
    $this->assertNull($result);
  }

  #[Group('unit')]
  public function testCommentsIdTranslationGetReturnsBadRequestForDeletedComment(): void
  {
    $comment = $this->createStub(UserComment::class);
    $comment->method('getIsDeleted')->willReturn(true);
    $comment->method('getText')->willReturn('text');

    $comment_repository = $this->createStub(UserCommentRepository::class);
    $comment_repository->method('findOneBy')->willReturn($comment);

    $api = $this->buildApi(comment_repository: $comment_repository);

    $response_code = 200;
    $response_headers = [];

    $result = $api->commentsIdTranslationGet(1, 'fr', 'en', null, $response_code, $response_headers);

    $this->assertSame(Response::HTTP_BAD_REQUEST, $response_code);
    $this->assertNull($result);
  }

  #[Group('unit')]
  public function testCommentsIdTranslationGetReturnsUnprocessableWhenSourceEqualsTarget(): void
  {
    $comment = $this->createStub(UserComment::class);
    $comment->method('getIsDeleted')->willReturn(false);
    $comment->method('getText')->willReturn('text');

    $comment_repository = $this->createStub(UserCommentRepository::class);
    $comment_repository->method('findOneBy')->willReturn($comment);

    $api = $this->buildApi(comment_repository: $comment_repository);

    $response_code = 200;
    $response_headers = [];

    $result = $api->commentsIdTranslationGet(1, 'de', 'en', 'de', $response_code, $response_headers);

    $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response_code);
    $this->assertNull($result);
  }

  #[Group('unit')]
  public function testCommentsIdTranslationGetReturnsBadRequestOnInvalidLanguage(): void
  {
    $comment = $this->createStub(UserComment::class);
    $comment->method('getIsDeleted')->willReturn(false);
    $comment->method('getText')->willReturn('text');

    $comment_repository = $this->createStub(UserCommentRepository::class);
    $comment_repository->method('findOneBy')->willReturn($comment);

    $translation_delegate = $this->createStub(TranslationDelegate::class);
    $translation_delegate->method('translate')->willThrowException(new \InvalidArgumentException('invalid language'));

    $api = $this->buildApi(
      comment_repository: $comment_repository,
      translation_delegate: $translation_delegate,
    );

    $response_code = 200;
    $response_headers = [];

    $result = $api->commentsIdTranslationGet(1, 'xx', 'en', null, $response_code, $response_headers);

    $this->assertSame(Response::HTTP_BAD_REQUEST, $response_code);
    $this->assertNull($result);
  }

  #[Group('unit')]
  public function testCommentsIdTranslationGetReturnsServiceUnavailableWhenTranslationFails(): void
  {
    $comment = $this->createStub(UserComment::class);
    $comment->method('getIsDeleted')->willReturn(false);
    $comment->method('getText')->willReturn('text');

    $comment_repository = $this->createStub(UserCommentRepository::class);
    $comment_repository->method('findOneBy')->willReturn($comment);

    $translation_delegate = $this->createStub(TranslationDelegate::class);
    $translation_delegate->method('translate')->willReturn(null);

    $api = $this->buildApi(
      comment_repository: $comment_repository,
      translation_delegate: $translation_delegate,
    );

    $response_code = 200;
    $response_headers = [];

    $result = $api->commentsIdTranslationGet(1, 'fr', 'en', null, $response_code, $response_headers);

    $this->assertSame(Response::HTTP_SERVICE_UNAVAILABLE, $response_code);
    $this->assertNull($result);
  }

  #[Group('unit')]
  public function testCommentsIdTranslationGetReturnsTranslationResponse(): void
  {
    $comment = $this->createStub(UserComment::class);
    $comment->method('getId')->willReturn(42);
    $comment->method('getIsDeleted')->willReturn(false);
    $comment->method('getText')->willReturn('hello');

    $comment_repository = $this->createStub(UserCommentRepository::class);
    $comment_repository->method('findOneBy')->willReturn($comment);

    $translation_result = new TranslationResult();
    $translation_result->translation = 'bonjour';
    $translation_result->detected_source_language = 'en';
    $translation_result->provider = 'test-provider';

    $translation_delegate = $this->createStub(TranslationDelegate::class);
    $translation_delegate->method('translate')->willReturn($translation_result);

    $api = $this->buildApi(
      comment_repository: $comment_repository,
      translation_delegate: $translation_delegate,
    );

    $response_code = 200;
    $response_headers = [];

    $result = $api->commentsIdTranslationGet(42, 'fr', 'en', null, $response_code, $response_headers);

    $this->assertSame(Response::HTTP_OK, $response_code);
    $this->assertInstanceOf(CommentTranslationResponse::class, $result);
    $this->assertSame('bonjour', $result->getTranslation());
    $this->assertSame('fr', $result->getTargetLanguage());
    $this->assertSame('en', $result->getSourceLanguage());
    $this->assertSame('test-provider', $result->getProvider());
  }
}
