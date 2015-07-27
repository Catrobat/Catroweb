<?php

namespace spec\Catrobat\AppBundle\Listeners;

use Catrobat\AppBundle\Services\ExtractedCatrobatFile;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Filesystem\Filesystem;

class RemixUpdaterSpec extends ObjectBehavior
{
    /**
   * @param \Catrobat\AppBundle\Entity\ProgramRepository $repository
   * @param \Catrobat\AppBundle\Entity\Program $program_entity
   * @param \Catrobat\AppBundle\Entity\User $user
   * @param \Symfony\Bundle\FrameworkBundle\Routing\Router $router
   */
  public function let($repository, $program_entity, $user, $router)
  {
      $this->beConstructedWith($repository, $router);

      $filesystem = new Filesystem();
      $filesystem->mirror(__SPEC_GENERATED_FIXTURES_DIR__.'/base/', __SPEC_CACHE_DIR__.'/base/');

      $user->getUsername()->willReturn('catroweb');
      $router->generate(Argument::exact('program'), Argument::exact(array('id' => 1337)))->willReturn('http://share.catrob.at/details/1337');
      $router->generate(Argument::exact('program'), Argument::exact(array('id' => 1338)))->willReturn('http://share.catrob.at/details/1338');
      $program_entity->getUser()->willReturn($user);
  }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Catrobat\AppBundle\Listeners\RemixUpdater');
    }

    public function it_saves_the_new_url_to_xml($program_entity)
    {
        $expected_url = 'http://share.catrob.at/details/1337';
    //$expected_url = $client->getContainer()->get('router')->generate("program",array("id"=>1337));
    $xml = simplexml_load_file(__SPEC_CACHE_DIR__.'/base/code.xml');
        $file = new ExtractedCatrobatFile(__SPEC_CACHE_DIR__.'/base/', '/webpath', 'hash');
        $program_entity->getId()->willReturn(1337);

        expect($xml->header->url)->notToBeLike($expected_url);
        $this->update($file, $program_entity);
        $xml = simplexml_load_file(__SPEC_CACHE_DIR__.'/base/code.xml');
        expect($xml->header->url)->toBeLike($expected_url);
    }

    public function it_saves_the_old_url_to_remixOf($program_entity)
    {
        $current_url = 'http://share.catrob.at/details/1337';
        $new_url = 'http://share.catrob.at/details/1338';

        $xml = simplexml_load_file(__SPEC_CACHE_DIR__.'/base/code.xml');
        $xml->header->url = $current_url;
        $xml->header->remixOf = '';
        $xml->asXML(__SPEC_CACHE_DIR__.'/base/code.xml');

        $file = new ExtractedCatrobatFile(__SPEC_CACHE_DIR__.'/base/', '/webpath', 'hash');
        $program_entity->getId()->willReturn(1338);

        $this->update($file, $program_entity);
        $xml = simplexml_load_file(__SPEC_CACHE_DIR__.'/base/code.xml');
        expect($xml->header->url)->toBeLike($new_url);
        expect($xml->header->remixOf)->toBeLike($current_url);
    }

    /**
     * @param \Catrobat\AppBundle\Entity\Program $parent_entity
     */
    public function it_saves_the_scratch_url_to_remixOf($program_entity, $parent_entity, $repository)
    {
        $current_url = 'https://scratch.mit.edu/projects/70058680';
        $new_url = 'http://share.catrob.at/details/1338';

        $repository->find(70058680)->willReturn($parent_entity);

        $xml = simplexml_load_file(__SPEC_CACHE_DIR__.'/base/code.xml');
        $xml->header->url = $current_url;
        $xml->header->remixOf = '';
        $xml->asXML(__SPEC_CACHE_DIR__.'/base/code.xml');

        $file = new ExtractedCatrobatFile(__SPEC_CACHE_DIR__.'/base/', '/webpath', 'hash');
        $program_entity->getId()->willReturn(1338);
        $program_entity->setRemixOf(Argument::any())->shouldNotBeCalled();

        $this->update($file, $program_entity);
        $xml = simplexml_load_file(__SPEC_CACHE_DIR__.'/base/code.xml');
        expect($xml->header->url)->toBeLike($new_url);
        expect($xml->header->remixOf)->toBeLike($current_url);
    }

  /**
   * @param \Catrobat\AppBundle\Entity\Program $parent_entity
   */
  public function it_update_the_remixOf_of_the_entity($program_entity, $parent_entity, $repository)
  {
      $current_url = 'http://share.catrob.at/details/1337';
      $repository->find(1337)->willReturn($parent_entity);
      $program_entity->setRemixOf($parent_entity)->shouldBeCalled();

      $xml = simplexml_load_file(__SPEC_CACHE_DIR__.'/base/code.xml');
      $xml->header->url = $current_url;
      $xml->header->remixOf = '';
      $xml->asXML(__SPEC_CACHE_DIR__.'/base/code.xml');

      $file = new ExtractedCatrobatFile(__SPEC_CACHE_DIR__.'/base/', '/webpath', 'hash');
      $program_entity->getId()->willReturn(1338);

      $this->update($file, $program_entity);
      $xml = simplexml_load_file(__SPEC_CACHE_DIR__.'/base/code.xml');
      expect($xml->header->userHandle)->toBeLike('catroweb');
  }
}
