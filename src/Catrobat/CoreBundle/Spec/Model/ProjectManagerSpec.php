<?php

namespace Catrobat\CoreBundle\Spec\Model;

use Catrobat\CoreBundle\Services\CatrobatFileExtractor;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProjectManagerSpec extends ObjectBehavior
{
  
    protected $file_extractor;
    protected $file_repository;
    protected $screenshot_repository;
    protected $doctrine;
    protected $extracted_file_validator;

  
    /**
     * @param \Catrobat\CoreBundle\Services\CatrobatFileExtractor $file_extractor
     * @param \Catrobat\CoreBundle\Services\ProjectFileRepository $file_repository
     * @param \Catrobat\CoreBundle\Services\ScreenshotRepository $screenshot_repository
     * @param \Catrobat\CoreBundle\Services\Validators\ExtractedFileValidator $extracted_file_validator
     * @param \Doctrine\Common\Persistence\ObjectManager $doctrine
     */
    
    function let($file_extractor, $file_repository, $screenshot_repository, $doctrine, $extracted_file_validator)
    {
      $this->file_extractor = $file_extractor;
      $this->file_repository = $file_repository;
      $this->screenshot_repository = $screenshot_repository;
      $this->doctrine = $this->getDoctrine();//$this->getDoctrine();
      $this->extracted_file_validator = $extracted_file_validator;
      $this->beConstructedWith($this->file_extractor, $this->file_repository, $this->screenshot_repository,$this->doctrine,$this->extracted_file_validator);
    }
  
    function it_is_initializable()
    {
        $this->shouldHaveType('Catrobat\CoreBundle\Model\ProjectManager');
    }
}
