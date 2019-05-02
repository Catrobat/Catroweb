<?php

use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Symfony\Component\Finder\Finder;
use PHPUnit\Framework\Assert;


/**
 * Class FeatureContext
 * @package App\Catrobat\Features\GameJam\Context
 */
class GamejamFeatureContext extends BaseContext
{

  /**
   * @var
   */
  private $i;

  /**
   * @var
   */
  private $gamejam;

  /**
   * Initializes context with parameters from behat.yml.
   *
   * @param $error_directory
   */
  public function __construct($error_directory)
  {
    parent::__construct();
    $this->setErrorDirectory($error_directory);
  }

  /**
   * @return string
   */
  static public function getAcceptedSnippetType()
  {
    return 'turnip';
  }

  /**
   * @Given There is an ongoing game jam
   *
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function thereIsAnOngoingGameJam()
  {
    $this->gamejam = $this->getSymfonySupport()->insertDefaultGamejam();
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
   *
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function iSubmittedAGame()
  {
    if ($this->gamejam == null)
    {
      $this->getSymfonySupport()->insertDefaultGamejam();
    }
    $file = $this->getSymfonySupport()->getDefaultProgramFile();
    $this->getSymfonySupport()->submit($file, null);
  }

  /**
   * @Then I should get the url to the google form
   */
  public function iShouldGetTheUrlToTheGoogleForm()
  {
    $answer = json_decode($this->getClient()
      ->getResponse()
      ->getContent(), true);
    Assert::assertArrayHasKey('form', $answer);
    Assert::assertEquals("https://catrob.at/url/to/form", $answer['form']);
  }

  /**
   * @Then The game is not yet accepted
   */
  public function theGameIsNotYetAccepted()
  {
    $program = $this->getProgramManger()->find(1);
    Assert::assertFalse($program->isAcceptedForGameJam());
  }

  /**
   * @When I fill out the google form
   */
  public function iFillOutTheGoogleForm()
  {
    $this->getClient()->request("GET", "/app/api/gamejam/finalize/1");
    Assert::assertEquals("200", $this->getClient()
      ->getResponse()
      ->getStatusCode());
  }

  /**
   * @Then My game should be accepted
   */
  public function myGameShouldBeAccepted()
  {
    $program = $this->getProgramManger()->find(1);
    Assert::assertTrue($program->isAcceptedForGameJam());
  }

  /**
   * @Given I already submitted my game
   */
  public function iAlreadySubmittedMyGame()
  {
    $file = $this->getSymfonySupport()->getDefaultProgramFile();
    $this->getSymfonySupport()->submit($file, $this->i);
  }

  /**
   * @Given the program has the id :arg1
   */
  public function theProgramHasTheId($id)
  {
    /**
     * @var \App\Entity\Program $program
     */
    $program = $this->getProgramManger()->findAll()[0];
    $program->setId($id);
    $this->getManager()->persist($program);
    $this->getManager()->flush();
  }


  /**
   * @Given I already filled the google form
   */
  public function iAlreadyFilledTheGoogleForm()
  {
    $this->getClient()->request("GET", "/app/api/gamejam/finalize/1");
    Assert::assertEquals("200", $this->getClient()
      ->getResponse()
      ->getStatusCode());
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
    Assert::assertEquals("200", $this->getClient()
      ->getResponse()
      ->getStatusCode());
  }

  /**
   * @Then I should not get the url to the google form
   */
  public function iShouldNotGetTheUrlToTheGoogleForm()
  {
    $answer = json_decode($this->getClient()
      ->getResponse()
      ->getContent(), true);
    Assert::assertArrayNotHasKey('form', $answer);
  }

  /**
   * @Then My game should still be accepted
   */
  public function myGameShouldStillBeAccepted()
  {
    $program = $this->getProgramManger()->find(1);
    Assert::assertTrue($program->isAcceptedForGameJam());
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
    $answer = json_decode($this->getClient()
      ->getResponse()
      ->getContent(), true);
    Assert::assertNotEquals("200", $answer['statusCode']);
  }

  /**
   * @Then The message schould be:
   *
   * @param PyStringNode $string
   */
  public function theMessageSchouldBe(PyStringNode $string)
  {
    $answer = json_decode($this->getClient()
      ->getResponse()
      ->getContent(), true);
    Assert::assertEquals($string->getRaw(), $answer['answer']);
  }

  /**
   * @When I upload my game
   */
  public function iUploadMyGame()
  {
    $file = $this->getSymfonySupport()->getDefaultProgramFile();
    $this->getSymfonySupport()->upload($file, null, null);
  }

  /**
   * @Given The form url of the current jam is
   * @param PyStringNode $string
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function theFormUrlOfTheCurrentJamIs(PyStringNode $string)
  {
    $this->getSymfonySupport()->insertDefaultGamejam([
      "formurl" => $string->getRaw(),
    ]);
  }

  /**
   * @Given I am :arg1 with email :arg2
   * @param $arg1
   * @param $arg2
   */
  public function iAmWithEmail($arg1, $arg2)
  {
    $this->i = $this->insertUser([
      "name"  => $arg1,
      "email" => "$arg2",
    ]);
  }

  /**
   * @When I submit a game which gets the id :arg1
   * @param $arg1
   */
  public function iSubmitAGameWhichGetsTheId($arg1)
  {
    $file = $this->getSymfonySupport()->getDefaultProgramFile();
    $this->getSymfonySupport()->submit($file, $this->i);
  }

  /**
   * @Then The returned url with id :id should be
   * @param PyStringNode $string
   */
  public function theReturnedUrlShouldBe($id, PyStringNode $string)
  {
    $answer = (array) json_decode($this->getClient()->getResponse()->getContent());

    $form_url = $answer['form'];
    $form_url = preg_replace("/&id=.*?&mail=/", "&id=" . $id ."&mail=", $form_url, -1);

    Assert::assertEquals($string->getRaw(), $form_url);
  }

  /**
   * @Given There are follwing gamejam programs:
   * @param TableNode $table
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function thereAreFollwingGamejamPrograms(TableNode $table)
  {
    $programs = $table->getHash();
    for ($i = 0; $i < count($programs); ++$i)
    {
      @$gamejam = $programs[$i]['GameJam'];

      if ($gamejam == null)
      {
        $gamejam = $this->gamejam;
      }
      else
      {
        $gamejam = $this->getSymfonySupport()->getSymfonyService('gamejamrepository')->findOneByName($gamejam);
      }

      @$config = [
        'name'     => $programs[$i]['Name'],
        'gamejam'  => ($programs[$i]['Submitted'] == "yes") ? $gamejam : null,
        'accepted' => $programs[$i]['Accepted'] == "yes" ? true : false,
      ];
      $this->insertProgram(null, $config);
    }
  }

  /**
   * @Given There are following gamejams:
   *
   * @param TableNode $table
   *
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   * @throws \Exception
   */
  public function thereAreFollowingGamejams(TableNode $table)
  {
    $jams = $table->getHash();
    for ($i = 0; $i < count($jams); ++$i)
    {
      $config = ['name' => $jams[$i]['Name']];

      $start = $jams[$i]['Starts in'];
      if ($start != null)
      {
        $config['start'] = $this->getDateFromNow(intval($start));
      }
      $end = $jams[$i]['Ends in'];
      if ($end != null)
      {
        $config['end'] = $this->getDateFromNow(intval($end));
      }
      $this->getSymfonySupport()->insertDefaultGamejam($config);
      $this->insertProgram(null, $config);
    }
  }

  /**
   * @param $days
   *
   * @return \DateTime
   * @throws \Exception
   */
  private function getDateFromNow($days)
  {
    $date = new \DateTime();
    if ($days < 0)
    {
      $days = abs($days);
      $date->sub(new \DateInterval('P' . $days . 'D'));
    }
    else
    {
      $date->add(new \DateInterval('P' . $days . 'D'));
    }

    return $date;
  }

  /**
   * @When I GET :arg1
   * @param $arg1
   */
  public function iGet($arg1)
  {
    $this->getClient()->request('GET', $arg1);
  }

  /**
   * @Then I should receive the following programs:
   * @param TableNode $table
   */
  public function iShouldReceiveTheFollowingPrograms(TableNode $table)
  {
    $response = $this->getClient()->getResponse();
    $responseArray = json_decode($response->getContent(), true);
    $returned_programs = $responseArray['CatrobatProjects'];
    $expected_programs = $table->getHash();
    for ($i = 0; $i < count($returned_programs); ++$i)
    {
      Assert::assertEquals($expected_programs[$i]['Name'], $returned_programs[$i]['ProjectName'], 'Wrong order of results');
    }
    Assert::assertEquals(count($expected_programs), count($returned_programs), 'Wrong number of returned programs');
  }

  /**
   * @Then The total number of found projects should be :arg1
   * @param $arg1
   */
  public function theTotalNumberOfFoundProjectsShouldBe($arg1)
  {
    $response = $this->getClient()->getResponse();
    $responseArray = json_decode($response->getContent(), true);
    Assert::assertEquals($arg1, $responseArray['CatrobatInformation']['TotalProjects']);
  }

  /**
   * @Then I should receive my program
   */
  public function iShouldReceiveMyProgram()
  {
    $response = $this->getClient()->getResponse();
    $responseArray = json_decode($response->getContent(), true);
    $returned_programs = $responseArray['CatrobatProjects'];
    Assert::assertEquals("test", $returned_programs[0]['ProjectName'], 'Could not find the program');
  }

  /**
   * @Given I have a limited account
   */
  public function iHaveALimitedAccount()
  {
    $this->i = $this->getSymfonySupport()->insertUser(['limited' => true]);
  }

  /**
   * @When I update my program
   */
  public function iUpdateMyProgram()
  {
    $file = $this->getSymfonySupport()->getDefaultProgramFile();
    $this->getSymfonySupport()->upload($file, $this->i, null);
    $this->getSymfonySupport()->upload($file, $this->i, null);
  }

  /**
   * @Then A copy of this program will be stored on the server
   */
  public function aCopyOfThisProgramWillBeStoredOnTheServer()
  {
    $dir = $this->getSymfonyParameter("catrobat.snapshot.dir");
    $finder = new Finder();
    Assert::assertEquals(1, $finder->files()->in($dir)->count(), "Snapshot was not stored!");
  }

}
