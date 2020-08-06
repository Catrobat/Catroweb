<?php

namespace Tests\phpUnit\Catrobat\Listeners;

use App\Catrobat\Listeners\RemixUpdater;
use App\Catrobat\Services\AsyncHttpClient;
use App\Catrobat\Services\ExtractedCatrobatFile;
use App\Catrobat\Services\RemixData;
use App\Entity\Program;
use App\Entity\RemixManager;
use App\Entity\User;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Tests\phpUnit\Hook\RefreshTestEnvHook;

/**
 * @internal
 * @covers  \App\Catrobat\Listeners\RemixUpdater
 */
class RemixUpdaterTest extends TestCase
{
  private RemixUpdater $remix_updater;

  /**
   * @var MockObject|RemixManager
   */
  private $remix_manager;

  /**
   * @var AsyncHttpClient|MockObject
   */
  private $async_http_client;

  /**
   * @var MockObject|Program
   */
  private $program_entity;

  protected function setUp(): void
  {
    $this->remix_manager = $this->createMock(RemixManager::class);

    $this->async_http_client = $this->createMock(AsyncHttpClient::class);
    $router = $this->createMock(RouterInterface::class);

    $route_map = [
      // It is important to explicitly define all optional parameters!
      ['program', ['id' => '3571', 'flavor' => 'pocketcode'], UrlGeneratorInterface::ABSOLUTE_PATH, 'http://share.catrob.at/details/3571'],
      ['program', ['id' => '3572', 'flavor' => 'pocketcode'], UrlGeneratorInterface::ABSOLUTE_PATH, 'http://share.catrob.at/details/3572'],
    ];

    $router
      ->expects($this->any())
      ->method('generate')
      ->willReturn($this->returnValueMap($route_map))
    ;

    $this->program_entity = $this->createMock(Program::class);

    $this->remix_updater = new RemixUpdater($this->remix_manager, $this->async_http_client, $router, '.');

    $filesystem = new Filesystem();
    $filesystem->mirror(RefreshTestEnvHook::$GENERATED_FIXTURES_DIR.'base/', RefreshTestEnvHook::$CACHE_DIR.'base/');

    $user = $this->createMock(User::class);

    $user
      ->expects($this->any())
      ->method('getUsername')
      ->willReturn('catroweb')
    ;

    $this->program_entity
      ->expects($this->any())
      ->method('getUser')
      ->willReturn($user)
      ;
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(RemixUpdater::class, $this->remix_updater);
  }

  /**
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function testSavesTheNewUrlToXml(): void
  {
    $expected_url = 'http://share.catrob.at/details/3571';
    $xml = simplexml_load_file(RefreshTestEnvHook::$CACHE_DIR.'base/code.xml');
    $file = new ExtractedCatrobatFile(RefreshTestEnvHook::$CACHE_DIR.'base/', '/webpath', 'hash');

    $this->program_entity
      ->expects($this->atLeastOnce())
      ->method('getId')
      ->willReturn('3571')
      ;

    $this->program_entity
      ->expects($this->atLeastOnce())
      ->method('isInitialVersion')
      ->willReturn(true)
      ;

    $this->async_http_client
      ->expects($this->atLeastOnce())
      ->method('fetchScratchProgramDetails')->with($this->isType('array'))
      ->willReturn([])
      ;

    $this->remix_manager
      ->expects($this->atLeastOnce())
      ->method('filterExistingScratchProgramIds')->with(['117697631'])
      ->willReturn([])
      ;

    $this->remix_manager
      ->expects($this->atLeastOnce())
      ->method('addScratchPrograms')
      ->with([])
      ;

    $this->remix_manager->expects($this->atLeastOnce())
      ->method('addRemixes')->with($this->isInstanceOf(Program::class));

    $this->remix_manager->expects($this->atLeastOnce())->method('getProgramRepository');

    Assert::assertNotEquals($xml->header->url->__toString(), $expected_url);

    $this->remix_updater->update($file, $this->program_entity);

    $xml = simplexml_load_file(RefreshTestEnvHook::$CACHE_DIR.'base/code.xml');
    Assert::assertEquals($xml->header->url->__toString(), $expected_url);
  }

  /**
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function testCallFetchesScratchProgramDetailsAndAddScratchProgramMethodIfCatrobatLanguageVersionIs0993(): void
  {
    $new_program_id = '3571';
    $first_expected_scratch_id = '118499611';
    $second_expected_scratch_id = '70058680';
    $expected_scratch_ids = [$first_expected_scratch_id, $second_expected_scratch_id];
    $current_url = 'Scratch 1 [https://scratch.mit.edu/projects/'.$first_expected_scratch_id
        .'], Scratch 2 [https://scratch.mit.edu/projects/'.$second_expected_scratch_id.']';
    $xml = simplexml_load_file(RefreshTestEnvHook::$CACHE_DIR.'base/code.xml');
    $xml->header->catrobatLanguageVersion = '0.993';
    $xml->header->url = $current_url;
    $xml->header->remixOf = '';
    $xml->asXML(RefreshTestEnvHook::$CACHE_DIR.'base/code.xml');

    $file = new ExtractedCatrobatFile(RefreshTestEnvHook::$CACHE_DIR.'base/', '/webpath', 'hash');
    $this->program_entity->expects($this->atLeastOnce())->method('getId')->willReturn($new_program_id);
    $this->program_entity->expects($this->atLeastOnce())->method('isInitialVersion')->willReturn(true);
    $expected_scratch_info = [
      ['id' => $first_expected_scratch_id, 'creator' => ['username' => 'Techno-CAT']],
      ['id' => $second_expected_scratch_id, 'creator' => ['username' => 'bubble103']],
    ];
    $this->async_http_client
      ->expects($this->atLeastOnce())
      ->method('fetchScratchProgramDetails')->with($this->isType('array'))
      ->willReturn($expected_scratch_info)
      ;
    $this->remix_manager
      ->expects($this->atLeastOnce())
      ->method('filterExistingScratchProgramIds')->with($expected_scratch_ids)
      ->willReturn([])
      ;

    $this->remix_manager
      ->expects($this->atLeastOnce())
      ->method('addScratchPrograms')->with($this->isType('array'))
      ->will($this->returnCallback(function ($scratch_programs_data) use ($expected_scratch_info)
      {
        $this->assertCount(2, $scratch_programs_data);
        $this->assertSame($expected_scratch_info, $scratch_programs_data);
      }))
      ;
    $this->remix_manager
      ->expects($this->atLeastOnce())
      ->method('getProgramRepository')
      ;

    $this->remix_manager
      ->expects($this->atLeastOnce())
      ->method('addRemixes')->with($this->isInstanceOf(Program::class));

    $this->remix_updater->update($file, $this->program_entity);
  }

  /**
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function testIgnoresMultipleRemixParentsIfCatrobatLanguageVersionIs0992OrLower(): void
  {
    $new_program_id = '3571';
    $first_expected_scratch_id = '118499611';
    $second_expected_scratch_id = '70058680';
    $current_url = 'Scratch 1 [https://scratch.mit.edu/projects/'.$first_expected_scratch_id
        .'], Scratch 2 [https://scratch.mit.edu/projects/'.$second_expected_scratch_id.']';
    $xml = simplexml_load_file(RefreshTestEnvHook::$CACHE_DIR.'base/code.xml');
    $xml->header->catrobatLanguageVersion = '0.992';
    $xml->header->url = $current_url;
    $xml->header->remixOf = '';
    $xml->asXML(RefreshTestEnvHook::$CACHE_DIR.'base/code.xml');

    $file = new ExtractedCatrobatFile(RefreshTestEnvHook::$CACHE_DIR.'base/', '/webpath', 'hash');
    $this->program_entity->expects($this->atLeastOnce())->method('getId')->willReturn($new_program_id);
    $this->program_entity->expects($this->atLeastOnce())->method('isInitialVersion')->willReturn(true);
    $this->async_http_client
      ->expects($this->atLeastOnce())
      ->method('fetchScratchProgramDetails')->with($this->isType('array'))
      ->willReturn([])
      ;
    $this->remix_manager
      ->expects($this->atLeastOnce())
      ->method('filterExistingScratchProgramIds')->with([$first_expected_scratch_id, $second_expected_scratch_id])
      ->willReturn([])
      ;
    $this->remix_manager->expects($this->atLeastOnce())->method('addScratchPrograms')->with([]);
    $this->remix_manager->expects($this->atLeastOnce())
      ->method('addRemixes')->with($this->isInstanceOf(Program::class));
    $this->remix_manager->expects($this->atLeastOnce())->method('getProgramRepository');
    $this->remix_updater->update($file, $this->program_entity);
  }

  /**
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function testCallFetchesOnlyDetailsOfNotYetExistingScratchPrograms(): void
  {
    $new_program_id = '3571';
    $first_expected_scratch_id = '118499611';
    $second_expected_scratch_id = '70058680';
    $expected_scratch_ids = [$first_expected_scratch_id, $second_expected_scratch_id];
    $expected_already_existing_scratch_programs = [$first_expected_scratch_id];
    $current_url = 'Scratch 1 [https://scratch.mit.edu/projects/'.$first_expected_scratch_id
        .'], Scratch 2 [https://scratch.mit.edu/projects/'.$second_expected_scratch_id.']';
    $xml = simplexml_load_file(RefreshTestEnvHook::$CACHE_DIR.'base/code.xml');
    $xml->header->catrobatLanguageVersion = '0.993';
    $xml->header->url = $current_url;
    $xml->header->remixOf = '';
    $xml->asXML(RefreshTestEnvHook::$CACHE_DIR.'/base/code.xml');

    $file = new ExtractedCatrobatFile(RefreshTestEnvHook::$CACHE_DIR.'/base/', '/webpath', 'hash');
    $this->program_entity->expects($this->atLeastOnce())->method('getId')->willReturn($new_program_id);
    $this->program_entity->expects($this->atLeastOnce())->method('isInitialVersion')->willReturn(true);
    $expected_scratch_info = [['id' => $second_expected_scratch_id, 'creator' => ['username' => 'bubble103']]];
    $this->async_http_client
      ->expects($this->atLeastOnce())
      ->method('fetchScratchProgramDetails')->with($this->isType('array'))
      ->willReturn($expected_scratch_info)
      ;
    $this->remix_manager
      ->expects($this->atLeastOnce())
      ->method('filterExistingScratchProgramIds')->with($expected_scratch_ids)
      ->willReturn($expected_already_existing_scratch_programs)
      ;
    $this->remix_manager
      ->expects($this->atLeastOnce())
      ->method('addScratchPrograms')->with($this->isType('array'))
      ->will($this->returnCallback(function ($scratch_programs_data) use ($expected_scratch_info)
      {
        $this->assertCount(1, $scratch_programs_data);
        $this->assertSame($expected_scratch_info, $scratch_programs_data);
      }))
      ;

    $this->remix_manager->expects($this->atLeastOnce())
      ->method('addRemixes')->with($this->isInstanceOf(Program::class));
    $this->remix_manager->expects($this->atLeastOnce())->method('getProgramRepository');
    $this->remix_updater->update($file, $this->program_entity);
  }

  /**
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function testCallAddRemixesMethodOfRemixManagerWithCorrectRemixesData(): void
  {
    $first_expected_url = 'https://scratch.mit.edu/projects/117697631/';
    $second_expected_url = '/app/project/3570';
    $expected_scratch_info = [[
      'id' => '117697631',
      'creator' => ['username' => 'Techno-CAT'],
      'title' => 'やねうら部屋(びっくりハウス) remix お化け屋敷',
      'description' => '◆「Why!?大喜利」8月のお題・キミのびっくりハウスをつくろう！～やねうら部屋 編～広い“屋根裏部屋”には、'
        .'何もないみたいだね。好きなものを書いたり、おいたりして“びっくり”を作ろう。スプライトには助っ人として、'
        .'マックスとリンゴがあるぞ！◆自由にリミックス（改造）して、遊んでください！面白い作品ができたら、'
        .'こちらまで投稿を！http://www.nhk.or.jp/xxx※リミックス作品を投稿する時は”共有”を忘れないでね。',
    ]];
    $xml = simplexml_load_file(RefreshTestEnvHook::$CACHE_DIR.'base/code.xml');
    $xml->header->catrobatLanguageVersion = '0.993';
    $xml->asXML(RefreshTestEnvHook::$CACHE_DIR.'base/code.xml');

    $file = new ExtractedCatrobatFile(RefreshTestEnvHook::$CACHE_DIR.'/base/', '/webpath', 'hash');
    $this->program_entity->expects($this->atLeastOnce())->method('getId')->willReturn('3571');
    $this->program_entity->expects($this->atLeastOnce())->method('isInitialVersion')->willReturn(true);
    $this->async_http_client
      ->expects($this->atLeastOnce())
      ->method('fetchScratchProgramDetails')->with($this->isType('array'))
      ->willReturn($expected_scratch_info)
      ;
    $this->remix_manager
      ->expects($this->atLeastOnce())
      ->method('filterExistingScratchProgramIds')->with([$expected_scratch_info[0]['id']])
      ->willReturn([])
      ;
    $this->remix_manager->expects($this->atLeastOnce())
      ->method('addRemixes')
      ->will($this->returnCallback(function (Program $project, array $remixes_data) use ($first_expected_url, $second_expected_url)
      {
        $this->assertEquals($this->program_entity, $project);

        $this->assertCount(2, $remixes_data);

        /** @var RemixData $first_parent_remix_data */
        $first_parent_remix_data = $remixes_data[0];
        $this->assertInstanceOf(RemixData::class, $first_parent_remix_data);
        $this->assertSame($first_expected_url, $first_parent_remix_data->getUrl());
        $this->assertSame('117697631', $first_parent_remix_data->getProgramId());
        $this->assertTrue($first_parent_remix_data->isScratchProgram());
        $this->assertTrue($first_parent_remix_data->isAbsoluteUrl());

        /** @var RemixData $second_parent_remix_data */
        $second_parent_remix_data = $remixes_data[1];
        $this->assertInstanceOf(RemixData::class, $second_parent_remix_data);
        $this->assertSame($second_expected_url, $second_parent_remix_data->getUrl());
        $this->assertSame('3570', $second_parent_remix_data->getProgramId());
        $this->assertFalse($second_parent_remix_data->isScratchProgram());
        $this->assertFalse($second_parent_remix_data->isAbsoluteUrl());
      }))
      ;
    $this->remix_manager
      ->expects($this->atLeastOnce())
      ->method('addScratchPrograms')->with($this->isType('array'))
      ->will($this->returnCallback(function ($scratch_programs_data) use ($expected_scratch_info)
      {
        $this->assertCount(1, $scratch_programs_data);
        $this->assertSame($expected_scratch_info, $scratch_programs_data);
      }))
      ;
    $this->remix_manager->expects($this->atLeastOnce())->method('getProgramRepository');
    $this->remix_updater->update($file, $this->program_entity);
  }

  /**
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function testSavesTheOldUrlToRemixOf(): void
  {
    $current_url = 'http://share.catrob.at/details/3570';
    $new_url = 'http://share.catrob.at/details/3571';
    $xml = simplexml_load_file(RefreshTestEnvHook::$CACHE_DIR.'base/code.xml');
    $xml->header->url = $current_url;
    $xml->header->remixOf = '';
    $xml->asXML(RefreshTestEnvHook::$CACHE_DIR.'/base/code.xml');

    $file = new ExtractedCatrobatFile(RefreshTestEnvHook::$CACHE_DIR.'base/', '/webpath', 'hash');
    $this->program_entity->expects($this->atLeastOnce())->method('getId')->willReturn('3571');
    $this->program_entity->expects($this->atLeastOnce())->method('isInitialVersion')->willReturn(true);
    $this->remix_updater->update($file, $this->program_entity);
    $xml = simplexml_load_file(RefreshTestEnvHook::$CACHE_DIR.'base/code.xml');
    Assert::assertEquals($xml->header->url, $new_url);
    Assert::assertEquals($xml->header->remixOf, $current_url);
  }

  /**
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function testSavesTheScratchUrlToRemixOf(): void
  {
    $expected_scratch_program_id = '70058680';
    $current_url = 'https://scratch.mit.edu/projects/'.$expected_scratch_program_id;
    $new_url = 'http://share.catrob.at/details/3571';
    $xml = simplexml_load_file(RefreshTestEnvHook::$CACHE_DIR.'base/code.xml');
    $xml->header->url = $current_url;
    $xml->header->remixOf = '';
    $xml->asXML(RefreshTestEnvHook::$CACHE_DIR.'/base/code.xml');

    $file = new ExtractedCatrobatFile(RefreshTestEnvHook::$CACHE_DIR.'base/', '/webpath', 'hash');
    $this->program_entity->expects($this->atLeastOnce())->method('getId')->willReturn('3571');
    $this->program_entity->expects($this->atLeastOnce())->method('isInitialVersion')->willReturn(true);
    $this->async_http_client->expects($this->atLeastOnce())->method('fetchScratchProgramDetails')->with([])->willReturn([]);
    $this->remix_manager
      ->expects($this->atLeastOnce())
      ->method('filterExistingScratchProgramIds')->with([$expected_scratch_program_id])
      ->willReturn([$expected_scratch_program_id])
      ;
    $this->remix_manager->expects($this->atLeastOnce())->method('addScratchPrograms')->with([]);
    $this->remix_manager->expects($this->atLeastOnce())->method('addRemixes')->with($this->isInstanceOf(Program::class));
    $this->remix_manager->expects($this->atLeastOnce())->method('getProgramRepository');
    $this->remix_updater->update($file, $this->program_entity);
    $xml = simplexml_load_file(RefreshTestEnvHook::$CACHE_DIR.'base/code.xml');
    Assert::assertEquals($xml->header->url, $new_url);
    Assert::assertEquals($xml->header->remixOf, $current_url);
  }

  /**
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function testSavesRemixOfMultipleScratchUrlsToRemixOf(): void
  {
    $first_expected_scratch_id = '118499611';
    $second_expected_scratch_id = '70058680';
    $current_url = 'Scratch 1 [https://scratch.mit.edu/projects/'.$first_expected_scratch_id
        .'], Scratch 2 [https://scratch.mit.edu/projects/'.$second_expected_scratch_id.']';
    $new_url = 'http://share.catrob.at/details/3571';
    $xml = simplexml_load_file(RefreshTestEnvHook::$CACHE_DIR.'base/code.xml');
    $xml->header->catrobatLanguageVersion = '0.993';
    $xml->header->url = $current_url;
    $xml->header->remixOf = '';
    $xml->asXML(RefreshTestEnvHook::$CACHE_DIR.'base/code.xml');
    $this->async_http_client
      ->expects($this->atLeastOnce())
      ->method('fetchScratchProgramDetails')->with($this->isType('array'))
      ->willReturn([])
      ;
    $this->remix_manager
      ->expects($this->atLeastOnce())
      ->method('filterExistingScratchProgramIds')->with([$first_expected_scratch_id, $second_expected_scratch_id])
      ->willReturn([])
      ;
    $this->remix_manager->expects($this->atLeastOnce())->method('addScratchPrograms')->with([]);
    $this->remix_manager->expects($this->atLeastOnce())->method('addRemixes')->with($this->isInstanceOf(Program::class));

    $file = new ExtractedCatrobatFile(RefreshTestEnvHook::$CACHE_DIR.'base/', '/webpath', 'hash');
    $this->program_entity->expects($this->atLeastOnce())->method('getId')->willReturn('3571');
    $this->program_entity->expects($this->atLeastOnce())->method('isInitialVersion')->willReturn(true);
    $this->remix_manager->expects($this->atLeastOnce())->method('getProgramRepository');
    $this->remix_updater->update($file, $this->program_entity);
    $xml = simplexml_load_file(RefreshTestEnvHook::$CACHE_DIR.'base/code.xml');
    Assert::assertEquals($xml->header->url, $new_url);
    Assert::assertEquals($xml->header->remixOf, $current_url);
  }

  /**
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function testUpdateTheRemixOfOfTheEntity(): void
  {
    $current_url = 'http://share.catrob.at/details/3570';
    $xml = simplexml_load_file(RefreshTestEnvHook::$CACHE_DIR.'/base/code.xml');
    $xml->header->url = $current_url;
    $xml->header->remixOf = '';
    $xml->asXML(RefreshTestEnvHook::$CACHE_DIR.'base/code.xml');

    $file = new ExtractedCatrobatFile(RefreshTestEnvHook::$CACHE_DIR.'base/', '/webpath', 'hash');
    $this->program_entity->expects($this->atLeastOnce())->method('getId')->willReturn('3571');
    $this->program_entity->expects($this->atLeastOnce())->method('isInitialVersion')->willReturn(true);
    $this->remix_updater->update($file, $this->program_entity);
    $xml = simplexml_load_file(RefreshTestEnvHook::$CACHE_DIR.'base/code.xml');
    Assert::assertEquals($xml->header->userHandle, 'catroweb');
  }
}
