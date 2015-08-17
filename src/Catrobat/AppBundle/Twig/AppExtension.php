<?php

namespace Catrobat\AppBundle\Twig;

use Catrobat\AppBundle\Entity\MediaPackageFile;
use Catrobat\AppBundle\Services\MediaPackageFileRepository;
use Symfony\Component\Intl\Intl;
use Symfony\Component\HttpFoundation\RequestStack;

class AppExtension extends \Twig_Extension
{
    private $request_stack;
    private $mediapackage_file_repository;
    private $supported_languages = array(
        'en',
        'de',
    //    "zh_TW"
    );

    public function __construct(RequestStack $request_stack, MediaPackageFileRepository $mediapackage_file_repo)
    {
        $this->request_stack = $request_stack;
        $this->mediapackage_file_repository = $mediapackage_file_repo;
    }

    public function getFunctions()
    {
        return array(
            'countriesList' => new \Twig_Function_Method($this, 'getCountriesList'),
            'isWebview' => new \Twig_Function_Method($this, 'isWebview'),
            'getLanguageOptions' => new \Twig_Function_Method($this, 'getLanguageOptions'),
            'getMediaPackageImageUrl' => new \Twig_Function_Method($this, 'getMediaPackageImageUrl'),
            'getMediaPackageSoundUrl' => new \Twig_Function_Method($this, 'getMediaPackageSoundUrl')
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
        $current_language = $this->request_stack->getCurrentRequest()->getLocale();
        $selected = $this->supported_languages[0];
        if (in_array($current_language, $this->supported_languages)) {
            $selected = $current_language;
        } elseif (in_array(substr($current_language, 0, 2), $this->supported_languages)) {
            $selected = substr($current_language, 0, 2);
        }

        $list = array();
        foreach ($this->supported_languages as $language) {
            $list[] = array(
                $language,
                Intl::getLocaleBundle()->getLocaleName($language, $language),
                $selected === $language,
            );
        }

        return $list;
    }

    public function isWebview()
    {
        $request = $this->request_stack->getCurrentRequest();
        $user_agent = $request->headers->get('User-Agent');

        // Example Webview: $user_agent = "Catrobat/0.93 PocketCode/0.9.14 Platform/Android";
        return preg_match('/Catrobat/', $user_agent);
    }

    /**
     * @param $object MediaPackageFile
     * @return null|string
     */
    public function getMediaPackageImageUrl($object)
    {
        switch($object->getExtension())
        {
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
     * @param $object MediaPackageFile
     * @return null|string
     */
    public function getMediaPackageSoundUrl($object)
    {
        switch($object->getExtension())
        {
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
}
