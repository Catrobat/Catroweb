<?php

namespace App\Commands\Create;

use App\Catrobat\Services\MediaPackageFileRepository;
use App\Repository\FlavorRepository;
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
  private FlavorRepository $flavor_repo;

  /**
   * CreateMediaPackageSamplesCommand constructor.
   */
  public function __construct(MediaPackageRepository $media_package_repo, MediaPackageCategoryRepository $media_package_category_repo,
                              MediaPackageFileRepository $media_package_file_repo, ParameterBagInterface $parameter_bag,
                              FlavorRepository $flavor_repo)
  {
    parent::__construct();

    $this->media_package_repo = $media_package_repo;
    $this->media_package_category_repo = $media_package_category_repo;
    $this->media_package_file_repo = $media_package_file_repo;
    $this->parameter_bag = $parameter_bag;
    $this->flavor_repo = $flavor_repo;
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
    $pocketcode_flavor = $this->flavor_repo->getFlavorByName('pocketcode');
    $luna_flavor = $this->flavor_repo->getFlavorByName('luna');
    $create_at_school_flavor = $this->flavor_repo->getFlavorByName('create@school');

    $category_pocket_family = $this->media_package_category_repo->createMediaPackageCategory('Pocket Family', new ArrayCollection([$package_looks]));
    $this->media_package_file_repo->createMediaPackageFile('Penguin', new File($sample_pckg_path.'Looks/Pocket Family/Penguin.png'), $category_pocket_family, [$pocketcode_flavor], 'Catrobat');
    $this->media_package_file_repo->createMediaPackageFile('Elephant', new File($sample_pckg_path.'Looks/Pocket Family/Elephant.png'), $category_pocket_family, [$pocketcode_flavor], 'Catrobat');
    $this->media_package_file_repo->createMediaPackageFile('Panda', new File($sample_pckg_path.'Looks/Pocket Family/Panda.png'), $category_pocket_family, [$pocketcode_flavor], 'Catrobat');

    // Creating MediaPackageCategory People and filling it with MediaPackageFiles
    $category_people = $this->media_package_category_repo->createMediaPackageCategory('People', new ArrayCollection([$package_looks]));
    $this->media_package_file_repo->createMediaPackageFile('Boy', new File($sample_pckg_path.'Looks/People/Boy.png'), $category_people, [$pocketcode_flavor], 'Catrobat');
    $this->media_package_file_repo->createMediaPackageFile('Girl', new File($sample_pckg_path.'Looks/People/Girl.png'), $category_people, [$pocketcode_flavor], 'Catrobat');

    // Creating MediaPackageCategory Luna and filling it with MediaPackageFiles
    $category_luna = $this->media_package_category_repo->createMediaPackageCategory('ThemeSpecial Luna & Cat', new ArrayCollection([$package_looks]));
    $this->media_package_file_repo->createMediaPackageFile('Luna Cat', new File($sample_pckg_path.'Looks/Luna/Luna-Cat.png'), $category_luna, [$luna_flavor], 'Catrobat');
    $this->media_package_file_repo->createMediaPackageFile('Luna Girl', new File($sample_pckg_path.'Looks/Luna/Luna-Girl.png'), $category_luna, [$luna_flavor], 'Catrobat');

    /*
     * Creating MediaPackage Sounds
     */
    $package_looks = $this->media_package_repo->createMediaPackage('Sounds', 'sounds');

    // Creating MediaPackageCategory Animals and filling it with MediaPackageFiles
    $category_animal_sounds = $this->media_package_category_repo->createMediaPackageCategory('Animals', new ArrayCollection([$package_looks]));
    $this->media_package_file_repo->createMediaPackageFile('Owl', new File($sample_pckg_path.'Sounds/Animals/Owl.wav'), $category_animal_sounds, [$pocketcode_flavor], 'Catrobat');
    $this->media_package_file_repo->createMediaPackageFile('SeaLion', new File($sample_pckg_path.'Sounds/Animals/SeaLion.mpga'), $category_animal_sounds, [$pocketcode_flavor], 'Catrobat');

    // Creating MediaPackageCategory Machines and filling it with MediaPackageFiles
    $category_machine_sounds = $this->media_package_category_repo->createMediaPackageCategory('Machines', new ArrayCollection([$package_looks]));
    $this->media_package_file_repo->createMediaPackageFile('Plane', new File($sample_pckg_path.'Sounds/Machines/Plane.mpga'), $category_machine_sounds, [$pocketcode_flavor, $create_at_school_flavor], 'Catrobat');
    $this->media_package_file_repo->createMediaPackageFile('Ufo', new File($sample_pckg_path.'Sounds/Machines/Ufo.wav'), $category_machine_sounds, [$pocketcode_flavor, $create_at_school_flavor], 'Catrobat');

    /*
     * Creating MediaPackage Objects
     */
    $package_objects = $this->media_package_repo->createMediaPackage('Objects', 'Objects');
    // Creating MediaPackageCategory Miscellaneous and filling it with MediaPackageFiles
    $category_miscellaneous = $this->media_package_category_repo->createMediaPackageCategory('Miscellaneous', new ArrayCollection([$package_objects]));
    $this->media_package_file_repo->createMediaPackageFile('House', new File($sample_pckg_path.'Objects/Miscellaneous/House.catrobat'), $category_miscellaneous, [$pocketcode_flavor], 'Catrobat');

    // Creating MediaPackageCategory Miscellaneous

    return 0;
  }
}
