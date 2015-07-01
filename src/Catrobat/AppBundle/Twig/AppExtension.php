<?php

namespace Catrobat\AppBundle\Twig;

use Symfony\Component\Intl\Intl;
use Symfony\Component\HttpFoundation\RequestStack;

class AppExtension extends \Twig_Extension
{
    private $request_stack;

    private $supported_languages = array(
        'en',
        'de',
    //    "zh_TW"
    );

    public function __construct(RequestStack $request_stack)
    {
        $this->request_stack = $request_stack;
    }

    public function getFunctions()
    {
        return array(
            'countriesList' => new \Twig_Function_Method($this, 'getCountriesList'),
            'isWebview' => new \Twig_Function_Method($this, 'isWebview'),
            'getLanguageOptions' => new \Twig_Function_Method($this, 'getLanguageOptions'),
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
}
