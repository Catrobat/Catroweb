<?php

namespace App\Catrobat\Commands\Helpers;

use App\Catrobat\Services\CatrobatFileExtractor;
use App\Catrobat\Services\ExtractedCatrobatFile;
use App\Catrobat\Services\RemixUrlIndicator;
use Symfony\Component\HttpFoundation\File\File;

class RemixManipulationCatrobatFileExtractor extends CatrobatFileExtractor
{
  private int $current_program_id;

  private $remix_graph_mapping;

  /**
   * RemixManipulationCatrobatFileExtractor constructor.
   *
   * @param mixed $remix_graph_mapping
   * @param mixed $extract_dir
   * @param mixed $extract_path
   */
  public function __construct($remix_graph_mapping, $extract_dir, $extract_path)
  {
    $this->current_program_id = 1;
    $this->remix_graph_mapping = $remix_graph_mapping;
    parent::__construct($extract_dir, $extract_path);
  }

  public function extract(File $file): ExtractedCatrobatFile
  {
    $extracted_catrobat_file = parent::extract($file);

    $all_parent_program_ids = [];
    foreach ($this->remix_graph_mapping as $parent_program_data => $child_program_ids)
    {
      if (in_array($this->current_program_id, $child_program_ids, true))
      {
        $all_parent_program_ids[] = explode(',', $parent_program_data);
      }
    }

    $previous_parent_string = '';
    foreach ($all_parent_program_ids as $parent_program_index => $all_parent_program_id)
    {
      $parent_program_data = $all_parent_program_id;
      $parent_id = $parent_program_data[0];
      $current_parent_url = '' === $parent_program_data[1]
          ? '//app/project/'.$parent_id
          : 'https://scratch.mit.edu/projects/'.$parent_id.'/';
      $previous_parent_string = $this->generateRemixUrlsStringForMergedProgram($previous_parent_string,
          $current_parent_url);
    }

    $remix_url_string = $previous_parent_string;
    $program_xml_properties = $extracted_catrobat_file->getProgramXmlProperties();

    // NOTE: force using Catrobat language version 0.994 in order to allow multiple parents (see: RemixUpdater.php)
    if ($program_xml_properties->header->catrobatLanguageVersion < '0.994')
    {
      $program_xml_properties->header->catrobatLanguageVersion = '0.993';
    }

    $program_xml_properties->header->remixOf = '';
    $program_xml_properties->header->url = $remix_url_string;
    $extracted_catrobat_file->saveProgramXmlProperties();

    ++$this->current_program_id;

    return $extracted_catrobat_file;
  }

  /**
   * @param mixed $previous_parent_string
   * @param mixed $current_parent_url
   */
  public function generateRemixUrlsStringForMergedProgram($previous_parent_string, $current_parent_url): string
  {
    if ('' == $previous_parent_string)
    {
      return $current_parent_url;
    }

    return 'PREVIOUS: '
      .RemixUrlIndicator::PREFIX_INDICATOR.$previous_parent_string.RemixUrlIndicator::SUFFIX_INDICATOR.', '
      .'NEXT: '
      .RemixUrlIndicator::PREFIX_INDICATOR.$current_parent_url.RemixUrlIndicator::SUFFIX_INDICATOR;
  }
}
