<?php

declare(strict_types=1);

namespace App\Api\Services\Moderation;

use App\Api\Services\Base\AbstractResponseManager;
use App\DB\Entity\Moderation\ContentAppeal;
use App\DB\Entity\Moderation\ContentReport;
use App\DB\Enum\ReportState;

class ModerationResponseManager extends AbstractResponseManager
{
  /**
   * @param ContentReport[] $reports
   *
   * @return array{data: array<int, array<string, mixed>>, next_cursor: ?string, has_more: bool}
   */
  public function buildReportsResponse(array $reports, bool $has_more, ?string $next_cursor): array
  {
    $data = array_map(static fn (ContentReport $r): array => [
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

    return [
      'data' => $data,
      'next_cursor' => $next_cursor,
      'has_more' => $has_more,
    ];
  }

  /**
   * @param ContentReport[] $reports
   *
   * @return array{data: array<int, array<string, mixed>>, next_cursor: ?string, has_more: bool}
   */
  public function buildUserReportsResponse(array $reports, bool $has_more, ?string $next_cursor): array
  {
    $data = array_map(static fn (ContentReport $r): array => [
      'id' => $r->getId(),
      'content_type' => $r->getContentType(),
      'content_id' => $r->getContentId(),
      'category' => $r->getCategory(),
      'status' => self::mapStateToStatus($r->getState()),
      'created_at' => $r->getCreatedAt()?->format(\DateTimeInterface::ATOM),
      'resolved_at' => $r->getResolvedAt()?->format(\DateTimeInterface::ATOM),
    ], $reports);

    return [
      'data' => $data,
      'next_cursor' => $next_cursor,
      'has_more' => $has_more,
    ];
  }

  private static function mapStateToStatus(int $state): string
  {
    return match (ReportState::tryFrom($state)) {
      ReportState::Accepted => 'accepted',
      ReportState::Rejected => 'rejected',
      default => 'pending',
    };
  }

  /**
   * @param ContentAppeal[] $appeals
   *
   * @return array{data: array<int, array<string, mixed>>, next_cursor: ?string, has_more: bool}
   */
  public function buildAppealsResponse(array $appeals, bool $has_more, ?string $next_cursor): array
  {
    $data = array_map(static fn (ContentAppeal $a): array => [
      'id' => $a->getId(),
      'content_type' => $a->getContentType(),
      'content_id' => $a->getContentId(),
      'appellant_id' => $a->getAppellant()?->getId(),
      'reason' => $a->getReason(),
      'state' => $a->getState(),
      'created_at' => $a->getCreatedAt()?->format(\DateTimeInterface::ATOM),
      'resolution_note' => $a->getResolutionNote(),
    ], $appeals);

    return [
      'data' => $data,
      'next_cursor' => $next_cursor,
      'has_more' => $has_more,
    ];
  }
}
