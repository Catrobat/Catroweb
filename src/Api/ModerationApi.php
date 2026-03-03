<?php

declare(strict_types=1);

namespace App\Api;

use App\Api\Services\AuthenticationManager;
use App\Api\Services\Base\AbstractApiController;
use App\DB\Entity\Moderation\ContentAppeal;
use App\DB\Entity\Moderation\ContentReport;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\Moderation\ContentAppealRepository;
use App\DB\EntityRepository\Moderation\ContentReportRepository;
use App\DB\Enum\AppealState;
use App\DB\Enum\ContentType;
use App\DB\Enum\ReportState;
use App\Moderation\AppealException;
use App\Moderation\AppealProcessor;
use App\Moderation\ReportException;
use App\Moderation\ReportProcessor;
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
    private readonly AuthenticationManager $authentication_manager,
    private readonly ReportProcessor $report_processor,
    private readonly AppealProcessor $appeal_processor,
    private readonly ContentReportRepository $report_repository,
    private readonly ContentAppealRepository $appeal_repository,
    private readonly RateLimiterFactory $appealDailyLimiter,
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

    $limit = min(max($limit, 1), 100);
    $cursor_data = $this->decodeModerationCursor($cursor);
    $cursor_created_at = $cursor_data['created_at'] ?? null;
    $cursor_id = $cursor_data['id'] ?? null;
    $legacy_cursor_id = $cursor_data['legacy_id'] ?? null;

    if (null !== $legacy_cursor_id && (null === $cursor_created_at || null === $cursor_id)) {
      $legacy_report = $this->report_repository->find($legacy_cursor_id);
      if ($legacy_report instanceof ContentReport && $legacy_report->getCreatedAt() instanceof \DateTimeInterface) {
        $cursor_created_at = $legacy_report->getCreatedAt();
        $cursor_id = $legacy_report->getId();
        $legacy_cursor_id = null;
      }
    }

    $reports = $this->report_repository->findPendingReports(
      $limit,
      $cursor_created_at,
      $cursor_id,
      $legacy_cursor_id,
    );

    $has_more = count($reports) > $limit;
    if ($has_more) {
      array_pop($reports);
    }

    $data = array_map(fn (ContentReport $r) => [
      'id' => $r->getId(),
      'reporter_id' => $r->getReporter()?->getId(),
      'content_type' => $r->getContentType(),
      'content_id' => $r->getContentId(),
      'category' => $r->getCategory(),
      'note' => $r->getNote(),
      'state' => $r->getState(),
      'reporter_trust_score' => $r->getReporterTrustScore(),
      'created_at' => $r->getCreatedAt()?->format(\DateTimeInterface::ATOM),
    ], $reports);

    $last = end($reports);
    $next_cursor = $has_more && false !== $last
      ? $this->encodeModerationCursor($last->getCreatedAt(), $last->getId())
      : null;

    $responseCode = Response::HTTP_OK;

    return [
      'data' => $data,
      'next_cursor' => $next_cursor,
      'has_more' => $has_more,
    ];
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

    $admin = $this->authentication_manager->getAuthenticatedUser();
    if (!$admin instanceof User) {
      $responseCode = Response::HTTP_UNAUTHORIZED;

      return;
    }

    $report = $this->report_repository->find($id);
    if (!$report instanceof ContentReport) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return;
    }

    if (ReportState::New->value !== $report->getState()) {
      $responseCode = Response::HTTP_BAD_REQUEST;

      return;
    }

    $action = $resolve_report_request->getAction();
    if ('accept' !== $action && 'reject' !== $action) {
      $responseCode = Response::HTTP_BAD_REQUEST;

      return;
    }

    try {
      $this->report_processor->resolveReport($report, $admin, $action);
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

    $limit = min(max($limit, 1), 100);
    $cursor_data = $this->decodeModerationCursor($cursor);
    $cursor_created_at = $cursor_data['created_at'] ?? null;
    $cursor_id = $cursor_data['id'] ?? null;
    $legacy_cursor_id = $cursor_data['legacy_id'] ?? null;

    if (null !== $legacy_cursor_id && (null === $cursor_created_at || null === $cursor_id)) {
      $legacy_appeal = $this->appeal_repository->find($legacy_cursor_id);
      if ($legacy_appeal instanceof ContentAppeal && $legacy_appeal->getCreatedAt() instanceof \DateTimeInterface) {
        $cursor_created_at = $legacy_appeal->getCreatedAt();
        $cursor_id = $legacy_appeal->getId();
        $legacy_cursor_id = null;
      }
    }

    $appeals = $this->appeal_repository->findPendingAppeals(
      $limit,
      $cursor_created_at,
      $cursor_id,
      $legacy_cursor_id,
    );

    $has_more = count($appeals) > $limit;
    if ($has_more) {
      array_pop($appeals);
    }

    $data = array_map(fn (ContentAppeal $a) => [
      'id' => $a->getId(),
      'content_type' => $a->getContentType(),
      'content_id' => $a->getContentId(),
      'appellant_id' => $a->getAppellant()?->getId(),
      'reason' => $a->getReason(),
      'state' => $a->getState(),
      'created_at' => $a->getCreatedAt()?->format(\DateTimeInterface::ATOM),
      'resolution_note' => $a->getResolutionNote(),
    ], $appeals);

    $last = end($appeals);
    $next_cursor = $has_more && false !== $last
      ? $this->encodeModerationCursor($last->getCreatedAt(), $last->getId())
      : null;

    $responseCode = Response::HTTP_OK;

    return [
      'data' => $data,
      'next_cursor' => $next_cursor,
      'has_more' => $has_more,
    ];
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

    $admin = $this->authentication_manager->getAuthenticatedUser();
    if (!$admin instanceof User) {
      $responseCode = Response::HTTP_UNAUTHORIZED;

      return;
    }

    $appeal = $this->appeal_repository->find($id);
    if (!$appeal instanceof ContentAppeal) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return;
    }

    if (AppealState::Pending->value !== $appeal->getState()) {
      $responseCode = Response::HTTP_BAD_REQUEST;

      return;
    }

    $action = $resolve_appeal_request->getAction();
    $note = $resolve_appeal_request->getNote();

    if ('approve' === $action) {
      $this->appeal_processor->approveAppeal($appeal, $admin, $note);
    } elseif ('reject' === $action) {
      $this->appeal_processor->rejectAppeal($appeal, $admin, $note);
    } else {
      $responseCode = Response::HTTP_BAD_REQUEST;

      return;
    }

    $responseCode = Response::HTTP_OK;
  }

  private function handleReport(
    ContentType $content_type,
    string $content_id,
    ContentReportRequest $request,
    int &$responseCode,
  ): void {
    $user = $this->authentication_manager->getAuthenticatedUser();
    if (!$user instanceof User) {
      $responseCode = Response::HTTP_UNAUTHORIZED;

      return;
    }

    try {
      $this->report_processor->processReport(
        $user,
        $content_type,
        $content_id,
        $request->getCategory() ?? '',
        $request->getNote(),
      );
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
    $user = $this->authentication_manager->getAuthenticatedUser();
    if (!$user instanceof User) {
      $responseCode = Response::HTTP_UNAUTHORIZED;

      return;
    }

    if (!$this->checkUserRateLimit($user, $this->appealDailyLimiter)) {
      $responseCode = Response::HTTP_TOO_MANY_REQUESTS;

      return;
    }

    try {
      $this->appeal_processor->processAppeal(
        $user,
        $content_type,
        $content_id,
        $request->getReason() ?? '',
      );
      $responseCode = Response::HTTP_CREATED;
    } catch (AppealException $e) {
      $responseCode = $e->getCode();
    }
  }

  private function decodeModerationCursor(?string $cursor): ?array
  {
    if (null === $cursor || '' === trim($cursor)) {
      return null;
    }

    $decoded = base64_decode($cursor, true);
    if (false === $decoded || '' === trim($decoded)) {
      return null;
    }

    if (!str_contains($decoded, '|')) {
      $legacy_id = filter_var($decoded, FILTER_VALIDATE_INT);
      if (false === $legacy_id) {
        return null;
      }

      return [
        'created_at' => null,
        'id' => null,
        'legacy_id' => $legacy_id,
      ];
    }

    [$created_at_raw, $id_raw] = explode('|', $decoded, 2);
    $id = filter_var($id_raw, FILTER_VALIDATE_INT);
    if (false === $id) {
      return null;
    }

    try {
      $created_at = new \DateTimeImmutable($created_at_raw, new \DateTimeZone('UTC'));
    } catch (\Exception) {
      return null;
    }

    return [
      'created_at' => $created_at->setTimezone(new \DateTimeZone('UTC')),
      'id' => $id,
      'legacy_id' => null,
    ];
  }

  private function encodeModerationCursor(?\DateTimeInterface $created_at, ?int $id): ?string
  {
    if (!($created_at instanceof \DateTimeInterface) || null === $id) {
      return null;
    }

    $utc_created_at = \DateTimeImmutable::createFromInterface($created_at)->setTimezone(new \DateTimeZone('UTC'));

    return base64_encode($utc_created_at->format(\DateTimeInterface::ATOM).'|'.$id);
  }
}
