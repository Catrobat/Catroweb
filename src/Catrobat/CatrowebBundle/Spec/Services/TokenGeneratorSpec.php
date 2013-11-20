<?php

namespace Catrobat\CatrowebBundle\Spec\Services;

use Catrobat\CatrowebBundle\Services\TokenGenerator;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TokenGeneratorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Catrobat\CatrowebBundle\Services\TokenGenerator');
    }
    
    function it_generates_a_different_token_each_time()
    {

      $test = $this->generateToken();

    }
}
