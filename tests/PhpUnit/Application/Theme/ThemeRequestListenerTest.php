<?php

namespace Tests\PhpUnit\Application\Theme;

use App\Application\Theme\ThemeRequestListener;
use App\System\Testing\PhpUnit\DefaultTestCase;
use App\Utils\RequestHelper;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class ThemeRequestListenerTest.
 *
 * @covers \App\Application\Theme\ThemeRequestListener
 *
 * @internal
 */
class ThemeRequestListenerTest extends DefaultTestCase
{
  /**
   * @var ThemeRequestListener|MockObject
   */
  protected $object;

  /**
   * @var ParameterBagInterface|MockObject
   */
  protected $parameter_bag;

  protected function setUp(): void
  {
    $this->object = $this->mockThemeRequestListener();
    $this->parameter_bag = $this->mockParameterBag();
  }

  /**
   * @group integration
   */
  public function testTestClassExists(): void
  {
    $this->assertTrue(class_exists(ThemeRequestListener::class));
    $this->assertInstanceOf(ThemeRequestListener::class, $this->object);
  }

  /**
   * @group integration
   *
   * @dataProvider kernelRequestDataProvider
   *
   * @throws ReflectionException
   */
  public function testOnKernelRequestThemeInRequest(
    string $request_theme, string $request_uri, string $expected_routing_theme, string $expected_flavor
  ): void {
    $request_attributes = $this->mockRequestAttributes();
    $event = $this->mockRequestEvent(HttpKernelInterface::MASTER_REQUEST, $request_attributes, $request_uri);

    $app_request = $this->mockAppRequest($request_theme);

    $request_context = $this->mockRequestContext();
    $router = $this->mockRouter($request_context);

    $this->expectRoutingThemeToEqual($request_context, $expected_routing_theme);
    $this->expectAttributesToEqual($request_attributes, $expected_routing_theme, $expected_flavor);

    $this->object = $this->mockThemeRequestListener([$this->parameter_bag, $router, $app_request]);
    $this->object->onKernelRequest($event);
  }

  public function kernelRequestDataProvider(): array
  {
    return [
      'Using a valid request theme' => [
        'request_theme' => 'pocketcode',
        'request_uri' => '',
        'expected_routing_theme' => 'app',
        'expected_flavor' => 'pocketcode',
      ],
      'Using a valid request theme 2' => [
        'request_theme' => 'luna',
        'request_uri' => '',
        'expected_routing_theme' => 'app',
        'expected_flavor' => 'luna',
      ],
      'Using umbrella theme that is no flavor must use default flavor' => [
        'request_theme' => 'app',
        'request_uri' => '',
        'expected_routing_theme' => 'app',
        'expected_flavor' => 'pocketcode',
      ],
      'Using a invalid request theme must use default theme/flavor' => [
        'request_theme' => 'invalid',
        'request_uri' => '',
        'expected_routing_theme' => 'app',
        'expected_flavor' => 'pocketcode',
      ],
      'Using a request theme has higher priority than legacy URL theming' => [
        'request_theme' => 'luna',
        'request_uri' => 'http://share.catrob.at/pocketcode',
        'expected_routing_theme' => 'app',
        'expected_flavor' => 'luna',
      ],
      'No set request theme must use and keep legacy URL theming' => [
        'request_theme' => '',
        'request_uri' => 'http://share.catrob.at/luna',
        'expected_routing_theme' => 'luna',
        'expected_flavor' => 'luna',
      ],
      'Umbrella URL theming must use default but keep route' => [
        'request_theme' => '',
        'request_uri' => 'http://share.catrob.at/app/',
        'expected_routing_theme' => 'app',
        'expected_flavor' => 'pocketcode',
      ],
      'Invalid Legacy URL theming must use default but keep route' => [
        'request_theme' => '',
        'request_uri' => 'http://share.catrob.at/invalid',
        'expected_routing_theme' => 'invalid',
        'expected_flavor' => 'pocketcode',
      ],
      'Should also work with index(test?).php in route' => [
        'request_theme' => '',
        'request_uri' => 'http://localhost/index_test.php/luna',
        'expected_routing_theme' => 'luna',
        'expected_flavor' => 'luna',
      ],
      'It must be possible to return from the admin interface' => [
        'request_theme' => 'luna',
        'request_uri' => 'http://localhost/admin',
        'expected_routing_theme' => 'app',
        'expected_flavor' => 'luna',
      ],
      'It must be possible to return from the admin interface (legacy)' => [
        'request_theme' => '',
        'request_uri' => 'http://localhost/admin',
        'expected_routing_theme' => 'app',
        'expected_flavor' => 'pocketcode',
      ],
    ];
  }

  /**
   * @group integration
   *
   * @throws ReflectionException
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
    $this->expectAttributesToEqual($request_attributes, 'app', 'pocketcode');

    $this->object = $this->mockThemeRequestListener([$this->parameter_bag, $router, $app_request]);
    $this->object->onKernelRequest($event);
  }

  /**
   * @param MockObject|RequestContext $request_context
   */
  private function expectRoutingThemeToEqual($request_context, string $theme): void
  {
    $request_context->expects($this->once())
      ->method('setParameter')
      ->with('theme', $theme)
    ;
  }

  /**
   * @param MockObject|ParameterBag $request_attributes
   */
  private function expectAttributesToEqual($request_attributes, string $theme, string $flavor): void
  {
    $request_attributes->expects($this->exactly(2))
      ->method('set')
      ->withConsecutive(
        ['theme', $theme],
        ['flavor', $flavor]
      )
    ;
  }

  /**
   * @return ThemeRequestListener|MockObject
   */
  private function mockThemeRequestListener(array $ctor_args = null)
  {
    if (null === $ctor_args) {
      return $this->getMockBuilder(ThemeRequestListener::class)
        ->disableOriginalConstructor()
        ->setMethodsExcept(['onKernelRequest'])
        ->getMock()
      ;
    }

    return $this->getMockBuilder(ThemeRequestListener::class)
      ->setConstructorArgs($ctor_args)
      ->setMethodsExcept(['onKernelRequest'])
      ->getMock()
    ;
  }

  /**
   * @return MockObject|ParameterBagInterface
   */
  private function mockParameterBag()
  {
    $parameter_bag = $this->getMockBuilder(ParameterBagInterface::class)
      ->disableOriginalConstructor()
      ->setMethodsExcept(['get'])
      ->getMockForAbstractClass()
    ;

    $parameter_bag
      ->expects($this->any())
      ->method('get')
      ->will(
        $this->returnCallback(
          function ($param) {
            switch ($param) {
              case 'flavors':
                return ['pocketcode', 'luna'];

              case 'umbrellaTheme':
                return 'app';

              case 'adminTheme':
                return 'admin';

              case 'defaultFlavor':
                return 'pocketcode';
            }

            return '';
          }
        )
      )
    ;

    return $parameter_bag;
  }

  /**
   * @param ParameterBag|MockObject|null $attributes
   *
   * @throws ReflectionException
   *
   * @return MockObject|RequestEvent
   */
  private function mockRequestEvent(int $request_type, $attributes = null, string $uri = null)
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

  /**
   * @return MockObject|ParameterBagInterface
   */
  private function mockRequestAttributes()
  {
    return $this->getMockBuilder(ParameterBagInterface::class)
      ->disableOriginalConstructor()
      ->getMockForAbstractClass()
    ;
  }

  /**
   * @return MockObject|RequestContext
   */
  private function mockRequestContext()
  {
    return $this->getMockBuilder(RequestContext::class)
      ->disableOriginalConstructor()
      ->getMock()
    ;
  }

  /**
   * @return RequestHelper|MockObject
   */
  private function mockAppRequest(string $response = '')
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

  /**
   * @param MockObject|RequestContext $request_context
   *
   * @return MockObject|RouterInterface
   */
  private function mockRouter($request_context = null)
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
