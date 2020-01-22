<?php

namespace App\Catrobat\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use App\Entity\MediaPackageFile;
use App\Entity\MediaPackageCategory;
use App\Entity\MediaPackage;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Types\GuidType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\File\File;
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
  const REMIX_GRAPH_NO_LAYOUT = 0;

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
   * @param Filesystem                      $filesystem
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
    $this->setName('catrobat:mediaimport')
      ->setDescription('Import media files from a given directory to the application')
      ->addArgument('directory', InputArgument::REQUIRED, 'Directory containing catrobat files for import');
  }

  /**
   * @param InputInterface  $input
   * @param OutputInterface $output
   * @param MediaPackageFile $media
   * @param GuidType $id
   * @return int|void|null
   * @throws \Exception
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $server_json = json_decode(file_get_contents('https://share.catrob.at/app/api/media/category/json'), true);
     
    foreach ($server_json['data'] as $category)
    {
    try
    {
      $add_media_request = new AddMediaCategoryRequest($category['id'],$category['name'],'1');
      $category = $this->media_manager->addMediaCategory($add_media_request);
      $output->writeln('Added media file <' . $category->getName(). '>');
    } catch (InvalidCatrobatFileException $e)
    {
     $output->writeln('FAILED to add media!');
     $output->writeln($e->getMessage() . ' (' . $e->getCode() . ')');
    }
    }
    $add_media_package = new AddMediaPackageRequest('1','Looks','https://share.catrob.at/pocketcode/media-library/looks');
    $package = $this->media_manager->addMediaPackage($add_media_package);

    $server_json = json_decode(file_get_contents('https://share.catrob.at/app/api/media/package/Looks/json'), true);

    foreach ($server_json as $media)
    {      
    try
    {
     $category = $this->media_manager->findCategory($media);
     $output->writeln($category->getName());
     $add_media_request = new AddMediaFileRequest($media['name'],$media['author'],$media['id'],$category,$media['extension'],$media['download_url'],$media['flavor']);
     $media = $this->media_manager->addMedia($add_media_request);
     $output->writeln('Added media file <' . $media->getName(). '>');
    } catch (InvalidCatrobatFileException $e)
    {
     $output->writeln('FAILED to add media!');
     $output->writeln($e->getMessage() . ' (' . $e->getCode() . ')');
    }
    } 

    $add_media_request = new AddMediaPackageRequest('1','Sounds','https://share.catrob.at/pocketcode/media-library/sounds');
    $media = $this->media_manager->addMediaPackage($add_media_request);

    $server_json = json_decode(file_get_contents('https://share.catrob.at/app/api/media/package/Sounds/json'), true);
  
    foreach ($server_json as $media)
    {
    try
    {
     $category = $this->media_manager->findCategory($media);
     $output->writeln($category->getName());
     $add_media_request = new AddMediaFileRequest($media['name'],$media['author'],$media['id'],$category,$media['extension'],$media['download_url'],$media['flavor']);
     $media = $this->media_manager->addMedia($add_media_request);
     $output->writeln('Added media file <' . $media->getName(). '>');
    } catch (InvalidCatrobatFileException $e)
    {
     $output->writeln('FAILED to add media!');
     $output->writeln($e->getMessage() . ' (' . $e->getCode() . ')');
    }
    }                    
  }
}
