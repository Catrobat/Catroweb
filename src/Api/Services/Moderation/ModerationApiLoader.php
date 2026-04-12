<?php

declare(strict_types=1);

namespace App\Api\Services\Moderation;

use App\Api\Services\Base\AbstractApiLoader;
use App\DB\Entity\Moderation\ContentAppeal;
use App\DB\Entity\Moderation\ContentReport;
use App\DB\EntityRepository\Moderation\ContentAppealRepository;
use App\DB\EntityRepository\Moderation\ContentReportRepository;

class ModerationApiLoader extends AbstractApiLoader
{
  public function __construct(
    private readonly ContentReportRepository $report_repository,
    private readonly ContentAppealRepository $appeal_repository,
  ) {
  }

  /**
   * @return array{data: ContentReport[], has_more: bool, next_cursor: ?string}
   */
  public function loadPendingReports(int $limit, ?string $cursor): array
  {
    [$limit, $cursor_created_at, $cursor_id, $legacy_cursor_id] = $this->parseCursorParams($limit, $cursor);
    [$cursor_created_at, $cursor_id, $legacy_cursor_id] = $this->resolveReportLegacyCursor($cursor_created_at, $cursor_id, $legacy_cursor_id);

    $reports = $this->report_repository->findPendingReports($limit, $cursor_created_at, $cursor_id, $legacy_cursor_id);

    return $this->paginateResults($reports, $limit);
  }

  /**
   * @return array{data: ContentAppeal[], has_more: bool, next_cursor: ?string}
   */
  public function loadPendingAppeals(int $limit, ?string $cursor): array
  {
    [$limit, $cursor_created_at, $cursor_id, $legacy_cursor_id] = $this->parseCursorParams($limit, $cursor);

    if (null !== $legacy_cursor_id && (null === $cursor_created_at || null === $cursor_id)) {
      $legacy_appeal = $this->appeal_repository->find($legacy_cursor_id);
      if ($legacy_appeal instanceof ContentAppeal && $legacy_appeal->getCreatedAt() instanceof \DateTimeInterface) {
        $cursor_created_at = $legacy_appeal->getCreatedAt();
        $cursor_id = $legacy_appeal->getId();
        $legacy_cursor_id = null;
      }
    }

    $appeals = $this->appeal_repository->findPendingAppeals($limit, $cursor_created_at, $cursor_id, $legacy_cursor_id);

    return $this->paginateResults($appeals, $limit);
  }

  /**
   * @return array{data: ContentReport[], has_more: bool, next_cursor: ?string}
   */
  public function loadUserReports(string $user_id, int $limit, ?string $cursor): array
  {
    [$limit, $cursor_created_at, $cursor_id, $legacy_cursor_id] = $this->parseCursorParams($limit, $cursor);
    [$cursor_created_at, $cursor_id, $legacy_cursor_id] = $this->resolveReportLegacyCursor($cursor_created_at, $cursor_id, $legacy_cursor_id);

    $reports = $this->report_repository->findReportsByUser($user_id, $limit, $cursor_created_at, $cursor_id);

    return $this->paginateResults($reports, $limit);
  }

  public function findReport(string $id): ?ContentReport
  {
    return $this->report_repository->find($id);
  }

  public function findAppeal(string $id): ?ContentAppeal
  {
    return $this->appeal_repository->find($id);
  }

  /**
   * Resolve a legacy (id-only) report cursor into created_at+id components.
   *
   * @return array{?\DateTimeInterface, ?string, ?string}
   */
  private function resolveReportLegacyCursor(
    ?\DateTimeInterface $cursor_created_at,
    ?string $cursor_id,
    ?string $legacy_cursor_id = null,
  ): array {
    if (null !== $legacy_cursor_id && (null === $cursor_created_at || null === $cursor_id)) {
      $legacy_report = $this->report_repository->find($legacy_cursor_id);
      if ($legacy_report instanceof ContentReport && $legacy_report->getCreatedAt() instanceof \DateTimeInterface) {
        return [$legacy_report->getCreatedAt(), $legacy_report->getId(), null];
      }
    }

    return [$cursor_created_at, $cursor_id, $legacy_cursor_id];
  }

  /**
   * Parse and normalize cursor pagination parameters.
   *
   * @return array{int, ?\DateTimeInterface, ?string, ?string}
   */
  private function parseCursorParams(int $limit, ?string $cursor): array
  {
    $limit = min(max($limit, 1), 100);
    $cursor_data = $this->decodeModerationCursor($cursor);

    $cursor_created_at = $cursor_data['created_at'] ?? null;
    $cursor_id = $cursor_data['id'] ?? null;
    $legacy_cursor_id = $cursor_data['legacy_id'] ?? null;

    return [$limit, $cursor_created_at, $cursor_id, $legacy_cursor_id];
  }

  /**
   * Apply overflow-based pagination (fetch N+1, pop if more).
   *
   * @param array<ContentReport|ContentAppeal> $items
   *
   * @return array{data: array, has_more: bool, next_cursor: ?string}
   */
  private function paginateResults(array $items, int $limit): array
  {
    $has_more = count($items) > $limit;
    if ($has_more) {
      array_pop($items);
    }

    $last = end($items);
    $next_cursor = $has_more && false !== $last
      ? $this->encodeModerationCursor($last->getCreatedAt(), $last->getId())
      : null;

    return [
      'data' => $items,
      'has_more' => $has_more,
      'next_cursor' => $next_cursor,
    ];
  }

  /**
   * @return array{created_at: ?\DateTimeInterface, id: ?string, legacy_id: ?string}|null
   */
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
      return [
        'created_at' => null,
        'id' => null,
        'legacy_id' => $decoded,
      ];
    }

    [$created_at_raw, $id_raw] = explode('|', $decoded, 2);
    if ('' === $id_raw) {
      return null;
    }

    try {
      $created_at = new \DateTimeImmutable($created_at_raw, new \DateTimeZone('UTC'));
    } catch (\Exception) {
      return null;
    }

    return [
      'created_at' => $created_at->setTimezone(new \DateTimeZone('UTC')),
      'id' => $id_raw,
      'legacy_id' => null,
    ];
  }

  private function encodeModerationCursor(?\DateTimeInterface $created_at, ?string $id): ?string
  {
    if (!($created_at instanceof \DateTimeInterface) || null === $id) {
      return null;
    }

    $utc_created_at = \DateTimeImmutable::createFromInterface($created_at)->setTimezone(new \DateTimeZone('UTC'));

    return base64_encode($utc_created_at->format(\DateTimeInterface::ATOM).'|'.$id);
  }
}
