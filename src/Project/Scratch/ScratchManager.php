<?php

declare(strict_types=1);

namespace App\Project\Scratch;

use App\DB\Entity\Flavor;
use App\DB\Entity\Project\Project;
use App\Project\AddProjectRequest;
use App\Project\ProjectManager;
use App\Project\Scratch\Converter\ScratchConversionException;
use App\Project\Scratch\Converter\ScratchFetcher;
use App\Project\Scratch\Converter\ScratchToCatrobatConverter;
use App\User\UserManager;
use App\Utils\TimeUtils;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Imports a public Scratch project as a real Pocket Code project: converted, then published through the normal
 * upload pipeline (malware scan, sanitizing, validation) under a shadow account of its Scratch author.
 */
class ScratchManager
{
  public function __construct(
    private readonly ProjectManager $project_manager,
    private readonly UserManager $user_manager,
    private readonly ScratchFetcher $fetcher,
    private readonly ScratchToCatrobatConverter $converter,
    #[Autowire('%kernel.project_dir%/var/scratch-converter.lock')]
    private readonly string $lock_file,
  ) {
  }

  /**
   * @param callable(): void $before_conversion called only when a conversion is actually needed (rate limiting)
   *
   * @throws ScratchConversionException with a message that can be shown to the user
   */
  public function importProject(int $scratch_id, callable $before_conversion, ?string $ip): Project
  {
    $metadata = $this->fetcher->fetchMetadata($scratch_id);
    $modified = TimeUtils::dateTimeFromScratch((string) ($metadata['history']['modified'] ?? ''));

    /** @var Project|null $existing */
    $existing = $this->project_manager->findOneByScratchId($scratch_id);
    $up_to_date = null !== $existing && $existing->getFilesize() > 0
      && (!$modified instanceof \DateTime || $existing->getLastModifiedAt()->getTimestamp() >= $modified->getTimestamp());
    if ($up_to_date) {
      return $existing;
    }

    $before_conversion();
    set_time_limit(120);

    // ponytail: one conversion at a time per server; a job queue if people regularly see "busy".
    $lock = fopen($this->lock_file, 'c');
    if (false === $lock || !flock($lock, LOCK_EX | LOCK_NB)) {
      throw new ScratchConversionException('Another Scratch project is being converted right now. Please try again in a minute.');
    }

    $temp = (string) tempnam(sys_get_temp_dir(), 'scratch');
    $archive = $temp.'.catrobat';
    try {
      $project_json = $this->fetcher->fetchProject($scratch_id, $metadata);
      $this->converter->convert($scratch_id, $project_json, $metadata, $this->fetcher->fetchAssets($project_json), $this->fetcher->fetchThumbnail($scratch_id), $archive);

      $user = $existing?->getUser() ?? $this->user_manager->createUserFromScratch($metadata['author'] ?? []);
      if (null === $user) {
        throw new ScratchConversionException('The Scratch author could not be resolved.');
      }

      $project = $this->project_manager->addProject(new AddProjectRequest($user, new File($archive), $ip, null, Flavor::POCKETCODE, $existing?->getId()));
      if (null === $project) {
        throw new ScratchConversionException('The converted project could not be published.');
      }

      $project->setScratchId($scratch_id);
      $project->setLastModifiedAt($modified instanceof \DateTime ? $modified : TimeUtils::getDateTime());
      $this->project_manager->save($project);

      return $project;
    } finally {
      @unlink($archive);
      @unlink($temp);
      flock($lock, LOCK_UN);
      fclose($lock);
    }
  }
}
