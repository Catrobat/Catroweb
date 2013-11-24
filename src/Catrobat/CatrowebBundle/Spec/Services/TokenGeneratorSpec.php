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

    function it_generates_a_string()
    {
      $generated_token = $this->generateToken();
      $generated_token->shouldBeString();
    }
    
    function it_generates_a_different_token_each_time()
    {
      $generated_tokens = array();
      for ($i=0; $i<100; $i++)
      {
        $generated_token = $this->generateToken();
        $generated_tokens[] = $generated_token->getWrappedObject();
      }
      expect(count(array_unique($generated_tokens)))->toBe(100);
    }
}
