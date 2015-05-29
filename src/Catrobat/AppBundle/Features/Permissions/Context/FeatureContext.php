<?php
namespace Catrobat\AppBundle\Features\Permissions\Context;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\TableNode;
use Catrobat\AppBundle\Entity\RudeWord;
use Catrobat\AppBundle\Features\Helpers\BaseContext;
use Catrobat\AppBundle\Entity\User;
use Catrobat\AppBundle\Entity\Program;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Catrobat\AppBundle\Services\TokenGenerator;
use Catrobat\AppBundle\Services\CatrobatFileCompressor;
use Catrobat\AppBundle\Entity\FeaturedProgram;
use Catrobat\AppBundle\Entity\ProgramManager;
require_once 'PHPUnit/Framework/Assert/Functions.php';

/**
 * Feature context.
 */
class FeatureContext extends BaseContext
{
    
    // //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // //////////////////////////////////////////// Support Functions

    private function getPhiroProgramFile()
    {
        $filepath = self::FIXTUREDIR . "/GeneratedFixtures/phiro.catrobat";
        assertTrue(file_exists($filepath), "File not found");
        return new UploadedFile($filepath, "test.catrobat");
    }

    private function getLegoProgramFile()
    {
        $filepath = self::FIXTUREDIR . "/GeneratedFixtures/lego.catrobat";
        assertTrue(file_exists($filepath), "File not found");
        return new UploadedFile($filepath, "test.catrobat");
    }
    
    // //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // ////////////////////////////////////////////
    
    /**
     * @When /^I upload a catrobat program with phiro bricks$/
     */
    public function iUploadACatrobatProgramWithPhiroBricks()
    {
        $program = $this->getPhiroProgramFile();
        $response = $this->upload($program, null);
        assertEquals(200, $response->getStatusCode(), "Wrong response code. " . $response->getContent());
    }

    /**
     * @When /^I upload a catrobat program with lego bricks$/
     */
    public function iUploadACatrobatProgramWithLegoBricks()
    {
        $program = $this->getLegoProgramFile();
        $response = $this->upload($program, null);
        assertEquals(200, $response->getStatusCode(), "Wrong response code. " . $response->getContent());
    }

    /**
     * @Then /^the program should be flagged as lego$/
     */
    public function theProgramShouldBeFlaggedAsLego()
    {
        $program_manager = $this->getProgramManger();
        $program = $program_manager->find(1);
        assertNotNull($program, "No program added");
        assertTrue($program->getLego(), "Program is NOT flagged a lego");
    }

    /**
     * @Then /^the program should be flagged as phiro$/
     */
    public function theProgramShouldBeFlaggedAsPhiroPro()
    {
        $program_manager = $this->getProgramManger();
        $program = $program_manager->find(1);
        assertNotNull($program, "No program added");
        assertTrue($program->getPhiro(), "Program is NOT flagged a phiro");
    }
}
