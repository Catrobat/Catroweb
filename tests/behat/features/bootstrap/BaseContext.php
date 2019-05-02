<?php

use Behat\Behat\Hook\Scope\AfterStepScope;
use App\Catrobat\Services\TestEnv\SymfonySupport;
use Symfony\Component\HttpKernel\KernelInterface;
use Behat\Symfony2Extension\Context\KernelAwareContext;


/**
 * Feature context.
 */
class BaseContext implements KernelAwareContext
{

  const FIXTUREDIR = './tests/testdata/DataFixtures/';

  /**
   * @var SymfonySupport
   */
  private $symfony_support;

  /**
   * BaseContext constructor.
   */
  public function __construct()
  {
    $this->symfony_support = new SymfonySupport(self::FIXTUREDIR);
  }

  /**
   * @return string
   */
  public static function getAcceptedSnippetType()
  {
    return 'regex';
  }

  /**
   * Sets HttpKernel instance.
   * This method will be automatically called by Symfony2Extension ContextInitializer.
   *
   * @param KernelInterface $kernel
   */
  public function setKernel(KernelInterface $kernel)
  {
    $this->symfony_support->setKernel($kernel);
  }

  // //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // //////////////////////////////////////////// Getter & Setter

  /**
   * @return SymfonySupport
   */
  public function getSymfonySupport()
  {
    return $this->symfony_support;
  }

  /**
   *
   * @return \Symfony\Bundle\FrameworkBundle\Client
   */
  public function getClient()
  {
    return $this->symfony_support->getClient();
  }

  /**
   *
   * @return \App\Entity\UserManager
   */
  public function getUserManager()
  {
    return $this->symfony_support->getUserManager();
  }

  /**
   *
   * @return \App\Entity\ProgramManager
   */
  public function getProgramManger()
  {
    return $this->symfony_support->getProgramManager();
  }

  /**
   *
   * @return \App\Repository\TagRepository
   */
  public function getTagRepository()
  {
    return $this->symfony_support->getTagRepository();
  }

  /**
   *
   * @return \App\Repository\ProgramRemixRepository
   */
  public function getProgramRemixForwardRepository()
  {
    return $this->symfony_support->getProgramRemixForwardRepository();
  }

  /**
   * @return \App\Repository\ProgramRemixBackwardRepository
   */
  public function getProgramRemixBackwardRepository()
  {
    return $this->symfony_support->getProgramRemixBackwardRepository();
  }

  /**
   * @return \App\Repository\ScratchProgramRepository
   */
  public function getScratchProgramRepository()
  {
    return $this->symfony_support->getScratchProgramRepository();
  }

  /**
   * @return \App\Repository\ScratchProgramRemixRepository
   */
  public function getScratchProgramRemixRepository()
  {
    return $this->symfony_support->getScratchProgramRemixRepository();
  }

  /**
   *
   * @return \App\Catrobat\Services\ProgramFileRepository
   */
  public function getFileRepository()
  {
    return $this->symfony_support->getFileRepository();
  }

  /**
   *
   * @return \App\Catrobat\Services\ExtractedFileRepository
   */
  public function getExtractedFileRepository()
  {
    return $this->symfony_support->getExtractedFileRepository();
  }

  /**
   *
   * @return \App\Catrobat\Services\MediaPackageFileRepository
   */
  public function getMediaPackageFileRepository()
  {
    return $this->symfony_support->getMediaPackageFileRepository();
  }

  /**
   *
   * @return \Doctrine\ORM\EntityManager
   */
  public function getManager()
  {
    return $this->symfony_support->getManager();
  }

  /**
   * @param $param
   *
   * @return mixed
   */
  public function getSymfonyParameter($param)
  {
    return $this->symfony_support->getSymfonyParameter($param);
  }

  /**
   * @param $param
   *
   * @return mixed
   */
  public function getSymfonyService($param)
  {
    return $this->symfony_support->getSymfonyService($param);
  }

  /**
   *
   * @return \Symfony\Component\HttpKernel\Profiler\Profiler
   */
  public function getSymfonyProfile()
  {
    return $this->symfony_support->getSymfonyProfile();
  }

  /**
   *
   * @return \App\Entity\User
   */
  public function getDefaultUser()
  {
    return $this->symfony_support->getDefaultUser();
  }

  /**
   * @param $dir
   */
  public function setErrorDirectory($dir)
  {
    $this->symfony_support->setErrorDirectory($dir);
  }

  // //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // //////////////////////////////////////////// HOOKS

  /**
   * @BeforeScenario
   */
  public function clearDefaultUser()
  {
    $this->symfony_support->clearDefaultUser();
  }

  /**
   * @BeforeScenario
   */
  public function emptyStorage()
  {
    $this->emptyDirectory($this->getSymfonyParameter('catrobat.file.extract.dir'));
    $this->emptyDirectory($this->getSymfonyParameter('catrobat.file.storage.dir'));
    $this->emptyDirectory($this->getSymfonyParameter('catrobat.screenshot.dir'));
    $this->emptyDirectory($this->getSymfonyParameter('catrobat.thumbnail.dir'));
    $this->emptyDirectory($this->getSymfonyParameter('catrobat.featuredimage.dir'));
    $this->emptyDirectory($this->getSymfonyParameter('catrobat.apk.dir'));
    $this->emptyDirectory($this->getSymfonyParameter('catrobat.backup.dir'));
    $this->emptyDirectory($this->getSymfonyParameter('catrobat.snapshot.dir'));
  }

  /**
   * @AfterStep
   *
   * @param AfterStepScope $scope
   */
  public function saveResponseToFile(AfterStepScope $scope)
  {
    $this->symfony_support->logOnError($scope);
  }

  // //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // //////////////////////////////////////////// Support Functions
  /**
   * @param $directory
   */
  public function emptyDirectory($directory)
  {
    $this->symfony_support->emptyDirectory($directory);
  }

  /**
   * @param array $config
   *
   * @return \FOS\UserBundle\Model\UserInterface|mixed
   */
  public function insertUser($config = [])
  {
    return $this->symfony_support->insertUser($config);
  }

  /**
   *
   */
  public function computeAllLikeSimilaritiesBetweenUsers()
  {
    $this->symfony_support->computeAllLikeSimilaritiesBetweenUsers();
  }

  /**
   * @return array
   */
  public function getAllLikeSimilaritiesBetweenUsers()
  {
    return $this->symfony_support->getUserLikeSimilarityRelationRepository()->findAll();
  }

  /**
   *
   */
  public function computeAllRemixSimilaritiesBetweenUsers()
  {
    $this->symfony_support->computeAllRemixSimilaritiesBetweenUsers();
  }

  /**
   * @return array
   */
  public function getAllRemixSimilaritiesBetweenUsers()
  {
    return $this->symfony_support->getUserRemixSimilarityRelationRepository()->findAll();
  }

  /**
   * @param array $config
   *
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function insertUserLikeSimilarity($config = [])
  {
    $this->symfony_support->insertUserLikeSimilarity($config);
  }

  /**
   * @param array $config
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function insertUserRemixSimilarity($config = [])
  {
    $this->symfony_support->insertUserRemixSimilarity($config);
  }

  /**
   * @param array $config
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function insertProgramLike($config = [])
  {
    $this->symfony_support->insertProgramLike($config);
  }

  /**
   * @param $user
   * @param $config
   *
   * @return \App\Entity\Program
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function insertProgram($user, $config)
  {
    return $this->symfony_support->insertProgram($user, $config);
  }

  /**
   * @param $config
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function insertTag($config)
  {
    $this->symfony_support->insertTag($config);
  }

  /**
   * @param $config
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function insertExtension($config)
  {
    $this->symfony_support->insertExtension($config);
  }

  /**
   * @param $config
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function insertForwardRemixRelation($config)
  {
    $this->symfony_support->insertForwardRemixRelation($config);
  }

  /**
   * @param $config
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function insertBackwardRemixRelation($config)
  {
    $this->symfony_support->insertBackwardRemixRelation($config);
  }

  /**
   * @param $config
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function insertScratchRemixRelation($config)
  {
    $this->symfony_support->insertScratchRemixRelation($config);
  }

  /**
   * @param $program
   * @param $config
   *
   * @return \App\Entity\ProgramDownloads
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function insertProgramDownloadStatistics($program, $config)
  {
    return $this->symfony_support->insertProgramDownloadStatistics($program, $config);
  }

  /**
   * @param $parameters
   *
   * @return string
   */
  public function generateProgramFileWith($parameters)
  {
    return $this->symfony_support->generateProgramFileWith($parameters);
  }

  /**
   * @param $file
   * @param $user
   * @param $desired_id
   * @param string $flavor
   * @param null $request_parameters
   * @return \Symfony\Component\HttpFoundation\Response|null
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function upload($file, $user, $desired_id=null, $flavor = 'pocketcode', $request_parameters = null)
  {
    return $this->symfony_support->upload($file, $user, $desired_id, $flavor, $request_parameters);
  }

  /**
   * @param $path
   *
   * @return bool|string
   */
  protected function getTempCopy($path)
  {
    $temppath = tempnam(sys_get_temp_dir(), 'apktest');
    copy($path, $temppath);

    return $temppath;
  }

}
