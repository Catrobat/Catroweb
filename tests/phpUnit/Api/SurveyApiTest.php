<?php

namespace Tests\Api;

use App\Api\UtilityApi;
use App\Entity\Survey;
use OpenAPI\Server\Model\SurveyResponse;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Response;
use Tests\phpUnit\CatrowebPhpUnit\CatrowebTestCase;

/**
 * @covers \App\Api\UtilityApi
 *
 * @internal
 */
class SurveyApiTest extends CatrowebTestCase
{
  /**
   * @var UtilityApi|MockObject
   */
  private $utility_api;

  public function setUp(): void
  {
    $this->utility_api = $this->getMockBuilder(UtilityApi::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['getActiveSurvey'])
      ->getMock()
    ;
  }

  public function testClassExists(): void
  {
    $this->assertTrue(class_exists(UtilityApi::class));
    $this->assertInstanceOf(UtilityApi::class, $this->utility_api);
  }

  /**
   * @dataProvider dataProvider_GetSurvey
   */
  public function testGetSurveyResponse(?array $survey_data, int $expected_code, ?string $expected_url): void
  {
    $response_code = Response::HTTP_NOT_IMPLEMENTED;
    $response_headers = [];

    $survey = null;
    if (null !== $survey_data) {
      $survey = new Survey();
      $survey->setLanguageCode($survey_data['language_code']);
      $survey->setUrl($survey_data['url']);
    }
    $this->utility_api->expects($this->once())
      ->method('getActiveSurvey')
      ->willReturn($survey)
    ;

    /** @var SurveyResponse|null $survey_response */
    $survey_response = $this->utility_api->surveyLangCodeGet(
      'language_code',
      $response_code,
      $response_headers
    );

    Assert::assertEquals($expected_code, $response_code);
    if (null !== $expected_url) {
      Assert::assertEquals($expected_url, $survey_response->getUrl());
    }
  }

  public function dataProvider_GetSurvey(): array
  {
    return [
      '404' => [
        'survey_data' => null,
        'expected_code' => Response::HTTP_NOT_FOUND,
        'expected_response' => null,
      ],
      '200' => [
        'survey_data' => [
          'language_code' => 'de',
          'url' => 'www.survey.catrob.at',
        ],
        'expected_code' => Response::HTTP_OK,
        'expected_response' => 'www.survey.catrob.at',
      ],
    ];
  }
}
