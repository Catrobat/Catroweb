<?php
namespace Catrobat\AppBundle\Features\GameJam\Context;

use Behat\Behat\Tester\Exception\PendingException;
use Catrobat\AppBundle\Features\Helpers\BaseContext;
use Behat\Gherkin\Node\PyStringNode;
use Catrobat\AppBundle\Entity\GameJam;

class FeatureContext extends BaseContext
{

    /**
     * Initializes context with parameters from behat.yml.
     *
     * @param array $parameters
     */
    public function __construct($error_directory)
    {
        parent::__construct();
        $this->setErrorDirectory($error_directory);
    }
    
    static public function getAcceptedSnippetType()
    {
        return 'turnip';
    }

    /**
     * @Given There is an ongoing game jam
     */
    public function thereIsAnOngoingGameJam()
    {
        $this->getSymfonySupport()->insertDefaultGamejam();
    }

    /**
     * @When I submit a game
     */
    public function iSubmitAGame()
    {
        $file = $this->getSymfonySupport()->getDefaultProgramFile();
        $this->getSymfonySupport()->submit($file, null);
    }

    /**
     * @Given I submitted a game
     */
    public function iSubmittedAGame()
    {
        $this->getSymfonySupport()->insertDefaultGamejam();
        $file = $this->getSymfonySupport()->getDefaultProgramFile();
        $this->getSymfonySupport()->submit($file, null);
    }
    
    
    /**
     * @Then I should get the url to the google form
     */
    public function iShouldGetTheUrlToTheGoogleForm()
    {
        $answer = json_decode($this->getClient()->getResponse()->getContent(), true);
        assertArrayHasKey('form', $answer);
        assertEquals("https://catrob.at/url/to/form", $answer['form']);
    }

    /**
     * @Then The game is not yet accepted
     */
    public function theGameIsNotYetAccepted()
    {
        $program = $this->getProgramManger()->find(1);
        assertFalse($program->isAccepted());
    }

    /**
     * @When I fill out the google form
     */
    public function iFillOutTheGoogleForm()
    {
        $this->getClient()->request("GET", "/pocketcode/gamejam/submit/1");
        assertEquals("200", $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * @Then My game should be accepted
     */
    public function myGameShouldBeAccepted()
    {
        $program = $this->getProgramManger()->find(1);
        assertTrue($program->isAccepted());
    }

    /**
     * @Given I already submitted my game
     */
    public function iAlreadySubmittedMyGame()
    {
        $file = $this->getSymfonySupport()->getDefaultProgramFile();
        $this->getSymfonySupport()->submit($file, null);
    }

    /**
     * @Given I already filled the google form
     */
    public function iAlreadyFilledTheGoogleForm()
    {
        $this->getClient()->request("GET", "/pocketcode/gamejam/submit/1");
        assertEquals("200", $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * @When I resubmit my game
     */
    public function iResubmitMyGame()
    {
        $file = $this->getSymfonySupport()->getDefaultProgramFile();
        $this->getSymfonySupport()->submit($file, null);
    }

    /**
     * @Then It should be updated
     */
    public function itShouldBeUpdated()
    {
        assertEquals("200", $this->getClient()->getResponse()->getStatusCode());
    }

    /**
     * @Then I should not get the url to the google form
     */
    public function iShouldNotGetTheUrlToTheGoogleForm()
    {
        $answer = json_decode($this->getClient()->getResponse()->getContent(), true);
        assertArrayNotHasKey('form', $answer);
    }

    /**
     * @Then My game should still be accepted
     */
    public function myGameShouldStillBeAccepted()
    {
        $program = $this->getProgramManger()->find(1);
        assertTrue($program->isAccepted());
    }

    /**
     * @Given I did not fill out the google form
     */
    public function iDidNotFillOutTheGoogleForm()
    {
    }

    /**
     * @Given there is no ongoing game jam
     */
    public function thereIsNoOngoingGameJam()
    {
    }

    /**
     * @Then The submission should be rejected
     */
    public function theSubmissionShouldBeRejected()
    {
        $answer = json_decode($this->getClient()->getResponse()->getContent(), true);
        assertNotEquals("200", $answer['statusCode']);
    }

    /**
     * @Then The message schould be:
     */
    public function theMessageSchouldBe(PyStringNode $string)
    {
        $answer = json_decode($this->getClient()->getResponse()->getContent(), true);
        assertEquals($string->getRaw(), $answer['answer']);
    }

    /**
     * @When I upload my game
     */
    public function iUploadMyGame()
    {
        $file = $this->getSymfonySupport()->getDefaultProgramFile();
        $this->getSymfonySupport()->upload($file, null);
    }
}
