<?php

namespace Catrobat\CoreBundle\Spec\Model;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProjectManagerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Catrobat\CoreBundle\Model\ProjectManager');
    }
}
