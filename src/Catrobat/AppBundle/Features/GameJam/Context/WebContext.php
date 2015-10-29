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
        $this->getSymfonySupport()->insertProgram($this->i, array('name' => 'My Program'));
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
        $this->gamejam = $this->getSymfonySupport()->insertDefaultGamejam(array('formurl' => 'https://localhost/url/to/form'));
        $this->my_program = $this->getSymfonySupport()->insertProgram($this->i, array('name' => 'My Program', 'gamejam' => $this->gamejam));
    }
    
    /**
     * @Given /^I filled out the google form$/
     */
    public function iFilledOutTheGoogleForm()
    {
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
    
    
}
