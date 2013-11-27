<?php

namespace Catrobat\CatrowebBundle\Spec\Model\Requests;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AddProjectRequestSpec extends ObjectBehavior
{

  /**
   * @param \Catrobat\CatrowebBundle\Entity\User $user
   * @param \Symfony\Component\HttpFoundation\File\File $file
   */
  function let($user, $file)
  {
    $this->beConstructedWith($user, $file);
  }

  function it_is_initializable()
  {
    $this->shouldHaveType('Catrobat\CatrowebBundle\Model\Requests\AddProjectRequest');
  }

  /**
   * @param \Catrobat\CatrowebBundle\Entity\User $new_user
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
    $this->getProjectfile()->shouldReturn($file);
    $this->setProjectfile($new_file);
    $this->getProjectfile()->shouldReturn($new_file);
  }
  
}
