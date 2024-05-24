<?php

declare(strict_types=1);

namespace App\Project\CatrobatFile;

use App\DB\EntityRepository\Project\ProgramRepository;
use App\Project\CatrobatCode\CodeObject;
use App\Project\CatrobatCode\StatementFactory;
use App\Project\Remix\RemixData;
use App\Project\Remix\RemixUrlIndicator;
use App\Project\Remix\RemixUrlParsingState;
use Symfony\Component\Finder\Finder;

class ExtractedCatrobatFile
{
  protected \SimpleXMLElement $project_xml_properties;

  private array $xml_filenames;

  public function __construct(protected string $path, protected string $web_path, protected ?string $dir_hash)
  {
    if (!file_exists($path.'code.xml')) {
      throw new InvalidCatrobatFileException('errors.xml.missing', 507);
    }

    $content = file_get_contents($path.'code.xml');
    if ('' === $content || '0' === $content || false === $content) {
      throw new InvalidCatrobatFileException('errors.xml.invalid', 508);
    }

    $content = str_replace('&#x0;', '', $content);

    preg_match_all('@fileName=?[">](.*?)[<"]@', $content, $matches);
    $this->xml_filenames = count($matches) > 1 ? $matches[1] : [];
    $counter = count($this->xml_filenames);
    for ($i = 0; $i < $counter; ++$i) {
      $this->xml_filenames[$i] = $this->decodeXmlEntities($this->xml_filenames[$i]);
    }

    $xml = @simplexml_load_string($content);
    if (!$xml) {
      throw new InvalidCatrobatFileException('errors.xml.invalid', 508);
    }

    $this->project_xml_properties = $xml;
  }

  public function getName(): string
  {
    return (string) $this->project_xml_properties->header->programName;
  }

  /**
   * @psalm-suppress UndefinedPropertyAssignment
   */
  public function setName(string $name): void
  {
    $this->project_xml_properties->header->programName = $name;
  }

  public function isDebugBuild(): bool
  {
    if (!isset($this->project_xml_properties->header->applicationBuildType)) {
      return false; // old project do not have this field, + they should be release projects
    }

    return 'debug' === (string) $this->project_xml_properties->header->applicationBuildType;
  }

  public function getLanguageVersion(): string
  {
    return (string) $this->project_xml_properties->header->catrobatLanguageVersion;
  }

  public function getDescription(): string
  {
    return (string) $this->project_xml_properties->header->description;
  }

  /**
   * @psalm-suppress UndefinedPropertyAssignment
   */
  public function setDescription(string $description): void
  {
    $this->project_xml_properties->header->description = $description;
  }

  public function getNotesAndCredits(): string
  {
    return (string) $this->project_xml_properties->header->notesAndCredits;
  }

  /**
   * @psalm-suppress UndefinedPropertyAssignment
   */
  public function setNotesAndCredits(string $notesAndCredits): void
  {
    $this->project_xml_properties->header->notesAndCredits = $notesAndCredits;
  }

  public function getDirHash(): ?string
  {
    return $this->dir_hash;
  }

  public function getTags(): array
  {
    $tags = (string) $this->project_xml_properties->header->tags;
    if (strlen($tags) > 0) {
      return explode(',', (string) $this->project_xml_properties->header->tags);
    }

    return [];
  }

  public function getContainingImagePaths(): array
  {
    $finder = new Finder();
    $file_paths = [];

    if ($this->hasScenes()) {
      $dir_regex = $this->path.'/*/images/';
      $this->createDirectoryInSceneIfNotExist($this->path, $dir_regex, '/images');
      $finder->files()->in($dir_regex);
      foreach ($finder as $file) {
        $parts = explode($this->dir_hash.'/', (string) $file->getRealPath());
        $file_paths[] = '/'.$this->web_path.$parts[1];
      }
    } else {
      $directory = $this->path.'images/';
      $this->createDirectoryIfNotExist($directory);
      $finder->files()->in($directory);
      foreach ($finder as $file) {
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

    if ($this->hasScenes()) {
      $dir_regex = $this->path.'/*/sounds/';
      $this->createDirectoryInSceneIfNotExist($this->path, $dir_regex, '/sounds');
      $finder->files()->in($dir_regex);
      foreach ($finder as $file) {
        $parts = explode($this->dir_hash.'/', (string) $file->getRealPath());
        $file_paths[] = '/'.$this->web_path.$parts[1];
      }
    } else {
      $directory = $this->path.'sounds/';
      $this->createDirectoryIfNotExist($directory);
      $finder->files()->in($directory);
      foreach ($finder as $file) {
        $file_paths[] = '/'.$this->web_path.'sounds/'.$file->getFilename();
      }
    }

    return $file_paths;
  }

  public function getContainingStrings(): array
  {
    $xml = file_get_contents($this->path.'code.xml');
    $matches = [];
    preg_match_all('#>(.*[a-zA-Z].*)<#', (string) $xml, $matches);

    return array_unique($matches[1]);
  }

  /**
   * The Apps have a screenshot for every scene. However, we only need one for the project image.
   * This method goes through all possible locations to search a screenshot file that exists.
   */
  public function getScreenshotPath(): ?string
  {
    $finder = new Finder();
    $screenshots = iterator_to_array($finder->in($this->path)
      ->files()
      ->name(['manual_screenshot.png', 'automatic_screenshot.png'])
      ->sortByName()
      ->reverseSorting() // Priority: manual - automatic - screenshot
    );

    if (!$finder->hasResults()) {
      // Legacy screenshot.png support
      $screenshots = iterator_to_array($finder->in($this->path)->files()->name('screenshot.png'));
    }

    return $finder->hasResults() ? reset($screenshots)->getPathname() : null;
  }

  public function getApplicationVersion(): string
  {
    return (string) $this->project_xml_properties->header->applicationVersion;
  }

  public function getRemixUrlsString(): string
  {
    return trim((string) $this->project_xml_properties->header->url);
  }

  public function getRemixMigrationUrlsString(): string
  {
    return trim((string) $this->project_xml_properties->header->remixOf);
  }

  public function getPath(): string
  {
    return $this->path;
  }

  public function getWebPath(): string
  {
    return $this->web_path;
  }

  public function getProjectXmlProperties(): \SimpleXMLElement
  {
    return $this->project_xml_properties;
  }

  /**
   * @throws \Exception
   */
  public function saveProjectXmlProperties(): void
  {
    $file_overwritten = $this->project_xml_properties->asXML($this->path.'code.xml');
    if (!$file_overwritten) {
      throw new \Exception("Can't overwrite code.xml file");
    }

    $xml_string = file_get_contents($this->path.'code.xml');

    $xml_string = preg_replace('#<receivedMessage>(.*)&lt;-&gt;ANYTHING</receivedMessage>#',
      '<receivedMessage>$1&lt;&#x0;-&#x0;&gt;&#x0;ANYTHING&#x0;</receivedMessage>', $xml_string);

    $xml_string = preg_replace('#<receivedMessage>(.*)&lt;-&gt;(.*)</receivedMessage>#',
      '<receivedMessage>$1&lt;&#x0;-&#x0;&gt;$2</receivedMessage>', (string) $xml_string);

    if (null != $xml_string) {
      file_put_contents($this->path.'code.xml', $xml_string);
    }
  }

  /**
   * based on: http://stackoverflow.com/a/27295688.
   */
  public function getRemixesData(string $project_id, bool $is_initial_version, ProgramRepository $project_repository, bool $migration_mode = false): array
  {
    $remixes_string = $migration_mode ? $this->getRemixMigrationUrlsString() : $this->getRemixUrlsString();
    $state = RemixUrlParsingState::STARTING;
    $extracted_remixes = [];
    $temp = '';

    for ($index = 0; $index < strlen($remixes_string); ++$index) {
      $current_character = $remixes_string[$index];

      if (RemixUrlIndicator::PREFIX_INDICATOR === $current_character) {
        if (RemixUrlParsingState::STARTING == $state) {
          $state = RemixUrlParsingState::BETWEEN;
        } elseif (RemixUrlParsingState::TOKEN == $state) {
          $temp = '';
          $state = RemixUrlParsingState::BETWEEN;
        }
      } elseif (RemixUrlIndicator::SUFFIX_INDICATOR === $current_character) {
        if (RemixUrlParsingState::TOKEN == $state) {
          $extracted_url = trim($temp);
          if (!str_contains($extracted_url, RemixUrlIndicator::SEPARATOR) && strlen($extracted_url) > 0) {
            $extracted_remixes[] = new RemixData($extracted_url);
          }

          $temp = '';
          $state = RemixUrlParsingState::BETWEEN;
        }
      } else {
        $state = RemixUrlParsingState::TOKEN;
        $temp .= $current_character;
      }
    }

    if (0 == count($extracted_remixes) && strlen($remixes_string) > 0
      && !str_contains($remixes_string, RemixUrlIndicator::SEPARATOR)) {
      $extracted_remixes[] = new RemixData($remixes_string);
    }

    $unique_remixes = [];
    foreach ($extracted_remixes as $remix_data) {
      /** @var RemixData $remix_data */
      if ('' === $remix_data->getProjectId()) {
        continue;
      }

      if (!$remix_data->isScratchProject()) {
        // projects can't be a remix of them self
        if ($remix_data->getProjectId() === $project_id) {
          continue;
        }

        // This id/date back and forth is for the legacy spec tests.
        // Real world scenarios should always be in the date scenario
        $parent_upload_time = $remix_data->getProjectId();
        $child_upload_time = $project_id;

        $parent = null;
        $child = null;
        $parent = $project_repository->find($remix_data->getProjectId());
        $child = $project_repository->find($project_id);

        if (null !== $parent && null !== $child) {
          $parent_upload_time = $parent->getUploadedAt();
          $child_upload_time = $child->getUploadedAt();
        }

        // case initial version: child must be newer than parent
        if ($is_initial_version && $child_upload_time < $parent_upload_time) {
          continue;
        }
      }

      $unique_key = $remix_data->getProjectId().'_'.$remix_data->isScratchProject();
      if (!array_key_exists($unique_key, $unique_remixes)) {
        $unique_remixes[$unique_key] = $remix_data;
      }
    }

    return array_values($unique_remixes);
  }

  public function getContainingCodeObjects(): array
  {
    $objects = [];
    $objectList = $this->getCodeObjects();
    foreach ($objectList as $object) {
      $objects = $this->addObjectsToArray($objects, $object->getCodeObjectsRecursively());
    }

    return $objectList + $objects;
  }

  public function getCodeObjects(): array
  {
    $objects = [];
    $objectList = $this->project_xml_properties->objectList->children();
    foreach ($objectList as $object) {
      $newObject = $this->getObject($object);
      if (null != $newObject) {
        $objects[] = $newObject;
      }
    }

    return $objects;
  }

  public function hasScenes(): bool
  {
    return 0 !== count($this->project_xml_properties->xpath('//scenes'));
  }

  private function getObject(\SimpleXMLElement $objectTree): ?CodeObject
  {
    $factory = new StatementFactory();

    return $factory->createObject($objectTree);
  }

  private function addObjectsToArray(array $objects, mixed $objectsToAdd): array
  {
    foreach ($objectsToAdd as $object) {
      $objects[] = $object;
    }

    return $objects;
  }

  private function createDirectoryInSceneIfNotExist(string $base_path, string $dir_regex, string $dir_name): void
  {
    preg_match('@'.$dir_regex.'@', $dir_regex, $scene_names);

    /** @var string $scene_name */
    foreach ($scene_names as $scene_name) {
      $directory = $base_path.$scene_name.$dir_name;
      if (!file_exists($directory)) {
        mkdir($directory, 0777, true);
      }
    }
  }

  private function createDirectoryIfNotExist(string $directory): void
  {
    if (!file_exists($directory)) {
      mkdir($directory, 0777, true);
    }
  }

  private function decodeXmlEntities(string $input): string
  {
    $match = ['/&amp;/', '/&gt;/', '/&lt;/', '/&apos;/', '/&quot;/'];
    $replace = ['&', '<', '>', "'", '"'];

    return preg_replace($match, $replace, $input);
  }
}
