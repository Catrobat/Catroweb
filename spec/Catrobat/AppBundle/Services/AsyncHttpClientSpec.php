<?php

namespace spec\Catrobat\AppBundle\Services;

use Catrobat\AppBundle\Services\RemixData;
use PhpSpec\ObjectBehavior;

class AsyncHttpClientSpec extends ObjectBehavior
{
  public function let()
  {
    $this->beConstructedWith(['timeout' => 8.0, 'max_number_of_concurrent_requests' => 4]);
  }

  public function it_is_initializable()
  {
    $this->shouldHaveType('Catrobat\AppBundle\Services\AsyncHttpClient');
  }

  public function it_returns_empty_array_when_no_details_can_be_fetched()
  {
    $invalid_scratch_program_id = 0;
    $scratch_info_data = $this->fetchScratchProgramDetails([$invalid_scratch_program_id]);
    $scratch_info_data->shouldBeArray();
    $scratch_info_data->shouldHaveCount(0);
  }

  public function it_returns_empty_array_when_no_ids_are_given()
  {
    $scratch_info_data = $this->fetchScratchProgramDetails([]);
    $scratch_info_data->shouldBeArray();
    $scratch_info_data->shouldHaveCount(0);
  }

  public function it_fetches_scratch_program_details_of_single_program()
  {
    $expected_id_of_first_program = 117697631;
    $scratch_info_data = $this->fetchScratchProgramDetails([$expected_id_of_first_program]);
    $scratch_info_data->shouldBeArray();
    $scratch_info_data->shouldHaveCount(1);
    $scratch_info_data->shouldHaveKey($expected_id_of_first_program);
    $first_program_data = $scratch_info_data[$expected_id_of_first_program];
    $first_program_data->shouldHaveKeyWithValue('id', $expected_id_of_first_program);
    $first_program_data->shouldHaveKey('title');
    $first_program_data->shouldHaveKey('description');
    $first_program_data->shouldHaveKey('creator');
    $first_program_data['creator']->shouldHaveKeyWithValue('username', 'nposss');
  }

  public function it_handles_error_when_http_timeout_is_exceeded_correctly()
  {
    // this timeout is so short so that the client will close the connection before the response is received
    $this->beConstructedWith(['timeout' => 0.01]);

    $expected_id_of_first_program = 117697631;
    $scratch_info_data = $this->fetchScratchProgramDetails([$expected_id_of_first_program]);
    $scratch_info_data->shouldBeArray();
    $scratch_info_data->shouldHaveCount(0);
  }

  public function it_fetches_scratch_program_details_of_two_programs_at_once()
  {
    $expected_id_of_first_program = 117697631;
    $expected_id_of_second_program = 118499611;

    $scratch_info_data = $this->fetchScratchProgramDetails([$expected_id_of_first_program, $expected_id_of_second_program]);
    $scratch_info_data->shouldBeArray();
    $scratch_info_data->shouldHaveCount(2);
    $scratch_info_data->shouldHaveKey($expected_id_of_first_program);
    $scratch_info_data->shouldHaveKey($expected_id_of_second_program);

    $first_program_data = $scratch_info_data[$expected_id_of_first_program];
    $first_program_data->shouldHaveKeyWithValue('id', $expected_id_of_first_program);
    $first_program_data->shouldHaveKey('title');
    $first_program_data->shouldHaveKey('description');
    $first_program_data->shouldHaveKey('creator');
    $first_program_data['creator']->shouldHaveKeyWithValue('username', 'nposss');

    $second_program_data = $scratch_info_data[$expected_id_of_second_program];
    $second_program_data->shouldHaveKeyWithValue('id', $expected_id_of_second_program);
    $second_program_data->shouldHaveKey('title');
    $second_program_data->shouldHaveKey('description');
    $second_program_data->shouldHaveKey('creator');
    $second_program_data['creator']->shouldHaveKeyWithValue('username', 'Techno-CAT');
  }

  public function it_fetches_scratch_program_details_of_more_than_two_programs_at_once_should_only_fetch_details_of_first_two_programs_because_maximum_limit_is_exceeded()
  {
    $this->beConstructedWith(['max_number_of_total_requests' => 2, 'max_number_of_concurrent_requests' => 4]);

    $expected_id_of_first_program = 117697631;
    $expected_id_of_second_program = 118499611;
    $expected_id_of_third_program = 134333442;

    $scratch_info_data = $this->fetchScratchProgramDetails([$expected_id_of_first_program,
      $expected_id_of_second_program, $expected_id_of_third_program]);
    $scratch_info_data->shouldBeArray();
    $scratch_info_data->shouldHaveCount(2);
    $scratch_info_data->shouldHaveKey($expected_id_of_first_program);
    $scratch_info_data->shouldHaveKey($expected_id_of_second_program);

    $first_program_data = $scratch_info_data[$expected_id_of_first_program];
    $first_program_data->shouldHaveKeyWithValue('id', $expected_id_of_first_program);
    $first_program_data->shouldHaveKey('title');
    $first_program_data->shouldHaveKey('description');
    $first_program_data->shouldHaveKey('creator');
    $first_program_data['creator']->shouldHaveKeyWithValue('username', 'nposss');

    $second_program_data = $scratch_info_data[$expected_id_of_second_program];
    $second_program_data->shouldHaveKeyWithValue('id', $expected_id_of_second_program);
    $second_program_data->shouldHaveKey('title');
    $second_program_data->shouldHaveKey('description');
    $second_program_data->shouldHaveKey('creator');
    $second_program_data['creator']->shouldHaveKeyWithValue('username', 'Techno-CAT');
  }

}
