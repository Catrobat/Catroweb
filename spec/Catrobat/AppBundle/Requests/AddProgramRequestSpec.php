<?php

namespace spec\Catrobat\AppBundle\Requests;

use PhpSpec\ObjectBehavior;
use Catrobat\AppBundle\Entity\User;
use Symfony\Component\HttpFoundation\File\File;

class AddProgramRequestSpec extends ObjectBehavior
{
  public function let(User $user, File $file)
  {
      $this->beConstructedWith($user, $file);
  }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Catrobat\AppBundle\Requests\AddProgramRequest');
    }


  public function it_holds_a_user(User $user, User $new_user)
  {
      $this->getUser()->shouldReturn($user);
      $this->setUser($new_user);
      $this->getUser()->shouldReturn($new_user);
  }

  public function it_holds_a_file(File $file, File $new_file)
  {
      $this->getProgramfile()->shouldReturn($file);
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
