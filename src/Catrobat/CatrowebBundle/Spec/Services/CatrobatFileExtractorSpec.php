<?php

namespace Catrobat\CatrowebBundle\Spec\Services;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class CatrobatFileExtractorSpec extends ObjectBehavior
{
    function let()
    {
    	$this->beConstructedWith(__DIR__ . "/../Cache/");
    }
    
    function it_is_initializable()
    {
    	$this->shouldHaveType('Catrobat\CatrowebBundle\Services\CatrobatFileExtractor');
    }
    
    function it_extracts_a_valid_file()
    {
    	$filesystem = new Filesystem();
    	$valid_catrobat_file = new File(__DIR__ . "/../../Tests/DataFixtures/CatrobatFiles/scaryghost.catrobat");
    	$path_to_extracted_folder = $this->extract($valid_catrobat_file);
    	//    	echo($path_to_extracted_folder);
    	$filesystem->remove($path_to_extracted_folder->getWrappedObject());
    }
    
    function it_throws_an_exception_while_extracting_an_invalid_file()
    {
    	$invalid_catrobat_file = new File(__DIR__ . "/../../Tests/DataFixtures/CatrobatFiles/invalid_archive.catrobat");
    	$this->shouldThrow('Catrobat\CatrowebBundle\Exceptions\InvalidCatrobatFileException')->duringExtract($invalid_catrobat_file);
    }
}
