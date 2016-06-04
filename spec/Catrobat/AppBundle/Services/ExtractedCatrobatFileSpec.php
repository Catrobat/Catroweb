<?php

namespace spec\Catrobat\AppBundle\Services;

use PhpSpec\ObjectBehavior;
use Symfony\Component\Filesystem\Filesystem;

class ExtractedCatrobatFileSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(__SPEC_GENERATED_FIXTURES_DIR__.'base/', '/webpath', 'hash');
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Catrobat\AppBundle\Services\ExtractedCatrobatFile');
    }

    public function it_gets_the_program_name_from_xml()
    {
        $this->getName()->shouldReturn('test');
    }

    public function it_gets_the_program_description_from_xml()
    {
        $this->getDescription()->shouldReturn('');
    }

    public function it_gets_the_language_version_from_xml()
    {
        $this->getLanguageVersion()->shouldReturn('0.92');
    }

    public function it_gets_the_application_version_from_xml()
    {
        $this->getApplicationVersion()->shouldReturn('0.9.7');
    }

    public function it_returns_the_path_of_the_base_directory()
    {
        $this->getPath()->shouldReturn(__SPEC_GENERATED_FIXTURES_DIR__.'base/');
    }

    public function it_returns_the_xml_properties()
    {
        $this->getProgramXmlProperties()->shouldHaveType('SimpleXMLElement');
    }

    public function it_returns_the_path_of_the_automatic_screenshot()
    {
        $this->getScreenshotPath()->shouldReturn(__SPEC_GENERATED_FIXTURES_DIR__.'base/automatic_screenshot.png');
    }

    public function it_returns_the_path_of_the_manual_screenshot()
    {
        $this->beConstructedWith(__SPEC_GENERATED_FIXTURES_DIR__.'program_with_manual_screenshot/', '/webpath', 'hash');
        $this->getScreenshotPath()->shouldReturn(__SPEC_GENERATED_FIXTURES_DIR__.'program_with_manual_screenshot/manual_screenshot.png');
    }

    public function it_returns_the_path_of_the_screenshot()
    {
        $this->beConstructedWith(__SPEC_GENERATED_FIXTURES_DIR__.'program_with_screenshot/', '/webpath', 'hash');
        $this->getScreenshotPath()->shouldReturn(__SPEC_GENERATED_FIXTURES_DIR__.'program_with_screenshot/screenshot.png');
    }

    public function it_throws_an_exception_when_code_xml_is_missing()
    {
        $this->shouldThrow('Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException')->during('__construct', array(__SPEC_GENERATED_FIXTURES_DIR__.'program_with_missing_code_xml/', '/webpath', 'hash'));
    }

    public function it_throws_an_exception_when_code_xml_is_invalid()
    {
        $this->shouldThrow('Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException')->during('__construct', array(__SPEC_GENERATED_FIXTURES_DIR__.'program_with_invalid_code_xml/', '/webpath', 'hash'));
    }
    
    public function it_ignores_an_invalid_0_xmlchar()
    {
        $this->beConstructedWith(__SPEC_FIXTURES_DIR__.'program_with_0_xmlchar/', '/webpath', 'hash');
        $this->getProgramXmlProperties()->shouldHaveType('SimpleXMLElement');
    }
    
    public function it_preserves_invalid_0_xmlchar_from_collissions_with_other_actors()
    {
        $filesystem = new Filesystem();
        $filesystem->mirror(__SPEC_FIXTURES_DIR__.'/program_with_0_xmlchar/', __SPEC_CACHE_DIR__.'/program_with_0_xmlchar/');

        $base_xml_string = file_get_contents(__SPEC_CACHE_DIR__.'/program_with_0_xmlchar/code.xml');
        $count = substr_count($base_xml_string, "<receivedMessage>cupcake4&lt;&#x0;-&#x0;&gt;&#x0;ANYTHING&#x0;</receivedMessage>");
        expect($count)->toBe(1);
        
        $this->beConstructedWith(__SPEC_CACHE_DIR__.'/program_with_0_xmlchar/', '/webpath', 'hash');
        $this->getProgramXmlProperties()->shouldHaveType('SimpleXMLElement');
        $this->saveProgramXmlProperties();

        $base_xml_string = file_get_contents(__SPEC_CACHE_DIR__.'/program_with_0_xmlchar/code.xml');
        $count = substr_count($base_xml_string, "<receivedMessage>cupcake2&lt;&#x0;-&#x0;&gt;cupcake4</receivedMessage>");
        expect($count)->toBe(1);
    }
    
    public function it_preserves_invalid_0_xmlchar_from_collissions_with_anything()
    {
        $filesystem = new Filesystem();
        $filesystem->mirror(__SPEC_FIXTURES_DIR__.'/program_with_0_xmlchar/', __SPEC_CACHE_DIR__.'/program_with_0_xmlchar/');
    
        $base_xml_string = file_get_contents(__SPEC_CACHE_DIR__.'/program_with_0_xmlchar/code.xml');
        $count = substr_count($base_xml_string, "<receivedMessage>cupcake4&lt;&#x0;-&#x0;&gt;&#x0;ANYTHING&#x0;</receivedMessage>");
        expect($count)->toBe(1);
    
        $this->beConstructedWith(__SPEC_CACHE_DIR__.'/program_with_0_xmlchar/', '/webpath', 'hash');
        $this->getProgramXmlProperties()->shouldHaveType('SimpleXMLElement');
        $this->saveProgramXmlProperties();
    
        $base_xml_string = file_get_contents(__SPEC_CACHE_DIR__.'/program_with_0_xmlchar/code.xml');
        $count = substr_count($base_xml_string, "<receivedMessage>cupcake4&lt;&#x0;-&#x0;&gt;&#x0;ANYTHING&#x0;</receivedMessage>");
        expect($count)->toBe(1);
    }
    
    
}
