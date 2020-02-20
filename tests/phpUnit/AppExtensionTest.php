<?php

namespace tests;

use App\Catrobat\Twig\AppExtension;
use Prophecy\Prophet;


class AppExtensionTest extends \PHPUnit\Framework\TestCase
{
  private $prophet;

  private $translationPath;

  protected function setup(): void
  {
    parent::setUp();
    $this->prophet = new Prophet();
    $this->translationPath = __DIR__ . '/../../translations';
  }


  /**
   * @test
   */
  public function arrayMustContainFourLanguages()
  {
    $short = "de";

    $appExtension = $this->createAppExtension($short);
    $list = $appExtension->getLanguageOptions();
    // TODO change this to a dynamic number
    $this->assertEquals(count($list), 69);

    $this->assertTrue($this->inArray('Deutsch', $list));
    $this->assertTrue($this->inArray('English', $list));
    $this->assertTrue($this->inArray('italiano', $list));
    $this->assertTrue($this->inArray('polski', $list));
    $this->assertTrue($this->inArray('English (Canada)', $list));
    $this->assertTrue($this->isSelected($short, $list));
  }


  /**
   * @test
   */
  public function englishMustBeSelected()
  {
    $short = "en";
    $notShort = "de";

    $appExtention = $this->createAppExtension($short);
    $list = $appExtention->getLanguageOptions();

    $this->assertTrue($this->isSelected($short, $list));
    $this->assertFalse($this->isSelected($notShort, $list));
  }

  /**
   * @test
   */
  public function englishCanadaMustBeSelected()
  {
    $short = "en_CA";
    $notShort = "en";

    $appExtention = $this->createAppExtension($short);
    $list = $appExtention->getLanguageOptions();

    $this->assertTrue($this->isSelected($short, $list));
    $this->assertFalse($this->isSelected($notShort, $list));
  }

  private function mockRequestStack($locale)
  {
    $requestStack = $this->prophet->prophesize('Symfony\Component\HttpFoundation\RequestStack');

    $request = $this->prophet->prophesize('Symfony\Component\HttpFoundation\Request');

    $requestStack->getCurrentRequest()->willReturn($request);

    $request->getLocale()->willReturn($locale);

    return $requestStack;
  }

  private function mockParameterBag()
  {
    $container = $this->prophet->prophesize('Symfony\Component\DependencyInjection\ParameterBag\ParameterBag');

    return $container;
  }

  private function createAppExtension($locale)
  {
    $repo = $this->mockMediaPackageFileRepository();
    $requestStack = $this->mockRequestStack($locale);
    $gamejamRepository = $this->prophet->prophesize('App\Repository\GameJamRepository');
    $theme = $this->prophet->prophesize('Liip\ThemeBundle\ActiveTheme');
    $parameter_bag = $this->mockParameterBag();

    return new AppExtension($requestStack->reveal(), $repo->reveal(), $gamejamRepository->reveal(),
      $theme->reveal(), $parameter_bag->reveal(), $this->translationPath);
  }

  private function mockMediaPackageFileRepository()
  {
    return $this->prophet->prophesize('App\Catrobat\Services\MediaPackageFileRepository');
  }

  private function inArray($needle, $haystack)
  {
    foreach ($haystack as $value)
    {
      if (strcmp($needle, $value[1]) === 0)
      {
        return true;
      }

    }

    return false;
  }

  private function isSelected($short, $locales)
  {
    foreach ($locales as $value)
    {
      if (strcmp($short, $value[0]) === 0 && strcmp("1", $value[2]) === 0)
      {
        return true;
      }
    }

    return false;
  }
}