<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Application\Twig;

use App\Admin\System\FeatureFlag\FeatureFlagManager;
use App\Application\Twig\TwigExtension;
use App\DB\EntityRepository\MediaLibrary\MediaPackageFileRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @internal
 *
 * @coversNothing
 */
class TwigExtensionTest extends TestCase
{
  private string $translationPath;

  #[\Override]
  protected function setup(): void
  {
    parent::setUp();
    $this->translationPath = 'translations';
  }

  public function testLanguageOptions(): void
  {
    $short = 'de_DE';

    $TwigExtension = $this->createTwigExtension($short);
    $language_options = $TwigExtension->getLanguageOptions();

    // Currently we have over 64 languages and dialects
    $this->assertGreaterThanOrEqual(64, count($language_options));

    $this->assertFalse($this->inArray('Deutsch (Deutschland)', $language_options));
    $this->assertTrue($this->inArray('português (Brasil)', $language_options));
    $this->assertTrue($this->inArray('português (Portugal)', $language_options));
    $this->assertTrue($this->inArray('italiano', $language_options));
    $this->assertTrue($this->inArray('polski', $language_options));
    $this->assertTrue($this->inArray('English (United States)', $language_options));
    $this->assertTrue($this->inArray('English (British)', $language_options));
    $this->assertTrue($this->isSelected($short, $language_options));
  }

  public function testEnglishMustBeSelected(): void
  {
    $short = 'en_GB';
    $notShort = 'de';

    $appExtention = $this->createTwigExtension($short);
    $list = $appExtention->getLanguageOptions();

    $this->assertTrue($this->isSelected($short, $list));
    $this->assertFalse($this->isSelected($notShort, $list));
  }

  public function testGermanMustBeSelected(): void
  {
    $short = 'de_DE';
    $notShort = 'en_GB';

    $app_extension = $this->createTwigExtension($short);
    $list = $app_extension->getLanguageOptions();

    $this->assertTrue($this->isSelected($short, $list));
    $this->assertFalse($this->isSelected($notShort, $list));
  }

  public function portugueseBrazilMustBeSelected(): void
  {
    $short = 'pt_BR';
    $notShort = 'pt_PT';

    $app_extension = $this->createTwigExtension($short);
    $list = $app_extension->getLanguageOptions();

    $this->assertTrue($this->isSelected($short, $list));
    $this->assertFalse($this->isSelected($notShort, $list));
  }

  public function portuguesePortugalMustBeSelected(): void
  {
    $short = 'pt_PT';
    $notShort = 'pt_BR';

    $app_extension = $this->createTwigExtension($short);
    $list = $app_extension->getLanguageOptions();

    $this->assertTrue($this->isSelected($short, $list));
    $this->assertFalse($this->isSelected($notShort, $list));
  }

  /**
   * @return MockObject&RequestStack
   */
  private function mockRequestStack(string $locale)
  {
    $requestStack = $this->createMock(RequestStack::class);

    $request = $this->createMock(Request::class);

    $requestStack->expects($this->atLeastOnce())->method('getCurrentRequest')->willReturn($request);

    $request->expects($this->atLeastOnce())->method('getLocale')->willReturn($locale);

    return $requestStack;
  }

  private function createTwigExtension(string $locale): TwigExtension
  {
    $repo = $this->createMock(MediaPackageFileRepository::class);
    $request_stack = $this->mockRequestStack($locale);
    $parameter_bag = $this->createMock(ParameterBag::class);
    $translator = $this->createMock(TranslatorInterface::class);
    $featureFlagManager = $this->createMock(FeatureFlagManager::class);

    return new TwigExtension($request_stack, $repo, $parameter_bag, $this->translationPath, $translator, $featureFlagManager);
  }

  private function inArray(string $needle, array $haystack): bool
  {
    foreach ($haystack as $value) {
      if (0 === strcmp($needle, (string) $value[1])) {
        return true;
      }
    }

    return false;
  }

  private function isSelected(string $short, array $locales): bool
  {
    foreach ($locales as $value) {
      if (0 !== strcmp($short, (string) $value[0])) {
        continue;
      }
      if (!$value[2]) {
        continue;
      }

      return true;
    }

    return false;
  }
}
