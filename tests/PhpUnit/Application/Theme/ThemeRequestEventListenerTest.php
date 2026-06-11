<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Application\Theme;

use App\Application\Theme\ThemeRequestEventListener;
use App\DB\Entity\Flavor;
use App\Utils\RequestHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;

/**
 * @internal
 */
#[CoversClass(ThemeRequestEventListener::class)]
class ThemeRequestEventListenerTest extends TestCase
{
  protected ParameterBagInterface $parameter_bag;

  #[\Override]
  protected function setUp(): void
  {
    $this->parameter_bag = $this->mockParameterBag();
  }

  #[Group('integration')]
  #[DataProvider('provideKernelRequestData')]
  public function testOnKernelRequestThemeInRequest(
    string $request_theme, string $request_uri, string $expected_routing_theme, string $expected_flavor,
  ): void {
    $request = Request::create('' !== $request_uri ? $request_uri : 'http://localhost/');
    $event = new RequestEvent($this->createStub(HttpKernelInterface::class), $request, HttpKernelInterface::MAIN_REQUEST);

    $app_request = $this->mockAppRequest($request_theme);

    $request_context = new RequestContext();
    $router = $this->mockRouter($request_context);

    $listener = new ThemeRequestEventListener($this->parameter_bag, $router, $app_request);
    $listener->onKernelRequest($event);

    $this->assertSame($expected_routing_theme, $request_context->getParameter('theme'));
    $this->assertSame($expected_routing_theme, $request->attributes->get('theme'));
    $this->assertSame($expected_flavor, $request->attributes->get('flavor'));
  }

  public static function provideKernelRequestData(): array
  {
    return [
      'Using a valid request theme' => [
        'request_theme' => Flavor::POCKETCODE,
        'request_uri' => '',
        'expected_routing_theme' => 'app',
        'expected_flavor' => Flavor::POCKETCODE,
      ],
      'Using a valid request theme 2' => [
        'request_theme' => Flavor::LUNA,
        'request_uri' => '',
        'expected_routing_theme' => 'app',
        'expected_flavor' => Flavor::LUNA,
      ],
      'Using umbrella theme that is no flavor must use default flavor' => [
        'request_theme' => 'app',
        'request_uri' => '',
        'expected_routing_theme' => 'app',
        'expected_flavor' => Flavor::POCKETCODE,
      ],
      'Using a invalid request theme must use default theme/flavor' => [
        'request_theme' => 'invalid',
        'request_uri' => '',
        'expected_routing_theme' => 'app',
        'expected_flavor' => Flavor::POCKETCODE,
      ],
      'Using a request theme has higher priority than legacy URL theming' => [
        'request_theme' => Flavor::LUNA,
        'request_uri' => 'http://share.catrob.at/pocketcode',
        'expected_routing_theme' => 'app',
        'expected_flavor' => Flavor::LUNA,
      ],
      'No set request theme must use and keep legacy URL theming' => [
        'request_theme' => '',
        'request_uri' => 'http://share.catrob.at/luna',
        'expected_routing_theme' => Flavor::LUNA,
        'expected_flavor' => Flavor::LUNA,
      ],
      'Umbrella URL theming must use default but keep route' => [
        'request_theme' => '',
        'request_uri' => 'http://share.catrob.at/app/',
        'expected_routing_theme' => 'app',
        'expected_flavor' => Flavor::POCKETCODE,
      ],
      'Invalid Legacy URL theming must use default but keep route' => [
        'request_theme' => '',
        'request_uri' => 'http://share.catrob.at/invalid',
        'expected_routing_theme' => 'invalid',
        'expected_flavor' => Flavor::POCKETCODE,
      ],
      'Should also work with index(test?).php in route' => [
        'request_theme' => '',
        'request_uri' => 'http://localhost/index_test.php/luna',
        'expected_routing_theme' => Flavor::LUNA,
        'expected_flavor' => Flavor::LUNA,
      ],
      'It must be possible to return from the admin interface' => [
        'request_theme' => Flavor::LUNA,
        'request_uri' => 'http://localhost/admin',
        'expected_routing_theme' => 'app',
        'expected_flavor' => Flavor::LUNA,
      ],
      'It must be possible to return from the admin interface (legacy)' => [
        'request_theme' => '',
        'request_uri' => 'http://localhost/admin',
        'expected_routing_theme' => 'app',
        'expected_flavor' => Flavor::POCKETCODE,
      ],
    ];
  }

  #[Group('integration')]
  public function testOnKernelRequestSubRequest(): void
  {
    $request = Request::create('http://localhost/index.php/js/randomStuf123');
    $event = new RequestEvent($this->createStub(HttpKernelInterface::class), $request, HttpKernelInterface::SUB_REQUEST);

    $app_request = $this->mockAppRequest();

    $request_context = new RequestContext();
    $router = $this->mockRouter($request_context);

    $listener = new ThemeRequestEventListener($this->parameter_bag, $router, $app_request);
    $listener->onKernelRequest($event);

    $this->assertSame('app', $request_context->getParameter('theme'));
    $this->assertSame('app', $request->attributes->get('theme'));
    $this->assertSame(Flavor::POCKETCODE, $request->attributes->get('flavor'));
  }

  private function mockParameterBag(): ParameterBagInterface
  {
    $parameter_bag = $this->createStub(ParameterBagInterface::class);
    $parameter_bag
      ->method('get')
      ->willReturnCallback(
        static fn ($param): array|string => match ($param) {
          'flavors' => [Flavor::POCKETCODE, Flavor::LUNA],
          'umbrellaTheme' => 'app',
          'adminTheme' => 'admin',
          'defaultFlavor' => Flavor::POCKETCODE,
          default => '',
        }
      )
    ;

    return $parameter_bag;
  }

  private function mockAppRequest(string $response = ''): RequestHelper
  {
    if ('' === $response) {
      // No expectations needed - use stub
      return $this->createStub(RequestHelper::class);
    }

    // Has expectations - use mock
    $app_request = $this->createMock(RequestHelper::class);
    $app_request->expects($this->once())
      ->method('getThemeDefinedInRequest')
      ->willReturn($response)
    ;

    return $app_request;
  }

  private function mockRouter(RequestContext $request_context): MockObject&RouterInterface
  {
    $router = $this->createMock(RouterInterface::class);
    $router->expects($this->once())->method('getContext')->willReturn($request_context);

    return $router;
  }
}
