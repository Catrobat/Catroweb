<?php

namespace Catrobat\CoreBundle\Spec\Model;

use Catrobat\CoreBundle\Services\CatrobatFileExtractor;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProjectManagerSpec extends ObjectBehavior
{
  
    /**
     * @param \Catrobat\CoreBundle\Services\CatrobatFileExtractor $file_extractor
     * @param \Catrobat\CoreBundle\Services\ProjectFileRepository $file_repository
     * @param \Catrobat\CoreBundle\Services\ScreenshotRepository $screenshot_repository
     * @param \Catrobat\CoreBundle\Services\Validators\ExtractedFileValidator $extracted_file_validator
     * @param \Catrobat\CoreBundle\Entity\ProjectRepository $project_repository
     * @param \Doctrine\ORM\EntityManager $entity_manager
     */
    
    function let($file_extractor, $file_repository, $screenshot_repository, $entity_manager, $project_repository, $extracted_file_validator)
    {
      $this->beConstructedWith($file_extractor, $file_repository, $screenshot_repository, $entity_manager, $project_repository, $extracted_file_validator);
    }
  
    function it_is_initializable()
    {
        $this->shouldHaveType('Catrobat\CoreBundle\Model\ProjectManager');
    }
}
