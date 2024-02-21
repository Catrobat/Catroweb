<?php

namespace Tests\PhpUnit\Project\Scratch;

use App\Project\Scratch\AsyncHttpClient;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers  \App\Project\Scratch\AsyncHttpClient
 */
class AsyncHttpClientTest extends TestCase
{
  private AsyncHttpClient $async_http_client;

  protected function setUp(): void
  {
    $this->async_http_client = new AsyncHttpClient(['timeout' => 8.0, 'max_number_of_concurrent_requests' => 4]);
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(AsyncHttpClient::class, $this->async_http_client);
  }

  public function testReturnsEmptyArrayWhenNoDetailsCanBeFetched(): void
  {
    $invalid_scratch_project_id = 0;
    $scratch_info_data = $this->async_http_client->fetchScratchProjectDetails([$invalid_scratch_project_id]);
    $this->assertCount(0, $scratch_info_data);
  }

  public function testReturnsEmptyArrayWhenNoIdsAreGiven(): void
  {
    $scratch_info_data = $this->async_http_client->fetchScratchProjectDetails([]);
    $this->assertCount(0, $scratch_info_data);
  }

  public function testFetchesScratchProjectDetailsOfSingleProject(): void
  {
    $expected_id_of_first_project = 117_697_631;
    $scratch_info_data = $this->async_http_client->fetchScratchProjectDetails([$expected_id_of_first_project]);
    $this->assertCount(1, $scratch_info_data);
    Assert::assertArrayHasKey($expected_id_of_first_project, $scratch_info_data);
    $first_project_data = $scratch_info_data[$expected_id_of_first_project];
    Assert::assertEquals($expected_id_of_first_project, $first_project_data['id']);
    Assert::assertArrayHasKey('title', $first_project_data);
    Assert::assertArrayHasKey('description', $first_project_data);
    Assert::assertArrayHasKey('author', $first_project_data);
    Assert::assertEquals('nposss', $first_project_data['author']['username']);
  }

  public function testHandlesErrorWhenHttpTimeoutIsExceededCorrectly(): void
  {
    // this timeout is so short so that the client will close the connection before the response is received
    $this->async_http_client = new AsyncHttpClient(['timeout' => 0.01]);

    $expected_id_of_first_project = 117_697_631;
    $scratch_info_data = $this->async_http_client->fetchScratchProjectDetails([$expected_id_of_first_project]);
    $this->assertCount(0, $scratch_info_data);
  }

  public function testFetchesScratchProjectDetailsOfTwoProjectsAtOnce(): void
  {
    $expected_id_of_first_project = 117_697_631;
    $expected_id_of_second_project = 118_499_611;

    $scratch_info_data = $this->async_http_client->fetchScratchProjectDetails([$expected_id_of_first_project, $expected_id_of_second_project]);

    $this->assertTheTwoFetchedProjects($scratch_info_data, $expected_id_of_first_project, $expected_id_of_second_project);
  }

  public function testFetchesScratchProjectDetailsOfMoreThanTwoProjectsAtOnceShouldOnlyFetchDetailsOfFirstTwoProjectsBecauseMaximumLimitIsExceeded(): void
  {
    $this->async_http_client = new AsyncHttpClient(['max_number_of_total_requests' => 2, 'max_number_of_concurrent_requests' => 4]);

    $expected_id_of_first_project = 117_697_631;
    $expected_id_of_second_project = 118_499_611;
    $expected_id_of_third_project = 134_333_442;

    $scratch_info_data = $this->async_http_client->fetchScratchProjectDetails([$expected_id_of_first_project,
      $expected_id_of_second_project, $expected_id_of_third_project, ]);

    $this->assertTheTwoFetchedProjects($scratch_info_data, $expected_id_of_first_project, $expected_id_of_second_project);
  }

  private function assertTheTwoFetchedProjects(array $scratch_info_data, int $expected_id_of_first_project, int $expected_id_of_second_project): void
  {
    $this->assertCount(2, $scratch_info_data);
    Assert::assertArrayHasKey($expected_id_of_first_project, $scratch_info_data);
    Assert::assertArrayHasKey($expected_id_of_second_project, $scratch_info_data);

    $first_project_data = $scratch_info_data[$expected_id_of_first_project];
    Assert::assertEquals($expected_id_of_first_project, $first_project_data['id']);
    Assert::assertArrayHasKey('title', $first_project_data);
    Assert::assertArrayHasKey('description', $first_project_data);
    Assert::assertArrayHasKey('author', $first_project_data);
    Assert::assertEquals('nposss', $first_project_data['author']['username']);

    $second_project_data = $scratch_info_data[$expected_id_of_second_project];
    Assert::assertEquals($expected_id_of_second_project, $second_project_data['id']);
    Assert::assertArrayHasKey('title', $second_project_data);
    Assert::assertArrayHasKey('description', $second_project_data);
    Assert::assertArrayHasKey('author', $second_project_data);
    Assert::assertEquals('Techno-CAT', $second_project_data['author']['username']);
  }
}
