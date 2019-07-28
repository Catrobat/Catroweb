<?php

namespace App\Catrobat\Twig;

use App\Entity\MediaPackageFile;
use App\Catrobat\Services\MediaPackageFileRepository;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Repository\GameJamRepository;
use Liip\ThemeBundle\ActiveTheme;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Class AppExtension
 * @package App\Catrobat\Twig
 */
class AppExtension extends AbstractExtension
{

  /**
   * @var RequestStack
   */
  private $request_stack;

  /**
   * @var MediaPackageFileRepository
   */
  private $mediapackage_file_repository;

  /**
   * @var GameJamRepository
   */
  private $gamejamrepository;

  /**
   * @var ActiveTheme
   */
  private $theme;

  /**
   * @var
   */
  private $translationPath;

  /**
   * @var Container
   */
  private $container;

  /**
   * AppExtension constructor.
   *
   * @param RequestStack               $request_stack
   * @param MediaPackageFileRepository $mediapackage_file_repo
   * @param GameJamRepository          $gamejamrepository
   * @param ActiveTheme                $theme
   * @param                            $translationPath
   * @param Container                  $container
   */
  public function __construct(RequestStack $request_stack, MediaPackageFileRepository
  $mediapackage_file_repo, GameJamRepository $gamejamrepository, ActiveTheme $theme,
                              $translationPath, Container $container)
  {
    $this->translationPath = $translationPath;
    $this->request_stack = $request_stack;
    $this->mediapackage_file_repository = $mediapackage_file_repo;
    $this->gamejamrepository = $gamejamrepository;
    $this->theme = $theme;
    $this->container = $container;
  }

  /**
   * @return array|\Twig_Filter[]
   */
  public function getFilters()
  {
    return [
      new TwigFilter('decamelize', [$this, 'decamelizeFilter']),
    ];
  }

  /**
   * @param $input
   *
   * @return string|string[]|null
   */
  public function decamelizeFilter($input)
  {
    if (!is_string($input))
    {
      return $input;
    }

    return preg_replace('/(?<!^)[A-Z]/', ' $0', $input);
  }

  /**
   * @return array|\Twig_Function[]
   */
  public function getFunctions()
  {
    return [
      new TwigFunction('getenv', 'getenv'),
      new TwigFunction('countriesList', [$this, 'getCountriesList']),
      new TwigFunction('isWebview', [$this, 'isWebview']),
      new TwigFunction('isIOSWebview', [$this, 'isIOSWebview']),
      new TwigFunction('checkCatrobatLanguage', [$this, 'checkCatrobatLanguage']),
      new TwigFunction('getLanguageOptions', [$this, 'getLanguageOptions']),
      new TwigFunction('getMediaPackageImageUrl', [$this, 'getMediaPackageImageUrl']),
      new TwigFunction('getMediaPackageSoundUrl', [$this, 'getMediaPackageSoundUrl']),
      new TwigFunction('flavor', [$this, 'getFlavor']),
      new TwigFunction('theme', [$this, 'getTheme']),
      new TwigFunction('getThemeDisplayName', [$this, 'getThemeDisplayName']),
      new TwigFunction('getCurrentGameJam', [$this, 'getCurrentGameJam']),
      new TwigFunction('getJavascriptPath', [$this, 'getJavascriptPath']),
      new TwigFunction('getCommunityStats', [$this, 'getCommunityStats'])
    ];
  }

  /**
   * @return string
   */
  public function getName()
  {
    return 'app_extension';
  }

  /**
   * @return string[]
   */
  public function getCountriesList()
  {
    return Intl::getRegionBundle()->getCountryNames();
  }

  /**
   * @return array
   */
  public function getLanguageOptions()
  {
    $path = $this->translationPath;
    $current_language = $this->request_stack->getCurrentRequest()->getLocale();

    if (strpos($current_language, '_DE') !== false || strpos($current_language, '_US') !== false)
    {
      $current_language = substr($current_language, 0, 2);
    }

    $list = [];

    $finder = new Finder();
    $finder->files()
      ->in($path)
      ->sortByName();

    $isSelectedLangugage = false;

    foreach ($finder as $translationFileName)
    {
      $shortName = $this->getShortLanguageNameFromFileName($translationFileName->getRelativePathname());

      $isSelectedLangugage = $current_language === $shortName;

      if (strcmp($current_language, $shortName))
      {
        $isSelectedLangugage = true;
      }

      $locale = Intl::getLocaleBundle()->getLocaleName($shortName, $shortName);
      if ($locale != null)
      {
        $list[] = [
          $shortName,
          $locale,
          strcmp($current_language, $shortName) === 0,
        ];
      }
    }

    if (!$isSelectedLangugage)
    {
      $list = $this->setSelectedLanguage($list, $current_language);
    }

    return $list;
  }

  /**
   * @param $languages
   * @param $currentLanguage
   *
   * @return array
   */
  private function setSelectedLanguage($languages, $currentLanguage)
  {
    $list = [];
    foreach ($languages as $language)
    {
      if (strpos($currentLanguage, $language[0]) !== false)
      {

        $language = [
          $language[0],
          $language[1],
          true,
        ];
      }
      $list[] = $language;
    }

    return $list;
  }

  /**
   * @param $filename
   *
   * @return bool|string
   */
  private function getShortLanguageNameFromFileName($filename)
  {
    $firstOccurrence = strpos($filename, '.') + 1;
    $lastOccurrence = strpos($filename, '.', $firstOccurrence);

    return substr($filename, $firstOccurrence, $lastOccurrence - $firstOccurrence);
  }

  /**
   * @return bool
   */
  public function isWebview()
  {
    $request = $this->request_stack->getCurrentRequest();
    $user_agent = $request->headers->get('User-Agent');

    // Example Webview: $user_agent = "Catrobat/0.93 PocketCode/0.9.14 Platform/Android";
    return preg_match('/Catrobat/', $user_agent) || strpos($user_agent, 'Android') !== false ||
      strpos($user_agent, 'iPad') !== false || strpos($user_agent, 'iPhone') !== false;
  }

  /**
   * @return bool
   */
  public function isIOSWebview()
  {
    $request = $this->request_stack->getCurrentRequest();
    $user_agent = $request->headers->get('User-Agent');

    return strpos($user_agent, 'iPad') !== false || strpos($user_agent, 'iPhone') !== false;
  }

  /**
   *
   * @param
   *            $program_catrobat_language
   *
   * @return true|false
   */
  public function checkCatrobatLanguage($program_catrobat_language)
  {
    $request = $this->request_stack->getCurrentRequest();
    $user_agent = $request->headers->get('User-Agent');

    // Example Webview: $user_agent = "Catrobat/0.93 PocketCode/0.9.14 Platform/Android";
    if (preg_match('/Catrobat/', $user_agent))
    {
      $user_agent_array = explode("/", $user_agent);

      // $user_agent_array = [ "Catrobat", "0.93 PocketCode", 0.9.14 Platform", "Android" ];
      $catrobat_language_array = explode(" ", $user_agent_array[1]);
      // $catrobat_language_array = [ "0.93", "PocketCode" ];
      $catrobat_language = $catrobat_language_array[0] * 1.0;

      if ($catrobat_language < $program_catrobat_language)
      {
        return false;
      }
    }

    return true;
  }

  /**
   * @return mixed
   */
  public function getFlavor()
  {
    $request = $this->request_stack->getCurrentRequest();

    return $request->attributes->get('flavor');
  }

  /**
   * @return string
   */
  public function getTheme()
  {
    return $this->theme->getName();
  }

  /**
   * @return string
   */
  public function getThemeDisplayName()
  {
    switch ($this->getTheme()) {
      case 'luna':
        return "Luna & Cat";

      case 'phirocode':
        return "Phirocode";

      case 'create@school':
        return "Create@School";

      default:
        return "Pocket Code";
    }
  }

  /**
   * @Route("/api/twig/getMediaPackageImageUrl", name="catrobat_twig_getMediaPackageImageUrl",
   *                                                 methods={"POST"})
   *
   * @param $object MediaPackageFile
   *
   * @return null|string
   */
  public function getMediaPackageImageUrl($object)
  {
    switch ($object->getExtension())
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
   *
   * @param $object MediaPackageFile
   *
   * @return null|string
   */
  public function getMediaPackageSoundUrl($object)
  {
    switch ($object->getExtension())
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

  /**
   * @return mixed
   * @throws \Doctrine\ORM\NonUniqueResultException
   */
  public function getCurrentGameJam()
  {
    return $this->gamejamrepository->getCurrentGameJam();
  }

  /**
   * @param $jsFile
   *
   * @return mixed|string
   */
  public function getJavascriptPath($jsFile)
  {
    $jsPath = $this->container->getParameter('jspath');
    $jsPath .= $jsFile;
    $jsPath = str_replace("//", "/", $jsPath);

    return $jsPath;
  }

  /**
   * Twig extension to provide a function to retrieve the community statistics in any view.
   * Needed to render the footer.
   *
   * See the fetchStatistics implementation of Services\CommunityStatisticsService.php
   *                                           for details.
   *
   * @return array|mixed
   * @throws \Exception
   */
  public function getCommunityStats()
  {
    $cms_s = $this->container->get("community_statistics_service");
    $stats = $cms_s->fetchStatistics();

    /* Numberformatter could be used to apply the locale. However this requires the intl extension to be fully working.

    $nf = new NumberFormatter($this->request_stack->getCurrentRequest()->getLocale(), 1);
    foreach ($stats as $key => $value)
    {
      $stats[$key] = $nf->format($value);
    }
    */

    return $stats;
  }
}
