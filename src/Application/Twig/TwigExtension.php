<?php

namespace App\Application\Twig;

use App\DB\Entity\MediaLibrary\MediaPackageFile;
use App\DB\EntityRepository\MediaLibrary\MediaPackageFileRepository;
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

class TwigExtension extends AbstractExtension
{
  private RequestStack $request_stack;

  private MediaPackageFileRepository $media_package_file_repository;

  private string $translation_path;

  private ParameterBagInterface $parameter_bag;

  private TranslatorInterface $translator;

  public function __construct(RequestStack $request_stack, MediaPackageFileRepository $media_package_file_repo,
                              ParameterBagInterface $parameter_bag, string $catrobat_translation_dir,
                              TranslatorInterface $translator)
  {
    $this->translation_path = $catrobat_translation_dir;
    $this->parameter_bag = $parameter_bag;
    $this->request_stack = $request_stack;
    $this->media_package_file_repository = $media_package_file_repo;
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
    if (!is_string($input)) {
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

    return TwigExtension::humanFriendlyNumber($input, $this->translator, $user_locale);
  }

  /**
   * @param mixed $input
   * @param mixed $user_locale
   *
   * @return bool|false|string
   */
  public static function humanFriendlyNumber($input, TranslatorInterface $translator, $user_locale)
  {
    if (!is_numeric($input)) {
      return $input;
    }

    $number_formatter = new NumberFormatter($user_locale, NumberFormatter::DECIMAL);

    if ($input >= 1_000_000) {
      $number_formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, 1);

      return $number_formatter->format($input / 1_000_000).' '.
        $translator->trans('format.million_abbreviation', [], 'catroweb');
    }

    $number_formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, 0);

    return $number_formatter->format($input);
  }

  /**
   * {@inheritDoc}
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
      new TwigFunction('themeAssets', [$this, 'getFlavor']),
      new TwigFunction('getThemeDisplayName', [$this, 'getThemeDisplayName']),
      new TwigFunction('assetExists', [$this, 'assetExists']),
      new TwigFunction('assetFileExists', [$this, 'assetFileExists']),
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
    $hl_locale_code = null;
    $path = $this->translation_path;
    $current_language = $this->request_stack->getCurrentRequest()->getLocale();

    $list = [];

    $finder = new Finder();
    $finder->files()
      ->in($path)
      ->sortByName()
    ;

    $available_locales = Locales::getNames();

    $shortNames = [];
    foreach ($finder as $translationFileName) {
      $shortName = $this->getShortLanguageNameFromFileName($translationFileName->getRelativePathname());
      $shortNames[] = $shortName;
    }

    foreach ($shortNames as $shortName) {
      if ('en_AU' === $shortName || 'en_CA' === $shortName) {
        continue;
      }

      // Is this locale available in Symfony?
      if (array_key_exists($shortName, $available_locales)) {
        $hl_locale_code = $shortName;
        $shortName = $this->handleSpecialShortNames($shortName);
        $locale_name = Locales::getName($shortName, $shortName);
        $locale_name = $this->handleSpecialLocales($locale_name, $shortName);
        $list[] = [
          $hl_locale_code, // We still need the full locale code for the automapping to work correctly
          $locale_name,
          0 === strcmp($current_language, $hl_locale_code),
        ];
      }
    }

    if ($current_language !== $hl_locale_code) {
      $list = $this->setSelectedLanguage($list, $current_language);
    }

    return $list;
  }

  public function handleSpecialShortNames(string $shortName): string
  {
    switch ($shortName) {
          case 'fa_AF':
          case 'fa_IR':
          case 'pt_BR':
          case 'pt_PT':
          case 'zh_CN':
          case 'zh_TW':
          case 'en_GB':
              return $shortName;
          case 'en':
              return 'en_US';
          default:
              return explode('_', $shortName)[0];
      }
  }

  public function handleSpecialLocales(string $locale, string $shortName): string
  {
    switch ($shortName) {
            case 'en_GB':
                return 'English (British)';
            case 'zh_CN':
                return '中文 (简化字)';
            case 'zh_TW':
                return '中文 (繁體字)';
            default:
                return $locale;
        }
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
    if (preg_match('/Catrobat/', $user_agent)) {
      $user_agent_array = explode('/', $user_agent);

      // $user_agent_array = [ "Catrobat", "0.93 PocketCode", 0.9.14 Platform", "Android" ];
      $catrobat_language_array = explode(' ', $user_agent_array[1]);
      // $catrobat_language_array = [ "0.93", "PocketCode" ];
      $catrobat_language = floatval($catrobat_language_array[0]);

      if ($catrobat_language < $program_catrobat_language) {
        return false;
      }
    }

    return true;
  }

  public function getFlavor(): string
  {
    $request = $this->request_stack->getCurrentRequest();

    return $request->attributes->get('flavor');
  }

  public function getTheme(): string
  {
    $request = $this->request_stack->getCurrentRequest();

    return $request->attributes->get('theme');
  }

  public function getThemeDisplayName(): string
  {
    switch ($this->getFlavor()) {
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
    switch ($object->getExtension()) {
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
    switch ($object->getExtension()) {
      case 'mp3':
      case 'mpga':
      case 'wav':
      case 'ogg':
        return $this->media_package_file_repository->getWebPath($object->getId(), $object->getExtension());
      default:
        return null;
    }
  }

  public function assetExists(string $filename): bool
  {
    $path = $this->getPublicFilenamePath($filename);

    return file_exists($path);
  }

  public function assetFileExists(string $filename): bool
  {
    $path = $this->getPublicFilenamePath($filename);

    return file_exists($path) && !is_dir($path);
  }

  protected function getPublicFilenamePath(string $filename): string
  {
    $public_dir = $this->parameter_bag->get('catrobat.pubdir');
    $filename = rawurldecode($filename);

    return $public_dir.$filename;
  }

  private function setSelectedLanguage(array $languages, string $currentLanguage): array
  {
    $list = [];
    foreach ($languages as $language) {
      if (false !== strpos($currentLanguage, $language[0])) {
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
