<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Storage;

use App\Storage\PublicImageSourceResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/** @internal */
#[CoversClass(PublicImageSourceResolver::class)]
#[Group('unit')]
final class PublicImageSourceResolverTest extends TestCase
{
  private string $root;
  private PublicImageSourceResolver $resolver;

  #[\Override]
  protected function setUp(): void
  {
    $this->root = sys_get_temp_dir().'/catroweb-public-images-'.bin2hex(random_bytes(8));
    $this->resolver = new PublicImageSourceResolver();
    foreach (['images', 'resources/screenshots', 'resources/extract', 'resources/assets', 'private'] as $dir) {
      mkdir($this->root.'/'.$dir, 0o700, true);
    }
    $png = "\x89\x50\x4e\x47\x0d\x0a\x1a\x0a\x00\x00\x00\x0d\x49\x48\x44\x52\x00\x00\x00\x01\x00\x00\x00\x01\x08\x04\x00\x00\x00\xb5\x1c\x0c\x02\x00\x00\x00\x0b\x49\x44\x41\x54\x78\xda\x63\xfc\xff\x1f\x00\x03\x03\x02\x00\xef\x9a\xf1\x5d\x00\x00\x00\x00\x49\x45\x4e\x44\xae\x42\x60\x82"; // 1x1 PNG
    foreach (['images/picture.png', 'resources/screenshots/picture.png', 'resources/extract/private.png', 'resources/assets/private.png', 'private/secret.png'] as $path) {
      file_put_contents($this->root.'/'.$path, $png);
    }
  }

  #[\Override]
  protected function tearDown(): void
  {
    new Filesystem()->remove($this->root);
  }

  public function testPublicImageIsResolvedWithAnAdjacentDestination(): void
  {
    $images = realpath($this->root.'/images');
    self::assertIsString($images);
    self::assertSame([
      'source' => realpath($this->root.'/images/picture.png'),
      'destination' => $images.'/picture.webp',
    ], $this->resolver->resolve($this->root, $this->root.'/images/picture.png'));
  }

  public function testSharedResourceRootMayBeSymlinked(): void
  {
    mkdir($this->root.'/shared/screenshots', 0o700, true);
    copy($this->root.'/images/picture.png', $this->root.'/shared/screenshots/picture.png');
    new Filesystem()->remove($this->root.'/resources');
    symlink($this->root.'/shared', $this->root.'/resources');
    self::assertNotNull($this->resolver->resolve($this->root, $this->root.'/resources/screenshots/picture.png'));
  }

  public function testDeploymentAliasesResolveToTheSamePublicRoot(): void
  {
    symlink($this->root.'/images', $this->root.'/public-alias');
    self::assertNotNull($this->resolver->resolve($this->root, $this->root.'/public-alias/picture.png'));
  }

  public function testProtectedAndUnrelatedFilesAreRejected(): void
  {
    foreach (['resources/extract/private.png', 'resources/assets/private.png', 'private/secret.png'] as $path) {
      self::assertNull($this->resolver->resolve($this->root, $this->root.'/'.$path));
    }
  }

  public function testTraversalAndEncodedPathsAreRejected(): void
  {
    foreach (['images/../private/secret.png', 'images/%2e%2e/private/secret.png', 'images/picture.png%00', 'images/./picture.png', 'images/picture.png'."\0", 'images\picture.png'] as $path) {
      self::assertNull($this->resolver->resolve($this->root, $this->root.'/'.$path));
    }
  }

  public function testEscapingSourceLinksAndProtectedRootAliasesAreRejected(): void
  {
    symlink($this->root.'/private/secret.png', $this->root.'/images/escape.png');
    self::assertNull($this->resolver->resolve($this->root, $this->root.'/images/escape.png'));
    new Filesystem()->remove($this->root.'/resources/screenshots');
    symlink($this->root.'/resources/assets', $this->root.'/resources/screenshots');
    self::assertNull($this->resolver->resolve($this->root, $this->root.'/resources/screenshots/private.png'));
  }

  public function testUnsafeDestinationLinksAreRejectedEvenIfDangling(): void
  {
    symlink($this->root.'/private/new-file.webp', $this->root.'/images/picture.webp');
    self::assertNull($this->resolver->resolve($this->root, $this->root.'/images/picture.png'));
  }

  public function testNonImageContentsAndUnsupportedExtensionsAreRejected(): void
  {
    file_put_contents($this->root.'/images/fake.png', '<?php echo "not an image";');
    copy($this->root.'/images/picture.png', $this->root.'/images/hidden.php');
    self::assertNull($this->resolver->resolve($this->root, $this->root.'/images/fake.png'));
    self::assertNull($this->resolver->resolve($this->root, $this->root.'/images/hidden.php'));
  }
}
