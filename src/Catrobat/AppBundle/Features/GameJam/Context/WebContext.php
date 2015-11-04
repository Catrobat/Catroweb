<?php
namespace Catrobat\AppBundle\Features\GameJam\Context;

use Behat\Behat\Tester\Exception\PendingException;
use Catrobat\AppBundle\Features\Helpers\BaseContext;
use Behat\Gherkin\Node\PyStringNode;
use Catrobat\AppBundle\Entity\GameJam;
use Behat\Gherkin\Node\TableNode;
use Symfony\Component\HttpFoundation\RedirectResponse;

class WebContext extends BaseContext
{

    private $i;
    
    private $my_program;

    private $gamejam;
    
    private $response;

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

    /**
     * @Given There is an ongoing game jam
     */
    public function thereIsAnOngoingGameJam()
    {
        $this->gamejam = $this->getSymfonySupport()->insertDefaultGamejam();
    }
    
    /**
     * @Given /^I am logged in$/
     */
    public function iAmLoggedIn()
    {
        $this->i = $this->getSymfonySupport()->insertUser(array('name' => 'Generated', 'password' => 'generated'));
        $this->getSymfonySupport()->getClient()->setServerParameter('PHP_AUTH_USER', 'Generated');
        $this->getSymfonySupport()->getClient()->setServerParameter('PHP_AUTH_PW', 'generated');
    }
    
    /**
     * @When /^I visit the details page of my program$/
     */
    public function iVisitTheDetailsPageOfMyProgram()
    {
        if ($this->my_program == null) {
            $this->getSymfonySupport()->insertProgram($this->i, array('name' => 'My Program'));
        }
        $this->response = $this->getSymfonySupport()->getClient()->request("GET", "/pocketcode/program/1");
    }
    
    /**
     * @Then /^There should be a button to submit it to the jam$/
     */
    public function thereShouldBeAButtonToSubmitItToTheJam()
    {
        assertEquals(200, $this->getClient()->getResponse()->getStatusCode());
        assertEquals(1, $this->response->filter("#gamejam-submittion")->count());
    }
    
    /**
     * @Then /^There should not be a button to submit it to the jam$/
     */
    public function thereShouldNotBeAButtonToSubmitItToTheJam()
    {
        assertEquals(200, $this->getClient()->getResponse()->getStatusCode());
        assertEquals(0, $this->response->filter("#gamejam-submittion")->count());
    }
    
    /**
     * @When /^I submit my program to a gamejam$/
     */
    public function iSubmitMyProgramToAGamejam()
    {
        $this->getClient()->followRedirects(false);
        $this->gamejam = $this->getSymfonySupport()->insertDefaultGamejam(array('formurl' => 'https://localhost/url/to/form'));
        $this->iAmLoggedIn();
        $this->getSymfonySupport()->insertProgram($this->i, array('name' => 'My Program'));
        $this->response = $this->getSymfonySupport()->getClient()->request("GET", "/pocketcode/program/1");
        $link = $this->response->filter("#gamejam-submittion")->parents()->link();
        $this->response = $this->getClient()->click($link);
    }
    
    /**
     * @Then /^I should be redirected to the google form$/
     */
    public function iShouldBeRedirectedToTheGoogleForm()
    {
        assertTrue($this->getClient()->getResponse() instanceof RedirectResponse);
        assertEquals("https://localhost/url/to/form", $this->getClient()->getResponse()->headers->get('location'));
    }
    
    /**
     * @Given /^I submitted a program to the gamejam$/
     */
    public function iSubmittedAProgramToTheGamejam()
    {
        if ($this->gamejam == null) {
            $this->gamejam = $this->getSymfonySupport()->insertDefaultGamejam(array('formurl' => 'https://localhost/url/to/form'));
        }
        $this->my_program = $this->getSymfonySupport()->insertProgram($this->i, array('name' => 'My Program', 'gamejam' => $this->gamejam));
    }

    /**
     * @Given /^I submit a program to this gamejam$/
     */
    public function iSubmitAProgramToThisGamejam()
    {
        $this->my_program = $this->getSymfonySupport()->insertProgram($this->i, array('name' => 'My Program'));
        $this->response = $this->getSymfonySupport()->getClient()->request("GET", "/pocketcode/program/1");
        $link = $this->response->filter("#gamejam-submittion")->parents()->link();
        $this->response = $this->getClient()->click($link);
    }

    /**
     * @Given /^I filled out the google form$/
     */
    public function iFilledOutTheGoogleForm()
    {
        $this->my_program = $this->getProgramManger()->find(1);
        $this->my_program->setAcceptedForGameJam(true);
        $this->getManager()->persist($this->my_program);
        $this->getManager()->flush();
    }
    
    /**
     * @When /^I visit the details page of a program from another user$/
     */
    public function iVisitTheDetailsPageOfAProgramFromAnotherUser()
    {
        $other = $this->getSymfonySupport()->insertUser(array('name' => 'other'));
        $this->getSymfonySupport()->insertProgram($other, array('name' => 'other program'));
        $this->response = $this->getSymfonySupport()->getClient()->request("GET", "/pocketcode/program/1");
    }
    
    /**
     * @Given /^There is no ongoing game jam$/
     */
    public function thereIsNoOngoingGameJam()
    {
    }
    
    /**
     * @Given /^I have a limited account$/
     */
    public function iHaveALimitedAccount()
    {
        $this->i->setLimited(true);
        $this->getSymfonySupport()->getManager()->persist($this->i);
        $this->getSymfonySupport()->getManager()->flush($this->i);
    }
    
    /**
     * @When /^I visit my profile$/
     */
    public function iVisitMyProfile()
    {
        $profile_url = $this->getSymfonySupport()->getRouter()->generate("profile", array("flavor" => "pocketcode"));
        $this->response = $this->getClient()->request("GET", $profile_url);
    }

    /**
     * @Then /^I do not see a form to edit my profile$/
     */
    public function iDoNotSeeAFormToEditMyProfile()
    {
        assertEquals(200, $this->getClient()->getResponse()->getStatusCode());
        assertEquals(0, $this->response->filter("#profile-form")->count());
    }
    
    /**
     * @Given /^I have a program named "([^"]*)"$/
     */
    public function iHaveAProgramNamed($arg1)
    {
        $this->getSymfonySupport()->insertProgram($this->i, array('name' => $arg1));
    }
    
    /**
     * @Then /^I see the program "([^"]*)"$/
     */
    public function iSeeTheProgram($arg1)
    {
    }
    
    /**
     * @Then /^I do not see a delete button$/
     */
    public function iDoNotSeeADeleteButton()
    {
        throw new PendingException();
    }
    
    /**
     * @Then /^I do not see a button to change the profile picture$/
     */
    public function iDoNotSeeAButtonToChangeTheProfilePicture()
    {
        assertEquals(200, $this->getClient()->getResponse()->getStatusCode());
        assertEquals(0, $this->response->filter("#avatar-upload")->count());
    }

    /**
     * @Given /^There is an ongoing game jam with the hashtag "([^"]*)"$/
     */
    public function thereIsAnOngoingGameJamWithTheHashtag($hashtag)
    {
        $this->gamejam = $this->getSymfonySupport()->insertDefaultGamejam(array('hashtag' => $hashtag));
    }

    /**
     * @Then /^I should see the hashtag "([^"]*)" in the program description$/
     */
    public function iShouldSeeTheHashtagInTheProgramDescription($hashtag)
    {
        assertContains($hashtag,$this->getClient()->getResponse()->getContent());
    }


}
