<?php

namespace spec\AppBundle\Services;

use AppBundle\Services\TokenGenerator;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TokenGeneratorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('AppBundle\Services\TokenGenerator');
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
    
    function it_generates_a_token_with_a_length_of_32()
    {
      $generated_token = $this->generateToken();
      $generated_token->shouldHaveLength(32);
    }
    
    
    public function getMatchers()
    {
      return [
      'haveLength' => function($subject, $key) {
        return strlen($subject) === $key;
      }
      ];
    }
}
