<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api;

use App\Api\Services\Base\AbstractApiController;
use App\Api\Services\Utility\UtilityApiFacade;
use App\Api\Services\Utility\UtilityApiLoader;
use App\Api\UtilityApi;
use App\DB\Entity\Flavor;
use App\DB\Entity\Survey;
use App\System\Testing\PhpUnit\DefaultTestCase;
use OpenAPI\Server\Api\UtilityApiInterface;
use OpenAPI\Server\Model\SurveyResponse;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 *
 * @coversDefaultClass \App\Api\UtilityApi
 */
class UtilityApiTest extends DefaultTestCase
{
  protected MockObject|UtilityApi $object;

  protected MockObject|UtilityApiFacade $facade;

  #[\Override]
  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(UtilityApi::class)
      ->disableOriginalConstructor()
      ->getMockForAbstractClass()
    ;

    $this->facade = $this->createMock(UtilityApiFacade::class);
    $this->mockProperty(UtilityApi::class, $this->object, 'facade', $this->facade);
  }

  /**
   * @group integration
   *
   * @small
   */
  public function testTestClassExists(): void
  {
    $this->assertTrue(class_exists(UtilityApi::class));
    $this->assertInstanceOf(UtilityApi::class, $this->object);
  }

  /**
   * @group integration
   *
   * @small
   */
  public function testTestClassExtends(): void
  {
    $this->assertInstanceOf(AbstractApiController::class, $this->object);
  }

  /**
   * @group integration
   *
   * @small
   */
  public function testTestClassImplements(): void
  {
    $this->assertInstanceOf(UtilityApiInterface::class, $this->object);
  }

  /**
   * @group integration
   *
   * @small
   */
  public function testCtor(): void
  {
    $this->object = new UtilityApi($this->facade);
    $this->assertInstanceOf(UtilityApi::class, $this->object);
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers \App\Api\UtilityApi::healthGet
   */
  public function testHealthCheck(): void
  {
    $response_code = 200;
    $response_headers = [];

    $this->object->healthGet($response_code, $response_headers);

    $this->assertEquals(Response::HTTP_NO_CONTENT, $response_code);
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers \App\Api\UtilityApi::surveyLangCodeGet
   */
  public function testSurveyLangCodeGetNotFound(): void
  {
    $response_code = 200;
    $response_headers = [];

    $loader = $this->createMock(UtilityApiLoader::class);
    $loader->method('getSurvey')->willReturn(null);
    $this->facade->method('getLoader')->willReturn($loader);

    $response = $this->object->surveyLangCodeGet('de', '', '', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_NOT_FOUND, $response_code);
    $this->assertNull($response);
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers \App\Api\UtilityApi::surveyLangCodeGet
   */
  public function testSurveyLangCodeGet(): void
  {
    $response_code = 200;
    $response_headers = [];

    $loader = $this->createMock(UtilityApiLoader::class);
    $loader->method('getSurvey')->willReturn($this->createMock(Survey::class));
    $this->facade->method('getLoader')->willReturn($loader);

    $response = $this->object->surveyLangCodeGet('de', '', '', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);

    $this->assertInstanceOf(SurveyResponse::class, $response);
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers \App\Api\UtilityApi::surveyLangCodeGet
   */
  public function testSurveyLangCodeGetFlavorPlatform(): void
  {
    $response_code = 200;
    $response_headers = [];

    $loader = $this->createMock(UtilityApiLoader::class);
    $loader->method('getSurvey')->willReturn($this->createMock(Survey::class));
    $flavor = new Flavor();
    $flavor->setName('embroidery');
    $loader->method('getSurveyFlavor')->willReturn($flavor);
    $this->facade->method('getLoader')->willReturn($loader);

    $response = $this->object->surveyLangCodeGet('de', 'embroidery', 'android', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);

    $this->assertInstanceOf(SurveyResponse::class, $response);
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers \App\Api\UtilityApi::surveyLangCodeGet
   */
  public function testSurveyLangCodeGetFlavor(): void
  {
    $response_code = 200;
    $response_headers = [];

    $loader = $this->createMock(UtilityApiLoader::class);
    $loader->method('getSurvey')->willReturn($this->createMock(Survey::class));
    $flavor = new Flavor();
    $flavor->setName(Flavor::POCKETCODE);
    $loader->method('getSurveyFlavor')->willReturn($flavor);
    $this->facade->method('getLoader')->willReturn($loader);

    $response = $this->object->surveyLangCodeGet('de', Flavor::POCKETCODE, '', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);

    $this->assertInstanceOf(SurveyResponse::class, $response);
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers \App\Api\UtilityApi::surveyLangCodeGet
   */
  public function testSurveyLangCodeGetFlavorError(): void
  {
    $response_code = 200;
    $response_headers = [];

    $loader = $this->createMock(UtilityApiLoader::class);
    $loader->method('getSurvey')->willReturn($this->createMock(Survey::class));
    $loader->method('getSurveyFlavor')->willReturn(null);
    $this->facade->method('getLoader')->willReturn($loader);

    $response = $this->object->surveyLangCodeGet('de', 'invalid', '', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_BAD_REQUEST, $response_code);

    $this->assertNull($response);
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers \App\Api\UtilityApi::surveyLangCodeGet
   */
  public function testSurveyLangCodeGetPlatform(): void
  {
    $response_code = 200;
    $response_headers = [];

    $loader = $this->createMock(UtilityApiLoader::class);
    $loader->method('getSurvey')->willReturn($this->createMock(Survey::class));
    $this->facade->method('getLoader')->willReturn($loader);

    $response = $this->object->surveyLangCodeGet('de', '', 'ios', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);

    $this->assertInstanceOf(SurveyResponse::class, $response);
  }

  /**
   * @group unit
   *
   * @small
   *
   * @covers \App\Api\UtilityApi::surveyLangCodeGet
   */
  public function testSurveyLangCodeGetPlatformError(): void
  {
    $response_code = 200;
    $response_headers = [];

    $loader = $this->createMock(UtilityApiLoader::class);
    $loader->method('getSurvey')->willReturn($this->createMock(Survey::class));
    $this->facade->method('getLoader')->willReturn($loader);

    $response = $this->object->surveyLangCodeGet('de', '', 'windows', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_BAD_REQUEST, $response_code);

    $this->assertNull($response);
  }
}
