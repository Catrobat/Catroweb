<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api;

use App\Api\Services\Utility\UtilityApiFacade;
use App\Api\Services\Utility\UtilityApiLoader;
use App\Api\UtilityApi;
use App\DB\Entity\Flavor;
use App\DB\Entity\System\Survey;
use App\System\Testing\PhpUnit\DefaultTestCase;
use OpenAPI\Server\Model\SurveyResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[CoversClass(UtilityApi::class)]
class UtilityApiTest extends DefaultTestCase
{
  protected UtilityApi $utility_api;

  protected MockObject|UtilityApiFacade $facade;

  /**
   * @throws Exception
   */
  #[\Override]
  protected function setUp(): void
  {
    $this->facade = $this->createMock(UtilityApiFacade::class);
    $this->utility_api = new UtilityApi($this->facade);
  }

  #[Group('unit')]
  public function testHealthCheck(): void
  {
    $response_code = 200;
    $response_headers = [];

    $this->utility_api->healthGet($response_code, $response_headers);

    $this->assertEquals(Response::HTTP_NO_CONTENT, $response_code);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testSurveyLangCodeGetNotFound(): void
  {
    $response_code = 200;
    $response_headers = [];

    $loader = $this->createMock(UtilityApiLoader::class);
    $loader->method('getSurvey')->willReturn(null);
    $this->facade->method('getLoader')->willReturn($loader);

    $response = $this->utility_api->surveyLangCodeGet('de', '', '', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_NOT_FOUND, $response_code);
    $this->assertNull($response);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testSurveyLangCodeGet(): void
  {
    $response_code = 200;
    $response_headers = [];

    $loader = $this->createMock(UtilityApiLoader::class);
    $loader->method('getSurvey')->willReturn($this->createMock(Survey::class));
    $this->facade->method('getLoader')->willReturn($loader);

    $response = $this->utility_api->surveyLangCodeGet('de', '', '', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);

    $this->assertInstanceOf(SurveyResponse::class, $response);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
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

    $response = $this->utility_api->surveyLangCodeGet('de', 'embroidery', 'android', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);

    $this->assertInstanceOf(SurveyResponse::class, $response);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
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

    $response = $this->utility_api->surveyLangCodeGet('de', Flavor::POCKETCODE, '', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);

    $this->assertInstanceOf(SurveyResponse::class, $response);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testSurveyLangCodeGetFlavorError(): void
  {
    $response_code = 200;
    $response_headers = [];

    $loader = $this->createMock(UtilityApiLoader::class);
    $loader->method('getSurvey')->willReturn($this->createMock(Survey::class));
    $loader->method('getSurveyFlavor')->willReturn(null);
    $this->facade->method('getLoader')->willReturn($loader);

    $response = $this->utility_api->surveyLangCodeGet('de', 'invalid', '', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_BAD_REQUEST, $response_code);

    $this->assertNull($response);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testSurveyLangCodeGetPlatform(): void
  {
    $response_code = 200;
    $response_headers = [];

    $loader = $this->createMock(UtilityApiLoader::class);
    $loader->method('getSurvey')->willReturn($this->createMock(Survey::class));
    $this->facade->method('getLoader')->willReturn($loader);

    $response = $this->utility_api->surveyLangCodeGet('de', '', 'ios', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);

    $this->assertInstanceOf(SurveyResponse::class, $response);
  }

  /**
   * @throws Exception
   */
  #[Group('unit')]
  public function testSurveyLangCodeGetPlatformError(): void
  {
    $response_code = 200;
    $response_headers = [];

    $loader = $this->createMock(UtilityApiLoader::class);
    $loader->method('getSurvey')->willReturn($this->createMock(Survey::class));
    $this->facade->method('getLoader')->willReturn($loader);

    $response = $this->utility_api->surveyLangCodeGet('de', '', 'windows', $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_BAD_REQUEST, $response_code);

    $this->assertNull($response);
  }
}
