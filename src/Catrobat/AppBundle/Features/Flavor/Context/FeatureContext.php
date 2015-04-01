<?php

namespace Catrobat\AppBundle\Features\Flavor\Context;

use Catrobat\AppBundle\Entity\RudeWord;
use Catrobat\AppBundle\Features\Helpers\BaseContext;
use Catrobat\AppBundle\Entity\User;
use Catrobat\AppBundle\Entity\Program;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Catrobat\AppBundle\Services\TokenGenerator;
use Catrobat\AppBundle\Services\CatrobatFileCompressor;
use Catrobat\AppBundle\Entity\FeaturedProgram;
use Catrobat\AppBundle\Model\ProgramManager;

require_once 'PHPUnit/Framework/Assert/Functions.php';

/**
 * Feature context.
 */
class FeatureContext extends BaseContext
{

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////// Support Functions

  private function getStandardProgramFile()
  {
    $filepath = self::FIXTUREDIR . "test.catrobat";
    assertTrue(file_exists($filepath), "File not found");
    return new UploadedFile($filepath, "test.catrobat");
  }

  private function getKodeyProgramFile()
  {
    $filepath = $this->generateProgramFileWith(array('applicationName' => 'Pocket Kodey'));
    assertTrue(file_exists($filepath), "File not found");
    return new UploadedFile($filepath, "program_generated.catrobat");
  }

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////

    /**
     * @When /^I upload a catrobat program with the kodey app$/
     */
    public function iUploadACatrobatProgramWithTheKodeyApp()
    {
        $user = $this->insertUser();
        $program = $this->getKodeyProgramFile();
        $response = $this->upload($program, $user);
        assertEquals(200, $response->getStatusCode(), "Wrong response code. " . $response->getContent());
    }

    /**
     * @Then /^the program should be flagged as kodey$/
     */
    public function theProgramShouldBeFlaggedAsKodey()
    {
        $program_manager = $this->getProgramManger();
        $program = $program_manager->find(1);
        assertNotNull($program, "No program added");
        assertEquals("pocketkodey", $program->getFlavor(), "Program is NOT flagged a kodey");
    }

    /**
     * @When /^I upload a standard catrobat program$/
     */
    public function iUploadAStandardCatrobatProgram()
    {
        $user = $this->insertUser();
        $program = $this->getStandardProgramFile();
        $response = $this->upload($program, $user);
        assertEquals(200, $response->getStatusCode(), "Wrong response code. " . $response->getContent());
    }

    /**
     * @Then /^the program should not be flagged as kodey$/
     */
    public function theProgramShouldNotBeFlaggedAsKodey()
    {
        $program_manager = $this->getProgramManger();
        $program = $program_manager->find(1);
        assertNotNull($program, "No program added");
        assertNotEquals("pocketkodey", $program->getFlavor(), "Program is flagged a kodey");
    }
    

    
}
