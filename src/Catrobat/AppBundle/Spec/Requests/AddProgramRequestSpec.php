<?php

namespace Catrobat\AppBundle\Spec\Requests;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AddProgramRequestSpec extends ObjectBehavior
{

  /**
   * @param \Catrobat\AppBundle\Entity\User $user
   * @param \Symfony\Component\HttpFoundation\File\File $file
   */
  function let($user, $file)
  {
    $this->beConstructedWith($user, $file);
  }

  function it_is_initializable()
  {
    $this->shouldHaveType('Catrobat\AppBundle\Requests\AddProgramRequest');
  }

  /**
   * @param \Catrobat\AppBundle\Entity\User $new_user
   */
  function it_holds_a_user($user,$new_user)
  {
    $this->getUser()->shouldReturn($user);
    $this->setUser($new_user);
    $this->getUser()->shouldReturn($new_user);
  }

  /**
   * @param \Symfony\Component\HttpFoundation\File\File $new_file
   */
  function it_holds_a_file($file,$new_file)
  {
    $this->getProgramfile()->shouldReturn($file);
    $this->setProgramfile($new_file);
    $this->getProgramfile()->shouldReturn($new_file);
  }
  
}
