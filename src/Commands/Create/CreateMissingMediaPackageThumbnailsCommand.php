<?php

namespace App\Commands\Create;

use App\Catrobat\Services\MediaPackageFileRepository;
use Exception;
use ImagickException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Symfony Command to create missing media package thumbnails.
 */
class CreateMissingMediaPackageThumbnailsCommand extends Command
{
  protected static $defaultName = 'catrobat:create:media-package-thumbnails';
  private MediaPackageFileRepository $media_package_file_repository;

  public function __construct(MediaPackageFileRepository $media_package_file_repository)
  {
    parent::__construct();
    $this->media_package_file_repository = $media_package_file_repository;
  }

  protected function configure(): void
  {
    $this->setName('catrobat:create:media-package-thumbnails')
      ->setDescription('Creates missing thumbnails for images in media package.')
      ->addOption('force')
    ;
  }

  /**
   * @throws ImagickException
   * @throws Exception
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $username = posix_getpwuid(posix_geteuid())['name'];

    if (
      !$input->getOption('force') &&
      !in_array($username, ['www-data', 'apache', 'httpd', '_www', 'nginx'], true)
    ) {
      throw new Exception('Please run this command as web server user '.'(e.g. sudo -u www-data bin/console ...) or run with --force.');
    }

    $this->media_package_file_repository->createMissingThumbnails();

    return 0;
  }
}
