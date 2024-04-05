<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser;

class ParsedObjectAsset
{
  protected ?string $name = null;

  protected ?string $file_name = null;

  public function __construct(protected \SimpleXMLElement $asset_xml_properties)
  {
    $this->name = $asset_xml_properties[Constants::NAME_ATTRIBUTE]?->__toString();
    $this->file_name = $this->extractFileName($asset_xml_properties);
  }

  public function getName(): ?string
  {
    return $this->name;
  }

  public function getFileName(): ?string
  {
    return $this->file_name;
  }

  private function extractFileName(\SimpleXMLElement $asset_xml_properties): string
  {
    // different catrobat versions: fileName is either in the attributes (old) or a separate member (new)
    $file_name = $asset_xml_properties['fileName'];
    if ('' === $this->file_name) {
      $file_name = $asset_xml_properties->fileName;
    }

    return rawurlencode($file_name?->__toString() ?? '');
  }
}
