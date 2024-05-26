<?php

declare(strict_types=1);

namespace App\System\Commands\Create;

use App\DB\EntityRepository\MediaLibrary\MediaPackageFileRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'catrobat:create:media-package-thumbnails', description: 'Creates missing thumbnails for images in media package.')]
class CreateMissingMediaPackageThumbnailsCommand extends Command
{
  public function __construct(private readonly MediaPackageFileRepository $media_package_file_repository)
  {
    parent::__construct();
  }

  #[\Override]
  protected function configure(): void
  {
    $this
      ->addOption('force')
    ;
  }

  /**
   * @throws \ImagickException
   * @throws \Exception
   */
  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $username = posix_getpwuid(posix_geteuid())['name'];

    if (
      !$input->getOption('force')
      && !in_array($username, ['www-data', 'apache', 'httpd', '_www', 'nginx'], true)
    ) {
      throw new \Exception('Please run this command as web server user (e.g. sudo -u www-data bin/console ...) or run with --force.');
    }

    $this->media_package_file_repository->createMissingThumbnails();

    return 0;
  }
}
