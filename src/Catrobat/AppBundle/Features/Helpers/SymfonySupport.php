<?php
namespace Catrobat\AppBundle\Features\Helpers;

use Catrobat\AppBundle\Entity\Extension;
use Catrobat\AppBundle\Entity\ProgramDownloads;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Catrobat\AppBundle\Entity\Program;
use Catrobat\AppBundle\Entity\Tag;
use Behat\Behat\Tester\Exception\PendingException;
use Catrobat\AppBundle\Services\CatrobatFileCompressor;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Catrobat\AppBundle\Entity\GameJam;
use Symfony\Component\Validator\Constraints\DateTime;

class SymfonySupport
{
    private $fixture_dir;
    
    private $kernel;
    private $client;
    private $test_user_count = 0;
    private $default_user;
    private $error_directory;
    
    public function __construct($fixture_dir)
    {
        $this->fixture_dir = $fixture_dir;
    }
    
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }
    
    
    /**
     * @return \Symfony\Bundle\FrameworkBundle\Client
     */
    public function getClient()
    {
        if ($this->client == null) {
            $this->client = $this->kernel->getContainer()->get('test.client');
        }

        return $this->client;
    }
    
    /**
     * @return \Catrobat\AppBundle\Entity\UserManager
     */
    public function getUserManager()
    {
        return $this->kernel->getContainer()->get('usermanager');
    }
    
    /**
     * @return \Catrobat\AppBundle\Entity\ProgramManager
     */
    public function getProgramManger()
    {
        return $this->kernel->getContainer()->get('programmanager');
    }

    /**
     * @return \Catrobat\AppBundle\Entity\TagRepository
     */
    public function getTagRepository()
    {
        return $this->kernel->getContainer()->get('tagrepository');
    }

    /**
     * @return \Catrobat\AppBundle\Entity\ExtensionRepository
     */
    public function getExtensionRepository()
    {
        return $this->kernel->getContainer()->get('extensionrepository');
    }
    
    /**
     * @return \Catrobat\AppBundle\Services\ProgramFileRepository
     */
    public function getFileRepository()
    {
        return $this->kernel->getContainer()->get('filerepository');
    }

    /**
     * @return \Catrobat\AppBundle\Services\ExtractedFileRepository
     */
    public function getExtractedFileRepository()
    {
        return $this->kernel->getContainer()->get('extractedfilerepository');
    }
    
    /**
     * @return \Catrobat\AppBundle\Services\MediaPackageFileRepository
     */
    public function getMediaPackageFileRepository()
    {
        return $this->kernel->getContainer()->get('mediapackagefilerepository');
    }
    
    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getManager()
    {
        return $this->kernel->getContainer()->get('doctrine')->getManager();
    }
    
    /**
     * @return \Symfony\Component\Routing\Router
     */
    public function getRouter()
    {
        return $this->kernel->getContainer()->get('router');
    }

    /**
     * @return \Catrobat\AppBundle\Services\FacebookPostService
     */
    public function getRealFacebookPostServiceForTests()
    {
        return $this->kernel->getContainer()->get('real_facebook_post_service');
    }
    
    /**
     * @return mixed
     */
    public function getSymfonyParameter($param)
    {
        return $this->kernel->getContainer()->getParameter($param);
    }
    
    /**
     * @return mixed
     */
    public function getSymfonyService($param)
    {
        return $this->kernel->getContainer()->get($param);
    }

    /**
     * @return \Symfony\Component\HttpKernel\Profiler\Profiler
     */
    public function getSymfonyProfile()
    {
        $profile = $this->getClient()->getProfile();
        if (false === $profile) {
            throw new \RuntimeException(
                'The profiler is disabled. Activate it by setting '.
                'framework.profiler.only_exceptions to false in '.
                'your config'
            );
        }
    
        return $profile;
    }
    
    /**
     * @return \Catrobat\AppBundle\Entity\User
     */
    public function getDefaultUser()
    {
        if ($this->default_user == null) {
            $this->default_user = $this->insertUser();
        } else {
            $this->default_user = $this->getUserManager()->find($this->default_user->getId());
        }
 
        return $this->default_user;
    }
    
    
    public function getDefaultProgramFile()
    {
        $file = $this->fixture_dir . "/test.catrobat";
        assertTrue(is_file($file));
        return $file;
    }
    
    public function setErrorDirectory($dir)
    {
        $this->error_directory = $dir;
    }
    
    public function emptyDirectory($directory)
    {
        if (!is_dir($directory)) {
            return;
        }
        $filesystem = new Filesystem();
    
        $finder = new Finder();
        $finder->in($directory)->depth(0);
        foreach ($finder as $file) {
            $filesystem->remove($file);
        }
    }
    
    public function insertDefaultGamejam($config = array())
    {
        $gamejam = new GameJam();
        @$gamejam->setName($config['name'] ?: "pocketalice");
        @$gamejam->setHashtag($config['hashtag'] ?: null);
        
        $start_date = new \DateTime();
        $start_date->sub(new \DateInterval('P10D'));
        $end_date = new \DateTime();
        $end_date->add(new \DateInterval('P10D'));
        
        @$gamejam->setStart($config['start'] ?: $start_date);
        @$gamejam->setEnd($config['end'] ?: $end_date);
        
        @$gamejam->setFormUrl($config['formurl'] ?: "https://catrob.at/url/to/form");
        
        $this->getManager()->persist($gamejam);
        $this->getManager()->flush();
        return $gamejam;
    }
    
    public function insertUser($config = array())
    {
        ++$this->test_user_count;
        $user_manager = $this->getUserManager();
        $user = $user_manager->createUser();
        @$user->setUsername($config['name'] ?: 'GeneratedUser'.$this->test_user_count);
        @$user->setEmail($config['email'] ?: 'default'.$this->test_user_count.'@pocketcode.org');
        @$user->setPlainPassword($config['password'] ?: 'GeneratedPassword');
        @$user->setEnabled($config['enabled'] ?: true);
        @$user->setUploadToken($config['token'] ?: 'GeneratedToken');
        @$user->setCountry($config['country'] ?: 'at');
        @$user->setLimited($config['limited'] ?: 'false');
        @$user->addRole($config['role']?: 'ROLE_USER');
        @$user_manager->updateUser($user, true);
    
        return $user;
    }

    public function insertTag($config)
    {
        $em = $this->getManager();
        $tag = new Tag();

        $tag->setEn($config['en']);
        $tag->setDe($config['de']);

        $em->persist($tag);
        $em->flush();

    }

    public function insertExtension($config)
    {
        $em = $this->getManager();
        $extension = new Extension();

        $extension->setName($config['name']);
        $extension->setPrefix($config['prefix']);

        $em->persist($extension);
        $em->flush();

    }

    
    public function insertProgram($user, $config)
    {
        if ($user == null) {
            $user = $this->getDefaultUser();
        }
    
        $em = $this->getManager();
        $program = new Program();
        $program->setUser($user);
        $program->setName($config['name'] ?: 'Generated program');
        $program->setDescription(isset($config['description']) ? $config['description'] : 'Generated');
        $program->setViews(isset($config['views']) ? $config['views'] : 1);
        $program->setDownloads(isset($config['downloads']) ? $config['downloads'] : 1);
        $program->setUploadedAt(isset($config['uploadtime']) ? new \DateTime($config['uploadtime'], new \DateTimeZone('UTC')) : new \DateTime());
        $program->setCatrobatVersion(isset($config['catrobatversion']) ? $config['catrobatversion'] : 1);
        $program->setCatrobatVersionName(isset($config['catrobatversionname']) ? $config['catrobatversionname'] : '0.9.1');
        $program->setLanguageVersion(isset($config['languageversion']) ? $config['languageversion'] : 1);
        $program->setUploadIp('127.0.0.1');
        $program->setRemixCount(0);
        $program->setRemixOf(isset($config['remixof']) ? $config['remixof'] : null);
        $program->setFilesize(isset($config['filesize']) ? $config['filesize'] : 0);
        $program->setVisible(isset($config['visible']) ? boolval($config['visible']) : true);
        $program->setUploadLanguage('en');
        $program->setApproved(isset($config['approved']) ? $config['approved'] : true);
        $program->setFlavor(isset($config['flavor']) ? $config['flavor'] : 'pocketcode');
        $program->setApkStatus(isset($config['apk_status']) ? $config['apk_status'] : Program::APK_NONE);
        $program->setDirectoryHash(isset($config['directory_hash']) ?$config['directory_hash']: null);
        $program->setAcceptedForGameJam(isset($config['accepted']) ? $config['accepted'] : false);
        $program->setGamejam(isset($config['gamejam']) ? $config['gamejam'] : null);

        if (isset($config['tags']) && $config['tags'] != null) {
            $tags = explode(',', $config['tags']);
            foreach ($tags as $tag_id) {
                $tag = $this->getTagRepository()->find($tag_id);
                $program->addTag($tag);
            }
        }

        if (isset($config['extensions']) && $config['extensions'] != null) {
            $extensions = explode(',', $config['extensions']);
            foreach ($extensions as $extension_name) {
                $extension = $this->getExtensionRepository()->findOneByName($extension_name);
                $program->addExtension($extension);
            }
        }

        $em->persist($program);
        
        $user->addProgram($program);
        $em->persist($user);
        
        // FIXXME: why exactly do we have to do this?
        if (isset($config['gamejam']))
        {
            $jam = $config['gamejam'];
            $jam->addProgram($program);
            $em->persist($jam);
        }

        $em->flush();
        
        return $program;
    }

    public function insertProgramDownloadStatistics($program, $config)
    {
        $em = $this->getManager();
        /*
         * @var $program_statistics Entity\ProgramDownloads;
         * @var $program Entity\Program;
         */
        $program_statistics = new ProgramDownloads();
        $program_statistics->setProgram($program);
        $program_statistics->setDownloadedAt(new \DateTime($config['downloaded_at']) ?: new DateTime());
        $program_statistics->setIp(isset($config['ip']) ? $config['ip'] : '88.116.169.222');
        $program_statistics->setLatitude(isset($config['latitude']) ? $config['latitude'] : 47.2);
        $program_statistics->setLongitude(isset($config['longitude']) ? $config['longitude'] : 10.7);
        $program_statistics->setCountryCode(isset($config['country_code']) ? $config['country_code'] : 'AT');
        $program_statistics->setCountryName(isset($config['country_name']) ? $config['country_name'] : 'Austria');
        $program_statistics->setStreet(isset($config['street']) ? $config['street'] : 'Duck Street 1');
        $program_statistics->setPostalCode(isset($config['postal_code']) ? $config['postal_code'] : '1234');
        $program_statistics->setLocality(isset($config['locality']) ? $config['locality'] : 'Entenhausen');
        $program_statistics->setUserAgent(isset($config['user_agent']) ? $config['user_agent'] : 'okhttp');
        $program_statistics->setReferrer(isset($config['referrer']) ? $config['referrer'] : 'Facebook');

        if(isset($config['username'])) {
            $userManager = $this->getUserManager();
            $user = $userManager->createUser();
            $user->setUsername($config['username']);
            $user->setEmail('dog@robat.at');
            $user->setPassword('test');
            $userManager->updateUser($user);
            $program_statistics->setUser($user);
        }

        $em->persist($program_statistics);

        $program->addProgramDownloads($program_statistics);
        $em->persist($program);

        $em->flush();

        return $program_statistics;
    }
    
    public function generateProgramFileWith($parameters)
    {
        $filesystem = new Filesystem();
        $this->emptyDirectory(sys_get_temp_dir().'/program_generated/');
        $new_program_dir = sys_get_temp_dir().'/program_generated/';
        $filesystem->mirror($this->fixture_dir.'/GeneratedFixtures/base', $new_program_dir);
        $properties = simplexml_load_file($new_program_dir.'/code.xml');
    
        foreach ($parameters as $name => $value) {
            switch ($name) {
                case 'description':
                    $properties->header->description = $value;
                    break;
                case 'name':
                    $properties->header->programName = $value;
                    break;
                case 'platform':
                    $properties->header->platform = $value;
                    break;
                case 'catrobatLanguageVersion':
                    $properties->header->catrobatLanguageVersion = $value;
                    break;
                case 'applicationVersion':
                    $properties->header->applicationVersion = $value;
                    break;
                case 'applicationName':
                    $properties->header->applicationName = $value;
                    break;
                case 'url':
                    $properties->header->url = $value;
                    break;
                case 'tags':
                    $properties->header->tags = $value;
                    break;
    
                default:
                    throw new PendingException('unknown xml field '.$name);
            }
        }
    
        $properties->asXML($new_program_dir.'/code.xml');
        $compressor = new CatrobatFileCompressor();
    
        return $compressor->compress($new_program_dir, sys_get_temp_dir().'/', 'program_generated');
    }
    
    public function upload($file, $user, $flavor = "pocketcode", $request_param = null)
    {
        if ($user == null) {
            $user = $this->getDefaultUser();
        }
    
        if (is_string($file)) {
            $file = new UploadedFile($file, 'uploadedFile');
        }
    
        $parameters = array();
        $parameters['username'] = $user->getUsername();
        $parameters['token'] = $user->getUploadToken();
        $parameters['fileChecksum'] = md5_file($file->getPathname());

        if ($request_param['deviceLanguage'] != null) {
            $parameters['deviceLanguage'] = $request_param['deviceLanguage'];
        }

        $client = $this->getClient();
        $client->request('POST', '/' . $flavor . '/api/upload/upload.json', $parameters, array($file));
        $response = $client->getResponse();
    
        return $response;
    }
    
    public function submit($file, $user)
    {
        if ($user == null) {
            $user = $this->getDefaultUser();
        }
    
        if (is_string($file)) {
            $file = new UploadedFile($file, 'uploadedFile');
        }
    
        $parameters = array();
        $parameters['username'] = $user->getUsername();
        $parameters['token'] = $user->getUploadToken();
        $parameters['fileChecksum'] = md5_file($file->getPathname());
        $client = $this->getClient();
        $client->request('POST', '/pocketcode/api/gamejam/submit.json', $parameters, array($file));
        $response = $client->getResponse();
    
        return $response;
    }
    
    public function logOnError(AfterStepScope $scope)
    {
        if ($this->error_directory == null) {
            return;
        }
    
        if (! $scope->getTestResult()->isPassed() && $this->getClient() != null) {
            $response = $this->getClient()->getResponse();
            if ($response != null && $response->getContent() != '') {
                file_put_contents($this->error_directory . 'errors.json', $response->getContent());
            }
        }
    }

    protected function getTempCopy($path)
    {
        $temppath = tempnam(sys_get_temp_dir(), 'apktest');
        copy($path, $temppath);
    
        return $temppath;
    }
    
    public function clearDefaultUser()
    {
        $this->default_user = null;
    }
}