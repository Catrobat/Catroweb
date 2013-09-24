<?php

namespace Catrobat\CatrowebBundle\Spec\Helper;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProjectDirectoryValidatorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Catrobat\CatrowebBundle\Helper\ProjectDirectoryValidator');
    }
}
