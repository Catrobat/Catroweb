<?php

namespace App\Catrobat\Commands;

use App\Catrobat\Services\MediaPackageFileRepository;
use App\Repository\MediaPackageCategoryRepository;
use App\Repository\MediaPackageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\File;

/**
 * CreateMediaPackageSamplesCommand class. Used for inserting sample entities in the Media Library.
 * These are:.
 *
 * - MediaPackage: Containing MediaPackageCategories
 * - MediaPackageCategory: Containing MediaPackageFiles
 * - MediaPackageFile: A single media file (e.g. picture, audio ...)
 *
 *                                          Media Library example:
 *
 *                          Media Package 1                           Media Package 2
 *                       /                 \                                |
 *               Category 1               Category 2                    Category 3
 *              /     |    \              /        \                        |
 *         File 1  File 2  File 3      File 4    File 5                  File 6
 */
class CreateMediaPackageSamplesCommand extends Command
{
  protected static $defaultName = 'catrobat:create:media-packages-samples';
  private MediaPackageRepository $media_package_repo;
  private MediaPackageCategoryRepository $media_package_category_repo;
  private MediaPackageFileRepository $media_package_file_repo;
  private ParameterBagInterface $parameter_bag;

  /**
   * CreateMediaPackageSamplesCommand constructor.
   */
  public function __construct(MediaPackageRepository $media_package_repo, MediaPackageCategoryRepository $media_package_category_repo,
                              MediaPackageFileRepository $media_package_file_repo, ParameterBagInterface $parameter_bag)
  {
    parent::__construct();

    $this->media_package_repo = $media_package_repo;
    $this->media_package_category_repo = $media_package_category_repo;
    $this->media_package_file_repo = $media_package_file_repo;
    $this->parameter_bag = $parameter_bag;
  }

  protected function configure(): void
  {
    $this->setName($this::$defaultName)
      ->setDescription('create sample Media Packages')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $sample_pckg_path = $this->parameter_bag->get('catrobat.mediapackage.sample.path');

    /*
     * Creating MediaPackage Looks
     */
    $package_looks = $this->media_package_repo->createMediaPackage('Looks', 'looks');

    // Creating MediaPackageCategory Animals and filling it with MediaPackageFiles
    $category_pocket_family = $this->media_package_category_repo->createMediaPackageCategory('Pocket Family', new ArrayCollection([$package_looks]));
    $this->media_package_file_repo->createMediaPackageFile('Penguin', new File($sample_pckg_path.'Looks/Pocket Family/Penguin.jpeg'), $category_pocket_family, 'pocketcode', 'Catrobat');
    $this->media_package_file_repo->createMediaPackageFile('Elephant', new File($sample_pckg_path.'Looks/Pocket Family/Elephant.jpeg'), $category_pocket_family, 'pocketcode', 'Catrobat');
    $this->media_package_file_repo->createMediaPackageFile('Panda', new File($sample_pckg_path.'Looks/Pocket Family/Panda.jpeg'), $category_pocket_family, 'pocketcode', 'Catrobat');

    // Creating MediaPackageCategory People and filling it with MediaPackageFiles
    $category_people = $this->media_package_category_repo->createMediaPackageCategory('People', new ArrayCollection([$package_looks]));
    $this->media_package_file_repo->createMediaPackageFile('Boy', new File($sample_pckg_path.'Looks/People/Boy.jpeg'), $category_people, 'pocketcode', 'Catrobat');
    $this->media_package_file_repo->createMediaPackageFile('Girl', new File($sample_pckg_path.'Looks/People/Girl.jpeg'), $category_people, 'pocketcode', 'Catrobat');

    // Creating MediaPackageCategory Luna and filling it with MediaPackageFiles
    $category_luna = $this->media_package_category_repo->createMediaPackageCategory('ThemeSpecial Luna & Cat', new ArrayCollection([$package_looks]));
    $this->media_package_file_repo->createMediaPackageFile('Luna Cat', new File($sample_pckg_path.'Looks/Luna/Luna-Cat.png'), $category_luna, 'luna', 'Catrobat');
    $this->media_package_file_repo->createMediaPackageFile('Luna Girl', new File($sample_pckg_path.'Looks/Luna/Luna-Girl.jpeg'), $category_luna, 'luna', 'Catrobat');

    /*
     * Creating MediaPackage Sounds
     */
    $package_looks = $this->media_package_repo->createMediaPackage('Sounds', 'sounds');

    // Creating MediaPackageCategory Animals and filling it with MediaPackageFiles
    $category_animal_sounds = $this->media_package_category_repo->createMediaPackageCategory('Animals', new ArrayCollection([$package_looks]));
    $this->media_package_file_repo->createMediaPackageFile('Owl', new File($sample_pckg_path.'Sounds/Animals/Owl.wav'), $category_animal_sounds, 'pocketcode', 'Catrobat');
    $this->media_package_file_repo->createMediaPackageFile('SeaLion', new File($sample_pckg_path.'Sounds/Animals/SeaLion.mpga'), $category_animal_sounds, 'pocketcode', 'Catrobat');

    // Creating MediaPackageCategory Machines and filling it with MediaPackageFiles
    $category_machine_sounds = $this->media_package_category_repo->createMediaPackageCategory('Machines', new ArrayCollection([$package_looks]));
    $this->media_package_file_repo->createMediaPackageFile('Plane', new File($sample_pckg_path.'Sounds/Machines/plane.mpga'), $category_machine_sounds, 'pocketcode', 'Catrobat');
    $this->media_package_file_repo->createMediaPackageFile('Ufo', new File($sample_pckg_path.'Sounds/Machines/ufo.wav'), $category_machine_sounds, 'pocketcode', 'Catrobat');

    return 0;
  }
}
