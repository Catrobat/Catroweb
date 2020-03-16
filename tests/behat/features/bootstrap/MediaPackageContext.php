<?php

use App\Catrobat\Services\MediaPackageFileRepository;
use App\Entity\MediaPackage;
use App\Entity\MediaPackageCategory;
use App\Entity\MediaPackageFile;
use Behat\Gherkin\Node\TableNode;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\ToolsException;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpKernel\KernelInterface;


/**
 * Feature context.
 */
class MediaPackageContext implements KernelAwareContext
{
  /**
   * @var KernelInterface
   */
  private $kernel;

  private const MEDIAPACKAGE_DIR = './tests/testdata/DataFixtures/MediaPackage/';

  /**
   * Sets HttpKernel instance.
   * This method will be automatically called by Symfony2Extension ContextInitializer.
   *
   * @param KernelInterface $kernel
   */
  public function setKernel(KernelInterface $kernel)
  {
    $this->kernel = $kernel;
  }

  /**
   * @var string
   */
  private $old_metadata_hash = "";

  /**
   * @BeforeScenario
   *
   * @throws ToolsException
   */
  public function clearData()
  {
    $em = $this->kernel->getContainer()->get('doctrine')->getManager();
    $metaData = $em->getMetadataFactory()->getAllMetadata();
    $new_metadata_hash = md5(json_encode($metaData));
    if ($this->old_metadata_hash === $new_metadata_hash)
    {
      return;
    }
    $this->old_metadata_hash = $new_metadata_hash;
    $tool = new SchemaTool($em);
    $tool->dropSchema($metaData);
    $tool->createSchema($metaData);
  }

  /**
   * @Given /^there are mediapackages:$/
   * @param TableNode $table
   *
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function thereAreMediapackages(TableNode $table)
  {
    /**
     * @var $em EntityManager
     */
    $em = $this->kernel->getContainer()->get('doctrine')->getManager();
    $packages = $table->getHash();
    foreach ($packages as $package)
    {
      $new_package = new MediaPackage();
      $new_package->setName($package['name']);
      $new_package->setNameUrl($package['name_url']);
      $em->persist($new_package);
    }
    $em->flush();
  }

  /**
   * @Given /^there are mediapackage categories:$/
   * @param TableNode $table
   *
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function thereAreMediapackageCategories(TableNode $table)
  {
    /**
     * @var $em EntityManager
     */
    $em = $this->kernel->getContainer()->get('doctrine')->getManager();
    $categories = $table->getHash();
    foreach ($categories as $category)
    {
      $new_category = new MediaPackageCategory();
      $new_category->setName($category['name']);
      $package = $em->getRepository('App\Entity\MediaPackage')->findOneBy(['name' => $category['package']]);
      if ($package == null)
      {
        Assert::assertTrue(false, "Fatal error package not found");
      }
      $new_category->setPackage([$package]);
      $current_categories = $package->getCategories();
      $current_categories = $current_categories == null ? [] : $current_categories;
      array_push($current_categories, $new_category);
      $package->setCategories($current_categories);
      $em->persist($new_category);
    }
    $em->flush();
  }

  /**
   * @Given /^there are mediapackage files:$/
   * @param TableNode $table
   *
   * @throws ORMException
   * @throws OptimisticLockException
   * @throws ImagickException
   */
  public function thereAreMediapackageFiles(TableNode $table)
  {
    /**
     * @var $em        EntityManager
     * @var $file_repo MediaPackageFileRepository
     */
    $em = $this->kernel->getContainer()->get('doctrine')->getManager();
    $file_repo = $this->kernel->getContainer()->get(MediaPackageFileRepository::class);
    //$file_repo = $this->getMediaPackageFileRepository();
    $files = $table->getHash();
    foreach ($files as $file)
    {
      $new_file = new MediaPackageFile();
      $new_file->setName($file['name']);
      $new_file->setDownloads(0);
      $new_file->setExtension($file['extension']);
      $new_file->setActive($file['active']);
      $category = $em->getRepository('App\Entity\MediaPackageCategory')->findOneBy(['name' => $file['category']]);
      if ($category == null)
      {
        Assert::assertTrue(false, "Fatal error category not found");
      }
      $new_file->setCategory($category);
      $old_files = $category->getFiles();
      $old_files = $old_files == null ? [] : $old_files;
      array_push($old_files, $new_file);
      $category->setFiles($old_files);
      if (!empty($file['flavor']))
      {
        $new_file->setFlavor($file['flavor']);
      }
      $new_file->setAuthor($file['author']);

      $file_repo->saveMediaPackageFile(new File(self::MEDIAPACKAGE_DIR . $file['id'] . '.' .
        $file['extension']), $file['id'], $file['extension']);

      $em->persist($new_file);
    }
    $em->flush();
  }
}