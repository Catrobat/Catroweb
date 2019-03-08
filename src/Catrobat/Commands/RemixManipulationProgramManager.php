<?php

namespace App\Catrobat\Commands;

use App\Entity\ProgramManager;
use App\Catrobat\Services\CatrobatFileExtractor;


/**
 * Class RemixManipulationProgramManager
 * @package App\Catrobat\Commands
 */
class RemixManipulationProgramManager extends ProgramManager
{

  /**
   * @param $remix_graph_mapping
   */
  public function useRemixManipulationFileExtractor($remix_graph_mapping)
  {
    /**
     * @var $old_file_extractor CatrobatFileExtractor
     */
    $old_file_extractor = $this->file_extractor;
    $this->file_extractor = new RemixManipulationCatrobatFileExtractor(
      $remix_graph_mapping,
      $old_file_extractor->getExtractDir(),
      $old_file_extractor->getExtractPath()
    );
  }
}
