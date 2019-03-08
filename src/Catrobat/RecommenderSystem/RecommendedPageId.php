<?php

namespace App\Catrobat\RecommenderSystem;

/**
 * Class RecommendedPageId
 * @package App\Catrobat\RecommenderSystem
 */
class RecommendedPageId
{
  // -----------------------------------------------------------------------------------------------------------------
  // WARNING: these IDs are fixed and heavily used for statistical analysis.
  //          Therefore they *MUST NEVER EVER* be changed !!!
  // -----------------------------------------------------------------------------------------------------------------
  const INVALID_PAGE = 0;
  const INDEX_PAGE = 1;
  const PROGRAM_DETAIL_PAGE_REMIX_GRAPH = 2;
  const NOTIFICATION_CENTER_PAGE = 3;
  const PROGRAM_DETAIL_PAGE = 4;
  // -> new page IDs go here...

  public static $VALID_PAGE_IDS = [
    self::INDEX_PAGE,
    self::PROGRAM_DETAIL_PAGE_REMIX_GRAPH,
    self::NOTIFICATION_CENTER_PAGE,
    self::PROGRAM_DETAIL_PAGE,
    // -> ... and here
  ];


  /**
   * @param $page_id
   *
   * @return bool
   */
  static public function isValidRecommendedPageId($page_id)
  {
    return in_array($page_id, self::$VALID_PAGE_IDS);
  }

}
