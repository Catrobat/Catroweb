<?php

declare(strict_types=1);

namespace App\Application\Twig;

use App\Admin\Tools\FeatureFlag\FeatureFlagManager;
use App\DB\Entity\Flavor;
use App\DB\Entity\MediaLibrary\MediaPackageFile;
use App\DB\EntityRepository\MediaLibrary\MediaPackageFileRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Locales;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension
{
  public function __construct(
    private readonly RequestStack $request_stack,
    private readonly MediaPackageFileRepository $media_package_file_repository,
    private readonly ParameterBagInterface $parameter_bag,
    private readonly string $catrobat_translation_dir,
    private readonly TranslatorInterface $translator,
    private readonly FeatureFlagManager $featureFlagManager,
  ) {
  }

  #[\Override]
  public function getFilters(): array
  {
    return [
      new TwigFilter('decamelize', $this->decamelizeFilter(...)),
      new TwigFilter('humanFriendlyNumber', $this->humanFriendlyNumberFilter(...)),
    ];
  }

  /**
   * @return string|string[]|null
   */
  public function decamelizeFilter(mixed $input)
  {
    if (!is_string($input)) {
      return $input;
    }

    return preg_replace('#(?<!^)[A-Z]#', ' $0', $input);
  }

  public function humanFriendlyNumberFilter(mixed $input): bool|string
  {
    $user_locale = $this->request_stack->getCurrentRequest()->getLocale();

    return TwigExtension::humanFriendlyNumber($input, $this->translator, $user_locale);
  }

  public static function humanFriendlyNumber(mixed $input, TranslatorInterface $translator, mixed $user_locale): bool|string
  {
    if (!is_numeric($input)) {
      return $input;
    }

    $number_formatter = new \NumberFormatter($user_locale, \NumberFormatter::DECIMAL);

    if ($input >= 1_000_000) {
      $number_formatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, 1);

      return $number_formatter->format($input / 1_000_000).' '.
        $translator->trans('format.million_abbreviation', [], 'catroweb');
    }

    $number_formatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, 0);

    return $number_formatter->format($input);
  }

  #[\Override]
  public function getFunctions(): array
  {
    return [
      new TwigFunction('countriesList', $this->getCountriesList(...)),
      new TwigFunction('isMobile', $this->isMobile(...)),
      new TwigFunction('getDatetimeAsString', $this->getDatetimeAsString(...)),
      new TwigFunction('isWebview', $this->isWebview(...)),
      new TwigFunction('isAndroid', $this->isAndroid(...)),
      new TwigFunction('isIOS', $this->isIOS(...)),
      new TwigFunction('checkCatrobatLanguage', $this->checkCatrobatLanguage(...)),
      new TwigFunction('getLanguageOptions', $this->getLanguageOptions(...)),
      new TwigFunction('getMediaPackageImageUrl', $this->getMediaPackageImageUrl(...)),
      new TwigFunction('getMediaPackageSoundUrl', $this->getMediaPackageSoundUrl(...)),
      new TwigFunction('flavor', $this->getFlavor(...)),
      new TwigFunction('theme', $this->getTheme(...)),
      new TwigFunction('themeAssets', $this->getFlavor(...)),
      new TwigFunction('getThemeDisplayName', $this->getThemeDisplayName(...)),
      new TwigFunction('assetExists', $this->assetExists(...)),
      new TwigFunction('assetFileExists', $this->assetFileExists(...)),
      new TwigFunction('isVersionSupportedByCatBlocks', $this->isVersionSupportedByCatBlocks(...)),
      new TwigFunction('isFeatureFlagEnabled', $this->isFeatureFlagEnabled(...)),
    ];
  }

  public function isFeatureFlagEnabled(string $featureFlag): bool
  {
    return $this->featureFlagManager->isEnabled($featureFlag);
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
    $path = $this->catrobat_translation_dir;
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
      if ('en_AU' === $shortName) {
        continue;
      }
      if ('en_CA' === $shortName) {
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
      return $this->setSelectedLanguage($list, $current_language);
    }

    return $list;
  }

  public function handleSpecialShortNames(string $shortName): string
  {
    return match ($shortName) {
      'fa_AF', 'fa_IR', 'pt_BR', 'pt_PT', 'zh_CN', 'zh_TW', 'en_GB' => $shortName,
      'en' => 'en_US',
      default => explode('_', $shortName)[0],
    };
  }

  public function handleSpecialLocales(string $locale, string $shortName): string
  {
    return match ($shortName) {
      'en_GB' => 'English (British)',
      'zh_CN' => '中文 (简化字)',
      'zh_TW' => '中文 (繁體字)',
      default => $locale,
    };
  }

  public function isMobile(): bool
  {
    return boolval(preg_match('/(Catrobat|Android|Windows Phone|iPad|iPhone)/', $this->getUserAgent()));
  }

  public function getDatetimeAsString(\DateTime $dateTime): string
  {
    return date('Y-m-d\TH:i:s\Z', $dateTime->getTimestamp());
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

  public function checkCatrobatLanguage(mixed $project_catrobat_language): bool
  {
    $user_agent = $this->getUserAgent();

    // Example Webview: $user_agent = "Catrobat/0.93 PocketCode/0.9.14 Platform/Android";
    if (preg_match('/Catrobat/', $user_agent)) {
      $user_agent_array = explode('/', $user_agent);

      // $user_agent_array = [ "Catrobat", "0.93 PocketCode", 0.9.14 Platform", "Android" ];
      $catrobat_language_array = explode(' ', $user_agent_array[1]);
      // $catrobat_language_array = [ "0.93", "PocketCode" ];
      $catrobat_language = floatval($catrobat_language_array[0]);

      if ($catrobat_language < $project_catrobat_language) {
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
    return match ($this->getFlavor()) {
      Flavor::LUNA => 'Luna & Cat',
      Flavor::PHIROCODE => 'Phirocode',
      Flavor::CREATE_AT_SCHOOL => 'Create@School',
      Flavor::EMBROIDERY => 'Embroidery Designer',
      Flavor::ARDUINO => 'Arduino Code',
      default => 'Pocket Code',
    };
  }

  /**
   * @deprecated
   */
  #[Route(path: '/api/twig/getMediaPackageImageUrl', name: 'catrobat_twig_getMediaPackageImageUrl', methods: ['POST'])]
  public function getMediaPackageImageUrl(MediaPackageFile $object): ?string
  {
    return match ($object->getExtension()) {
      'jpg', 'jpeg', 'png', 'gif' => $this->media_package_file_repository->getWebPath($object->getId(), $object->getExtension()),
      'catrobat' => $this->media_package_file_repository->getThumbnailWebPath($object->getId(), $object->getExtension()),
      default => null,
    };
  }

  public function getMediaPackageSoundUrl(MediaPackageFile $object): ?string
  {
    return match ($object->getExtension()) {
      'mp3', 'mpga', 'wav', 'ogg' => $this->media_package_file_repository->getWebPath($object->getId(), $object->getExtension()),
      default => null,
    };
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
      if (str_contains($currentLanguage, (string) $language[0])) {
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
