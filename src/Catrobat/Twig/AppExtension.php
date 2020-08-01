<?php

namespace App\Catrobat\Twig;

use App\Catrobat\Services\CommunityStatisticsService;
use App\Catrobat\Services\MediaPackageFileRepository;
use App\Entity\MediaPackageFile;
use Liip\ThemeBundle\ActiveTheme;
use NumberFormatter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Locales;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig_Function;

class AppExtension extends AbstractExtension
{
  private RequestStack $request_stack;

  private MediaPackageFileRepository $media_package_file_repository;

  private ActiveTheme $theme;

  private string $translation_path;

  private ParameterBagInterface $parameter_bag;

  private TranslatorInterface $translator;

  public function __construct(RequestStack $request_stack, MediaPackageFileRepository $media_package_file_repo, ActiveTheme $theme,
                              ParameterBagInterface $parameter_bag, string $catrobat_translation_dir,
                              TranslatorInterface $translator)
  {
    $this->translation_path = $catrobat_translation_dir;
    $this->parameter_bag = $parameter_bag;
    $this->request_stack = $request_stack;
    $this->media_package_file_repository = $media_package_file_repo;
    $this->theme = $theme;
    $this->translator = $translator;
  }

  public function getFilters(): array
  {
    return [
      new TwigFilter('decamelize', [$this, 'decamelizeFilter']),
      new TwigFilter('humanFriendlyNumber', [$this, 'humanFriendlyNumberFilter']),
    ];
  }

  /**
   * @param mixed $input
   *
   * @return string|string[]|null
   */
  public function decamelizeFilter($input)
  {
    if (!is_string($input))
    {
      return $input;
    }

    return preg_replace('#(?<!^)[A-Z]#', ' $0', $input);
  }

  /**
   * @param mixed $input
   *
   * @return bool|string
   */
  public function humanFriendlyNumberFilter($input)
  {
    $user_locale = $this->request_stack->getCurrentRequest()->getLocale();

    return AppExtension::humanFriendlyNumber($input, $this->translator, $user_locale);
  }

  /**
   * @param mixed $input
   * @param mixed $user_locale
   *
   * @return bool|false|string
   */
  public static function humanFriendlyNumber($input, TranslatorInterface $translator, $user_locale)
  {
    if (!is_numeric($input))
    {
      return $input;
    }

    $number_formatter = new NumberFormatter($user_locale, NumberFormatter::DECIMAL);

    if ($input >= 1_000_000)
    {
      $number_formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, 1);

      return $number_formatter->format($input / 1_000_000).' '.
        $translator->trans('format.million_abbreviation', [], 'catroweb');
    }

    $number_formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, 0);

    return $number_formatter->format($input);
  }

  /**
   * @return Twig_Function[]
   */
  public function getFunctions(): array
  {
    return [
      new TwigFunction('countriesList', [$this, 'getCountriesList']),
      new TwigFunction('isMobile', [$this, 'isMobile']),
      new TwigFunction('isWebview', [$this, 'isWebview']),
      new TwigFunction('isAndroid', [$this, 'isAndroid']),
      new TwigFunction('isIOS', [$this, 'isIOS']),
      new TwigFunction('checkCatrobatLanguage', [$this, 'checkCatrobatLanguage']),
      new TwigFunction('getLanguageOptions', [$this, 'getLanguageOptions']),
      new TwigFunction('getMediaPackageImageUrl', [$this, 'getMediaPackageImageUrl']),
      new TwigFunction('getMediaPackageSoundUrl', [$this, 'getMediaPackageSoundUrl']),
      new TwigFunction('flavor', [$this, 'getFlavor']),
      new TwigFunction('theme', [$this, 'getTheme']),
      new TwigFunction('getThemeDisplayName', [$this, 'getThemeDisplayName']),
      new TwigFunction('getCommunityStats', [$this, 'getCommunityStats']),
      new TwigFunction('assetExists', [$this, 'assetExists']),
      new TwigFunction('isVersionSupportedByCatBlocks', [$this, 'isVersionSupportedByCatBlocks']),
    ];
  }

  public function isVersionSupportedByCatBlocks(string $version): bool
  {
    $MIN_VERSION_SUPPORTED = '0.994';
    $EPSILON = 0.0000001;

    return floatval($MIN_VERSION_SUPPORTED) - floatval($version) < $EPSILON;
  }

  public function getName(): string
  {
    return 'app_extension';
  }

  /**
   * @return string[]
   */
  public function getCountriesList(): array
  {
    return Countries::getNames();
  }

  public function getLanguageOptions(): array
  {
    $path = $this->translation_path;
    $current_language = $this->request_stack->getCurrentRequest()->getLocale();

    if (false !== strpos($current_language, '_DE') || false !== strpos($current_language, '_US'))
    {
      $current_language = substr($current_language, 0, 2);
    }

    $list = [];

    $finder = new Finder();
    $finder->files()
      ->in($path)
      ->sortByName()
    ;

    $isSelectedLanguage = false;

    $available_locales = Locales::getNames();

    foreach ($finder as $translationFileName)
    {
      $shortName = $this->getShortLanguageNameFromFileName($translationFileName->getRelativePathname());

      $isSelectedLanguage = $current_language === $shortName;

      if (strcmp($current_language, $shortName))
      {
        $isSelectedLanguage = true;
      }

      // Is this locale available in Symfony?
      if (array_key_exists($shortName, $available_locales))
      {
        $locale = Locales::getName($shortName, $shortName);

        $list[] = [
          $shortName,
          $locale,
          0 === strcmp($current_language, $shortName),
        ];
      }
    }

    if (!$isSelectedLanguage)
    {
      $list = $this->setSelectedLanguage($list, $current_language);
    }

    return $list;
  }

  public function isMobile(): bool
  {
    return boolval(preg_match('/(Catrobat|Android|Windows Phone|iPad|iPhone)/', $this->getUserAgent()));
  }

  public function isWebview(): bool
  {
    // Example Webview: $user_agent = "Catrobat/0.93 PocketCode/0.9.14 Platform/Android";
    return boolval(preg_match('/Catrobat/', $this->getUserAgent()));
  }

  public function isAndroid(): bool
  {
    return boolval(preg_match('/Android/', $this->getUserAgent()));
  }

  public function isIOS(): bool
  {
    return boolval(preg_match('/(iPad|iPhone)/', $this->getUserAgent()));
  }

  /**
   * @param mixed $program_catrobat_language
   */
  public function checkCatrobatLanguage($program_catrobat_language): bool
  {
    $user_agent = $this->getUserAgent();

    // Example Webview: $user_agent = "Catrobat/0.93 PocketCode/0.9.14 Platform/Android";
    if (preg_match('/Catrobat/', $user_agent))
    {
      $user_agent_array = explode('/', $user_agent);

      // $user_agent_array = [ "Catrobat", "0.93 PocketCode", 0.9.14 Platform", "Android" ];
      $catrobat_language_array = explode(' ', $user_agent_array[1]);
      // $catrobat_language_array = [ "0.93", "PocketCode" ];
      $catrobat_language = floatval($catrobat_language_array[0]);

      if ($catrobat_language < $program_catrobat_language)
      {
        return false;
      }
    }

    return true;
  }

  public function getFlavor(): ?string
  {
    $request = $this->request_stack->getCurrentRequest();

    return $request->get('flavor');
  }

  public function getTheme(): string
  {
    return $this->theme->getName();
  }

  public function getThemeDisplayName(): string
  {
    switch ($this->getTheme()) {
      case 'luna':
        return 'Luna & Cat';

      case 'phirocode':
        return 'Phirocode';

      case 'create@school':
        return 'Create@School';

      case 'embroidery':
        return 'Embroidery Designer';

      case 'arduino':
        return 'Arduino Code';

      default:
        return 'Pocket Code';
    }
  }

  /**
   * @deprecated
   *
   * @Route("/api/twig/getMediaPackageImageUrl", name="catrobat_twig_getMediaPackageImageUrl",
   * methods={"POST"})
   */
  public function getMediaPackageImageUrl(MediaPackageFile $object): ?string
  {
    switch ($object->getExtension())
    {
      case 'jpg':
      case 'jpeg':
      case 'png':
      case 'gif':
        return $this->media_package_file_repository->getWebPath($object->getId(), $object->getExtension());
      case 'catrobat':
        return $this->media_package_file_repository->getThumbnailWebPath($object->getId(), $object->getExtension());
      default:
        return null;
    }
  }

  public function getMediaPackageSoundUrl(MediaPackageFile $object): ?string
  {
    switch ($object->getExtension())
    {
      case 'mp3':
      case 'mpga':
      case 'wav':
      case 'ogg':
        return $this->media_package_file_repository->getWebPath($object->getId(), $object->getExtension());
      default:
        return null;
    }
  }

  /**
   * Twig extension to provide a function to retrieve the community statistics in any view.
   * Needed to render the footer.
   *
   * See the fetchStatistics implementation of Services\CommunityStatisticsService.php for details.
   *
   * @return array|mixed
   */
  public function getCommunityStats(CommunityStatisticsService $communityStatisticsService)
  {
    $cms_s = $communityStatisticsService;

    return $cms_s->fetchStatistics();

    /* Numberformatter could be used to apply the locale. However this requires the intl extension to be fully working.

    $nf = new NumberFormatter($this->request_stack->getCurrentRequest()->getLocale(), 1);
    foreach ($stats as $key => $value)
    {
      $stats[$key] = $nf->format($value);
    }
    */
  }

  public function assetExists(string $filename): bool
  {
    $public_dir = $this->parameter_bag->get('catrobat.pubdir');
    $filename = rawurldecode($filename);
    $filename = $public_dir.$filename;

    return file_exists($filename);
  }

  private function setSelectedLanguage(array $languages, string $currentLanguage): array
  {
    $list = [];
    foreach ($languages as $language)
    {
      if (false !== strpos($currentLanguage, $language[0]))
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

  private function getShortLanguageNameFromFileName(string $filename): string
  {
    $firstOccurrence = strpos($filename, '.') + 1;
    $lastOccurrence = strpos($filename, '.', $firstOccurrence);

    return substr($filename, $firstOccurrence, $lastOccurrence - $firstOccurrence);
  }

  private function getUserAgent(): string
  {
    $request = $this->request_stack->getCurrentRequest();

    return $request->headers->get('User-Agent') ?? '';
  }
}
