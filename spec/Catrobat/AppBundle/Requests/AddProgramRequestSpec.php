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
}
