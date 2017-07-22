<?php

namespace spec\Catrobat\AppBundle\Listeners;

use Catrobat\AppBundle\Entity\Program;
use Catrobat\AppBundle\Entity\RemixManager;
use Catrobat\AppBundle\Services\ExtractedCatrobatFile;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Filesystem\Filesystem;


class RemixUpdaterSpec extends ObjectBehavior
{
    /**
     * @param \Catrobat\AppBundle\Entity\RemixManager $remix_manager
     * @param \Catrobat\AppBundle\Services\AsyncHttpClient $async_http_client
     * @param \Symfony\Component\Routing\Router $router
     * @param \Catrobat\AppBundle\Entity\Program $program_entity
     * @param \Catrobat\AppBundle\Entity\User $user
     * @internal param \Catrobat\AppBundle\Entity\Program $program_entity
     * @internal param \Catrobat\AppBundle\Entity\RemixManager $remix_manager
     * @internal param \Catrobat\AppBundle\Services\AsyncHttpClient $async_http_client
     */
    public function let($remix_manager, $async_http_client, $router, $program_entity, $user)
    {
        $this->beConstructedWith($remix_manager, $async_http_client, $router, '.');

        $filesystem = new Filesystem();
        $filesystem->mirror(__SPEC_GENERATED_FIXTURES_DIR__.'/base/', __SPEC_CACHE_DIR__.'/base/');

        $user->getUsername()->willReturn('catroweb');

        $router
            ->generate(Argument::exact('program'), Argument::exact(array('id' => 3571)))
            ->willReturn('http://share.catrob.at/details/3571');

        $router
            ->generate(Argument::exact('program'), Argument::exact(array('id' => 3572)))
            ->willReturn('http://share.catrob.at/details/3572');

        $program_entity->getUser()->willReturn($user);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Catrobat\AppBundle\Listeners\RemixUpdater');
    }

    public function it_saves_the_new_url_to_xml($program_entity, $async_http_client, $remix_manager)
    {
        $expected_url = 'http://share.catrob.at/details/3571';
        $xml = simplexml_load_file(__SPEC_CACHE_DIR__.'/base/code.xml');
        $file = new ExtractedCatrobatFile(__SPEC_CACHE_DIR__.'/base/', '/webpath', 'hash');
        $program_entity->getId()->willReturn(3571);
        $program_entity->isInitialVersion()->willReturn(true);

        $async_http_client
            ->fetchScratchProgramDetails(Argument::type('array'))
            ->shouldBeCalled()
            ->willReturn([]);

        $remix_manager
            ->filterExistingScratchProgramIds(Argument::exact([117697631]))
            ->shouldBeCalled()
            ->willReturn([]);

        $remix_manager->addScratchPrograms(Argument::exact([]))->shouldBeCalled();

        $remix_manager
            ->addRemixes(Argument::type('\Catrobat\AppBundle\Entity\Program'), Argument::type('array'))
            ->shouldBeCalled();

        expect($xml->header->url->__toString())->notToBeLike($expected_url);
        $this->update($file, $program_entity);
        $xml = simplexml_load_file(__SPEC_CACHE_DIR__.'/base/code.xml');
        expect($xml->header->url->__toString())->toBeLike($expected_url);
    }

    public function it_call_fetches_scratch_program_details_and_add_scratch_program_method_if_catrobat_language_version_is_0993(
        $program_entity, $remix_manager, $async_http_client)
    {
        $new_program_id = 3571;
        $first_expected_scratch_id = 118499611;
        $second_expected_scratch_id = 70058680;
        $expected_scratch_ids = [$first_expected_scratch_id, $second_expected_scratch_id];
        $current_url = 'Scratch 1 [https://scratch.mit.edu/projects/' . $first_expected_scratch_id
                     . '], Scratch 2 [https://scratch.mit.edu/projects/' . $second_expected_scratch_id . ']';

        $xml = simplexml_load_file(__SPEC_CACHE_DIR__.'/base/code.xml');
        $xml->header->catrobatLanguageVersion = '0.993';
        $xml->header->url = $current_url;
        $xml->header->remixOf = '';
        $xml->asXML(__SPEC_CACHE_DIR__.'/base/code.xml');

        $file = new ExtractedCatrobatFile(__SPEC_CACHE_DIR__.'/base/', '/webpath', 'hash');
        $program_entity->getId()->willReturn($new_program_id);
        $program_entity->isInitialVersion()->willReturn(true);
        $expected_scratch_info = [
            ['id' => $first_expected_scratch_id, 'creator' => ['username' => 'Techno-CAT']],
            ['id' => $second_expected_scratch_id, 'creator' => ['username' => 'bubble103']]
        ];

        $async_http_client
            ->fetchScratchProgramDetails(Argument::type('array'))
            ->shouldBeCalled()
            ->willReturn($expected_scratch_info);

        $remix_manager
            ->filterExistingScratchProgramIds(Argument::exact($expected_scratch_ids))
            ->shouldBeCalled()
            ->willReturn([]);

        $remix_manager
            ->addScratchPrograms(Argument::type('array'))
            ->shouldBeCalled()
            ->will(function ($args) use ($expected_scratch_info) {
                $scratch_programs_data = $args[0];
                expect($scratch_programs_data)->shouldHaveCount(2);
                expect($scratch_programs_data)->shouldReturn($expected_scratch_info);
            });

        $remix_manager
            ->addRemixes(Argument::type('\Catrobat\AppBundle\Entity\Program'), Argument::type('array'))
            ->shouldBeCalled();

        $this->update($file, $program_entity);
    }


    public function it_ignores_multiple_remix_parents_if_catrobat_language_version_is_0992_or_lower(
        $program_entity, $remix_manager, $async_http_client)
    {
        $new_program_id = 3571;
        $first_expected_scratch_id = 118499611;
        $second_expected_scratch_id = 70058680;
        $current_url = 'Scratch 1 [https://scratch.mit.edu/projects/' . $first_expected_scratch_id
                     . '], Scratch 2 [https://scratch.mit.edu/projects/' . $second_expected_scratch_id . ']';

        $xml = simplexml_load_file(__SPEC_CACHE_DIR__.'/base/code.xml');
        $xml->header->catrobatLanguageVersion = '0.992';
        $xml->header->url = $current_url;
        $xml->header->remixOf = '';
        $xml->asXML(__SPEC_CACHE_DIR__.'/base/code.xml');

        $file = new ExtractedCatrobatFile(__SPEC_CACHE_DIR__.'/base/', '/webpath', 'hash');
        $program_entity->getId()->willReturn($new_program_id);
        $program_entity->isInitialVersion()->willReturn(true);

        $async_http_client
            ->fetchScratchProgramDetails(Argument::type('array'))
            ->shouldBeCalled()
            ->willReturn([]);

        $remix_manager
            ->filterExistingScratchProgramIds(Argument::exact([$first_expected_scratch_id, $second_expected_scratch_id]))
            ->shouldBeCalled()
            ->willReturn([]);

        $remix_manager->addScratchPrograms(Argument::exact([]))->shouldBeCalled();

        $remix_manager
            ->addRemixes(Argument::type('\Catrobat\AppBundle\Entity\Program'), Argument::type('array'))
            ->shouldBeCalled();

        $this->update($file, $program_entity);
    }

    public function it_call_fetches_only_details_of_not_yet_existing_scratch_programs($program_entity, $remix_manager,
                                                                                      $async_http_client)
    {
        $new_program_id = 3571;
        $first_expected_scratch_id = 118499611;
        $second_expected_scratch_id = 70058680;
        $expected_scratch_ids = [$first_expected_scratch_id, $second_expected_scratch_id];
        $expected_already_existing_scratch_programs = [$first_expected_scratch_id];
        $current_url = 'Scratch 1 [https://scratch.mit.edu/projects/' . $first_expected_scratch_id
                     . '], Scratch 2 [https://scratch.mit.edu/projects/' . $second_expected_scratch_id . ']';

        $xml = simplexml_load_file(__SPEC_CACHE_DIR__.'/base/code.xml');
        $xml->header->catrobatLanguageVersion = '0.993';
        $xml->header->url = $current_url;
        $xml->header->remixOf = '';
        $xml->asXML(__SPEC_CACHE_DIR__.'/base/code.xml');

        $file = new ExtractedCatrobatFile(__SPEC_CACHE_DIR__.'/base/', '/webpath', 'hash');
        $program_entity->getId()->willReturn($new_program_id);
        $program_entity->isInitialVersion()->willReturn(true);
        $expected_scratch_info = [['id' => $second_expected_scratch_id, 'creator' => ['username' => 'bubble103']]];

        $async_http_client
            ->fetchScratchProgramDetails(Argument::type('array'))
            ->shouldBeCalled()
            ->willReturn($expected_scratch_info);

        $remix_manager
            ->filterExistingScratchProgramIds(Argument::exact($expected_scratch_ids))
            ->shouldBeCalled()
            ->willReturn($expected_already_existing_scratch_programs);

        $remix_manager
            ->addScratchPrograms(Argument::type('array'))
            ->shouldBeCalled()
            ->will(function ($args) use ($expected_scratch_info) {
                $scratch_programs_data = $args[0];
                expect($scratch_programs_data)->shouldHaveCount(1);
                expect($scratch_programs_data)->shouldReturn($expected_scratch_info);
            });

        $remix_manager
            ->addRemixes(Argument::type('\Catrobat\AppBundle\Entity\Program'), Argument::type('array'))
            ->shouldBeCalled();

        $this->update($file, $program_entity);
    }

    public function it_call_add_remixes_method_of_remix_manager_with_correct_remixes_data($program_entity, $remix_manager,
                                                                                          $async_http_client)
    {
        $first_expected_url = 'https://scratch.mit.edu/projects/117697631/';
        $second_expected_url = '/pocketcode/program/3570';
        $expected_scratch_info = [[
            'id' => 117697631,
            'creator' => ['username' => 'Techno-CAT'],
            'title' => 'やねうら部屋(びっくりハウス) remix お化け屋敷',
            'description' => '◆「Why!?大喜利」8月のお題・キミのびっくりハウスをつくろう！～やねうら部屋 編～広い“屋根裏部屋”には、'
                           . '何もないみたいだね。好きなものを書いたり、おいたりして“びっくり”を作ろう。スプライトには助っ人として、'
                           . 'マックスとリンゴがあるぞ！◆自由にリミックス（改造）して、遊んでください！面白い作品ができたら、'
                           . 'こちらまで投稿を！http://www.nhk.or.jp/xxx※リミックス作品を投稿する時は”共有”を忘れないでね。'
        ]];

        $xml = simplexml_load_file(__SPEC_CACHE_DIR__.'/base/code.xml');
        $xml->header->catrobatLanguageVersion = '0.993';
        $xml->asXML(__SPEC_CACHE_DIR__.'/base/code.xml');

        $file = new ExtractedCatrobatFile(__SPEC_CACHE_DIR__.'/base/', '/webpath', 'hash');
        $program_entity->getId()->willReturn(3571);
        $program_entity->isInitialVersion()->willReturn(true);

        $async_http_client
            ->fetchScratchProgramDetails(Argument::type('array'))
            ->shouldBeCalled()
            ->willReturn($expected_scratch_info);

        $remix_manager
            ->filterExistingScratchProgramIds(Argument::exact([$expected_scratch_info[0]['id']]))
            ->shouldBeCalled()
            ->willReturn([]);

        $remix_manager
            ->addRemixes(Argument::type('\Catrobat\AppBundle\Entity\Program'), Argument::type('array'))
            ->shouldBeCalled()
            ->will(function ($args) use ($first_expected_url, $second_expected_url, $program_entity) {
                expect($args[0])->shouldBeEqualTo($program_entity);
                $remixes_data = $args[1];
                expect($remixes_data)->shouldHaveCount(2);

                $first_parent_remix_data = $remixes_data[0];
                expect($first_parent_remix_data)->shouldBeAnInstanceOf('Catrobat\AppBundle\Services\RemixData');
                expect($first_parent_remix_data)->getUrl()->shouldReturn($first_expected_url);
                expect($first_parent_remix_data)->getProgramId()->shouldReturn(117697631);
                expect($first_parent_remix_data)->isScratchProgram()->shouldReturn(true);
                expect($first_parent_remix_data)->isAbsoluteUrl()->shouldReturn(true);

                $second_parent_remix_data = $remixes_data[1];
                expect($second_parent_remix_data)->shouldBeAnInstanceOf('Catrobat\AppBundle\Services\RemixData');
                expect($second_parent_remix_data)->getUrl()->shouldReturn($second_expected_url);
                expect($second_parent_remix_data)->getProgramId()->shouldReturn(3570);
                expect($second_parent_remix_data)->isScratchProgram()->shouldReturn(false);
                expect($second_parent_remix_data)->isAbsoluteUrl()->shouldReturn(false);
            });

        $remix_manager
            ->addScratchPrograms(Argument::type('array'))
            ->shouldBeCalled()
            ->will(function ($args) use ($expected_scratch_info) {
                $scratch_programs_data = $args[0];
                expect($scratch_programs_data)->shouldHaveCount(1);
                expect($scratch_programs_data)->shouldReturn($expected_scratch_info);
            });

        $this->update($file, $program_entity);
    }

    public function it_saves_the_old_url_to_remixOf($program_entity)
    {
        $current_url = 'http://share.catrob.at/details/3570';
        $new_url = 'http://share.catrob.at/details/3571';

        $xml = simplexml_load_file(__SPEC_CACHE_DIR__.'/base/code.xml');
        $xml->header->url = $current_url;
        $xml->header->remixOf = '';
        $xml->asXML(__SPEC_CACHE_DIR__.'/base/code.xml');

        $file = new ExtractedCatrobatFile(__SPEC_CACHE_DIR__.'/base/', '/webpath', 'hash');
        $program_entity->getId()->willReturn(3571);
        $program_entity->isInitialVersion()->willReturn(true);

        $this->update($file, $program_entity);
        $xml = simplexml_load_file(__SPEC_CACHE_DIR__.'/base/code.xml');
        expect($xml->header->url)->toBeLike($new_url);
        expect($xml->header->remixOf)->toBeLike($current_url);
    }

    public function it_saves_the_scratch_url_to_remixOf($program_entity, $async_http_client, $remix_manager)
    {
        $expected_scratch_program_id = 70058680;
        $current_url = 'https://scratch.mit.edu/projects/' . $expected_scratch_program_id;
        $new_url = 'http://share.catrob.at/details/3571';

        $xml = simplexml_load_file(__SPEC_CACHE_DIR__.'/base/code.xml');
        $xml->header->url = $current_url;
        $xml->header->remixOf = '';
        $xml->asXML(__SPEC_CACHE_DIR__.'/base/code.xml');

        $file = new ExtractedCatrobatFile(__SPEC_CACHE_DIR__.'/base/', '/webpath', 'hash');
        $program_entity->getId()->willReturn(3571);
        $program_entity->isInitialVersion()->willReturn(true);

        $async_http_client->fetchScratchProgramDetails(Argument::exact([]))->shouldBeCalled()->willReturn([]);

        $remix_manager
            ->filterExistingScratchProgramIds(Argument::exact([$expected_scratch_program_id]))
            ->shouldBeCalled()
            ->willReturn([$expected_scratch_program_id]);

        $remix_manager->addScratchPrograms(Argument::exact([]))->shouldBeCalled();
        $remix_manager->addRemixes(Argument::type('\Catrobat\AppBundle\Entity\Program'), Argument::type('array'))->shouldBeCalled();

        $this->update($file, $program_entity);
        $xml = simplexml_load_file(__SPEC_CACHE_DIR__.'/base/code.xml');
        expect($xml->header->url)->toBeLike($new_url);
        expect($xml->header->remixOf)->toBeLike($current_url);
    }

    public function it_saves_remix_of_multiple_scratch_urls_to_remixOf($program_entity, $async_http_client, $remix_manager)
    {
        $first_expected_scratch_id = 118499611;
        $second_expected_scratch_id = 70058680;
        $current_url = 'Scratch 1 [https://scratch.mit.edu/projects/' . $first_expected_scratch_id
                     . '], Scratch 2 [https://scratch.mit.edu/projects/' . $second_expected_scratch_id . ']';
        $new_url = 'http://share.catrob.at/details/3571';

        $xml = simplexml_load_file(__SPEC_CACHE_DIR__.'/base/code.xml');
        $xml->header->catrobatLanguageVersion = '0.993';
        $xml->header->url = $current_url;
        $xml->header->remixOf = '';
        $xml->asXML(__SPEC_CACHE_DIR__.'/base/code.xml');

        $async_http_client
            ->fetchScratchProgramDetails(Argument::type('array'))
            ->shouldBeCalled()
            ->willReturn([]);

        $remix_manager
            ->filterExistingScratchProgramIds(Argument::exact([$first_expected_scratch_id, $second_expected_scratch_id]))
            ->shouldBeCalled()
            ->willReturn([]);

        $remix_manager->addScratchPrograms(Argument::exact([]))->shouldBeCalled();
        $remix_manager->addRemixes(Argument::type('\Catrobat\AppBundle\Entity\Program'), Argument::type('array'))->shouldBeCalled();

        $file = new ExtractedCatrobatFile(__SPEC_CACHE_DIR__.'/base/', '/webpath', 'hash');
        $program_entity->getId()->willReturn(3571);
        $program_entity->isInitialVersion()->willReturn(true);

        $this->update($file, $program_entity);
        $xml = simplexml_load_file(__SPEC_CACHE_DIR__.'/base/code.xml');
        expect($xml->header->url)->toBeLike($new_url);
        expect($xml->header->remixOf)->toBeLike($current_url);
    }

    /**
     * @param \Catrobat\AppBundle\Entity\Program $program_entity
     * @param \Catrobat\AppBundle\Entity\Program $parent_entity
     * @param \Catrobat\AppBundle\Entity\ProgramRepository $programRepository
     */
    public function it_update_the_remixOf_of_the_entity($program_entity, $parent_entity, $programRepository)
    {
      $current_url = 'http://share.catrob.at/details/3570';
      $programRepository->find(3570)->willReturn($parent_entity);

      $xml = simplexml_load_file(__SPEC_CACHE_DIR__.'/base/code.xml');
      $xml->header->url = $current_url;
      $xml->header->remixOf = '';
      $xml->asXML(__SPEC_CACHE_DIR__.'/base/code.xml');

      $file = new ExtractedCatrobatFile(__SPEC_CACHE_DIR__.'/base/', '/webpath', 'hash');
      $program_entity->getId()->willReturn(3571);
      $program_entity->isInitialVersion()->willReturn(true);

      $this->update($file, $program_entity);
      $xml = simplexml_load_file(__SPEC_CACHE_DIR__.'/base/code.xml');
      expect($xml->header->userHandle)->toBeLike('catroweb');
    }
}
