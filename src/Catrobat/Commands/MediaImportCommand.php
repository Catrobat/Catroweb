<?php

namespace App\Catrobat\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use App\Catrobat\Exceptions\InvalidCatrobatFileException;
use App\Catrobat\Requests\AddMediaFileRequest;
use App\Catrobat\Requests\AddMediaCategoryRequest;
use App\Catrobat\Requests\AddMediaPackageRequest;
use App\Entity\MediaManager;


/**
 * Class MediaImportCommand
 * @package App\Catrobat\Commands
 */
class MediaImportCommand extends Command
{
  /**
   * @var Filesystem
   */
  private $file_system;


  /**
   * @var MediaManager
   */
  private $media_manager;

  /**
   * ProgramImportCommand constructor.
   *
   * @param Filesystem   $filesystem
   * @param MediaManager $media_manager
   */
  public function __construct(Filesystem $filesystem, MediaManager $media_manager)
  {
    parent::__construct();
    $this->file_system = $filesystem;
    $this->media_manager = $media_manager;
  }

  /**
   *
   */
  protected function configure()
  {
    $this->setName('catrobat:media:import')
      ->setDescription('Import media files from a given directory to the application')
      ->addArgument('directory', InputArgument::REQUIRED, 'Directory containing catrobat media files for import')
      ->addArgument('more-media', InputArgument::OPTIONAL, 'Option of adding more media files');
  }

  /**
   * @param InputInterface  $input
   * @param OutputInterface $output
   *
   * @return int|void
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $directory = $input->getArgument('directory');
    $this->downloadMediaLibraries($directory, 20, $output);

    $server_json = json_decode(file_get_contents('https://share.catrob.at/app/api/media/category/json'), true);
    foreach ($server_json['data'] as $category)
    {
      try
      {
        $add_media_request = new AddMediaCategoryRequest($category['id'], $category['name'], '1');
        $category = $this->media_manager->addMediaCategory($add_media_request);
        $output->writeln('Added media file <' . $category->getName() . '>');
      } catch (InvalidCatrobatFileException $e)
      {
        $output->writeln('FAILED to add media!');
        $output->writeln($e->getMessage() . ' (' . $e->getCode() . ')');
      }
    }
    $add_media_package = new AddMediaPackageRequest('1', 'Looks', 'looks');
    $this->media_manager->addMediaPackage($add_media_package);
    $add_media_request = new AddMediaPackageRequest('1', 'Sounds', 'sounds');
    $this->media_manager->addMediaPackage($add_media_request);   
  }
/**
   * @param                 $dir
   * @param OutputInterface $output
   */
  private function downloadMediaLibraries($dir, $max_number, OutputInterface $output)
  {
    $output->writeln('Downloading Media Files...');

    $this->downloadMediaFiles('https://share.catrob.at/app/api/media/package/Sounds/json',
      $dir, $max_number / 2, $output);

    $this->downloadMediaFiles('https://share.catrob.at/app/api/media/package/Looks/json',
      $dir, $max_number / 2, $output);
  }


  private function downloadMediaFiles($path, $dir, $max_number, OutputInterface $output)
  {
    $server_json = json_decode(file_get_contents($path), true);
    $number = 0;
    foreach ($server_json as $media)
    {
      try
      {
        $category = $this->media_manager->findCategory($media);
        $output->writeln($category->getName());
        $add_media_request = new AddMediaFileRequest($media['name'], $media['author'], $media['id'], $category, $media['extension'], $media['download_url'], $media['flavor']);
        $media = $this->media_manager->addMedia($add_media_request);
        $output->writeln('Added media file <' . $media->getName() . '>');
      } catch (InvalidCatrobatFileException $e)
      {
        $output->writeln('FAILED to add media!');
        $output->writeln($e->getMessage() . ' (' . $e->getCode() . ')');
      }
      if ($number >= $max_number)
      {
        break;
      }
      $this->downloadMedia($dir, $media, $output);
      $number++;
    }
  }

  private function downloadMedia($dir, $media, OutputInterface $output)
  {
    $url = 'https://share.catrob.at' . $media['download_url'];
    $name = $dir . $media['name'] . '.media';
    $output->writeln('Downloading ' . $name);
    try
    {
      file_put_contents($name, file_get_contents($url));
    } catch (Exception $e)
    {
      $output->writeln("File <" . $url . "> returned error 500, continuing...");
    }
  }
}
