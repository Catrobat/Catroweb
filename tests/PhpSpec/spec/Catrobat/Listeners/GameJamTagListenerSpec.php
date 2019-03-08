<?php

namespace tests\PhpSpec\spec\App\Catrobat\Listeners;

use App\Entity\GameJam;
use App\Entity\Program;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Filesystem\Filesystem;
use App\Catrobat\Services\ExtractedCatrobatFile;

class GameJamTagListenerSpec extends ObjectBehavior
{

  public function it_is_initializable()
  {
    $this->shouldHaveType('App\Catrobat\Listeners\GameJamTagListener');
  }

  public function it_does_not_add_the_hashtag_to_the_description_if_hashtag_exists()
  {
    $program = new Program();
    $gameJam = new GameJam();
    $gameJam->setHashtag('#AliceGameJam');
    $program->setGamejam($gameJam);

    $description = 'This is a sample description, best game EVER. #AliceGameJam';
    $program->setDescription($description);
    $this->checkDescriptionTag($program);
    expect($program->getDescription())->toBeLike($description);
  }

  public function it_does_add_the_hashtag_to_the_description_if_hashtag_does_not_exists()
  {
    $program = new Program();
    $gameJam = new GameJam();
    $gameJam->setHashtag('#AliceGameJam');
    $program->setGamejam($gameJam);

    $description = 'This is a sample description, best game EVER.';
    $program->setDescription($description);
    $this->checkDescriptionTag($program);
    $expected = $description . "\n\n#AliceGameJam";
    expect($program->getDescription())->toBeLike($expected);
  }

  public function it_does_not_add_the_hashtag_if_no_gamejam_exists()
  {
    $program = new Program();

    $description = 'This is a sample description, best game EVER.';
    $program->setDescription($description);
    $this->checkDescriptionTag($program);
    expect($program->getDescription())->toBeLike($description);
  }

  public function it_does_not_add_the_hashtag_if_gamejam_has_no_hashtag()
  {
    $program = new Program();
    $gameJam = new GameJam();
    $program->setGamejam($gameJam);

    $description = 'This is a sample description, best game EVER.';
    $program->setDescription($description);
    $this->checkDescriptionTag($program);
    expect($program->getDescription())->toBeLike($description);
  }

  public function it_does_not_add_the_hashtag_if_the_hashtag_is_an_empty_string()
  {
    $program = new Program();
    $gameJam = new GameJam();
    $gameJam->setHashtag("");
    $program->setGamejam($gameJam);

    $description = 'This is a sample description, best game EVER.';
    $program->setDescription($description);
    $this->checkDescriptionTag($program);
    expect($program->getDescription())->toBeLike($description);
  }

}
