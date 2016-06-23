<?php
namespace Catrobat\AppBundle\Twig;

use Catrobat\AppBundle\Entity\MediaPackageFile;
use Catrobat\AppBundle\Services\MediaPackageFileRepository;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\RequestStack;
use Catrobat\AppBundle\Entity\GameJamRepository;
use Liip\ThemeBundle\ActiveTheme;
use Symfony\Component\Intl\Intl;

class AppExtension extends \Twig_Extension
{

    private $request_stack;

    private $mediapackage_file_repository;

    private $gamejamrepository;

    private $theme;

    private $translationPath;

    private $container;

    public function __construct(RequestStack $request_stack, MediaPackageFileRepository $mediapackage_file_repo, GameJamRepository $gamejamrepository, ActiveTheme $theme, $translationPath, Container $container)
    {
        $this->translationPath = $translationPath;
        $this->request_stack = $request_stack;
        $this->mediapackage_file_repository = $mediapackage_file_repo;
        $this->gamejamrepository = $gamejamrepository;
        $this->theme = $theme;
        $this->container = $container;
    }

    public function getFunctions()
    {
        return array(
            'countriesList' => new \Twig_Function_Method($this, 'getCountriesList'),
            'isWebview' => new \Twig_Function_Method($this, 'isWebview'),
            'checkCatrobatLanguage' => new \Twig_Function_Method($this, 'checkCatrobatLanguage'),
            'getLanguageOptions' => new \Twig_Function_Method($this, 'getLanguageOptions'),
            'getMediaPackageImageUrl' => new \Twig_Function_Method($this, 'getMediaPackageImageUrl'),
            'getMediaPackageSoundUrl' => new \Twig_Function_Method($this, 'getMediaPackageSoundUrl'),
            'flavor' => new \Twig_Function_Method($this, 'getFlavor'),
            'theme' => new \Twig_Function_Method($this, 'getTheme'),
            'getCurrentGameJam' => new \Twig_Function_Method($this, 'getCurrentGameJam'),
            'getJavascriptPath' => new \Twig_Function_Method($this, 'getJavascriptPath')
        );
    }

    public function getName()
    {
        return 'app_extension';
    }

    public function getCountriesList()
    {
        return Intl::getRegionBundle()->getCountryNames();
    }

    public function getLanguageOptions()
    {
        $path = $this->translationPath;
        $current_language = $this->request_stack->getCurrentRequest()->getLocale();
        
        if (strpos($current_language, '_DE') !== false || strpos($current_language, '_US') !== false) {
            $current_language = substr($current_language, 0, 2);
        }
        
        $list = array();
        
        $finder = new Finder();
        $finder->files()
            ->in($path)
            ->sortByName();
        
        $isSelectedLangugage = false;
        
        foreach ($finder as $translationFileName) {
            $shortName = $this->getShortLanguageNameFromFileName($translationFileName->getRelativePathname());
            
            $isSelectedLangugage = $current_language === $shortName;
            
            if (strcmp($current_language, $shortName)) {
                $isSelectedLangugage = true;
            }
            
            $locale = Intl::getLocaleBundle()->getLocaleName($shortName, $shortName);
            if ($locale != null) {
                $list[] = array(
                    $shortName,
                    $locale,
                    strcmp($current_language, $shortName) === 0
                );
            }
        }
        
        if (! $isSelectedLangugage) {
            $list = $this->setSelectedLanguage($list, $current_language);
        }
        return $list;
    }

    private function setSelectedLanguage($languages, $currentLanguage)
    {
        $list = array();
        foreach ($languages as $language) {
            if (strpos($currentLanguage, $language[0]) !== false) {
                
                $language = array(
                    $language[0],
                    $language[1],
                    true
                );
            }
            $list[] = $language;
        }
        return $list;
    }

    private function getShortLanguageNameFromFileName($filename)
    {
        $firstOccurrence = strpos($filename, '.') + 1;
        $lastOccurrence = strpos($filename, '.', $firstOccurrence);
        
        return substr($filename, $firstOccurrence, $lastOccurrence - $firstOccurrence);
    }

    public function isWebview()
    {
        $request = $this->request_stack->getCurrentRequest();
        $user_agent = $request->headers->get('User-Agent');
        
        // Example Webview: $user_agent = "Catrobat/0.93 PocketCode/0.9.14 Platform/Android";
        return preg_match('/Catrobat/', $user_agent) || strpos($user_agent, 'Android') != false ||
               strpos($user_agent, 'iPad') != false  || strpos($user_agent, 'iPhone') != false;
    }

    /**
     *
     * @param
     *            $program_catrobat_language
     * @return true|false
     */
    public function checkCatrobatLanguage($program_catrobat_language)
    {
        $request = $this->request_stack->getCurrentRequest();
        $user_agent = $request->headers->get('User-Agent');

        // Example Webview: $user_agent = "Catrobat/0.93 PocketCode/0.9.14 Platform/Android";
        if (preg_match('/Catrobat/', $user_agent)) {
            $user_agent_array = explode("/", $user_agent);
            
            // $user_agent_array = [ "Catrobat", "0.93 PocketCode", 0.9.14 Platform", "Android" ];
            $catrobat_language_array = explode(" ", $user_agent_array[1]);
            // $catrobat_language_array = [ "0.93", "PocketCode" ];
            $catrobat_language = $catrobat_language_array[0] * 1.0;
            
            if ($catrobat_language < $program_catrobat_language) {
                return false;
            }
        }
        
        return true;
    }

    public function getFlavor()
    {
        $request = $this->request_stack->getCurrentRequest();
        return $request->attributes->get('flavor');
    }

    public function getTheme()
    {
        return $this->theme->getName();
    }

    /**
     *
     * @param $object MediaPackageFile            
     * @return null|string
     */
    public function getMediaPackageImageUrl($object)
    {
        switch ($object->getExtension()) {
            case "jpg":
            case "jpeg":
            case "png":
            case "gif":
                return $this->mediapackage_file_repository->getWebPath($object->getId(), $object->getExtension());
                break;
            default:
                return null;
        }
    }

    /**
     *
     * @param $object MediaPackageFile            
     * @return null|string
     */
    public function getMediaPackageSoundUrl($object)
    {
        switch ($object->getExtension()) {
            case "mp3":
            case "mpga":
            case "wav":
            case "ogg":
                return $this->mediapackage_file_repository->getWebPath($object->getId(), $object->getExtension());
                break;
            default:
                return null;
        }
    }

    public function getCurrentGameJam()
    {
        return $this->gamejamrepository->getCurrentGameJam();
    }

    public function getJavascriptPath($jsFile) {
        $jsPath = $this->container->getParameter('jspath');
        $jsPath .= $jsFile;
        $jsPath = str_replace("//", "/", $jsPath);
        return $jsPath;
    }
}
