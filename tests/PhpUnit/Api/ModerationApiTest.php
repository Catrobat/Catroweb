<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api;

use App\Api\ModerationApi;
use App\Api\Services\AuthenticationManager;
use App\Api\Services\Moderation\ModerationApiFacade;
use App\Api\Services\Moderation\ModerationApiLoader;
use App\Api\Services\Moderation\ModerationApiProcessor;
use App\Api\Services\Moderation\ModerationRequestValidator;
use App\Api\Services\Moderation\ModerationResponseManager;
use App\DB\Entity\Moderation\ContentAppeal;
use App\DB\Entity\Moderation\ContentReport;
use App\DB\Entity\User\User;
use App\Moderation\AppealException;
use App\Moderation\ReportException;
use OpenAPI\Server\Model\ContentAppealRequest;
use OpenAPI\Server\Model\ContentReportRequest;
use OpenAPI\Server\Model\ResolveAppealRequest;
use OpenAPI\Server\Model\ResolveReportRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @internal
 */
#[CoversClass(ModerationApi::class)]
final class ModerationApiTest extends TestCase
{
  private function createNoLimitFactory(): RateLimiterFactory
  {
    return new RateLimiterFactory(
      ['id' => 'test', 'policy' => 'no_limit'],
      new InMemoryStorage(),
    );
  }

  /**
   * Creates a rate limiter that rejects on the second consume (first succeeds, second rejected).
   * Use `sliding_window` with limit=1 so the first consume passes and the second is rejected.
   */
  private function createExhaustedLimiterFactory(): RateLimiterFactory
  {
    $factory = new RateLimiterFactory(
      ['id' => 'exhausted', 'policy' => 'sliding_window', 'limit' => 1, 'interval' => '1 hour'],
      new InMemoryStorage(),
    );

    // Pre-consume once so the next consume will be rejected
    $factory->create('user-1')->consume();

    return $factory;
  }

  /**
   * @throws Exception
   */
  private function buildFacade(
    ?User $user = null,
    ?ModerationApiLoader $loader = null,
    ?ModerationApiProcessor $processor = null,
    ?ModerationResponseManager $response_manager = null,
    ?ModerationRequestValidator $validator = null,
  ): Stub&ModerationApiFacade {
    $auth_manager = $this->createStub(AuthenticationManager::class);
    $auth_manager->method('getAuthenticatedUser')->willReturn($user);

    $facade = $this->createStub(ModerationApiFacade::class);
    $facade->method('getAuthenticationManager')->willReturn($auth_manager);
    $facade->method('getLoader')->willReturn($loader ?? $this->createStub(ModerationApiLoader::class));
    $facade->method('getProcessor')->willReturn($processor ?? $this->createStub(ModerationApiProcessor::class));
    $facade->method('getResponseManager')->willReturn($response_manager ?? $this->createStub(ModerationResponseManager::class));
    $facade->method('getRequestValidator')->willReturn($validator ?? $this->createStub(ModerationRequestValidator::class));

    return $facade;
  }

  /**
   * @throws Exception
   */
  private function buildApi(
    ?ModerationApiFacade $facade = null,
    ?RateLimiterFactory $appeal_daily_limiter = null,
    ?RateLimiterFactory $moderation_admin_burst_limiter = null,
    bool $is_admin = false,
  ): ModerationApi {
    $api = new ModerationApi(
      $facade ?? $this->buildFacade(),
      $appeal_daily_limiter ?? $this->createNoLimitFactory(),
      $moderation_admin_burst_limiter ?? $this->createNoLimitFactory(),
    );

    $auth_checker = $this->createStub(AuthorizationCheckerInterface::class);
    $auth_checker->method('isGranted')->willReturn($is_admin);

    $container = new Container();
    $container->set('security.authorization_checker', $auth_checker);
    $api->setContainer($container);

    return $api;
  }

  private function createUserStub(string $id = 'user-1', bool $is_minor = false): Stub&User
  {
    $user = $this->createStub(User::class);
    $user->method('getId')->willReturn($id);
    $user->method('isMinor')->willReturn($is_minor);

    return $user;
  }

  // ==================== Report endpoints (handleReport) ====================

  #[Group('unit')]
  public function testProjectReportUnauthorized(): void
  {
    $api = $this->buildApi();

    $code = 200;
    $headers = [];
    $result = $api->projectsIdReportPost('proj-1', new ContentReportRequest(), $code, $headers);

    $this->assertSame(Response::HTTP_UNAUTHORIZED, $code);
    $this->assertNull($result);
  }

  #[Group('unit')]
  public function testProjectReportForbiddenForMinor(): void
  {
    $user = $this->createUserStub(is_minor: true);
    $facade = $this->buildFacade(user: $user);
    $api = $this->buildApi(facade: $facade);

    $code = 200;
    $headers = [];
    $result = $api->projectsIdReportPost('proj-1', new ContentReportRequest(), $code, $headers);

    $this->assertSame(Response::HTTP_FORBIDDEN, $code);
    $this->assertNull($result);
  }

  #[Group('unit')]
  public function testProjectReportSuccess(): void
  {
    $user = $this->createUserStub();

    $report = $this->createStub(ContentReport::class);
    $report->method('getId')->willReturn('report-123');

    $processor = $this->createStub(ModerationApiProcessor::class);
    $processor->method('processReport')->willReturn($report);

    $facade = $this->buildFacade(user: $user, processor: $processor);
    $api = $this->buildApi(facade: $facade);

    $code = 200;
    $headers = [];
    $result = $api->projectsIdReportPost('proj-1', new ContentReportRequest(), $code, $headers);

    $this->assertSame(Response::HTTP_CREATED, $code);
    $this->assertIsArray($result);
    $this->assertSame('report-123', $result['report_id']);
  }

  #[Group('unit')]
  public function testCommentReportSuccess(): void
  {
    $user = $this->createUserStub();

    $report = $this->createStub(ContentReport::class);
    $report->method('getId')->willReturn('report-456');

    $processor = $this->createStub(ModerationApiProcessor::class);
    $processor->method('processReport')->willReturn($report);

    $facade = $this->buildFacade(user: $user, processor: $processor);
    $api = $this->buildApi(facade: $facade);

    $code = 200;
    $headers = [];
    $result = $api->commentsIdReportPost('42', new ContentReportRequest(), $code, $headers);

    $this->assertSame(Response::HTTP_CREATED, $code);
    $this->assertIsArray($result);
    $this->assertSame('report-456', $result['report_id']);
  }

  #[Group('unit')]
  public function testUserReportSuccess(): void
  {
    $user = $this->createUserStub();

    $report = $this->createStub(ContentReport::class);
    $report->method('getId')->willReturn('report-789');

    $processor = $this->createStub(ModerationApiProcessor::class);
    $processor->method('processReport')->willReturn($report);

    $facade = $this->buildFacade(user: $user, processor: $processor);
    $api = $this->buildApi(facade: $facade);

    $code = 200;
    $headers = [];
    $result = $api->usersIdReportPost('target-user', new ContentReportRequest(), $code, $headers);

    $this->assertSame(Response::HTTP_CREATED, $code);
    $this->assertIsArray($result);
    $this->assertSame('report-789', $result['report_id']);
  }

  #[Group('unit')]
  public function testStudioReportSuccess(): void
  {
    $user = $this->createUserStub();

    $report = $this->createStub(ContentReport::class);
    $report->method('getId')->willReturn('report-studio');

    $processor = $this->createStub(ModerationApiProcessor::class);
    $processor->method('processReport')->willReturn($report);

    $facade = $this->buildFacade(user: $user, processor: $processor);
    $api = $this->buildApi(facade: $facade);

    $code = 200;
    $headers = [];
    $result = $api->studiosIdReportPost('studio-1', new ContentReportRequest(), $code, $headers);

    $this->assertSame(Response::HTTP_CREATED, $code);
    $this->assertIsArray($result);
    $this->assertSame('report-studio', $result['report_id']);
  }

  #[Group('unit')]
  public function testReportExceptionReturnsExceptionCode(): void
  {
    $user = $this->createUserStub();

    $processor = $this->createStub(ModerationApiProcessor::class);
    $processor->method('processReport')->willThrowException(
      ReportException::contentNotFound()
    );

    $facade = $this->buildFacade(user: $user, processor: $processor);
    $api = $this->buildApi(facade: $facade);

    $code = 200;
    $headers = [];
    $result = $api->projectsIdReportPost('missing', new ContentReportRequest(), $code, $headers);

    $this->assertSame(Response::HTTP_NOT_FOUND, $code);
    $this->assertNull($result);
  }

  #[Group('unit')]
  public function testReportDuplicateReturnsConflict(): void
  {
    $user = $this->createUserStub();

    $processor = $this->createStub(ModerationApiProcessor::class);
    $processor->method('processReport')->willThrowException(
      ReportException::duplicateReport()
    );

    $facade = $this->buildFacade(user: $user, processor: $processor);
    $api = $this->buildApi(facade: $facade);

    $code = 200;
    $headers = [];
    $result = $api->projectsIdReportPost('proj-1', new ContentReportRequest(), $code, $headers);

    $this->assertSame(Response::HTTP_CONFLICT, $code);
    $this->assertNull($result);
  }

  #[Group('unit')]
  public function testReportRateLimitedReturns429(): void
  {
    $user = $this->createUserStub();

    $processor = $this->createStub(ModerationApiProcessor::class);
    $processor->method('processReport')->willThrowException(
      ReportException::rateLimited()
    );

    $facade = $this->buildFacade(user: $user, processor: $processor);
    $api = $this->buildApi(facade: $facade);

    $code = 200;
    $headers = [];
    $result = $api->projectsIdReportPost('proj-1', new ContentReportRequest(), $code, $headers);

    $this->assertSame(Response::HTTP_TOO_MANY_REQUESTS, $code);
    $this->assertNull($result);
  }

  // ==================== Appeal endpoints (handleAppeal) ====================

  #[Group('unit')]
  public function testProjectAppealUnauthorized(): void
  {
    $api = $this->buildApi();

    $code = 200;
    $headers = [];
    $api->projectsIdAppealPost('proj-1', new ContentAppealRequest(), $code, $headers);

    $this->assertSame(Response::HTTP_UNAUTHORIZED, $code);
  }

  #[Group('unit')]
  public function testProjectAppealRateLimited(): void
  {
    $user = $this->createUserStub();
    $facade = $this->buildFacade(user: $user);

    $api = $this->buildApi(facade: $facade, appeal_daily_limiter: $this->createExhaustedLimiterFactory());

    $code = 200;
    $headers = [];
    $api->projectsIdAppealPost('proj-1', new ContentAppealRequest(), $code, $headers);

    $this->assertSame(Response::HTTP_TOO_MANY_REQUESTS, $code);
  }

  #[Group('unit')]
  public function testProjectAppealSuccess(): void
  {
    $user = $this->createUserStub();
    $facade = $this->buildFacade(user: $user);

    $api = $this->buildApi(facade: $facade);

    $code = 200;
    $headers = [];
    $api->projectsIdAppealPost('proj-1', new ContentAppealRequest(), $code, $headers);

    $this->assertSame(Response::HTTP_CREATED, $code);
  }

  #[Group('unit')]
  public function testCommentAppealSuccess(): void
  {
    $user = $this->createUserStub();
    $facade = $this->buildFacade(user: $user);
    $api = $this->buildApi(facade: $facade);

    $code = 200;
    $headers = [];
    $api->commentsIdAppealPost('42', new ContentAppealRequest(), $code, $headers);

    $this->assertSame(Response::HTTP_CREATED, $code);
  }

  #[Group('unit')]
  public function testUserAppealSuccess(): void
  {
    $user = $this->createUserStub();
    $facade = $this->buildFacade(user: $user);
    $api = $this->buildApi(facade: $facade);

    $code = 200;
    $headers = [];
    $api->usersIdAppealPost('user-2', new ContentAppealRequest(), $code, $headers);

    $this->assertSame(Response::HTTP_CREATED, $code);
  }

  #[Group('unit')]
  public function testStudioAppealSuccess(): void
  {
    $user = $this->createUserStub();
    $facade = $this->buildFacade(user: $user);
    $api = $this->buildApi(facade: $facade);

    $code = 200;
    $headers = [];
    $api->studiosIdAppealPost('studio-1', new ContentAppealRequest(), $code, $headers);

    $this->assertSame(Response::HTTP_CREATED, $code);
  }

  #[Group('unit')]
  public function testAppealExceptionReturnsExceptionCode(): void
  {
    $user = $this->createUserStub();

    $processor = $this->createStub(ModerationApiProcessor::class);
    $processor->method('processAppeal')->willThrowException(
      AppealException::contentNotFound()
    );

    $facade = $this->buildFacade(user: $user, processor: $processor);
    $api = $this->buildApi(facade: $facade);

    $code = 200;
    $headers = [];
    $api->projectsIdAppealPost('missing', new ContentAppealRequest(), $code, $headers);

    $this->assertSame(Response::HTTP_NOT_FOUND, $code);
  }

  #[Group('unit')]
  public function testAppealAlreadyPendingReturnsConflict(): void
  {
    $user = $this->createUserStub();

    $processor = $this->createStub(ModerationApiProcessor::class);
    $processor->method('processAppeal')->willThrowException(
      AppealException::appealAlreadyExists()
    );

    $facade = $this->buildFacade(user: $user, processor: $processor);
    $api = $this->buildApi(facade: $facade);

    $code = 200;
    $headers = [];
    $api->projectsIdAppealPost('proj-1', new ContentAppealRequest(), $code, $headers);

    $this->assertSame(Response::HTTP_CONFLICT, $code);
  }

  // ==================== moderationReportsGet ====================

  #[Group('unit')]
  public function testModerationReportsGetForbiddenForNonAdmin(): void
  {
    $api = $this->buildApi(is_admin: false);

    $code = 200;
    $headers = [];
    $result = $api->moderationReportsGet(20, null, $code, $headers);

    $this->assertSame(Response::HTTP_FORBIDDEN, $code);
    $this->assertNull($result);
  }

  #[Group('unit')]
  public function testModerationReportsGetRateLimited(): void
  {
    $user = $this->createUserStub();
    $facade = $this->buildFacade(user: $user);

    $api = $this->buildApi(
      facade: $facade,
      moderation_admin_burst_limiter: $this->createExhaustedLimiterFactory(),
      is_admin: true,
    );

    $code = 200;
    $headers = [];
    $result = $api->moderationReportsGet(20, null, $code, $headers);

    $this->assertSame(Response::HTTP_TOO_MANY_REQUESTS, $code);
    $this->assertNull($result);
  }

  #[Group('unit')]
  public function testModerationReportsGetSuccess(): void
  {
    $user = $this->createUserStub();

    $loader = $this->createStub(ModerationApiLoader::class);
    $loader->method('loadPendingReports')->willReturn([
      'data' => [],
      'has_more' => false,
      'next_cursor' => null,
    ]);

    $response_manager = $this->createStub(ModerationResponseManager::class);
    $response_manager->method('buildReportsResponse')->willReturn([
      'data' => [],
      'has_more' => false,
      'next_cursor' => null,
    ]);

    $facade = $this->buildFacade(user: $user, loader: $loader, response_manager: $response_manager);
    $api = $this->buildApi(facade: $facade, is_admin: true);

    $code = 200;
    $headers = [];
    $result = $api->moderationReportsGet(20, null, $code, $headers);

    $this->assertSame(Response::HTTP_OK, $code);
    $this->assertIsArray($result);
    $this->assertSame([], $result['data']);
  }

  // ==================== moderationReportsIdResolvePut ====================

  #[Group('unit')]
  public function testResolveReportForbiddenForNonAdmin(): void
  {
    $api = $this->buildApi(is_admin: false);

    $code = 200;
    $headers = [];
    $api->moderationReportsIdResolvePut('report-1', new ResolveReportRequest(), $code, $headers);

    $this->assertSame(Response::HTTP_FORBIDDEN, $code);
  }

  #[Group('unit')]
  public function testResolveReportUnauthorizedWhenNoUser(): void
  {
    $api = $this->buildApi(is_admin: true);

    $code = 200;
    $headers = [];
    $api->moderationReportsIdResolvePut('report-1', new ResolveReportRequest(), $code, $headers);

    $this->assertSame(Response::HTTP_UNAUTHORIZED, $code);
  }

  #[Group('unit')]
  public function testResolveReportRateLimited(): void
  {
    $user = $this->createUserStub();
    $facade = $this->buildFacade(user: $user);

    $api = $this->buildApi(
      facade: $facade,
      moderation_admin_burst_limiter: $this->createExhaustedLimiterFactory(),
      is_admin: true,
    );

    $code = 200;
    $headers = [];
    $api->moderationReportsIdResolvePut('report-1', new ResolveReportRequest(), $code, $headers);

    $this->assertSame(Response::HTTP_TOO_MANY_REQUESTS, $code);
  }

  #[Group('unit')]
  public function testResolveReportNotFound(): void
  {
    $user = $this->createUserStub();

    $loader = $this->createStub(ModerationApiLoader::class);
    $loader->method('findReport')->willReturn(null);

    $facade = $this->buildFacade(user: $user, loader: $loader);
    $api = $this->buildApi(facade: $facade, is_admin: true);

    $code = 200;
    $headers = [];
    $api->moderationReportsIdResolvePut('nonexistent', new ResolveReportRequest(), $code, $headers);

    $this->assertSame(Response::HTTP_NOT_FOUND, $code);
  }

  #[Group('unit')]
  public function testResolveReportBadRequestWhenNotPending(): void
  {
    $user = $this->createUserStub();
    $report = $this->createStub(ContentReport::class);

    $loader = $this->createStub(ModerationApiLoader::class);
    $loader->method('findReport')->willReturn($report);

    $validator = $this->createStub(ModerationRequestValidator::class);
    $validator->method('isReportPending')->willReturn(false);

    $facade = $this->buildFacade(user: $user, loader: $loader, validator: $validator);
    $api = $this->buildApi(facade: $facade, is_admin: true);

    $code = 200;
    $headers = [];
    $api->moderationReportsIdResolvePut('report-1', new ResolveReportRequest(['action' => 'accept']), $code, $headers);

    $this->assertSame(Response::HTTP_BAD_REQUEST, $code);
  }

  #[Group('unit')]
  public function testResolveReportBadRequestWhenInvalidAction(): void
  {
    $user = $this->createUserStub();
    $report = $this->createStub(ContentReport::class);

    $loader = $this->createStub(ModerationApiLoader::class);
    $loader->method('findReport')->willReturn($report);

    $validator = $this->createStub(ModerationRequestValidator::class);
    $validator->method('isReportPending')->willReturn(true);
    $validator->method('isValidReportResolveAction')->willReturn(false);

    $facade = $this->buildFacade(user: $user, loader: $loader, validator: $validator);
    $api = $this->buildApi(facade: $facade, is_admin: true);

    $code = 200;
    $headers = [];
    $api->moderationReportsIdResolvePut('report-1', new ResolveReportRequest(['action' => 'invalid']), $code, $headers);

    $this->assertSame(Response::HTTP_BAD_REQUEST, $code);
  }

  #[Group('unit')]
  public function testResolveReportBadRequestWhenProcessorThrows(): void
  {
    $user = $this->createUserStub();
    $report = $this->createStub(ContentReport::class);

    $loader = $this->createStub(ModerationApiLoader::class);
    $loader->method('findReport')->willReturn($report);

    $validator = $this->createStub(ModerationRequestValidator::class);
    $validator->method('isReportPending')->willReturn(true);
    $validator->method('isValidReportResolveAction')->willReturn(true);

    $processor = $this->createStub(ModerationApiProcessor::class);
    $processor->method('resolveReport')->willThrowException(new \InvalidArgumentException());

    $facade = $this->buildFacade(user: $user, loader: $loader, processor: $processor, validator: $validator);
    $api = $this->buildApi(facade: $facade, is_admin: true);

    $code = 200;
    $headers = [];
    $api->moderationReportsIdResolvePut('report-1', new ResolveReportRequest(['action' => 'accept']), $code, $headers);

    $this->assertSame(Response::HTTP_BAD_REQUEST, $code);
  }

  #[Group('unit')]
  public function testResolveReportSuccess(): void
  {
    $user = $this->createUserStub();
    $report = $this->createStub(ContentReport::class);

    $loader = $this->createStub(ModerationApiLoader::class);
    $loader->method('findReport')->willReturn($report);

    $validator = $this->createStub(ModerationRequestValidator::class);
    $validator->method('isReportPending')->willReturn(true);
    $validator->method('isValidReportResolveAction')->willReturn(true);

    $facade = $this->buildFacade(user: $user, loader: $loader, validator: $validator);
    $api = $this->buildApi(facade: $facade, is_admin: true);

    $code = 200;
    $headers = [];
    $api->moderationReportsIdResolvePut('report-1', new ResolveReportRequest(['action' => 'accept']), $code, $headers);

    $this->assertSame(Response::HTTP_OK, $code);
  }

  // ==================== moderationAppealsGet ====================

  #[Group('unit')]
  public function testModerationAppealsGetForbiddenForNonAdmin(): void
  {
    $api = $this->buildApi(is_admin: false);

    $code = 200;
    $headers = [];
    $result = $api->moderationAppealsGet(20, null, $code, $headers);

    $this->assertSame(Response::HTTP_FORBIDDEN, $code);
    $this->assertNull($result);
  }

  #[Group('unit')]
  public function testModerationAppealsGetRateLimited(): void
  {
    $user = $this->createUserStub();
    $facade = $this->buildFacade(user: $user);

    $api = $this->buildApi(
      facade: $facade,
      moderation_admin_burst_limiter: $this->createExhaustedLimiterFactory(),
      is_admin: true,
    );

    $code = 200;
    $headers = [];
    $result = $api->moderationAppealsGet(20, null, $code, $headers);

    $this->assertSame(Response::HTTP_TOO_MANY_REQUESTS, $code);
    $this->assertNull($result);
  }

  #[Group('unit')]
  public function testModerationAppealsGetSuccess(): void
  {
    $user = $this->createUserStub();

    $loader = $this->createStub(ModerationApiLoader::class);
    $loader->method('loadPendingAppeals')->willReturn([
      'data' => [],
      'has_more' => false,
      'next_cursor' => null,
    ]);

    $response_manager = $this->createStub(ModerationResponseManager::class);
    $response_manager->method('buildAppealsResponse')->willReturn([
      'data' => [],
      'has_more' => false,
      'next_cursor' => null,
    ]);

    $facade = $this->buildFacade(user: $user, loader: $loader, response_manager: $response_manager);
    $api = $this->buildApi(facade: $facade, is_admin: true);

    $code = 200;
    $headers = [];
    $result = $api->moderationAppealsGet(20, null, $code, $headers);

    $this->assertSame(Response::HTTP_OK, $code);
    $this->assertIsArray($result);
    $this->assertSame([], $result['data']);
  }

  // ==================== moderationAppealsIdResolvePut ====================

  #[Group('unit')]
  public function testResolveAppealForbiddenForNonAdmin(): void
  {
    $api = $this->buildApi(is_admin: false);

    $code = 200;
    $headers = [];
    $api->moderationAppealsIdResolvePut('appeal-1', new ResolveAppealRequest(), $code, $headers);

    $this->assertSame(Response::HTTP_FORBIDDEN, $code);
  }

  #[Group('unit')]
  public function testResolveAppealUnauthorizedWhenNoUser(): void
  {
    $api = $this->buildApi(is_admin: true);

    $code = 200;
    $headers = [];
    $api->moderationAppealsIdResolvePut('appeal-1', new ResolveAppealRequest(), $code, $headers);

    $this->assertSame(Response::HTTP_UNAUTHORIZED, $code);
  }

  #[Group('unit')]
  public function testResolveAppealRateLimited(): void
  {
    $user = $this->createUserStub();
    $facade = $this->buildFacade(user: $user);

    $api = $this->buildApi(
      facade: $facade,
      moderation_admin_burst_limiter: $this->createExhaustedLimiterFactory(),
      is_admin: true,
    );

    $code = 200;
    $headers = [];
    $api->moderationAppealsIdResolvePut('appeal-1', new ResolveAppealRequest(), $code, $headers);

    $this->assertSame(Response::HTTP_TOO_MANY_REQUESTS, $code);
  }

  #[Group('unit')]
  public function testResolveAppealNotFound(): void
  {
    $user = $this->createUserStub();

    $loader = $this->createStub(ModerationApiLoader::class);
    $loader->method('findAppeal')->willReturn(null);

    $facade = $this->buildFacade(user: $user, loader: $loader);
    $api = $this->buildApi(facade: $facade, is_admin: true);

    $code = 200;
    $headers = [];
    $api->moderationAppealsIdResolvePut('nonexistent', new ResolveAppealRequest(), $code, $headers);

    $this->assertSame(Response::HTTP_NOT_FOUND, $code);
  }

  #[Group('unit')]
  public function testResolveAppealBadRequestWhenNotPending(): void
  {
    $user = $this->createUserStub();
    $appeal = $this->createStub(ContentAppeal::class);

    $loader = $this->createStub(ModerationApiLoader::class);
    $loader->method('findAppeal')->willReturn($appeal);

    $validator = $this->createStub(ModerationRequestValidator::class);
    $validator->method('isAppealPending')->willReturn(false);

    $facade = $this->buildFacade(user: $user, loader: $loader, validator: $validator);
    $api = $this->buildApi(facade: $facade, is_admin: true);

    $code = 200;
    $headers = [];
    $api->moderationAppealsIdResolvePut('appeal-1', new ResolveAppealRequest(['action' => 'approve']), $code, $headers);

    $this->assertSame(Response::HTTP_BAD_REQUEST, $code);
  }

  #[Group('unit')]
  public function testResolveAppealBadRequestWhenInvalidAction(): void
  {
    $user = $this->createUserStub();
    $appeal = $this->createStub(ContentAppeal::class);

    $loader = $this->createStub(ModerationApiLoader::class);
    $loader->method('findAppeal')->willReturn($appeal);

    $validator = $this->createStub(ModerationRequestValidator::class);
    $validator->method('isAppealPending')->willReturn(true);
    $validator->method('isValidAppealResolveAction')->willReturn(false);

    $facade = $this->buildFacade(user: $user, loader: $loader, validator: $validator);
    $api = $this->buildApi(facade: $facade, is_admin: true);

    $code = 200;
    $headers = [];
    $api->moderationAppealsIdResolvePut('appeal-1', new ResolveAppealRequest(['action' => 'invalid']), $code, $headers);

    $this->assertSame(Response::HTTP_BAD_REQUEST, $code);
  }

  #[Group('unit')]
  public function testResolveAppealApproveSuccess(): void
  {
    $user = $this->createUserStub();
    $appeal = $this->createStub(ContentAppeal::class);

    $loader = $this->createStub(ModerationApiLoader::class);
    $loader->method('findAppeal')->willReturn($appeal);

    $validator = $this->createStub(ModerationRequestValidator::class);
    $validator->method('isAppealPending')->willReturn(true);
    $validator->method('isValidAppealResolveAction')->willReturn(true);

    $facade = $this->buildFacade(user: $user, loader: $loader, validator: $validator);
    $api = $this->buildApi(facade: $facade, is_admin: true);

    $code = 200;
    $headers = [];
    $api->moderationAppealsIdResolvePut(
      'appeal-1',
      new ResolveAppealRequest(['action' => 'approve', 'note' => 'Looks fine']),
      $code,
      $headers,
    );

    $this->assertSame(Response::HTTP_OK, $code);
  }

  #[Group('unit')]
  public function testResolveAppealRejectSuccess(): void
  {
    $user = $this->createUserStub();
    $appeal = $this->createStub(ContentAppeal::class);

    $loader = $this->createStub(ModerationApiLoader::class);
    $loader->method('findAppeal')->willReturn($appeal);

    $validator = $this->createStub(ModerationRequestValidator::class);
    $validator->method('isAppealPending')->willReturn(true);
    $validator->method('isValidAppealResolveAction')->willReturn(true);

    $facade = $this->buildFacade(user: $user, loader: $loader, validator: $validator);
    $api = $this->buildApi(facade: $facade, is_admin: true);

    $code = 200;
    $headers = [];
    $api->moderationAppealsIdResolvePut(
      'appeal-1',
      new ResolveAppealRequest(['action' => 'reject', 'note' => 'Violation confirmed']),
      $code,
      $headers,
    );

    $this->assertSame(Response::HTTP_OK, $code);
  }

  // ==================== usersMeReportsGet ====================

  #[Group('unit')]
  public function testUsersMeReportsGetUnauthorized(): void
  {
    $api = $this->buildApi();

    $code = 200;
    $headers = [];
    $result = $api->usersMeReportsGet(20, null, $code, $headers);

    $this->assertSame(Response::HTTP_UNAUTHORIZED, $code);
    $this->assertNull($result);
  }

  #[Group('unit')]
  public function testUsersMeReportsGetUnauthorizedWhenUserIdNull(): void
  {
    $user = $this->createStub(User::class);
    $user->method('getId')->willReturn(null);

    $facade = $this->buildFacade(user: $user);
    $api = $this->buildApi(facade: $facade);

    $code = 200;
    $headers = [];
    $result = $api->usersMeReportsGet(20, null, $code, $headers);

    $this->assertSame(Response::HTTP_UNAUTHORIZED, $code);
    $this->assertNull($result);
  }

  #[Group('unit')]
  public function testUsersMeReportsGetSuccess(): void
  {
    $user = $this->createUserStub('user-1');

    $loader = $this->createStub(ModerationApiLoader::class);
    $loader->method('loadUserReports')->willReturn([
      'data' => [],
      'has_more' => false,
      'next_cursor' => null,
    ]);

    $response_manager = $this->createStub(ModerationResponseManager::class);
    $response_manager->method('buildUserReportsResponse')->willReturn([
      'data' => [],
      'has_more' => false,
      'next_cursor' => null,
    ]);

    $facade = $this->buildFacade(user: $user, loader: $loader, response_manager: $response_manager);
    $api = $this->buildApi(facade: $facade);

    $code = 200;
    $headers = [];
    $result = $api->usersMeReportsGet(20, null, $code, $headers);

    $this->assertSame(Response::HTTP_OK, $code);
    $this->assertIsArray($result);
    $this->assertSame([], $result['data']);
  }
}
