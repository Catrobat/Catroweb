<?php

namespace Catrobat\AppBundle\Commands;

use Catrobat\AppBundle\Services\CatrobatFileExtractor;
use Catrobat\AppBundle\Services\RemixUrlIndicator;
use Symfony\Component\HttpFoundation\File\File;


/**
 * Class RemixManipulationCatrobatFileExtractor
 * @package Catrobat\AppBundle\Commands
 */
class RemixManipulationCatrobatFileExtractor extends CatrobatFileExtractor
{
  /**
   * @var int
   */
  private $current_program_id;
  /**
   * @var
   */
  private $remix_graph_mapping;

  /**
   * RemixManipulationCatrobatFileExtractor constructor.
   *
   * @param $remix_graph_mapping
   * @param $extract_dir
   * @param $extract_path
   */
  public function __construct($remix_graph_mapping, $extract_dir, $extract_path)
  {
    $this->current_program_id = 1;
    $this->remix_graph_mapping = $remix_graph_mapping;
    parent::__construct($extract_dir, $extract_path);
  }

  /**
   * @param File $file
   *
   * @return \Catrobat\AppBundle\Services\ExtractedCatrobatFile
   */
  public function extract(File $file)
  {
    $extracted_catrobat_file = parent::extract($file);

    $all_parent_program_ids = [];
    foreach ($this->remix_graph_mapping as $parent_program_data => $child_program_ids)
    {
      if (in_array($this->current_program_id, $child_program_ids))
      {
        $all_parent_program_ids[] = explode(',', $parent_program_data);
      }
    }

    $previous_parent_string = '';
    for ($parent_program_index = 0; $parent_program_index < count($all_parent_program_ids); $parent_program_index++)
    {
      $parent_program_data = $all_parent_program_ids[$parent_program_index];
      $parent_id = $parent_program_data[0];
      $current_parent_url = !$parent_program_data[1]
        ? '/pocketcode/program/' . $parent_id
        : 'https://scratch.mit.edu/projects/' . $parent_id . '/';
      $previous_parent_string = $this->generateRemixUrlsStringForMergedProgram($previous_parent_string,
        $current_parent_url);
    }

    $remix_url_string = $previous_parent_string;
    $program_xml_properties = $extracted_catrobat_file->getProgramXmlProperties();

    // NOTE: force using Catrobat language version 0.993 in order to allow multiple parents (see: RemixUpdater.php) {
    $program_xml_properties->header->catrobatLanguageVersion = '0.993';
    // }

    $program_xml_properties->header->remixOf = '';
    $program_xml_properties->header->url = $remix_url_string;
    $extracted_catrobat_file->saveProgramXmlProperties();

    $this->current_program_id++;

    return $extracted_catrobat_file;
  }

  /**
   * @param $previous_parent_string
   * @param $current_parent_url
   *
   * @return string
   */
  public function generateRemixUrlsStringForMergedProgram($previous_parent_string, $current_parent_url)
  {
    if ($previous_parent_string == '')
    {
      return $current_parent_url;
    }

    return 'PREVIOUS: '
      . RemixUrlIndicator::PREFIX_INDICATOR . $previous_parent_string . RemixUrlIndicator::SUFFIX_INDICATOR . ', '
      . 'NEXT: '
      . RemixUrlIndicator::PREFIX_INDICATOR . $current_parent_url . RemixUrlIndicator::SUFFIX_INDICATOR;
  }
}
