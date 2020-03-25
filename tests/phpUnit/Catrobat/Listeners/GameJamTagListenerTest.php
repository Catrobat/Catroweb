<?php

namespace Tests\phpUnit\Catrobat\Listeners;

use App\Catrobat\Listeners\GameJamTagListener;
use App\Entity\GameJam;
use App\Entity\Program;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class GameJamTagListenerTest extends TestCase
{
  private GameJamTagListener $game_jam_tag_listener;

  protected function setUp(): void
  {
    $this->game_jam_tag_listener = new GameJamTagListener();
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(GameJamTagListener::class, $this->game_jam_tag_listener);
  }

  public function testDoesNotAddTheHashtagToTheDescriptionIfHashtagExists(): void
  {
    $program = new Program();
    $gameJam = new GameJam();
    $gameJam->setHashtag('#AliceGameJam');

    $program->setGamejam($gameJam);

    $description = 'This is a sample description, best game EVER. #AliceGameJam';
    $program->setDescription($description);
    $this->game_jam_tag_listener->checkDescriptionTag($program);

    Assert::assertEquals($program->getDescription(), $description);
  }

  public function testDoesAddTheHashtagToTheDescriptionIfHashtagDoesNotExists(): void
  {
    $program = new Program();
    $gameJam = new GameJam();
    $gameJam->setHashtag('#AliceGameJam');

    $program->setGamejam($gameJam);

    $description = 'This is a sample description, best game EVER.';
    $program->setDescription($description);
    $this->game_jam_tag_listener->checkDescriptionTag($program);
    $expected = $description."\n\n#AliceGameJam";

    Assert::assertEquals($program->getDescription(), $expected);
  }

  public function testDoesNotAddTheHashtagIfNoGamejamExists(): void
  {
    $program = new Program();

    $description = 'This is a sample description, best game EVER.';
    $program->setDescription($description);
    $this->game_jam_tag_listener->checkDescriptionTag($program);

    Assert::assertEquals($program->getDescription(), $description);
  }

  public function testDoesNotAddTheHashtagIfGamejamHasNoHashtag(): void
  {
    $program = new Program();
    $gameJam = new GameJam();
    $program->setGamejam($gameJam);

    $description = 'This is a sample description, best game EVER.';
    $program->setDescription($description);
    $this->game_jam_tag_listener->checkDescriptionTag($program);

    Assert::assertEquals($program->getDescription(), $description);
  }

  public function testDoesNotAddTheHashtagIfTheHashtagIsAnEmptyString(): void
  {
    $program = new Program();
    $gameJam = new GameJam();
    $gameJam->setHashtag('');

    $program->setGamejam($gameJam);

    $description = 'This is a sample description, best game EVER.';
    $program->setDescription($description);
    $this->game_jam_tag_listener->checkDescriptionTag($program);

    Assert::assertEquals($program->getDescription(), $description);
  }
}
