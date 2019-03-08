<?php

namespace App\Catrobat\Commands;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Symfony Command to create missing media package thumbnails
 *
 * @package App\Catrobat\Commands
 */
class CreateMissingMediaPackageThumbnailsCommand extends ContainerAwareCommand
{

  /**
   * Configures the current command.
   */
  protected function configure()
  {
    $this->setName('catrobat:create:media-package-thumbnails')
      ->setDescription('Creates missing thumbnails for images in media package.')
      ->addOption("force");
  }

  /**
   * Executes the current command.
   *
   * @param InputInterface $input
   * @param OutputInterface $output [optional]
   *
   * @return null|int null or 0 if everything went fine, or an error code
   * @throws \Exception
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $username = posix_getpwuid(posix_geteuid())['name'];

    if (
      !$input->getOption("force") &&
      !in_array($username, ["www-data", "apache", "httpd", "_www", "nginx"])
    )
    {
      throw new \Exception("Please run this command as web server user " .
        "(e.g. sudo -u www-data bin/console ...) or run with --force.");
    }

    $this->getContainer()->get('mediapackagefilerepository')->createMissingThumbnails();

    return null;
  }
} 