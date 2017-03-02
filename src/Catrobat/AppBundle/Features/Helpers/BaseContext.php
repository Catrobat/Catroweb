<?php
namespace Catrobat\AppBundle\Features\Helpers;

use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Behat\Tester\Exception\PendingException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\KernelInterface;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Catrobat\AppBundle\Entity\Program;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Behat\Behat\Context\CustomSnippetAcceptingContext;
use Catrobat\AppBundle\Services\CatrobatFileCompressor;
use Catrobat\AppBundle\Entity\ProgramManager;
require_once 'PHPUnit/Framework/Assert/Functions.php';

/**
 * Feature context.
 */
class BaseContext implements KernelAwareContext, CustomSnippetAcceptingContext
{

    const FIXTUREDIR = './testdata/DataFixtures/';

    private $symfony_support;

    public function __construct()
    {
        $this->symfony_support = new SymfonySupport(self::FIXTUREDIR);
    }

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
    
    public function getSymfonySupport()
    {
        return $this->symfony_support;
    }

    /**
     * @return \Catrobat\AppBundle\Services\FacebookPostService
     */
    public function getRealFacebookPostServiceForTests()
    {
        return $this->symfony_support->getRealFacebookPostServiceForTests();
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
     * @return \Catrobat\AppBundle\Entity\UserManager
     */
    public function getUserManager()
    {
        return $this->symfony_support->getUserManager();
    }

    /**
     *
     * @return \Catrobat\AppBundle\Entity\ProgramManager
     */
    public function getProgramManger()
    {
        return $this->symfony_support->getProgramManager();
    }

    /**
     *
     * @return \Catrobat\AppBundle\Entity\TagRepository
     */
    public function getTagRepository()
    {
        return $this->symfony_support->getTagRepository();
    }

    /**
     *
     * @return \Catrobat\AppBundle\Entity\ProgramRemixRepository
     */
    public function getProgramRemixForwardRepository()
    {
        return $this->symfony_support->getProgramRemixForwardRepository();
    }

    /**
     * @return \Catrobat\AppBundle\Entity\ProgramRemixBackwardRepository
     */
    public function getProgramRemixBackwardRepository()
    {
        return $this->symfony_support->getProgramRemixBackwardRepository();
    }

    /**
     * @return \Catrobat\AppBundle\Entity\ScratchProgramRepository
     */
    public function getScratchProgramRepository()
    {
        return $this->symfony_support->getScratchProgramRepository();
    }

    /**
     * @return \Catrobat\AppBundle\Entity\ScratchProgramRemixRepository
     */
    public function getScratchProgramRemixRepository()
    {
        return $this->symfony_support->getScratchProgramRemixRepository();
    }

    /**
     *
     * @return \Catrobat\AppBundle\Services\ProgramFileRepository
     */
    public function getFileRepository()
    {
        return $this->symfony_support->getFileRepository();
    }

    /**
     *
     * @return \Catrobat\AppBundle\Services\ExtractedFileRepository
     */
    public function getExtractedFileRepository()
    {
        return $this->symfony_support->getExtractedFileRepository();
    }

    /**
     *
     * @return \Catrobat\AppBundle\Services\MediaPackageFileRepository
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
     *
     * @return mixed
     */
    public function getSymfonyParameter($param)
    {
        return $this->symfony_support->getSymfonyParameter($param);
    }

    /**
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
     * @return \Catrobat\AppBundle\Entity\User
     */
    public function getDefaultUser()
    {
        return $this->symfony_support->getDefaultUser();
    }

    public function setErrorDirectory($dir)
    {
        return $this->symfony_support->setErrorDirectory($dir);
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
     */
    public function saveResponseToFile(AfterStepScope $scope)
    {
        $this->symfony_support->logOnError($scope);
    }
    
    // //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // //////////////////////////////////////////// Support Functions
    public function emptyDirectory($directory)
    {
        return $this->symfony_support->emptyDirectory($directory);
    }

    public function insertUser($config = array())
    {
        return $this->symfony_support->insertUser($config);
    }

    public function computeAllLikeSimilaritiesBetweenUsers()
    {
        return $this->symfony_support->computeAllLikeSimilaritiesBetweenUsers();
    }

    public function getAllLikeSimilaritiesBetweenUsers()
    {
        return $this->symfony_support->getUserLikeSimilarityRelationRepository()->findAll();
    }

    public function computeAllRemixSimilaritiesBetweenUsers()
    {
        return $this->symfony_support->computeAllRemixSimilaritiesBetweenUsers();
    }

    public function getAllRemixSimilaritiesBetweenUsers()
    {
        return $this->symfony_support->getUserRemixSimilarityRelationRepository()->findAll();
    }

    public function insertUserLikeSimilarity($config = array())
    {
        return $this->symfony_support->insertUserLikeSimilarity($config);
    }

    public function insertUserRemixSimilarity($config = array())
    {
        return $this->symfony_support->insertUserRemixSimilarity($config);
    }

    public function insertProgramLike($config = array())
    {
        return $this->symfony_support->insertProgramLike($config);
    }

    public function insertProgram($user, $config)
    {
        return $this->symfony_support->insertProgram($user, $config);
    }

    public function insertTag($config)
    {
        return $this->symfony_support->insertTag($config);
    }

    public function insertExtension($config)
    {
        return $this->symfony_support->insertExtension($config);
    }

    public function insertForwardRemixRelation($config)
    {
        return $this->symfony_support->insertForwardRemixRelation($config);
    }

    public function insertBackwardRemixRelation($config)
    {
        return $this->symfony_support->insertBackwardRemixRelation($config);
    }

    public function insertScratchRemixRelation($config)
    {
        return $this->symfony_support->insertScratchRemixRelation($config);
    }

    public function insertProgramDownloadStatistics($program, $config)
    {
        return $this->symfony_support->insertProgramDownloadStatistics($program, $config);
    }

    public function generateProgramFileWith($parameters)
    {
        return $this->symfony_support->generateProgramFileWith($parameters);
    }

    public function upload($file, $user, $flavor = 'pocketcode', $request_parameters = null)
    {
        return $this->symfony_support->upload($file, $user, $flavor, $request_parameters);
    }

    protected function getTempCopy($path)
    {
        $temppath = tempnam(sys_get_temp_dir(), 'apktest');
        copy($path, $temppath);
        
        return $temppath;
    }
    
    // //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // //////////////////////////////////////////////////////////////////////////////
}
