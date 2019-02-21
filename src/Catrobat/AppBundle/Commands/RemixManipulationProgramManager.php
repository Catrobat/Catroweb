<?php

namespace Catrobat\AppBundle\Commands;

use Catrobat\AppBundle\Entity\ProgramManager;
use Catrobat\AppBundle\Services\CatrobatFileExtractor;


/**
 * Class RemixManipulationProgramManager
 * @package Catrobat\AppBundle\Commands
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
