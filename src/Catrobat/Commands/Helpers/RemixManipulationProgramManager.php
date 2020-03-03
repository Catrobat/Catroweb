<?php

namespace App\Catrobat\Commands\Helpers;

use App\Catrobat\Services\CatrobatFileExtractor;
use App\Entity\ProgramManager;

/**
 * Class RemixManipulationProgramManager.
 */
class RemixManipulationProgramManager extends ProgramManager
{
  /**
   * @param $remix_graph_mapping
   */
  public function useRemixManipulationFileExtractor($remix_graph_mapping)
  {
    /**
     * @var CatrobatFileExtractor
     */
    $old_file_extractor = $this->file_extractor;
    $this->file_extractor = new RemixManipulationCatrobatFileExtractor(
      $remix_graph_mapping,
      $old_file_extractor->getExtractDir(),
      $old_file_extractor->getExtractPath()
    );
  }
}
