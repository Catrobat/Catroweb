<?php

declare(strict_types=1);

namespace App\Api;

use App\Api\Services\Base\AbstractApiController;
use App\Api\Services\Moderation\ModerationApiFacade;
use App\Api\Services\Moderation\ModerationRequestValidator;
use App\DB\Entity\User\User;
use App\DB\Enum\ContentType;
use App\Moderation\AppealException;
use App\Moderation\ReportException;
use OpenAPI\Server\Api\ModerationApiInterface;
use OpenAPI\Server\Model\ContentAppealRequest;
use OpenAPI\Server\Model\ContentReportRequest;
use OpenAPI\Server\Model\ResolveAppealRequest;
use OpenAPI\Server\Model\ResolveReportRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;

class ModerationApi extends AbstractApiController implements ModerationApiInterface
{
  use RateLimitTrait;

  public function __construct(
    private readonly ModerationApiFacade $facade,
    private readonly RateLimiterFactory $appealDailyLimiter,
    private readonly RateLimiterFactory $moderationAdminBurstLimiter,
  ) {
  }

  #[\Override]
  public function projectIdReportPost(
    string $id,
    ContentReportRequest $content_report_request,
    int &$responseCode,
    array &$responseHeaders,
  ): void {
    $this->handleReport(ContentType::Project, $id, $content_report_request, $responseCode);
  }

  #[\Override]
  public function commentsIdReportPost(
    int $id,
    ContentReportRequest $content_report_request,
    int &$responseCode,
    array &$responseHeaders,
  ): void {
    $this->handleReport(ContentType::Comment, (string) $id, $content_report_request, $responseCode);
  }

  #[\Override]
  public function userIdReportPost(
    string $id,
    ContentReportRequest $content_report_request,
    int &$responseCode,
    array &$responseHeaders,
  ): void {
    $this->handleReport(ContentType::User, $id, $content_report_request, $responseCode);
  }

  #[\Override]
  public function studioIdReportPost(
    string $id,
    ContentReportRequest $content_report_request,
    int &$responseCode,
    array &$responseHeaders,
  ): void {
    $this->handleReport(ContentType::Studio, $id, $content_report_request, $responseCode);
  }

  #[\Override]
  public function projectIdAppealPost(
    string $id,
    ContentAppealRequest $content_appeal_request,
    int &$responseCode,
    array &$responseHeaders,
  ): void {
    $this->handleAppeal(ContentType::Project, $id, $content_appeal_request, $responseCode);
  }

  #[\Override]
  public function commentsIdAppealPost(
    int $id,
    ContentAppealRequest $content_appeal_request,
    int &$responseCode,
    array &$responseHeaders,
  ): void {
    $this->handleAppeal(ContentType::Comment, (string) $id, $content_appeal_request, $responseCode);
  }

  #[\Override]
  public function userIdAppealPost(
    string $id,
    ContentAppealRequest $content_appeal_request,
    int &$responseCode,
    array &$responseHeaders,
  ): void {
    $this->handleAppeal(ContentType::User, $id, $content_appeal_request, $responseCode);
  }

  #[\Override]
  public function studioIdAppealPost(
    string $id,
    ContentAppealRequest $content_appeal_request,
    int &$responseCode,
    array &$responseHeaders,
  ): void {
    $this->handleAppeal(ContentType::Studio, $id, $content_appeal_request, $responseCode);
  }

  #[\Override]
  public function moderationReportsGet(
    int $limit,
    ?string $cursor,
    int &$responseCode,
    array &$responseHeaders,
  ): array|object|null {
    if (!$this->isGranted('ROLE_ADMIN')) {
      $responseCode = Response::HTTP_FORBIDDEN;

      return null;
    }

    $admin = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    if ($admin instanceof User && null === $this->checkUserRateLimit($admin, $this->moderationAdminBurstLimiter)) {
      $responseCode = Response::HTTP_TOO_MANY_REQUESTS;

      return null;
    }

    $result = $this->facade->getLoader()->loadPendingReports($limit, $cursor);
    $responseCode = Response::HTTP_OK;

    return $this->facade->getResponseManager()->buildReportsResponse(
      $result['data'],
      $result['has_more'],
      $result['next_cursor'],
    );
  }

  #[\Override]
  public function moderationReportsIdResolvePut(
    int $id,
    ResolveReportRequest $resolve_report_request,
    int &$responseCode,
    array &$responseHeaders,
  ): void {
    if (!$this->isGranted('ROLE_ADMIN')) {
      $responseCode = Response::HTTP_FORBIDDEN;

      return;
    }

    $admin = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    if (!$admin instanceof User) {
      $responseCode = Response::HTTP_UNAUTHORIZED;

      return;
    }

    if (null === $this->checkUserRateLimit($admin, $this->moderationAdminBurstLimiter)) {
      $responseCode = Response::HTTP_TOO_MANY_REQUESTS;

      return;
    }

    $report = $this->facade->getLoader()->findReport($id);
    if (null === $report) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return;
    }

    $validator = $this->facade->getRequestValidator();
    $action = $resolve_report_request->getAction();

    if (!$validator->isReportPending($report) || !$validator->isValidReportResolveAction($action)) {
      $responseCode = Response::HTTP_BAD_REQUEST;

      return;
    }

    try {
      $this->facade->getProcessor()->resolveReport($report, $admin, $action);
    } catch (\InvalidArgumentException) {
      $responseCode = Response::HTTP_BAD_REQUEST;

      return;
    }

    $responseCode = Response::HTTP_OK;
  }

  #[\Override]
  public function moderationAppealsGet(
    int $limit,
    ?string $cursor,
    int &$responseCode,
    array &$responseHeaders,
  ): array|object|null {
    if (!$this->isGranted('ROLE_ADMIN')) {
      $responseCode = Response::HTTP_FORBIDDEN;

      return null;
    }

    $admin = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    if ($admin instanceof User && null === $this->checkUserRateLimit($admin, $this->moderationAdminBurstLimiter)) {
      $responseCode = Response::HTTP_TOO_MANY_REQUESTS;

      return null;
    }

    $result = $this->facade->getLoader()->loadPendingAppeals($limit, $cursor);
    $responseCode = Response::HTTP_OK;

    return $this->facade->getResponseManager()->buildAppealsResponse(
      $result['data'],
      $result['has_more'],
      $result['next_cursor'],
    );
  }

  #[\Override]
  public function moderationAppealsIdResolvePut(
    int $id,
    ResolveAppealRequest $resolve_appeal_request,
    int &$responseCode,
    array &$responseHeaders,
  ): void {
    if (!$this->isGranted('ROLE_ADMIN')) {
      $responseCode = Response::HTTP_FORBIDDEN;

      return;
    }

    $admin = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    if (!$admin instanceof User) {
      $responseCode = Response::HTTP_UNAUTHORIZED;

      return;
    }

    if (null === $this->checkUserRateLimit($admin, $this->moderationAdminBurstLimiter)) {
      $responseCode = Response::HTTP_TOO_MANY_REQUESTS;

      return;
    }

    $appeal = $this->facade->getLoader()->findAppeal($id);
    if (null === $appeal) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return;
    }

    $validator = $this->facade->getRequestValidator();
    $action = $resolve_appeal_request->getAction();
    $note = $resolve_appeal_request->getNote();

    if (!$validator->isAppealPending($appeal)) {
      $responseCode = Response::HTTP_BAD_REQUEST;

      return;
    }

    if (!$validator->isValidAppealResolveAction($action)) {
      $responseCode = Response::HTTP_BAD_REQUEST;

      return;
    }

    $processor = $this->facade->getProcessor();
    if (ModerationRequestValidator::ACTION_APPEAL_APPROVE === $action) {
      $processor->approveAppeal($appeal, $admin, $note);
    } else {
      $processor->rejectAppeal($appeal, $admin, $note);
    }

    $responseCode = Response::HTTP_OK;
  }

  private function handleReport(
    ContentType $content_type,
    string $content_id,
    ContentReportRequest $request,
    int &$responseCode,
  ): void {
    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    if (!$user instanceof User) {
      $responseCode = Response::HTTP_UNAUTHORIZED;

      return;
    }

    try {
      $this->facade->getProcessor()->processReport($user, $content_type, $content_id, $request);
      $responseCode = Response::HTTP_NO_CONTENT;
    } catch (ReportException $e) {
      $responseCode = $e->getCode();
    }
  }

  private function handleAppeal(
    ContentType $content_type,
    string $content_id,
    ContentAppealRequest $request,
    int &$responseCode,
  ): void {
    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    if (!$user instanceof User) {
      $responseCode = Response::HTTP_UNAUTHORIZED;

      return;
    }

    if (null === $this->checkUserRateLimit($user, $this->appealDailyLimiter)) {
      $responseCode = Response::HTTP_TOO_MANY_REQUESTS;

      return;
    }

    try {
      $this->facade->getProcessor()->processAppeal($user, $content_type, $content_id, $request);
      $responseCode = Response::HTTP_CREATED;
    } catch (AppealException $e) {
      $responseCode = $e->getCode();
    }
  }
}
