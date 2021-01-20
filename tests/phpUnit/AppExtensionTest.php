<?php

namespace Tests\phpUnit;

use App\Catrobat\Services\MediaPackageFileRepository;
use App\Catrobat\Twig\AppExtension;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @internal
 * @coversNothing
 */
class AppExtensionTest extends TestCase
{
  private string $translationPath;

  protected function setup(): void
  {
    parent::setUp();
    $this->translationPath = __DIR__.'/../../translations';
  }

  public function testLanguageOptions(): void
  {
    $short = 'de_DE';

    $appExtension = $this->createAppExtension($short);
    $language_options = $appExtension->getLanguageOptions();

    // Currently we have over 65 languages and dialects
    $this->assertGreaterThanOrEqual(65, count($language_options));

    $this->assertFalse($this->inArray('Deutsch', $language_options));
    $this->assertTrue($this->inArray('Deutsch (Deutschland)', $language_options));
    $this->assertTrue($this->inArray('italiano (Italia)', $language_options));
    $this->assertTrue($this->inArray('polski (Polska)', $language_options));
    $this->assertTrue($this->inArray('English (Canada)', $language_options));
    $this->assertTrue($this->inArray('English (United Kingdom)', $language_options));
    $this->assertTrue($this->isSelected($short, $language_options));
  }

  /**
   * @test
   */
  public function englishMustBeSelected(): void
  {
    $short = 'en';
    $notShort = 'de';

    $appExtention = $this->createAppExtension($short);
    $list = $appExtention->getLanguageOptions();

    $this->assertTrue($this->isSelected($short, $list));
    $this->assertFalse($this->isSelected($notShort, $list));
  }

  /**
   * @test
   */
  public function englishCanadaMustBeSelected(): void
  {
    $short = 'en_CA';
    $notShort = 'en';

    $app_extension = $this->createAppExtension($short);
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

  private function createAppExtension(string $locale): AppExtension
  {
    $repo = $this->createMock(MediaPackageFileRepository::class);
    $request_stack = $this->mockRequestStack($locale);
    $parameter_bag = $this->createMock(ParameterBag::class);
    $translator = $this->createMock(TranslatorInterface::class);

    return new AppExtension($request_stack, $repo, $parameter_bag, $this->translationPath, $translator);
  }

  private function inArray(string $needle, array $haystack): bool
  {
    foreach ($haystack as $value)
    {
      if (0 === strcmp($needle, $value[1]))
      {
        return true;
      }
    }

    return false;
  }

  private function isSelected(string $short, array $locales): bool
  {
    foreach ($locales as $value)
    {
      if (0 === strcmp($short, $value[0]) && 0 === strcmp('1', $value[2]))
      {
        return true;
      }
    }

    return false;
  }
}
