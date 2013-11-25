<?php

namespace Catrobat\CatrowebBundle\Spec\Services;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProjectFileRepositorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Catrobat\CatrowebBundle\Services\ProjectFileRepository');
    }
}
