<?php
namespace Catrobat\CatrowebBundle\Tests\Helper;
use Catrobat\CatrowebBundle\Helper\ProjectDirectoryValidator;

class ProjectDirectoryValidatorTest extends \PHPUnit_Framework_TestCase
{

  public function testGetProjectInfo()
  {
    $validator = new ProjectDirectoryValidator();
    $project_info = $validator->getProjectInfo(__DIR__."/../DataFixtures/ExtractedProjects/simple_project/");
    $this->assertEquals("Simple Project", $project_info['name']);
  }
  
  public function testMissingProjectXml()
  {
    $this->setExpectedException('Catrobat\CatrowebBundle\Exceptions\InvalidCatrobatFileException');
    $validator = new ProjectDirectoryValidator();
    $project_info = $validator->getProjectInfo(__DIR__."/../DataFixtures/ExtractedProjects/missing_project_xml/");
  }
  
}