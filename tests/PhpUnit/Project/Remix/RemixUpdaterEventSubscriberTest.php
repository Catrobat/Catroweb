<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project\Remix;

use App\DB\Entity\Flavor;
use App\DB\Entity\Project\Program;
use App\DB\Entity\User\User;
use App\Project\CatrobatFile\ExtractedCatrobatFile;
use App\Project\Remix\RemixData;
use App\Project\Remix\RemixManager;
use App\Project\Remix\RemixUpdaterEventSubscriber;
use App\Project\Scratch\AsyncHttpClient;
use App\Storage\FileHelper;
use App\System\Testing\PhpUnit\Extension\BootstrapExtension;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * @internal
 *
 * @covers  \App\Project\Remix\RemixUpdaterEventSubscriber
 */
class RemixUpdaterEventSubscriberTest extends TestCase
{
  private RemixUpdaterEventSubscriber $remix_updater;

  private MockObject|RemixManager $remix_manager;

  private AsyncHttpClient|MockObject $async_http_client;

  private MockObject|Program $program_entity;

  #[\Override]
  protected function setUp(): void
  {
    $this->remix_manager = $this->createMock(RemixManager::class);

    $this->async_http_client = $this->createMock(AsyncHttpClient::class);
    $router = $this->createMock(RouterInterface::class);
    $logger = $this->createMock(LoggerInterface::class);

    $route_map = [
      // It is important to explicitly define all optional parameters!
      ['program', ['id' => '3571', 'theme' => Flavor::POCKETCODE], UrlGeneratorInterface::ABSOLUTE_PATH, 'http://share.catrob.at/details/3571'],
      ['program', ['id' => '3572', 'theme' => Flavor::POCKETCODE], UrlGeneratorInterface::ABSOLUTE_PATH, 'http://share.catrob.at/details/3572'],
    ];

    $router
      ->expects($this->any())
      ->method('generate')
      ->will($this->returnValueMap($route_map))
    ;

    $this->program_entity = $this->createMock(Program::class);

    $this->remix_updater = new RemixUpdaterEventSubscriber($this->remix_manager, $this->async_http_client, $router, $logger, '.');

    $filesystem = new Filesystem();
    $filesystem->mirror(BootstrapExtension::$GENERATED_FIXTURES_DIR.'base/', BootstrapExtension::$CACHE_DIR.'base/');

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

  #[\Override]
  protected function tearDown(): void
  {
    FileHelper::removeDirectory(BootstrapExtension::$CACHE_DIR.'base/');
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(RemixUpdaterEventSubscriber::class, $this->remix_updater);
  }

  /**
   * @throws \Exception
   */
  public function testSavesTheNewUrlToXml(): void
  {
    $expected_url = 'http://share.catrob.at/details/3571';
    $xml = simplexml_load_file(BootstrapExtension::$CACHE_DIR.'base/code.xml');
    $this->assertInstanceOf(\SimpleXMLElement::class, $xml);
    $file = new ExtractedCatrobatFile(BootstrapExtension::$CACHE_DIR.'base/', '/webpath', 'hash');

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
      ->method('fetchScratchProjectDetails')->with($this->isType('array'))
      ->willReturn([])
    ;

    $this->remix_manager
      ->expects($this->atLeastOnce())
      ->method('filterExistingScratchProjectIds')->with(['117697631'])
      ->willReturn([])
    ;

    $this->remix_manager
      ->expects($this->atLeastOnce())
      ->method('addScratchProjects')
      ->with([])
    ;

    $this->remix_manager->expects($this->atLeastOnce())
      ->method('addRemixes')->with($this->isInstanceOf(Program::class))
    ;

    $this->remix_manager->expects($this->atLeastOnce())->method('getProjectRepository');

    Assert::assertNotEquals($xml->header->url->__toString(), $expected_url);

    $this->remix_updater->update($file, $this->program_entity);

    $xml = simplexml_load_file(BootstrapExtension::$CACHE_DIR.'base/code.xml');
    Assert::assertInstanceOf(\SimpleXMLElement::class, $xml);
    Assert::assertEquals($xml->header->url->__toString(), $expected_url);
  }

  /**
   * @throws \Exception
   *
   * @psalm-suppress UndefinedPropertyAssignment
   */
  public function testCallFetchesScratchProgramDetailsAndAddScratchProgramMethodIfCatrobatLanguageVersionIs0993(): void
  {
    $new_program_id = '3571';
    $first_expected_scratch_id = '118499611';
    $second_expected_scratch_id = '70058680';
    $expected_scratch_ids = [$first_expected_scratch_id, $second_expected_scratch_id];
    $current_url = 'Scratch 1 [https://scratch.mit.edu/projects/'.$first_expected_scratch_id
        .'], Scratch 2 [https://scratch.mit.edu/projects/'.$second_expected_scratch_id.']';
    $xml = simplexml_load_file(BootstrapExtension::$CACHE_DIR.'base/code.xml');
    $this->assertInstanceOf(\SimpleXMLElement::class, $xml);
    $xml->header->catrobatLanguageVersion = '0.993';
    $xml->header->url = $current_url;
    $xml->header->remixOf = '';

    $file_overwritten = $xml->asXML(BootstrapExtension::$CACHE_DIR.'base/code.xml');
    if (!$file_overwritten) {
      throw new \Exception("Can't overwrite code.xml file");
    }

    $file = new ExtractedCatrobatFile(BootstrapExtension::$CACHE_DIR.'base/', '/webpath', 'hash');
    $this->program_entity->expects($this->atLeastOnce())->method('getId')->willReturn($new_program_id);
    $this->program_entity->expects($this->atLeastOnce())->method('isInitialVersion')->willReturn(true);
    $expected_scratch_info = [
      ['id' => $first_expected_scratch_id, 'creator' => ['username' => 'Techno-CAT']],
      ['id' => $second_expected_scratch_id, 'creator' => ['username' => 'bubble103']],
    ];
    $this->async_http_client
      ->expects($this->atLeastOnce())
      ->method('fetchScratchProjectDetails')->with($this->isType('array'))
      ->willReturn($expected_scratch_info)
    ;
    $this->remix_manager
      ->expects($this->atLeastOnce())
      ->method('filterExistingScratchProjectIds')->with($expected_scratch_ids)
      ->willReturn([])
    ;

    $this->remix_manager
      ->expects($this->atLeastOnce())
      ->method('addScratchProjects')->with($this->isType('array'))
      ->will($this->returnCallback(function ($scratch_programs_data) use ($expected_scratch_info): void {
        $this->assertCount(2, $scratch_programs_data);
        $this->assertSame($expected_scratch_info, $scratch_programs_data);
      }))
    ;
    $this->remix_manager
      ->expects($this->atLeastOnce())
      ->method('getProjectRepository')
    ;

    $this->remix_manager
      ->expects($this->atLeastOnce())
      ->method('addRemixes')->with($this->isInstanceOf(Program::class))
    ;

    $this->remix_updater->update($file, $this->program_entity);
  }

  /**
   * @throws \Exception
   *
   * @psalm-suppress UndefinedPropertyAssignment
   */
  public function testIgnoresMultipleRemixParentsIfCatrobatLanguageVersionIs0992OrLower(): void
  {
    $new_program_id = '3571';
    $first_expected_scratch_id = '118499611';
    $second_expected_scratch_id = '70058680';
    $current_url = 'Scratch 1 [https://scratch.mit.edu/projects/'.$first_expected_scratch_id
        .'], Scratch 2 [https://scratch.mit.edu/projects/'.$second_expected_scratch_id.']';
    $xml = simplexml_load_file(BootstrapExtension::$CACHE_DIR.'base/code.xml');
    $this->assertInstanceOf(\SimpleXMLElement::class, $xml);
    $xml->header->catrobatLanguageVersion = '0.992';
    $xml->header->url = $current_url;
    $xml->header->remixOf = '';

    $file_overwritten = $xml->asXML(BootstrapExtension::$CACHE_DIR.'base/code.xml');
    if (!$file_overwritten) {
      throw new \Exception("Can't overwrite code.xml file");
    }

    $file = new ExtractedCatrobatFile(BootstrapExtension::$CACHE_DIR.'base/', '/webpath', 'hash');
    $this->program_entity->expects($this->atLeastOnce())->method('getId')->willReturn($new_program_id);
    $this->program_entity->expects($this->atLeastOnce())->method('isInitialVersion')->willReturn(true);
    $this->async_http_client
      ->expects($this->atLeastOnce())
      ->method('fetchScratchProjectDetails')->with($this->isType('array'))
      ->willReturn([])
    ;
    $this->remix_manager
      ->expects($this->atLeastOnce())
      ->method('filterExistingScratchProjectIds')->with([$first_expected_scratch_id, $second_expected_scratch_id])
      ->willReturn([])
    ;
    $this->remix_manager->expects($this->atLeastOnce())->method('addScratchProjects')->with([]);
    $this->remix_manager->expects($this->atLeastOnce())
      ->method('addRemixes')->with($this->isInstanceOf(Program::class))
    ;
    $this->remix_manager->expects($this->atLeastOnce())->method('getProjectRepository');
    $this->remix_updater->update($file, $this->program_entity);
  }

  /**
   * @throws \Exception
   *
   * @psalm-suppress UndefinedPropertyAssignment
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
    $xml = simplexml_load_file(BootstrapExtension::$CACHE_DIR.'base/code.xml');
    $this->assertInstanceOf(\SimpleXMLElement::class, $xml);
    $xml->header->catrobatLanguageVersion = '0.993';
    $xml->header->url = $current_url;
    $xml->header->remixOf = '';

    $file_overwritten = $xml->asXML(BootstrapExtension::$CACHE_DIR.'base/code.xml');
    if (!$file_overwritten) {
      throw new \Exception("Can't overwrite code.xml file");
    }

    $file = new ExtractedCatrobatFile(BootstrapExtension::$CACHE_DIR.'base/', '/webpath', 'hash');
    $this->program_entity->expects($this->atLeastOnce())->method('getId')->willReturn($new_program_id);
    $this->program_entity->expects($this->atLeastOnce())->method('isInitialVersion')->willReturn(true);
    $expected_scratch_info = [['id' => $second_expected_scratch_id, 'creator' => ['username' => 'bubble103']]];
    $this->async_http_client
      ->expects($this->atLeastOnce())
      ->method('fetchScratchProjectDetails')->with($this->isType('array'))
      ->willReturn($expected_scratch_info)
    ;
    $this->remix_manager
      ->expects($this->atLeastOnce())
      ->method('filterExistingScratchProjectIds')->with($expected_scratch_ids)
      ->willReturn($expected_already_existing_scratch_programs)
    ;
    $this->remix_manager
      ->expects($this->atLeastOnce())
      ->method('addScratchProjects')->with($this->isType('array'))
      ->will($this->returnCallback(function ($scratch_programs_data) use ($expected_scratch_info): void {
        $this->assertCount(1, $scratch_programs_data);
        $this->assertSame($expected_scratch_info, $scratch_programs_data);
      }))
    ;

    $this->remix_manager->expects($this->atLeastOnce())
      ->method('addRemixes')->with($this->isInstanceOf(Program::class))
    ;
    $this->remix_manager->expects($this->atLeastOnce())->method('getProjectRepository');
    $this->remix_updater->update($file, $this->program_entity);
  }

  /**
   * @throws \Exception
   *
   * @psalm-suppress UndefinedPropertyAssignment
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
    $xml = simplexml_load_file(BootstrapExtension::$CACHE_DIR.'base/code.xml');
    $this->assertInstanceOf(\SimpleXMLElement::class, $xml);
    $xml->header->catrobatLanguageVersion = '0.993';

    $file_overwritten = $xml->asXML(BootstrapExtension::$CACHE_DIR.'base/code.xml');
    if (!$file_overwritten) {
      throw new \Exception("Can't overwrite code.xml file");
    }

    $file = new ExtractedCatrobatFile(BootstrapExtension::$CACHE_DIR.'base/', '/webpath', 'hash');
    $this->program_entity->expects($this->atLeastOnce())->method('getId')->willReturn('3571');
    $this->program_entity->expects($this->atLeastOnce())->method('isInitialVersion')->willReturn(true);
    $this->async_http_client
      ->expects($this->atLeastOnce())
      ->method('fetchScratchProjectDetails')->with($this->isType('array'))
      ->willReturn($expected_scratch_info)
    ;
    $this->remix_manager
      ->expects($this->atLeastOnce())
      ->method('filterExistingScratchProjectIds')->with([$expected_scratch_info[0]['id']])
      ->willReturn([])
    ;
    $this->remix_manager->expects($this->atLeastOnce())
      ->method('addRemixes')
      ->will($this->returnCallback(function (Program $project, array $remixes_data) use ($first_expected_url, $second_expected_url): void {
        $this->assertEquals($this->program_entity, $project);

        $this->assertCount(2, $remixes_data);

        /** @var RemixData $first_parent_remix_data */
        $first_parent_remix_data = $remixes_data[0];
        $this->assertInstanceOf(RemixData::class, $first_parent_remix_data);
        $this->assertSame($first_expected_url, $first_parent_remix_data->getUrl());
        $this->assertSame('117697631', $first_parent_remix_data->getProjectId());
        $this->assertTrue($first_parent_remix_data->isScratchProject());
        $this->assertTrue($first_parent_remix_data->isAbsoluteUrl());

        /** @var RemixData $second_parent_remix_data */
        $second_parent_remix_data = $remixes_data[1];
        $this->assertInstanceOf(RemixData::class, $second_parent_remix_data);
        $this->assertSame($second_expected_url, $second_parent_remix_data->getUrl());
        $this->assertSame('3570', $second_parent_remix_data->getProjectId());
        $this->assertFalse($second_parent_remix_data->isScratchProject());
        $this->assertFalse($second_parent_remix_data->isAbsoluteUrl());
      }))
    ;
    $this->remix_manager
      ->expects($this->atLeastOnce())
      ->method('addScratchProjects')->with($this->isType('array'))
      ->will($this->returnCallback(function ($scratch_programs_data) use ($expected_scratch_info): void {
        $this->assertCount(1, $scratch_programs_data);
        $this->assertSame($expected_scratch_info, $scratch_programs_data);
      }))
    ;
    $this->remix_manager->expects($this->atLeastOnce())->method('getProjectRepository');
    $this->remix_updater->update($file, $this->program_entity);
  }

  /**
   * @throws \Exception
   */
  public function testSavesTheOldUrlToRemixOf(): void
  {
    $current_url = 'http://share.catrob.at/details/3570';
    $new_url = 'http://share.catrob.at/details/3571';

    $this->getBaseXmlWithUrl($current_url);

    $file = new ExtractedCatrobatFile(BootstrapExtension::$CACHE_DIR.'base/', '/webpath', 'hash');
    $this->program_entity->expects($this->atLeastOnce())->method('getId')->willReturn('3571');
    $this->program_entity->expects($this->atLeastOnce())->method('isInitialVersion')->willReturn(true);
    $this->remix_updater->update($file, $this->program_entity);
    $xml = simplexml_load_file(BootstrapExtension::$CACHE_DIR.'base/code.xml');
    Assert::assertInstanceOf(\SimpleXMLElement::class, $xml);
    Assert::assertEquals($xml->header->url, $new_url);
    Assert::assertEquals($xml->header->remixOf, $current_url);
  }

  /**
   * @throws \Exception
   */
  public function testSavesTheScratchUrlToRemixOf(): void
  {
    $expected_scratch_program_id = '70058680';
    $current_url = 'https://scratch.mit.edu/projects/'.$expected_scratch_program_id;
    $new_url = 'http://share.catrob.at/details/3571';

    $this->getBaseXmlWithUrl($current_url);

    $file = new ExtractedCatrobatFile(BootstrapExtension::$CACHE_DIR.'base/', '/webpath', 'hash');
    $this->program_entity->expects($this->atLeastOnce())->method('getId')->willReturn('3571');
    $this->program_entity->expects($this->atLeastOnce())->method('isInitialVersion')->willReturn(true);
    $this->async_http_client->expects($this->atLeastOnce())->method('fetchScratchProjectDetails')->with([])->willReturn([]);
    $this->remix_manager
      ->expects($this->atLeastOnce())
      ->method('filterExistingScratchProjectIds')->with([$expected_scratch_program_id])
      ->willReturn([$expected_scratch_program_id])
    ;
    $this->remix_manager->expects($this->atLeastOnce())->method('addScratchProjects')->with([]);
    $this->remix_manager->expects($this->atLeastOnce())->method('addRemixes')->with($this->isInstanceOf(Program::class));
    $this->remix_manager->expects($this->atLeastOnce())->method('getProjectRepository');
    $this->remix_updater->update($file, $this->program_entity);
    $xml = simplexml_load_file(BootstrapExtension::$CACHE_DIR.'base/code.xml');
    Assert::assertInstanceOf(\SimpleXMLElement::class, $xml);
    Assert::assertEquals($xml->header->url, $new_url);
    Assert::assertEquals($xml->header->remixOf, $current_url);
  }

  /**
   * @throws \Exception
   *
   * @psalm-suppress UndefinedPropertyAssignment
   */
  public function testSavesRemixOfMultipleScratchUrlsToRemixOf(): void
  {
    $first_expected_scratch_id = '118499611';
    $second_expected_scratch_id = '70058680';
    $current_url = 'Scratch 1 [https://scratch.mit.edu/projects/'.$first_expected_scratch_id
        .'], Scratch 2 [https://scratch.mit.edu/projects/'.$second_expected_scratch_id.']';
    $new_url = 'http://share.catrob.at/details/3571';
    $xml = simplexml_load_file(BootstrapExtension::$CACHE_DIR.'base/code.xml');
    $this->assertInstanceOf(\SimpleXMLElement::class, $xml);
    $xml->header->catrobatLanguageVersion = '0.993';
    $xml->header->url = $current_url;
    $xml->header->remixOf = '';

    $file_overwritten = $xml->asXML(BootstrapExtension::$CACHE_DIR.'base/code.xml');
    if (!$file_overwritten) {
      throw new \Exception("Can't overwrite code.xml file");
    }

    $this->async_http_client
      ->expects($this->atLeastOnce())
      ->method('fetchScratchProjectDetails')->with($this->isType('array'))
      ->willReturn([])
    ;
    $this->remix_manager
      ->expects($this->atLeastOnce())
      ->method('filterExistingScratchProjectIds')->with([$first_expected_scratch_id, $second_expected_scratch_id])
      ->willReturn([])
    ;
    $this->remix_manager->expects($this->atLeastOnce())->method('addScratchProjects')->with([]);
    $this->remix_manager->expects($this->atLeastOnce())->method('addRemixes')->with($this->isInstanceOf(Program::class));

    $file = new ExtractedCatrobatFile(BootstrapExtension::$CACHE_DIR.'base/', '/webpath', 'hash');
    $this->program_entity->expects($this->atLeastOnce())->method('getId')->willReturn('3571');
    $this->program_entity->expects($this->atLeastOnce())->method('isInitialVersion')->willReturn(true);
    $this->remix_manager->expects($this->atLeastOnce())->method('getProjectRepository');
    $this->remix_updater->update($file, $this->program_entity);
    $xml = simplexml_load_file(BootstrapExtension::$CACHE_DIR.'base/code.xml');
    Assert::assertInstanceOf(\SimpleXMLElement::class, $xml);
    Assert::assertEquals($xml->header->url, $new_url);
    Assert::assertEquals($xml->header->remixOf, $current_url);
  }

  /**
   * @throws \Exception
   */
  public function testUpdateTheRemixOfOfTheEntity(): void
  {
    $current_url = 'http://share.catrob.at/details/3570';
    $this->getBaseXmlWithUrl($current_url);

    $file = new ExtractedCatrobatFile(BootstrapExtension::$CACHE_DIR.'base/', '/webpath', 'hash');
    $this->program_entity->expects($this->atLeastOnce())->method('getId')->willReturn('3571');
    $this->program_entity->expects($this->atLeastOnce())->method('isInitialVersion')->willReturn(true);
    $this->remix_updater->update($file, $this->program_entity);
    $xml = simplexml_load_file(BootstrapExtension::$CACHE_DIR.'base/code.xml');
    Assert::assertInstanceOf(\SimpleXMLElement::class, $xml);
    /* @psalm-suppress UndefinedPropertyAssignment */
    Assert::assertEquals($xml->header->userHandle, 'catroweb');
  }

  /**
   * @throws \Exception
   *
   * @psalm-suppress UndefinedPropertyAssignment
   */
  private function getBaseXmlWithUrl(string $url): \SimpleXMLElement
  {
    $xml = simplexml_load_file(BootstrapExtension::$CACHE_DIR.'base/code.xml');
    $this->assertInstanceOf(\SimpleXMLElement::class, $xml);
    $xml->header->url = $url;
    $xml->header->remixOf = '';
    $file_overwritten = $xml->asXML(BootstrapExtension::$CACHE_DIR.'base/code.xml');
    if (!$file_overwritten) {
      throw new \Exception("Can't overwrite code.xml file");
    }

    return $xml;
  }
}
