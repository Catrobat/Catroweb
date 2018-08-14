<?php

namespace spec\Catrobat\AppBundle\Services;

use PhpSpec\ObjectBehavior;

class TokenGeneratorSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('Catrobat\AppBundle\Services\TokenGenerator');
    }

    public function it_generates_a_string()
    {
        $generated_token = $this->generateToken();
        $generated_token->shouldBeString();
    }

    public function it_generates_a_different_token_each_time()
    {
        $generated_tokens = array();
        for ($i = 0; $i < 100; ++$i) {
            $generated_token = $this->generateToken();
            $generated_tokens[] = $generated_token->getWrappedObject();
        }
        expect(count(array_unique($generated_tokens)))->toBe(100);
    }

    public function it_generates_a_token_with_a_length_of_32()
    {
        $generated_token = $this->generateToken();
        $generated_token->shouldHaveLength(32);
    }

    public function getMatchers() : array
    {
        return [
      'haveLength' => function ($subject, $key) {
        return strlen($subject) === $key;
      },
      ];
    }
}
