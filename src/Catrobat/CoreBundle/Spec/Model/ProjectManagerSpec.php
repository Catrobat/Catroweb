<?php

namespace Catrobat\CoreBundle\Spec\Model;

use Catrobat\CoreBundle\Services\CatrobatFileExtractor;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Catrobat\CoreBundle\Model\Requests\AddProjectRequest;
use Symfony\Component\HttpFoundation\File\File;

class ProjectManagerSpec extends ObjectBehavior
{

  /**
   *
   * @param \Catrobat\CoreBundle\Services\CatrobatFileExtractor $file_extractor          
   * @param \Catrobat\CoreBundle\Services\ProjectFileRepository $file_repository          
   * @param \Catrobat\CoreBundle\Services\ScreenshotRepository $screenshot_repository          
   * @param \Catrobat\CoreBundle\Services\Validators\ExtractedFileValidator $extracted_file_validator          
   * @param \Catrobat\CoreBundle\Entity\ProjectRepository $project_repository          
   * @param \Doctrine\ORM\EntityManager $entity_manager          
   * @param \Symfony\Component\HttpFoundation\File\File $file          
   */
  function let($file_extractor, $file_repository, $screenshot_repository, $entity_manager, $project_repository, $extracted_file_validator, $file, $extracted_file)
  {
    $this->beConstructedWith($file_extractor, $file_repository, $screenshot_repository, $entity_manager, $project_repository, $extracted_file_validator);
    // $file_extractor->extract($file)->willReturn($extracted_file);
    // $extracted_file_validator->validate($extracted_file);
  }

  function it_is_initializable()
  {
    $this->shouldHaveType('Catrobat\CoreBundle\Model\ProjectManager');
  }

  /**
   *
   * @param \Catrobat\CoreBundle\Entity\User $user          
   * @param \Catrobat\CoreBundle\Model\Requests\AddProjectRequest $request          
   * @param \Catrobat\CoreBundle\Model\ExtractedCatrobatFile $extracted_file          
   * @param \Catrobat\CoreBundle\Entity\Project $inserted_project          
   */
  function it_returns_the_project_after_successfully_adding_a_project($request, $user, $projectfile, $file, $file_extractor, $extracted_file, $entity_manager, $inserted_project)
  {
    $request->getProjectfile()->willReturn($file);
    $request->getUser()->willReturn($user);
    $file_extractor->extract($file)->willReturn($extracted_file);
    $inserted_project->getId()->willReturn(0);
    $entity_manager->persist(Argument::any())->shouldBecalled();
    $entity_manager->flush()->shouldBecalled();
    
    $this->addProject($request)->shouldHaveType('Catrobat\CoreBundle\Entity\Project');
  }

}
