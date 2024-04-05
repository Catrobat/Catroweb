<?php

declare(strict_types=1);

namespace App\System\Commands\Helpers;

use App\Project\ProjectManager;

class RemixManipulationProjectManager extends ProjectManager
{
  public function useRemixManipulationFileExtractor(mixed $remix_graph_mapping): void
  {
    $old_file_extractor = $this->file_extractor;
    $this->file_extractor = new RemixManipulationCatrobatFileExtractor(
      $remix_graph_mapping,
      $old_file_extractor->getExtractDir(),
      $old_file_extractor->getExtractPath()
    );
  }
}
