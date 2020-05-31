<?php

namespace App\Catrobat\RecommenderSystem;

class RecommendedPageId
{
  // -----------------------------------------------------------------------------------------------------------------
  // WARNING: these IDs are fixed and heavily used for statistical analysis.
  //          Therefore they *MUST NEVER EVER* be changed !!!
  // -----------------------------------------------------------------------------------------------------------------
  /**
   * @var int
   */
  const INVALID_PAGE = 0;
  /**
   * @var int
   */
  const INDEX_PAGE = 1;
  /**
   * @var int
   */
  const PROGRAM_DETAIL_PAGE_REMIX_GRAPH = 2;
  /**
   * @var int
   */
  const NOTIFICATION_CENTER_PAGE = 3;
  /**
   * @var int
   */
  const PROGRAM_DETAIL_PAGE = 4;
  // -> new page IDs go here...

  /**
   * @var int[]
   */
  public static array $VALID_PAGE_IDS = [
    self::INDEX_PAGE,
    self::PROGRAM_DETAIL_PAGE_REMIX_GRAPH,
    self::NOTIFICATION_CENTER_PAGE,
    self::PROGRAM_DETAIL_PAGE,
    // -> ... and here
  ];

  public static function isValidRecommendedPageId(int $page_id): bool
  {
    return in_array($page_id, self::$VALID_PAGE_IDS, true);
  }
}
