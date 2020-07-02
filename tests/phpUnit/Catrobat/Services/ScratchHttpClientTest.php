<?php

namespace Tests\phpUnit\Catrobat\Services;

use App\Catrobat\Services\ScratchHttpClient;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\Exception\TimeoutExceptionInterface;

/**
 * @internal
 * @coversNothing
 */
class ScratchHttpClientTest extends TestCase
{
  private ScratchHttpClient $scratch_http_client;

  protected function setUp(): void
  {
    $this->scratch_http_client = new ScratchHttpClient(['timeout' => 8.0]);
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(ScratchHttpClient::class, $this->scratch_http_client);
  }

  public function testGetInvalidProject(): void
  {
    $id = 0;
    $scratch_info_data = $this->scratch_http_client->getProjectData($id);
    self::assertNull($scratch_info_data);
  }

  public function testGetProjectData(): void
  {
    $id = 117697631;
    $scratch_info_data = $this->scratch_http_client->getProjectData($id);
    self::assertEquals($id, $scratch_info_data['id']);
    self::assertArrayHasKey('title', $scratch_info_data);
    self::assertArrayHasKey('description', $scratch_info_data);
    self::assertArrayHasKey('author', $scratch_info_data);
    self::assertEquals('nposss', $scratch_info_data['author']['username']);
  }

  public function testGetInvalidUser(): void
  {
    $name = 'a';
    $scratch_info_data = $this->scratch_http_client->getUserData($name);
    self::assertNull($scratch_info_data);
  }

  public function testGetUserData(): void
  {
    $name = 'The-Green-Dragon';
    $scratch_info_data = $this->scratch_http_client->getUserData($name);
    self::assertEquals($name, $scratch_info_data['username']);
    self::assertArrayHasKey('id', $scratch_info_data);
    self::assertArrayHasKey('history', $scratch_info_data);
    self::assertArrayHasKey('joined', $scratch_info_data['history']);
    self::assertArrayHasKey('profile', $scratch_info_data);
    self::assertArrayHasKey('images', $scratch_info_data['profile']);
    self::assertArrayHasKey('90x90', $scratch_info_data['profile']['images']);
  }

  public function testTimeout(): void
  {
    $this->expectException(TimeoutExceptionInterface::class);
    $id = 117697631;
    $scratch_http_client = new ScratchHttpClient(['timeout' => 0.01]);
    $scratch_http_client->getProjectData($id);
  }

  public function testReturnsEmptyArrayWhenNoDetailsCanBeFetched(): void
  {
    $invalid_scratch_program_id = 0;
    $scratch_info_data = $this->scratch_http_client->fetchScratchProgramDetails([$invalid_scratch_program_id]);
    $this->assertIsIterable($scratch_info_data);
    $this->assertCount(0, $scratch_info_data);
  }

  public function testReturnsEmptyArrayWhenNoIdsAreGiven(): void
  {
    $scratch_info_data = $this->scratch_http_client->fetchScratchProgramDetails([]);
    $this->assertIsIterable($scratch_info_data);
    $this->assertCount(0, $scratch_info_data);
  }

  public function testFetchesScratchProgramDetailsOfSingleProgram(): void
  {
    $expected_id_of_first_program = 117_697_631;
    $scratch_info_data = $this->scratch_http_client->fetchScratchProgramDetails([$expected_id_of_first_program]);
    $this->assertIsIterable($scratch_info_data);
    $this->assertCount(1, $scratch_info_data);
    Assert::assertArrayHasKey($expected_id_of_first_program, $scratch_info_data);
    $first_program_data = $scratch_info_data[$expected_id_of_first_program];
    Assert::assertEquals($expected_id_of_first_program, $first_program_data['id']);
    Assert::assertArrayHasKey('title', $first_program_data);
    Assert::assertArrayHasKey('description', $first_program_data);
    Assert::assertArrayHasKey('author', $first_program_data);
    Assert::assertEquals('nposss', $first_program_data['author']['username']);
  }

  public function testHandlesErrorWhenHttpTimeoutIsExceededCorrectly(): void
  {
    // this timeout is so short so that the client will close the connection before the response is received
    $this->scratch_http_client = new ScratchHttpClient(['timeout' => 0.01]);

    $expected_id_of_first_program = 117_697_631;
    $scratch_info_data = $this->scratch_http_client->fetchScratchProgramDetails([$expected_id_of_first_program]);
    $this->assertIsIterable($scratch_info_data);
    $this->assertCount(0, $scratch_info_data);
  }

  public function testFetchesScratchProgramDetailsOfTwoProgramsAtOnce(): void
  {
    $expected_id_of_first_program = 117_697_631;
    $expected_id_of_second_program = 118_499_611;

    $scratch_info_data = $this->scratch_http_client->fetchScratchProgramDetails([$expected_id_of_first_program, $expected_id_of_second_program]);
    $this->assertIsIterable($scratch_info_data);
    $this->assertCount(2, $scratch_info_data);
    Assert::assertArrayHasKey($expected_id_of_first_program, $scratch_info_data);
    Assert::assertArrayHasKey($expected_id_of_second_program, $scratch_info_data);

    $first_program_data = $scratch_info_data[$expected_id_of_first_program];
    Assert::assertEquals($expected_id_of_first_program, $first_program_data['id']);
    Assert::assertArrayHasKey('title', $first_program_data);
    Assert::assertArrayHasKey('description', $first_program_data);
    Assert::assertArrayHasKey('author', $first_program_data);
    Assert::assertEquals('nposss', $first_program_data['author']['username']);

    $second_program_data = $scratch_info_data[$expected_id_of_second_program];
    Assert::assertEquals($expected_id_of_second_program, $second_program_data['id']);
    Assert::assertArrayHasKey('title', $second_program_data);
    Assert::assertArrayHasKey('description', $second_program_data);
    Assert::assertArrayHasKey('author', $second_program_data);
    Assert::assertEquals('Techno-CAT', $second_program_data['author']['username']);
  }
}
