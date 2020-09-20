<?php

namespace App\Catrobat\Services;

use App\Catrobat\CatrobatCode\CodeObject;
use App\Catrobat\CatrobatCode\StatementFactory;
use App\Catrobat\Exceptions\Upload\InvalidXmlException;
use App\Catrobat\Exceptions\Upload\MissingXmlException;
use App\Repository\ProgramRepository;
use SimpleXMLElement;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;

class ExtractedCatrobatFile
{
  protected string $path;

  protected string $web_path;

  protected ?string $dir_hash;

  protected SimpleXMLElement $program_xml_properties;

  private array $xml_filenames;

  public function __construct(string $base_dir, string $base_path, ?string $dir_hash)
  {
    $this->path = $base_dir;
    $this->dir_hash = $dir_hash;
    $this->web_path = $base_path;

    if (!file_exists($base_dir.'code.xml'))
    {
      throw new MissingXmlException();
    }

    $content = file_get_contents($base_dir.'code.xml');
    if (!$content)
    {
      throw new InvalidXmlException();
    }
    $content = str_replace('&#x0;', '', $content, $count);
    preg_match_all('@fileName="(.*?)"@', $content, $matches);
    $this->xml_filenames = sizeof($matches) > 1 ? $matches[1] : [];
    $xml = @simplexml_load_string($content);
    if (!$xml)
    {
      throw new InvalidXmlException();
    }
    $this->program_xml_properties = $xml;
  }

  public function getName(): string
  {
    return (string) $this->program_xml_properties->header->programName;
  }

  public function isDebugBuild(): bool
  {
    if (!isset($this->program_xml_properties->header->applicationBuildType))
    {
      return false; // old program do not have this field, + they should be release programs
    }

    return 'debug' === (string) $this->program_xml_properties->header->applicationBuildType;
  }

  public function getLanguageVersion(): string
  {
    return (string) $this->program_xml_properties->header->catrobatLanguageVersion;
  }

  public function getDescription(): string
  {
    return (string) $this->program_xml_properties->header->description;
  }

  public function getDirHash(): ?string
  {
    return $this->dir_hash;
  }

  public function getTags(): array
  {
    $tags = (string) $this->program_xml_properties->header->tags;
    if (strlen($tags) > 0)
    {
      return explode(',', (string) $this->program_xml_properties->header->tags);
    }

    return [];
  }

  public function getContainingImagePaths(): array
  {
    $finder = new Finder();
    $file_paths = [];

    if ($this->hasScenes())
    {
      $dir_regex = $this->path.'/*/images/';
      $this->createDirectoryInSceneIfNotExist($this->path, $dir_regex, '/images');
      $finder->files()->in($dir_regex);
      foreach ($finder as $file)
      {
        $parts = explode($this->dir_hash.'/', $file->getRealPath());
        $file_paths[] = '/'.$this->web_path.$parts[1];
      }
    }
    else
    {
      $directory = $this->path.'images/';
      $this->createDirectoryIfNotExist($directory);
      $finder->files()->in($directory);
      foreach ($finder as $file)
      {
        $file_paths[] = '/'.$this->web_path.'images/'.$file->getFilename();
      }
    }

    return $file_paths;
  }

  public function isFileMentionedInXml(string $filename): bool
  {
    return in_array($filename, $this->xml_filenames, true);
  }

  public function getContainingSoundPaths(): array
  {
    $finder = new Finder();
    $file_paths = [];

    if ($this->hasScenes())
    {
      $dir_regex = $this->path.'/*/sounds/';
      $this->createDirectoryInSceneIfNotExist($this->path, $dir_regex, '/sounds');
      $finder->files()->in($dir_regex);
      foreach ($finder as $file)
      {
        $parts = explode($this->dir_hash.'/', $file->getRealPath());
        $file_paths[] = '/'.$this->web_path.$parts[1];
      }
    }
    else
    {
      $directory = $this->path.'sounds/';
      $this->createDirectoryIfNotExist($directory);
      $finder->files()->in($directory);
      foreach ($finder as $file)
      {
        $file_paths[] = '/'.$this->web_path.'sounds/'.$file->getFilename();
      }
    }

    return $file_paths;
  }

  public function getContainingStrings(): array
  {
    $xml = file_get_contents($this->path.'code.xml');
    $matches = [];
    preg_match_all('#>(.*[a-zA-Z].*)<#', $xml, $matches);

    return array_unique($matches[1]);
  }

  public function getScreenshotPath(): ?string
  {
    $screenshot_path = null;
    if (is_file($this->path.'screenshot.png'))
    {
      $screenshot_path = $this->path.'screenshot.png';
    }
    elseif (is_file($this->path.'manual_screenshot.png'))
    {
      $screenshot_path = $this->path.'manual_screenshot.png';
    }
    elseif (is_file($this->path.'automatic_screenshot.png'))
    {
      $screenshot_path = $this->path.'automatic_screenshot.png';
    }
    $finder = new Finder();

    if (null === $screenshot_path)
    {
      $fu = $finder->in($this->path)->files()->name('manual_screenshot.png');

      /** @var File $file */
      foreach ($fu as $file)
      {
        $screenshot_path = $file->getPathname();
        break;
      }
    }
    if (null === $screenshot_path)
    {
      $fu = $finder->in($this->path)->files()->name('automatic_screenshot.png');

      /** @var File $file */
      foreach ($fu as $file)
      {
        $screenshot_path = $file->getPathname();
        break;
      }
    }

    return $screenshot_path;
  }

  public function getApplicationVersion(): string
  {
    return (string) $this->program_xml_properties->header->applicationVersion;
  }

  public function getRemixUrlsString(): string
  {
    return trim((string) $this->program_xml_properties->header->url);
  }

  public function getRemixMigrationUrlsString(): string
  {
    return trim((string) $this->program_xml_properties->header->remixOf);
  }

  public function getPath(): string
  {
    return $this->path;
  }

  public function getWebPath(): string
  {
    return $this->web_path;
  }

  public function getProgramXmlProperties(): SimpleXMLElement
  {
    return $this->program_xml_properties;
  }

  public function saveProgramXmlProperties(): void
  {
    $this->program_xml_properties->asXML($this->path.'code.xml');

    $xml_string = file_get_contents($this->path.'code.xml');

    $xml_string = preg_replace('#<receivedMessage>(.*)&lt;-&gt;ANYTHING</receivedMessage>#',
      '<receivedMessage>$1&lt;&#x0;-&#x0;&gt;&#x0;ANYTHING&#x0;</receivedMessage>', $xml_string);

    $xml_string = preg_replace('#<receivedMessage>(.*)&lt;-&gt;(.*)</receivedMessage>#',
      '<receivedMessage>$1&lt;&#x0;-&#x0;&gt;$2</receivedMessage>', $xml_string);

    if (null != $xml_string)
    {
      file_put_contents($this->path.'code.xml', $xml_string);
    }
  }

  /**
   * based on: http://stackoverflow.com/a/27295688.
   */
  public function getRemixesData(string $program_id, bool $is_initial_version, ProgramRepository $program_repository, bool $migration_mode = false): array
  {
    $remixes_string = $migration_mode ? $this->getRemixMigrationUrlsString() : $this->getRemixUrlsString();
    $state = RemixUrlParsingState::STARTING;
    $extracted_remixes = [];
    $temp = '';

    for ($index = 0; $index < strlen($remixes_string); ++$index)
    {
      $current_character = $remixes_string[$index];

      if (RemixUrlIndicator::PREFIX_INDICATOR == $current_character)
      {
        if (RemixUrlParsingState::STARTING == $state)
        {
          $state = RemixUrlParsingState::BETWEEN;
        }
        elseif (RemixUrlParsingState::TOKEN == $state)
        {
          $temp = '';
          $state = RemixUrlParsingState::BETWEEN;
        }
      }
      elseif (RemixUrlIndicator::SUFFIX_INDICATOR == $current_character)
      {
        if (RemixUrlParsingState::TOKEN == $state)
        {
          $extracted_url = trim($temp);
          if (false === strpos($extracted_url, RemixUrlIndicator::SEPARATOR) && strlen($extracted_url) > 0)
          {
            $extracted_remixes[] = new RemixData($extracted_url);
          }
          $temp = '';
          $state = RemixUrlParsingState::BETWEEN;
        }
      }
      else
      {
        $state = RemixUrlParsingState::TOKEN;
        $temp .= $current_character;
      }
    }

    if (0 == count($extracted_remixes) && strlen($remixes_string) > 0 &&
      false === strpos($remixes_string, RemixUrlIndicator::SEPARATOR))
    {
      $extracted_remixes[] = new RemixData($remixes_string);
    }

    $unique_remixes = [];
    foreach ($extracted_remixes as $remix_data)
    {
      /** @var RemixData $remix_data */
      if ('' === $remix_data->getProgramId())
      {
        continue;
      }

      if (!$remix_data->isScratchProgram())
      {
        // projects can't be a remix of them self
        if ($remix_data->getProgramId() === $program_id)
        {
          continue;
        }

        // This id/date back and forth is for the legacy spec tests.
        // Real world scenarios should always be in the date scenario
        $parent_upload_time = $remix_data->getProgramId();
        $child_upload_time = $program_id;

        $parent = null;
        $child = null;

        if (null !== $program_repository)
        {
          $parent = $program_repository->find($remix_data->getProgramId());
          $child = $program_repository->find($program_id);
        }

        if (null !== $parent && null !== $child)
        {
          $parent_upload_time = $parent->getUploadedAt();
          $child_upload_time = $child->getUploadedAt();
        }

        // case initial version: child must be newer than parent
        if ($is_initial_version && $child_upload_time < $parent_upload_time)
        {
          continue;
        }
      }

      $unique_key = $remix_data->getProgramId().'_'.$remix_data->isScratchProgram();
      if (!array_key_exists($unique_key, $unique_remixes))
      {
        $unique_remixes[$unique_key] = $remix_data;
      }
    }

    return array_values($unique_remixes);
  }

  public function getContainingCodeObjects(): array
  {
    $objects = [];
    $objectList = $this->getCodeObjects();
    foreach ($objectList as $object)
    {
      $objects = $this->addObjectsToArray($objects, $object->getCodeObjectsRecursively());
    }

    return $objectList + $objects;
  }

  public function getCodeObjects(): array
  {
    $objects = [];
    $objectList = $this->program_xml_properties->objectList->children();
    foreach ($objectList as $object)
    {
      $newObject = $this->getObject($object);
      if (null != $newObject)
      {
        $objects[] = $newObject;
      }
    }

    return $objects;
  }

  public function hasScenes(): bool
  {
    return 0 !== (is_countable($this->program_xml_properties->xpath('//scenes')) ? count($this->program_xml_properties->xpath('//scenes')) : 0);
  }

  private function getObject(SimpleXMLElement $objectTree): ?CodeObject
  {
    $factory = new StatementFactory();

    return $factory->createObject($objectTree);
  }

  /**
   * @param mixed $objectsToAdd
   */
  private function addObjectsToArray(array $objects, $objectsToAdd): array
  {
    foreach ($objectsToAdd as $object)
    {
      $objects[] = $object;
    }

    return $objects;
  }

  private function createDirectoryInSceneIfNotExist(string $base_path, string $dir_regex, string $dir_name): void
  {
    preg_match('@'.$dir_regex.'@', $dir_regex, $scene_names);

    /** @var string $scene_name */
    foreach ($scene_names as $scene_name)
    {
      $directory = $base_path.$scene_name.$dir_name;
      if (!file_exists($directory))
      {
        mkdir($directory, 0777, true);
      }
    }
  }

  private function createDirectoryIfNotExist(string $directory): void
  {
    if (!file_exists($directory))
    {
      mkdir($directory, 0777, true);
    }
  }
}
