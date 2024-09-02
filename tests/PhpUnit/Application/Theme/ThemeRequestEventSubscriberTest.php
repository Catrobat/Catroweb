<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Application\Theme;

use App\Application\Theme\ThemeRequestEventSubscriber;
use App\DB\Entity\Flavor;
use App\System\Testing\PhpUnit\DefaultTestCase;
use App\Utils\RequestHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class ThemeRequestEventSubscriberTest.
 *
 * @covers \App\Application\Theme\ThemeRequestEventSubscriber
 *
 * @internal
 */
class ThemeRequestEventSubscriberTest extends DefaultTestCase
{
  protected ThemeRequestEventSubscriber|MockObject $object;

  protected ParameterBagInterface|MockObject $parameter_bag;

  #[\Override]
  protected function setUp(): void
  {
    $this->object = $this->mockThemeRequestEventSubscriber();
    $this->parameter_bag = $this->mockParameterBag();
  }

  /**
   * @group integration
   */
  public function testTestClassExists(): void
  {
    $this->assertTrue(class_exists(ThemeRequestEventSubscriber::class));
    $this->assertInstanceOf(ThemeRequestEventSubscriber::class, $this->object);
  }

  /**
   * @group integration
   *
   * @throws \ReflectionException
   */
  #[DataProvider('provideKernelRequestData')]
  public function testOnKernelRequestThemeInRequest(
    string $request_theme, string $request_uri, string $expected_routing_theme, string $expected_flavor,
  ): void {
    $request_attributes = $this->mockRequestAttributes();
    $event = $this->mockRequestEvent(HttpKernelInterface::MAIN_REQUEST, $request_attributes, $request_uri);

    $app_request = $this->mockAppRequest($request_theme);

    $request_context = $this->mockRequestContext();
    $router = $this->mockRouter($request_context);

    $this->expectRoutingThemeToEqual($request_context, $expected_routing_theme);
    $this->expectAttributesToEqual($request_attributes, $expected_routing_theme, $expected_flavor);

    $this->object = $this->mockThemeRequestEventSubscriber([$this->parameter_bag, $router, $app_request]);
    $this->object->onKernelRequest($event);
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

  /**
   * @group integration
   *
   * @throws \ReflectionException
   */
  public function testOnKernelRequestSubRequest(): void
  {
    $request_attributes = $this->mockRequestAttributes();
    $event = $this->mockRequestEvent(
      HttpKernelInterface::SUB_REQUEST, $request_attributes, 'http://localhost/index.php/js/randomStuf123'
    );

    $app_request = $this->mockAppRequest();

    $request_context = $this->mockRequestContext();
    $router = $this->mockRouter($request_context);

    $this->expectRoutingThemeToEqual($request_context, 'app');
    $this->expectAttributesToEqual($request_attributes, 'app', Flavor::POCKETCODE);

    $this->object = $this->mockThemeRequestEventSubscriber([$this->parameter_bag, $router, $app_request]);
    $this->object->onKernelRequest($event);
  }

  private function expectRoutingThemeToEqual(RequestContext|MockObject $request_context, string $theme): void
  {
    $request_context->expects($this->once())
      ->method('setParameter')
      ->with('theme', $theme)
    ;
  }

  private function expectAttributesToEqual(MockObject|ParameterBag $request_attributes, string $theme, string $flavor): void
  {
    $request_attributes->expects($this->exactly(2))
      ->method('set')
      ->willReturnCallback(
        function ($key, $value) use ($theme, $flavor): void {
          switch ($key) {
            case 'theme':
              $this->assertEquals($theme, $value);
              break;
            case 'flavor':
              $this->assertEquals($flavor, $value);
              break;
          }
        }
      )
    ;
  }

  private function mockThemeRequestEventSubscriber(?array $ctor_args = null): ThemeRequestEventSubscriber|MockObject
  {
    if (null === $ctor_args) {
      return $this->getMockBuilder(ThemeRequestEventSubscriber::class)
        ->disableOriginalConstructor()
        ->onlyMethods([])
        ->getMock()
      ;
    }

    return $this->getMockBuilder(ThemeRequestEventSubscriber::class)
      ->setConstructorArgs($ctor_args)
      ->onlyMethods([])
      ->getMock()
    ;
  }

  private function mockParameterBag(): ParameterBagInterface|MockObject
  {
    $parameter_bag = $this->getMockBuilder(ParameterBagInterface::class)
      ->disableOriginalConstructor()
      ->onlyMethods([])
      ->getMockForAbstractClass()
    ;

    $parameter_bag
      ->expects($this->any())
      ->method('get')
      ->will(
        $this->returnCallback(
          static fn ($param): array|string => match ($param) {
            'flavors' => [Flavor::POCKETCODE, Flavor::LUNA],
            'umbrellaTheme' => 'app',
            'adminTheme' => 'admin',
            'defaultFlavor' => Flavor::POCKETCODE,
            default => '',
          }
        )
      )
    ;

    return $parameter_bag;
  }

  /**
   * @param ParameterBag|MockObject|null $attributes
   *
   * @return MockObject|RequestEvent
   *
   * @throws \ReflectionException
   */
  private function mockRequestEvent(int $request_type, $attributes = null, ?string $uri = null)
  {
    $event = $this->getMockBuilder(RequestEvent::class)->disableOriginalConstructor()->getMock();
    $event->expects($this->once())
      ->method('getRequestType')
      ->willReturn($request_type)
    ;

    $request = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();

    if (null !== $attributes) {
      $this->mockProperty(Request::class, $request, 'attributes', $attributes);
    }

    if (null !== $uri) {
      $request->expects($this->any())->method('getUri')->willReturn($uri);
    }

    $event->expects($this->any())
      ->method('getRequest')
      ->willReturn($request)
    ;

    return $event;
  }

  private function mockRequestAttributes(): ParameterBag
  {
    return $this->getMockBuilder(ParameterBag::class)
      ->disableOriginalConstructor()
      ->getMock()
    ;
  }

  private function mockRequestContext(): RequestContext|MockObject
  {
    return $this->getMockBuilder(RequestContext::class)
      ->disableOriginalConstructor()
      ->getMock()
    ;
  }

  private function mockAppRequest(string $response = ''): RequestHelper|MockObject
  {
    $app_request = $this->getMockBuilder(RequestHelper::class)->disableOriginalConstructor()->getMock();

    if ('' !== $response) {
      $app_request->expects($this->once())
        ->method('getThemeDefinedInRequest')
        ->willReturn($response)
      ;
    }

    return $app_request;
  }

  private function mockRouter(RequestContext|MockObject|null $request_context = null): RouterInterface|MockObject
  {
    $router = $this->getMockBuilder(RouterInterface::class)
      ->disableOriginalConstructor()
      ->getMock()
    ;

    if (null !== $request_context) {
      $router->expects($this->once())->method('getContext')->willReturn($request_context);
    }

    return $router;
  }
}
