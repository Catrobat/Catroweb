<?php

namespace App\System\Commands\Helpers;

use App\Project\CatrobatFile\CatrobatFileExtractor;
use App\Project\CatrobatFile\ExtractedCatrobatFile;
use App\Project\Remix\RemixUrlIndicator;
use Symfony\Component\HttpFoundation\File\File;

class RemixManipulationCatrobatFileExtractor extends CatrobatFileExtractor
{
  private int $current_program_id = 1;

  public function __construct(private mixed $remix_graph_mapping, mixed $extract_dir, mixed $extract_path)
  {
    parent::__construct($extract_dir, $extract_path);
  }

  /**
   * @psalm-suppress UndefinedPropertyAssignment
   *
   * @throws \Exception
   */
  public function extract(File $file): ExtractedCatrobatFile
  {
    $extracted_catrobat_file = parent::extract($file);

    $all_parent_program_ids = [];
    foreach ($this->remix_graph_mapping as $parent_program_data => $child_program_ids) {
      if (in_array($this->current_program_id, $child_program_ids, true)) {
        $all_parent_program_ids[] = explode(',', (string) $parent_program_data);
      }
    }

    $previous_parent_string = '';
    foreach ($all_parent_program_ids as $all_parent_program_id) {
      $parent_program_data = $all_parent_program_id;
      $parent_id = $parent_program_data[0];
      $current_parent_url = '' === $parent_program_data[1]
          ? '//app/project/'.$parent_id
          : 'https://scratch.mit.edu/projects/'.$parent_id.'/';
      $previous_parent_string = $this->generateRemixUrlsStringForMergedProgram($previous_parent_string,
        $current_parent_url);
    }

    $remix_url_string = $previous_parent_string;
    $program_xml_properties = $extracted_catrobat_file->getProjectXmlProperties();

    // NOTE: force using Catrobat language version 0.994 in order to allow multiple parents (see: RemixUpdater.php)
    if ($program_xml_properties->header->catrobatLanguageVersion < '0.994') {
      $program_xml_properties->header->catrobatLanguageVersion = '0.993';
    }

    $program_xml_properties->header->remixOf = '';
    $program_xml_properties->header->url = $remix_url_string;
    $extracted_catrobat_file->saveProjectXmlProperties();

    ++$this->current_program_id;

    return $extracted_catrobat_file;
  }

  public function generateRemixUrlsStringForMergedProgram(mixed $previous_parent_string, mixed $current_parent_url): string
  {
    if ('' == $previous_parent_string) {
      return $current_parent_url;
    }

    return 'PREVIOUS: '
      .RemixUrlIndicator::PREFIX_INDICATOR.$previous_parent_string.RemixUrlIndicator::SUFFIX_INDICATOR.', '
      .'NEXT: '
      .RemixUrlIndicator::PREFIX_INDICATOR.$current_parent_url.RemixUrlIndicator::SUFFIX_INDICATOR;
  }
}
