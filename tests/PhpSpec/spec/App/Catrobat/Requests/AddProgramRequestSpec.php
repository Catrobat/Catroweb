<?php

namespace tests\PhpSpec\spec\App\Catrobat\Requests;

use PhpSpec\ObjectBehavior;
use App\Entity\User;
use Symfony\Component\HttpFoundation\File\File;

class AddProgramRequestSpec extends ObjectBehavior
{
  private $file;

  public function let(User $user)
  {
    fopen('/tmp/phpSpecTest', 'w');
    $this->file = new File('/tmp/phpSpecTest');
    $this->beConstructedWith($user, $this->file);
  }

  public function it_is_initializable()
  {
    $this->shouldHaveType('App\Catrobat\Requests\AddProgramRequest');
  }


  public function it_holds_a_user(User $user, User $new_user)
  {
    $this->getUser()->shouldReturn($user);
    $this->setUser($new_user);
    $this->getUser()->shouldReturn($new_user);
  }

  public function it_holds_a_file()
  {
    $new_file = $this->file;

    $this->getProgramfile()->shouldReturn($this->file);
    $this->setProgramfile($new_file);
    $this->getProgramfile()->shouldReturn($new_file);
  }

  public function it_holds_an_ip()
  {
    $this->getIp()->shouldReturn("127.0.0.1");
  }

  public function it_is_not_a_gamejam()
  {
    $this->getGamejam()->shouldReturn(null);
  }

  public function it_has_a_language()
  {
    $this->getLanguage()->shouldReturn(null);
    $this->setLanguage('de');
    $this->getLanguage()->shouldReturn('de');
  }

  public function it_has_a_flavor()
  {
    $this->getFlavor()->shouldReturn('pocketcode');
  }
}
