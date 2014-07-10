<?php

namespace Catrobat\CoreBundle\Spec\Listeners;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class XmlFileValidatorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Catrobat\CoreBundle\Listeners\XmlFileValidator');
    }
}
