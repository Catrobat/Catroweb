<?php

namespace Catrobat\AppBundle\Features\GameJam\Context;

use Behat\Behat\Tester\Exception\PendingException;
use Catrobat\AppBundle\Features\Helpers\BaseContext;
use Behat\Gherkin\Node\TableNode;
use Behat\Gherkin\Node\PyStringNode;
use Prophecy\Prophet;
use Catrobat\AppBundle\Entity\GameJam;
use Symfony\Component\HttpFoundation\Request;
use Catrobat\AppBundle\Controller\Api\UploadController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Catrobat\AppBundle\Entity\Program;
use Prophecy\Argument;

class DomainContext extends BaseContext
{

  private $prophet;

  private $gamejam_repository;

  private $tokengenerator;

  private $translator;

  private $last_result;

  public function __construct()
  {
    $this->prophet = new Prophet();
    $this->tokengenerator = $this->prophet->prophesize('Catrobat\AppBundle\Services\TokenGenerator');
    $this->translator = $this->prophet->prophesize('Symfony\Component\Translation\TranslatorInterface');


  }

  static public function getAcceptedSnippetType()
  {
    return 'turnip';
  }

  /**
   * @Given I am :arg1 with email :arg2
   */
  public function iAmWithEmail($arg1, $arg2)
  {
    throw new PendingException();
  }

  /**
   * @When I submit a game which gets the id :arg1
   */
  public function iSubmitAGameWhichGetsTheId($arg1)
  {
    throw new PendingException();
  }

  /**
   * @Then The following patameters should be set in the form url:
   */
  public function theFollowingPatametersShouldBeSetInTheFormUrl(TableNode $table)
  {
    throw new PendingException();
  }

  /**
   * @Given The form url of the current jam is
   */
  public function theFormUrlOfTheCurrentJamIs(PyStringNode $string)
  {
    throw new PendingException();
  }

  /**
   * @Then The returned url should be
   */
  public function theReturnedUrlShouldBe(PyStringNode $string)
  {
    throw new PendingException();
  }

  /**
   * @Given The jam is on :arg1
   */
  public function theJamIsOn($arg1)
  {
    throw new PendingException();
  }

  /**
   * @Given I filled the google form for my game with id :arg1
   */
  public function iFilledTheGoogleFormForMyGameWithId($arg1)
  {
    throw new PendingException();
  }

  /**
   * @When I submit it to google
   */
  public function iSubmitItToGoogle()
  {
    throw new PendingException();
  }

  /**
   * @Then The url :arg1 should be called
   */
  public function theUrlShouldBeCalled($arg1)
  {
    throw new PendingException();
  }

  /**
   * @Given There is an ongoing game jam
   */
  public function thereIsAnOngoingGameJam()
  {
    $gamejam = new GameJam();
    $gamejam->setName("Generated Jam");
    $gamejam->setFormUrl('https://catrob.at/urlToForm');
    $this->gamejam_repository = $this->prophet->prophesize('Catrobat\AppBundle\Entity\GameJamRepository');
    $this->gamejam_repository->getCurrentGameJam()->willReturn($gamejam);
  }

  /**
   * @When I submit a game
   */
  public function iSubmitAGame()
  {
    $request = new Request();
    $request->files->add(['file1' => new UploadedFile(self::FIXTUREDIR . 'test.catrobat', "generated")]);
    $request->request->set('fileChecksum', md5_file(self::FIXTUREDIR . 'test.catrobat'));

    $usermanager = $this->prophet->prophesize('Catrobat\AppBundle\Entity\UserManager')->reveal();

    $user = $this->prophet->prophesize('Catrobat\AppBundle\Entity\User');

    $token = $this->prophet->prophesize('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
    $token->getUser()->willReturn($user->reveal());
    $tokenstorage = $this->prophet->prophesize('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage');
    $tokenstorage->getToken()->willReturn($token->reveal());

    $programmanager = $this->prophet->prophesize('Catrobat\AppBundle\Entity\ProgramManager');
    $programmanager->addProgram(Argument::any())->willReturn(new Program());

    $controller = new UploadController($usermanager, $tokenstorage->reveal(), $programmanager->reveal(), $this->tokengenerator->reveal(), $this->translator->reveal());
    $this->last_result = $controller->uploadAction($request);
  }

  /**
   * @Then I should get the url to the google form
   */
  public function iShouldGetTheUrlToTheGoogleForm()
  {
    expect(json_decode($this->last_result->getContent(), true)['url'])->toBe('https://catrob.at/urlToForm');
  }

  /**
   * @Then The game is not yet accepted
   */
  public function theGameIsNotYetAccepted()
  {
    throw new PendingException();
  }

  /**
   * @Given I submitted a game
   */
  public function iSubmittedAGame()
  {
    throw new PendingException();
  }

  /**
   * @When I fill out the google form
   */
  public function iFillOutTheGoogleForm()
  {
    throw new PendingException();
  }

  /**
   * @Then My game should be accepted
   */
  public function myGameShouldBeAccepted()
  {
    throw new PendingException();
  }

  /**
   * @Given I already submitted my game
   */
  public function iAlreadySubmittedMyGame()
  {
    throw new PendingException();
  }

  /**
   * @Given I already filled the google form
   */
  public function iAlreadyFilledTheGoogleForm()
  {
    throw new PendingException();
  }

  /**
   * @When I resubmit my game
   */
  public function iResubmitMyGame()
  {
    throw new PendingException();
  }

  /**
   * @Then It should be updated
   */
  public function itShouldBeUpdated()
  {
    throw new PendingException();
  }

  /**
   * @Then I should not get then url to the google form
   */
  public function iShouldNotGetThenUrlToTheGoogleForm()
  {
    throw new PendingException();
  }

  /**
   * @Then My game should still be accepted
   */
  public function myGameShouldStillBeAccepted()
  {
    throw new PendingException();
  }

  /**
   * @Given I did not fill out the google form
   */
  public function iDidNotFillOutTheGoogleForm()
  {
    throw new PendingException();
  }

  /**
   * @Given there is no ongoing game jam
   */
  public function thereIsNoOngoingGameJam()
  {
    throw new PendingException();
  }

  /**
   * @Then The submission should be rejected
   */
  public function theSubmissionShouldBeRejected()
  {
    throw new PendingException();
  }

  /**
   * @Then The message schould be:
   */
  public function theMessageSchouldBe(PyStringNode $string)
  {
    throw new PendingException();
  }

  /**
   * @When I upload my game
   */
  public function iUploadMyGame()
  {
    throw new PendingException();
  }
}
