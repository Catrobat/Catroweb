<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Application\Twig;

use OpenAPI\Server\Model\ImageVariants;
use OpenAPI\Server\Model\ImageVariantSet;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Loader\ChainLoader;
use Twig\Loader\FilesystemLoader;

/**
 * Markup of templates/Components/ResponsiveImage.html.twig: the start-page
 * banner pins detail@1x on phones (it is the mobile LCP image).
 *
 * @internal
 */
#[CoversNothing]
class ResponsiveImageMacroTest extends TestCase
{
  public function testMobileMediaPinsDetail1xBeforeTheDensitySources(): void
  {
    $html = $this->render(['mobile_media' => '(max-width: 480px)']);

    $this->assertStringContainsString('<source type="image/avif" media="(max-width: 480px)" srcset="/d1.avif">', $html);
    $this->assertStringContainsString('<source type="image/webp" media="(max-width: 480px)" srcset="/d1.webp">', $html);
    // Order matters: the browser takes the first matching <source>.
    $this->assertLessThan(strpos($html, 'srcset="/d1.avif 1x'), strpos($html, 'media="(max-width: 480px)"'));
  }

  public function testWithoutMobileMediaOnlyDensitySourcesAreRendered(): void
  {
    $html = $this->render([]);

    $this->assertStringNotContainsString('media=', $html);
    $this->assertStringContainsString('srcset="/d1.avif 1x, /d2.avif 2x"', $html);
  }

  public function testBannerMediaQueryIsTheSameInTwigAndJs(): void
  {
    $root = dirname(__DIR__, 4);
    $twig = (string) file_get_contents($root.'/templates/Index/IndexPage.html.twig');
    $js = (string) file_get_contents($root.'/assets/Index/FeaturedBanner.js');

    $this->assertSame(1, preg_match("/set banner_mobile_media = '([^']+)'/", $twig, $twig_match));
    $this->assertSame(1, preg_match("/BANNER_MOBILE_MEDIA = '([^']+)'/", $js, $js_match));
    $this->assertSame($twig_match[1], $js_match[1], 'SSR and carousel must pick the same file, or phones download the banner twice');
  }

  /**
   * @param array<string, string> $options
   */
  private function render(array $options): string
  {
    $detail = new ImageVariantSet();
    $detail->setAvif1x('/d1.avif');
    $detail->setAvif2x('/d2.avif');
    $detail->setWebp1x('/d1.webp');
    $detail->setWebp2x('/d2.webp');
    $variants = new ImageVariants();
    $variants->setDetail($detail);

    $twig = new Environment(new ChainLoader([
      new ArrayLoader(['test' => "{% import 'Components/ResponsiveImage.html.twig' as ri %}{{ ri.picture(v, 'alt', o) }}"]),
      new FilesystemLoader(dirname(__DIR__, 4).'/templates'),
    ]));

    return $twig->render('test', ['v' => $variants, 'o' => $options]);
  }
}
