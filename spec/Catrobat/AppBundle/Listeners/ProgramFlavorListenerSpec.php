<?php

namespace spec\Catrobat\AppBundle\Listeners;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Catrobat\AppBundle\Entity\Program;

class ProgramFlavorListenerSpec extends ObjectBehavior
{
    /**
     * @param \Symfony\Component\HttpFoundation\RequestStack $stack
     */
    public function let($stack)
    {
        $this->beConstructedWith($stack);
    }
    
    public function it_is_initializable()
    {
        $this->shouldHaveType('Catrobat\AppBundle\Listeners\ProgramFlavorListener');
    }

  /**
   * @param \Symfony\Component\HttpFoundation\RequestStack $stack
   */
  public function it_sets_the_flavor_of_a_program_based_on_its_request_flavor($stack)
  {
      $program = new Program();
      $request = new Request();
      $request->attributes->set('flavor', 'pocketcode');
      $stack->getCurrentRequest()->willReturn($request);
      $this->checkFlavor($program);
      expect($program->getFlavor())->toBe('pocketcode');
      
      $request->attributes->set('flavor', 'pocketphiro');
      $stack->getCurrentRequest()->willReturn($request);
      $this->checkFlavor($program);
      expect($program->getFlavor())->toBe('pocketphiro');
  }

}
